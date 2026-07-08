<?php

namespace App\Repositories\Contracts;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Activity Repository Interface
 * 
 * Defines specialized methods for activity operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface ActivityRepositoryInterface extends RepositoryInterface
{
    /**
     * Get activities for a fellow.
     */
    public function getForFellow(User $fellow, array $filters = []): LengthAwarePaginator;

    /**
     * Get activities by status.
     */
    public function getByStatus(ActivityStatus $status): Collection;

    /**
     * Get pending activities for admin review.
     */
    public function getPendingForReview(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get activities by type.
     */
    public function getByType(ActivityType $type): Collection;

    /**
     * Get activities for a track.
     */
    public function getForTrack(Track $track, array $filters = []): LengthAwarePaginator;

    /**
     * Get activities by pillar.
     */
    public function getByPillar(string $pillar): Collection;

    /**
     * Get recent approved activities.
     */
    public function getRecentApproved(int $limit = 10): Collection;

    /**
     * Get activities submitted this week.
     */
    public function getThisWeek(?User $fellow = null): Collection;

    /**
     * Get activity statistics for a fellow.
     */
    public function getFellowStatistics(User $fellow): array;

    /**
     * Get activity statistics for a track.
     */
    public function getTrackStatistics(Track $track): array;

    /**
     * Count activities by status.
     */
    public function countByStatus(): array;

    /**
     * Count activities by type.
     */
    public function countByType(): array;

    /**
     * Search activities by title or description.
     */
    public function search(string $term, array $filters = []): Collection;

    /**
     * Get activities due for follow-up (needs revision, not updated in X days).
     */
    public function getDueForFollowUp(int $days = 7): Collection;

    /**
     * Approve an activity.
     */
    public function approve(Activity $activity, User $admin, int $points, ?string $feedback = null): Activity;

    /**
     * Reject an activity.
     */
    public function reject(Activity $activity, User $admin, string $reason): Activity;

    /**
     * Request revision for an activity.
     */
    public function requestRevision(Activity $activity, User $admin, string $feedback): Activity;

    /**
     * Get activity points summary for a fellow.
     */
    public function getPointsSummary(User $fellow): array;

    /**
     * Get activities grouped by pillar for a fellow.
     */
    public function getGroupedByPillar(User $fellow): array;
}
