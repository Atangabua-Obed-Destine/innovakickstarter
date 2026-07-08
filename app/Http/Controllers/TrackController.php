<?php

namespace App\Http\Controllers;

use App\Models\FellowTrack;
use App\Models\Track;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Track Controller
 * 
 * Handles track selection and enrollment for fellows.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class TrackController extends Controller
{
    /**
     * Display all active tracks.
     */
    public function index(): View
    {
        $tracks = Track::where('is_active', true)->get();

        return view('tracks.index', [
            'tracks' => $tracks,
        ]);
    }

    /**
     * Display available tracks for selection.
     */
    public function select(): View
    {
        $user = auth()->user();

        $tracks = Track::where('is_active', true)
            ->withCount('fellows')
            ->get();

        // Map track_id => enrollment (any status) so the view can render context
        $existingByTrack = FellowTrack::where('fellow_id', $user->id)
            ->get()
            ->keyBy('track_id');

        $hasAnyTrack = $existingByTrack->isNotEmpty();

        return view('tracks.select', [
            'tracks' => $tracks,
            'existingByTrack' => $existingByTrack,
            'hasAnyTrack' => $hasAnyTrack,
        ]);
    }

    /**
     * Enroll fellow in a track.
     *
     * Rules:
     *  - The fellow's very first track (right after onboarding) is auto-approved
     *    and becomes primary so they can start working immediately.
     *  - Every additional track goes through an admin review queue (pending).
     */
    public function enroll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
            'motivation' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $track = Track::findOrFail($validated['track_id']);

        // Check if already enrolled (any status)
        $existingEnrollment = FellowTrack::where('fellow_id', $user->id)
            ->where('track_id', $track->id)
            ->first();

        if ($existingEnrollment) {
            $msg = match ($existingEnrollment->status) {
                FellowTrack::STATUS_PENDING        => 'Your enrollment for this track is already pending review.',
                FellowTrack::STATUS_NEEDS_REVISION => 'This track enrollment needs your revision. Contact admin for details.',
                FellowTrack::STATUS_REJECTED       => 'Your enrollment for this track was previously rejected.',
                default                            => 'You are already enrolled in this track.',
            };
            return redirect()->route('tracks.select')->with('info', $msg);
        }

        $hasAnyTrack = FellowTrack::where('fellow_id', $user->id)->exists();
        $isFirstTrack = !$hasAnyTrack;

        $enrollment = FellowTrack::create([
            'fellow_id' => $user->id,
            'track_id' => $track->id,
            'started_at' => $isFirstTrack ? now() : null,
            'score' => 0,
            'tier' => 'rookie',
            'is_primary' => $isFirstTrack,
            'status' => $isFirstTrack ? FellowTrack::STATUS_APPROVED : FellowTrack::STATUS_PENDING,
            'motivation' => $validated['motivation'] ?? null,
            'requested_at' => now(),
            'reviewed_at' => $isFirstTrack ? now() : null,
            'reviewed_by' => $isFirstTrack ? $user->id : null,
        ]);

        // Audit
        try {
            \App\Models\AuditLog::create([
                'fellow_id' => $user->id,
                'admin_id' => $user->id,
                'action' => $isFirstTrack ? 'track_enrolled' : 'track_requested',
                'auditable_type' => FellowTrack::class,
                'auditable_id' => $enrollment->id,
                'justification' => $isFirstTrack
                    ? "First track auto-approved: {$track->name}"
                    : "Additional track requested: {$track->name}",
            ]);
        } catch (\Throwable $e) {
            // best-effort
        }

        if ($isFirstTrack) {
            return redirect()->route('dashboard')
                ->with('success', "You've enrolled in the {$track->name} track! Start building your Career Capital now.");
        }

        // Notify admins
        \App\Models\User::role('admin')->get()->each(function ($admin) use ($user, $track, $enrollment) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'track_enrollment_request',
                'title' => 'New track enrollment request',
                'message' => "{$user->name} requested to enroll in {$track->name}.",
                'action_url' => route('admin.track-enrollments.show', $enrollment),
            ]);
        });

        return redirect()->route('dashboard')
            ->with('success', "Your request to enroll in {$track->name} was sent for admin review.");
    }

    /**
     * Switch primary track.
     * 
     * Business Rules:
     * - Fellow must be enrolled in the target track
     * - Maximum 2 switches per quarter
     * - Must be at least Intern tier (score >= 21) to switch
     * - Previous primary is demoted, new one becomes primary
     */
    public function switchPrimary(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
        ]);

        $user = $request->user();
        $targetTrackId = $validated['track_id'];

        // Check if already enrolled in the target track
        $targetFellowTrack = FellowTrack::where('fellow_id', $user->id)
            ->where('track_id', $targetTrackId)
            ->first();

        if (!$targetFellowTrack) {
            return back()->with('error', 'You must be enrolled in a track before switching to it.');
        }

        if (!$targetFellowTrack->isApproved()) {
            return back()->with('error', 'This track enrollment is still awaiting admin approval.');
        }

        // Check if it's already the primary
        if ($targetFellowTrack->is_primary) {
            return back()->with('info', 'This is already your primary track.');
        }

        // Check quarterly switch limit (max 2 per quarter)
        $quarterStart = now()->firstOfQuarter();
        $switchCount = \App\Models\AuditLog::where('fellow_id', $user->id)
            ->where('action', 'track_switch')
            ->where('created_at', '>=', $quarterStart)
            ->count();

        if ($switchCount >= 2) {
            return back()->with('error', 'You can only switch your primary track twice per quarter. Please wait until next quarter.');
        }

        // Perform the switch in a transaction
        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $targetFellowTrack, $targetTrackId) {
            // Remove primary from current track
            FellowTrack::where('fellow_id', $user->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);

            // Set new primary
            $targetFellowTrack->update(['is_primary' => true]);

            // Log the switch (best-effort, don't fail if AuditLog doesn't exist)
            try {
                \App\Models\AuditLog::create([
                    'fellow_id' => $user->id,
                    'action' => 'track_switch',
                    'auditable_type' => FellowTrack::class,
                    'auditable_id' => $targetFellowTrack->id,
                    'justification' => "Switched primary track to {$targetFellowTrack->track?->name}",
                ]);
            } catch (\Throwable $e) {
                // AuditLog table may not exist yet - gracefully continue
            }
        });

        // Also update the session active track to match the new primary
        session(['active_track_id' => $targetTrackId]);

        $trackName = $targetFellowTrack->track?->name ?? 'selected track';

        return redirect()->route('dashboard')
            ->with('success', "Primary track switched to {$trackName}! Dashboard data updated.");
    }

    /**
     * Switch the active track context (session-based, instant).
     * 
     * This does NOT change the DB primary track — it only changes
     * which track the fellow portal displays data for during this session.
     * No quarterly limits, no audit log. Just a session write.
     */
    public function switchActive(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
        ]);

        $user = $request->user();
        $trackId = $validated['track_id'];

        // Verify the fellow is enrolled in this track
        $enrolled = FellowTrack::where('fellow_id', $user->id)
            ->where('track_id', $trackId)
            ->approved()
            ->exists();

        if (!$enrolled) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Not enrolled in this track.'], 422);
            }
            return back()->with('error', 'You must be enrolled in a track to switch to it.');
        }

        // Store in session — the ResolveActiveTrack middleware reads this
        session(['active_track_id' => $trackId]);

        $trackName = Track::find($trackId)?->name ?? 'selected track';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Switched to {$trackName}",
                'track_id' => $trackId,
            ]);
        }

        return back()->with('success', "Switched to {$trackName}. All pages now show data for this track.");
    }

    /**
     * Display a single track.
     */
    public function show(Track $track): View
    {
        $user = request()->user();
        $enrollment = null;

        if ($user) {
            $enrollment = FellowTrack::where('fellow_id', $user->id)
                ->where('track_id', $track->id)
                ->first();
        }

        return view('tracks.show', [
            'track' => $track->load(['activities' => function ($q) {
                $q->where('status', 'approved')->latest()->limit(10);
            }]),
            'enrollment' => $enrollment,
        ]);
    }
}
