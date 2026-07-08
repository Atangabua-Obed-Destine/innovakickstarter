<?php

namespace App\Services;

use App\Enums\BadgeType;
use App\Enums\CurriculumStatus;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\Track;
use App\Models\User;
use App\Repositories\Contracts\CurriculumRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Streak Service
 * 
 * Manages weekly streak tracking and multiplier calculations.
 * A streak represents consecutive weeks where a fellow completes
 * curriculum activities across all required pillars.
 * 
 * Streak Tiers:
 * - Building (0-1 weeks): 1.0x multiplier
 * - On Fire 🔥 (2-3 weeks): 1.1x multiplier
 * - Unstoppable ⚡ (4-7 weeks): 1.25x multiplier
 * - Diamond 💎 (8+ weeks): 1.5x multiplier
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class StreakService
{
    public function __construct(
        protected CurriculumRepositoryInterface $curriculumRepository
    ) {}

    /**
     * Get or create a streak record for a fellow in a track.
     */
    public function getOrCreateStreak(User $fellow, ?Track $track): ?FellowStreak
    {
        if (!$track) {
            return null;
        }
        return $this->curriculumRepository->getOrCreateStreak($fellow, $track);
    }

    /**
     * Record a curriculum activity completion and check streak status.
     */
    public function recordCompletion(User $fellow, Track $track): void
    {
        $streak = $this->getOrCreateStreak($fellow, $track);
        if (!$streak) {
            return;
        }

        // Check if the fellow has completed at least one activity this week
        $thisWeekCompletions = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            })
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        // Update last active week
        $streak->update(['last_activity_at' => now()]);

        Log::info("Streak activity recorded", [
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'current_streak' => $streak->current_streak,
            'this_week_completions' => $thisWeekCompletions,
        ]);
    }

    /**
     * Evaluate all streaks at the end of the week (scheduled job).
     * Increments streaks for active fellows, breaks inactive ones.
     */
    public function evaluateWeeklyStreaks(): array
    {
        $results = [
            'incremented' => 0,
            'broken' => 0,
            'badges_awarded' => 0,
        ];

        $allStreaks = FellowStreak::where('is_active', true)->with(['fellow', 'track'])->get();

        foreach ($allStreaks as $streak) {
            $weeklyCompletions = FellowCurriculumProgress::where('fellow_id', $streak->fellow_id)
                ->where('status', CurriculumStatus::COMPLETED->value)
                ->whereHas('curriculumActivity', function ($q) use ($streak) {
                    $q->where('track_id', $streak->track_id);
                })
                ->where('completed_at', '>=', now()->subWeek()->startOfWeek())
                ->where('completed_at', '<', now()->startOfWeek())
                ->count();

            if ($weeklyCompletions > 0) {
                $previousTier = $streak->streak_tier;
                $streak->incrementStreak();
                $results['incremented']++;

                // Check if tier changed — award badge
                $newTier = $streak->fresh()->streak_tier;
                if ($newTier !== $previousTier && $newTier !== 'Building') {
                    $this->awardStreakBadge($streak->fellow, $streak->fresh());
                    $results['badges_awarded']++;
                }
            } else {
                $streak->breakStreak();
                $results['broken']++;
            }
        }

        Log::info("Weekly streak evaluation complete", $results);

        return $results;
    }

    /**
     * Award a streak tier badge.
     */
    protected function awardStreakBadge(User $fellow, FellowStreak $streak): void
    {
        // Check if they already have this tier badge
        if ($this->curriculumRepository->hasBadge(
            $fellow,
            BadgeType::STREAK->value,
            $streak->track_id
        )) {
            // Update existing badge metadata
            $existingBadge = FellowBadge::where('fellow_id', $fellow->id)
                ->where('badge_type', BadgeType::STREAK->value)
                ->where('track_id', $streak->track_id)
                ->first();

            if ($existingBadge) {
                $existingBadge->update([
                    'badge_name' => "{$streak->streak_tier} Streak",
                    'badge_icon' => $streak->streak_tier_icon,
                    'badge_description' => "{$streak->current_streak} consecutive weeks",
                    'metadata' => array_merge($existingBadge->metadata ?? [], [
                        'streak_count' => $streak->current_streak,
                        'updated_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }
        } else {
            FellowBadge::createStreakBadge($fellow, $streak);
        }
    }

    /**
     * Get streak multiplier for a fellow in a track.
     */
    public function getMultiplier(User $fellow, Track $track): float
    {
        $streak = FellowStreak::where('fellow_id', $fellow->id)
            ->where('track_id', $track->id)
            ->first();

        return $streak ? $streak->calculateMultiplier() : 1.0;
    }

    /**
     * Get streak leaderboard for a track.
     */
    public function getLeaderboard(Track $track, int $limit = 10)
    {
        return $this->curriculumRepository->getStreakLeaderboard($track, $limit);
    }

    /**
     * Get streak summary for display.
     */
    public function getStreakSummary(User $fellow, Track $track): array
    {
        $streak = $this->getOrCreateStreak($fellow, $track);

        if (!$streak) {
            return [
                'current' => 0,
                'longest' => 0,
                'multiplier' => 1.0,
                'tier' => 'Building',
                'tier_icon' => '🌱',
                'weeks_to_next' => 2,
            ];
        }

        return [
            'current' => $streak->current_streak,
            'longest' => $streak->longest_streak,
            'multiplier' => $streak->calculateMultiplier(),
            'tier' => $streak->streak_tier,
            'tier_icon' => $streak->streak_tier_icon,
            'weeks_to_next' => $streak->weeksToNextTier(),
        ];
    }
}
