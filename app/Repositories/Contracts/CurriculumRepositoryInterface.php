<?php

namespace App\Repositories\Contracts;

use App\Enums\CurriculumStatus;
use App\Enums\DifficultyLevel;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Curriculum Repository Interface
 * 
 * Defines specialized methods for curriculum system data access.
 * Covers milestones, curriculum activities, fellow progress,
 * streaks, badges, and accountability pairs.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface CurriculumRepositoryInterface extends RepositoryInterface
{
    // ==========================================
    // MILESTONE OPERATIONS
    // ==========================================

    /**
     * Get all milestones for a track, ordered.
     */
    public function getMilestonesForTrack(Track $track): Collection;

    /**
     * Get a milestone with its activities.
     */
    public function getMilestoneWithActivities(string $milestoneId): TrackMilestone;

    /**
     * Create a new milestone for a track.
     */
    public function createMilestone(Track $track, array $data): TrackMilestone;

    /**
     * Update a milestone.
     */
    public function updateMilestone(string $milestoneId, array $data): TrackMilestone;

    /**
     * Delete a milestone (soft or hard).
     */
    public function deleteMilestone(string $milestoneId): bool;

    /**
     * Reorder milestones for a track.
     */
    public function reorderMilestones(Track $track, array $orderedIds): void;

    // ==========================================
    // CURRICULUM ACTIVITY OPERATIONS
    // ==========================================

    /**
     * Get all curriculum activities for a milestone.
     */
    public function getActivitiesForMilestone(string $milestoneId): Collection;

    /**
     * Get all curriculum activities for a track.
     */
    public function getActivitiesForTrack(Track $track, array $filters = []): Collection;

    /**
     * Create a curriculum activity.
     */
    public function createActivity(array $data): TrackCurriculumActivity;

    /**
     * Update a curriculum activity.
     */
    public function updateActivity(string $activityId, array $data): TrackCurriculumActivity;

    /**
     * Delete a curriculum activity.
     */
    public function deleteActivity(string $activityId): bool;

    /**
     * Reorder activities within a milestone.
     */
    public function reorderActivities(string $milestoneId, array $orderedIds): void;

    /**
     * Get activities by difficulty level.
     */
    public function getActivitiesByDifficulty(DifficultyLevel $level, ?Track $track = null): Collection;

    // ==========================================
    // FELLOW PROGRESS OPERATIONS
    // ==========================================

    /**
     * Get fellow's progress for a track.
     */
    public function getFellowProgress(User $fellow, Track $track): Collection;

    /**
     * Get fellow's progress for a specific milestone.
     */
    public function getFellowMilestoneProgress(User $fellow, string $milestoneId): Collection;

    /**
     * Get or create a progress record for a specific activity.
     */
    public function getOrCreateProgress(User $fellow, TrackCurriculumActivity $activity): FellowCurriculumProgress;

    /**
     * Get progress records pending review (for admin/mentor).
     */
    public function getReviews(int $perPage = 15, ?Track $track = null, string $statusFilter = 'pending'): LengthAwarePaginator;

    /**
     * Get progress records pending peer review.
     */
    public function getPendingPeerReviews(User $fellow, ?Track $track = null): Collection;

    /**
     * Get overdue progress records.
     */
    public function getOverdueProgress(?Track $track = null): Collection;

    /**
     * Get progress statistics for a fellow in a track.
     */
    public function getFellowCurriculumStats(User $fellow, Track $track): array;

    /**
     * Get progress records by status.
     */
    public function getProgressByStatus(CurriculumStatus $status, ?Track $track = null): Collection;

    /**
     * Get recently completed activities across all fellows (for feed).
     */
    public function getRecentCompletions(int $limit = 20): Collection;

    // ==========================================
    // STREAK OPERATIONS
    // ==========================================

    /**
     * Get or create streak record for a fellow in a track.
     */
    public function getOrCreateStreak(User $fellow, Track $track): FellowStreak;

    /**
     * Get all active streaks.
     */
    public function getActiveStreaks(?Track $track = null): Collection;

    /**
     * Get streak leaderboard.
     */
    public function getStreakLeaderboard(Track $track, int $limit = 10): Collection;

    // ==========================================
    // BADGE OPERATIONS
    // ==========================================

    /**
     * Get all badges for a fellow.
     */
    public function getFellowBadges(User $fellow): Collection;

    /**
     * Award a badge to a fellow.
     */
    public function awardBadge(array $data): FellowBadge;

    /**
     * Check if fellow has a specific badge.
     */
    public function hasBadge(User $fellow, string $badgeType, ?string $referenceId = null): bool;

    // ==========================================
    // ANALYTICS & REPORTS
    // ==========================================

    /**
     * Get curriculum completion percentage for a track.
     */
    public function getTrackCompletionRate(Track $track): float;

    /**
     * Get average time to complete activities in a milestone.
     */
    public function getAverageCompletionTime(string $milestoneId): ?float;

    /**
     * Get fellow rankings for a track based on curriculum progress.
     */
    public function getCurriculumRankings(Track $track, int $limit = 20): Collection;
}
