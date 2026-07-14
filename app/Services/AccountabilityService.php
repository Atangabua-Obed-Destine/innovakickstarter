<?php

namespace App\Services;

use App\Models\AccountabilityPair;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Accountability Service
 * 
 * Manages accountability partner pairing for peer review.
 * Provides auto-pairing algorithms and partner management.
 * 
 * Pairing Logic:
 * - Fellows in the same track are paired together
 * - Optionally within the same cohort and milestone
 * - Pairs rotate periodically or upon milestone changes
 * - Unpaired fellows get paired with the most available partner
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AccountabilityService
{
    /**
     * Auto-pair fellows within a track.
     * Pairs unpaired active fellows within the same track.
     */
    public function autoPairTrack(Track $track, ?string $cohortId = null, ?string $milestoneId = null): int
    {
        $pairedCount = 0;

        DB::transaction(function () use ($track, $cohortId, $milestoneId, &$pairedCount) {
            // Get enrolled fellows who don't have an active pair for this track
            $enrolledFellowIds = FellowTrack::where('track_id', $track->id)
                ->pluck('fellow_id');

            $alreadyPairedIds = AccountabilityPair::where('track_id', $track->id)
                ->where('is_active', true)
                ->get()
                ->flatMap(function ($pair) {
                    return [$pair->fellow_a_id, $pair->fellow_b_id];
                })
                ->unique();

            $unpairedIds = $enrolledFellowIds->diff($alreadyPairedIds)->values();

            if ($unpairedIds->count() < 2) {
                return;
            }

            // Group unpaired fellows by their mentorship pod
            $podMemberships = \App\Models\MentorshipPodMember::whereIn('fellow_id', $unpairedIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('fellow_id');

            $podGroups = [];
            $noPodGroup = [];

            foreach ($unpairedIds as $fellowId) {
                if ($podMemberships->has($fellowId)) {
                    $podId = $podMemberships->get($fellowId)->pod_id;
                    $podGroups[$podId][] = $fellowId;
                } else {
                    $noPodGroup[] = $fellowId;
                }
            }

            $leftovers = [];

            // Pair fellows within their pods
            foreach ($podGroups as $podId => $fellowsInPod) {
                $shuffledPod = collect($fellowsInPod)->shuffle();
                
                // Pair sequentially inside the pod
                for ($i = 0; $i + 1 < $shuffledPod->count(); $i += 2) {
                    AccountabilityPair::create([
                        'fellow_a_id' => $shuffledPod[$i],
                        'fellow_b_id' => $shuffledPod[$i + 1],
                        'track_id' => $track->id,
                        'cohort_id' => $cohortId,
                        'milestone_id' => $milestoneId,
                        'paired_at' => now(),
                        'is_active' => true,
                    ]);
                    $pairedCount++;
                }

                // If odd number, add the last one to leftovers
                if ($shuffledPod->count() % 2 !== 0) {
                    $leftovers[] = $shuffledPod->last();
                }
            }

            // Combine leftovers and no-pod fellows
            $globalPool = collect(array_merge($leftovers, $noPodGroup))->shuffle();

            // Pair the global pool sequentially
            for ($i = 0; $i + 1 < $globalPool->count(); $i += 2) {
                AccountabilityPair::create([
                    'fellow_a_id' => $globalPool[$i],
                    'fellow_b_id' => $globalPool[$i + 1],
                    'track_id' => $track->id,
                    'cohort_id' => $cohortId,
                    'milestone_id' => $milestoneId,
                    'paired_at' => now(),
                    'is_active' => true,
                ]);
                $pairedCount++;
            }

            Log::info("Auto-paired fellows in track", [
                'track_id' => $track->id,
                'pairs_created' => $pairedCount,
                'unpaired_remaining' => $globalPool->count() % 2,
            ]);
        });

        return $pairedCount;
    }

    /**
     * Get accountability partner for a fellow in a track.
     */
    public function getPartner(User $fellow, Track $track): ?User
    {
        $pair = AccountabilityPair::where('track_id', $track->id)
            ->where('is_active', true)
            ->where(function ($q) use ($fellow) {
                $q->where('fellow_a_id', $fellow->id)
                    ->orWhere('fellow_b_id', $fellow->id);
            })
            ->first();

        return $pair?->getPartner($fellow);
    }

    /**
     * Get all active pairs for a track.
     */
    public function getActivePairs(Track $track): Collection
    {
        return AccountabilityPair::where('track_id', $track->id)
            ->where('is_active', true)
            ->with(['fellowA', 'fellowB'])
            ->orderBy('paired_at', 'desc')
            ->get();
    }

    /**
     * Manually pair two fellows.
     */
    public function createPair(
        User $fellowA,
        User $fellowB,
        Track $track,
        ?string $cohortId = null,
        ?string $milestoneId = null
    ): AccountabilityPair {
        // Deactivate any existing pairs for these fellows in this track
        $this->deactivatePairsForFellow($fellowA, $track);
        $this->deactivatePairsForFellow($fellowB, $track);

        return AccountabilityPair::create([
            'fellow_a_id' => $fellowA->id,
            'fellow_b_id' => $fellowB->id,
            'track_id' => $track->id,
            'cohort_id' => $cohortId,
            'milestone_id' => $milestoneId,
            'paired_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Deactivate all pairs for a fellow in a track.
     */
    public function deactivatePairsForFellow(User $fellow, Track $track): int
    {
        return AccountabilityPair::where('track_id', $track->id)
            ->where('is_active', true)
            ->where(function ($q) use ($fellow) {
                $q->where('fellow_a_id', $fellow->id)
                    ->orWhere('fellow_b_id', $fellow->id);
            })
            ->update(['is_active' => false]);
    }

    /**
     * Rotate pairs for a track (deactivate old, create new).
     */
    public function rotatePairs(Track $track, ?string $cohortId = null): int
    {
        DB::transaction(function () use ($track) {
            // Deactivate all current pairs
            AccountabilityPair::where('track_id', $track->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        });

        // Re-pair with new random assignments
        return $this->autoPairTrack($track, $cohortId);
    }

    /**
     * Get pair statistics for a track.
     */
    public function getPairStats(Track $track): array
    {
        $activePairs = AccountabilityPair::where('track_id', $track->id)
            ->where('is_active', true)
            ->count();

        $totalReviews = AccountabilityPair::where('track_id', $track->id)
            ->sum('reviews_completed');

        $enrolledFellows = FellowTrack::where('track_id', $track->id)->count();

        $pairedFellows = AccountabilityPair::where('track_id', $track->id)
            ->where('is_active', true)
            ->get()
            ->flatMap(fn ($pair) => [$pair->fellow_a_id, $pair->fellow_b_id])
            ->unique()
            ->count();

        return [
            'active_pairs' => $activePairs,
            'total_reviews' => $totalReviews,
            'enrolled_fellows' => $enrolledFellows,
            'paired_fellows' => $pairedFellows,
            'unpaired_fellows' => $enrolledFellows - $pairedFellows,
            'pairing_rate' => $enrolledFellows > 0 ? round(($pairedFellows / $enrolledFellows) * 100, 1) : 0,
        ];
    }
}
