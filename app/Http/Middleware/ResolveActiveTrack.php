<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve Active Track Middleware
 * 
 * For fellow users, resolves the session-based "active track" and shares
 * it globally with all views. This powers the global track switcher —
 * when a fellow switches tracks in the header, every page instantly
 * reflects data for the selected track.
 * 
 * Shared variables:
 * - $activeTrack      (FellowTrack|null)  — the currently active track
 * - $fellowTracks     (Collection)         — all enrolled tracks with health metadata
 * - $trackSwitcherMeta (array)            — cross-track achievements, nudges, etc.
 * 
 * @author IKS Engineering Team
 * @version 1.1 — Added health pulse, cross-track achievements, smart nudges
 */
class ResolveActiveTrack
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('fellow')) {
            // Resolve the active track (session-first, then DB primary)
            $activeTrack = $user->activeTrack();

            // Load all approved enrolled tracks for the switcher dropdown.
            // Pending / rejected enrollments never show up in track-scoped views.
            $fellowTracks = $user->fellowTracks()
                ->approved()
                ->with('track')
                ->orderByDesc('is_primary')
                ->orderByDesc('score')
                ->get();

            // ── Health Pulse & Smart Nudges ──
            // For each track, compute last activity date and health status
            $trackIds = $fellowTracks->pluck('track_id')->toArray();
            $lastActivities = Activity::where('fellow_id', $user->id)
                ->whereIn('track_id', $trackIds)
                ->selectRaw('track_id, MAX(created_at) as last_activity_at, COUNT(*) as total_activities')
                ->groupBy('track_id')
                ->pluck('last_activity_at', 'track_id')
                ->map(fn($date) => \Carbon\Carbon::parse($date));

            $activityCounts = Activity::where('fellow_id', $user->id)
                ->whereIn('track_id', $trackIds)
                ->selectRaw('track_id, COUNT(*) as total')
                ->groupBy('track_id')
                ->pluck('total', 'track_id');

            // Enrich each track with health metadata
            foreach ($fellowTracks as $ft) {
                $lastActive = $lastActivities->get($ft->track_id);
                $daysSince = $lastActive ? (int) $lastActive->diffInDays(now()) : null;

                // Health pulse: green (≤7 days), yellow (8-14), red (15+), gray (never)
                $ft->health_status = match (true) {
                    $daysSince === null => 'dormant',
                    $daysSince <= 7    => 'active',
                    $daysSince <= 14   => 'cooling',
                    default            => 'dormant',
                };
                $ft->days_since_activity = $daysSince;
                $ft->last_activity_at = $lastActive;
                $ft->activity_count = $activityCounts->get($ft->track_id, 0);

                // Smart nudge message for dormant tracks
                $ft->nudge = null;
                if ($daysSince !== null && $daysSince > 14) {
                    $ft->nudge = "Inactive for {$daysSince} days — your peers are pulling ahead!";
                } elseif ($daysSince === null && $ft->score < 5) {
                    $ft->nudge = "You haven't started yet — log your first activity!";
                }
            }

            // ── Cross-Track Achievements ──
            $meta = $this->computeTrackMeta($fellowTracks);

            // Share globally with all views
            View::share('activeTrack', $activeTrack);
            View::share('fellowTracks', $fellowTracks);
            View::share('trackSwitcherMeta', $meta);

            // Also attach to the request for controller access
            $request->attributes->set('activeTrack', $activeTrack);
            $request->attributes->set('fellowTracks', $fellowTracks);
            $request->attributes->set('trackSwitcherMeta', $meta);
        }

        return $next($request);
    }

    /**
     * Compute cross-track achievement badges and meta.
     */
    protected function computeTrackMeta($fellowTracks): array
    {
        $meta = [
            'achievements' => [],
            'totalScore' => 0,
            'averageScore' => 0,
            'strongestTrack' => null,
            'weakestTrack' => null,
        ];

        if ($fellowTracks->isEmpty()) {
            return $meta;
        }

        $meta['totalScore'] = round($fellowTracks->sum('score'), 1);
        $meta['averageScore'] = round($fellowTracks->avg('score'), 1);

        $strongest = $fellowTracks->sortByDesc('score')->first();
        $weakest = $fellowTracks->sortBy('score')->first();
        $meta['strongestTrack'] = $strongest?->track?->name;
        $meta['weakestTrack'] = $weakest?->track?->name;

        // Renaissance Fellow: 3+ tracks at Intern tier or above (score >= 21)
        $advancedTracks = $fellowTracks->filter(fn($ft) => $ft->score >= 21)->count();
        if ($advancedTracks >= 3) {
            $meta['achievements'][] = [
                'key' => 'renaissance',
                'name' => 'Renaissance Fellow',
                'icon' => '👑',
                'description' => "Intern+ tier across {$advancedTracks} tracks",
                'color' => 'amber',
            ];
        }

        // Specialist: Any track at Professional tier (score >= 41)
        $proTracks = $fellowTracks->filter(fn($ft) => $ft->score >= 41);
        if ($proTracks->count() > 0) {
            $meta['achievements'][] = [
                'key' => 'specialist',
                'name' => 'Specialist',
                'icon' => '⚡',
                'description' => "Professional tier in {$proTracks->first()->track?->name}",
                'color' => 'purple',
            ];
        }

        // Elite Status: Any track at Elite tier (score >= 61)
        $eliteTracks = $fellowTracks->filter(fn($ft) => $ft->score >= 61);
        if ($eliteTracks->count() > 0) {
            $meta['achievements'][] = [
                'key' => 'elite',
                'name' => 'Elite Performer',
                'icon' => '🏆',
                'description' => "Elite tier in {$eliteTracks->first()->track?->name}",
                'color' => 'amber',
            ];
        }

        // Consistent: All tracks active within 7 days
        $allActive = $fellowTracks->every(fn($ft) => $ft->health_status === 'active');
        if ($allActive && $fellowTracks->count() >= 2) {
            $meta['achievements'][] = [
                'key' => 'consistent',
                'name' => 'Consistent Performer',
                'icon' => '🔥',
                'description' => "All {$fellowTracks->count()} tracks active this week",
                'color' => 'orange',
            ];
        }

        // Multi-Track Pioneer: Enrolled in 3+ tracks
        if ($fellowTracks->count() >= 3) {
            $meta['achievements'][] = [
                'key' => 'pioneer',
                'name' => 'Multi-Track Pioneer',
                'icon' => '🚀',
                'description' => "Exploring {$fellowTracks->count()} career tracks",
                'color' => 'blue',
            ];
        }

        return $meta;
    }
}
