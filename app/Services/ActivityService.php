<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Track;
use App\Models\User;
use App\Models\WeeklyProgress;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Activity Service
 * 
 * Handles all business logic related to activities:
 * - Creating and submitting activities
 * - Approval/rejection workflow
 * - Points calculation
 * - Weekly pillar tracking
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ActivityService
{
    public function __construct(
        protected ActivityRepositoryInterface $activityRepository,
        protected CareerCapitalCalculator $calculator
    ) {}

    /**
     * Create a new activity submission.
     */
    public function create(User $fellow, Track $track, array $data): Activity
    {
        // Validate fellow is enrolled in track
        if (!$fellow->isEnrolledIn($track)) {
            throw new \Exception("Fellow is not enrolled in this track.");
        }

        // Get activity type
        $type = ActivityType::from($data['type']);

        // Create the activity
        $activity = Activity::create([
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'type' => $type,
            'title' => $data['title'],
            'description' => $data['description'],
            'proof_url' => $data['proof_url'] ?? null,
            'proof_files' => $data['proof_files'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'status' => ActivityStatus::PENDING,
            'submitted_at' => now(),
        ]);

        return $activity;
    }

    /**
     * Approve an activity and award points.
     */
    public function approve(Activity $activity, User $admin, int $points, ?string $feedback = null): Activity
    {
        return DB::transaction(function () use ($activity, $admin, $points, $feedback) {
            // Update activity status
            $activity->update([
                'status' => ActivityStatus::APPROVED,
                'points_earned' => $points,
                'verified_by_id' => $admin->id,
                'reviewed_at' => now(),
                'admin_feedback' => $feedback,
            ]);

            // Update weekly progress for the pillar
            $this->updateWeeklyProgress($activity);

            // Recalculate Career Capital score
            $this->calculator->updateScore($activity->fellow, $activity->track);

            // Send notification to fellow
            Notification::sendActivityApproved($activity->fellow, $activity);

            // Create audit log
            AuditLog::logActivityApproval($activity, $admin, 'approved', $feedback ?? 'Activity approved');

            return $activity->fresh();
        });
    }

    /**
     * Reject an activity.
     */
    public function reject(Activity $activity, User $admin, string $reason): Activity
    {
        return DB::transaction(function () use ($activity, $admin, $reason) {
            $activity->update([
                'status' => ActivityStatus::REJECTED,
                'verified_by_id' => $admin->id,
                'reviewed_at' => now(),
                'admin_feedback' => $reason,
            ]);

            // Send notification to fellow
            Notification::sendActivityRejected($activity->fellow, $activity, $reason);

            // Create audit log
            AuditLog::logActivityApproval($activity, $admin, 'rejected', $reason);

            return $activity->fresh();
        });
    }

    /**
     * Request revision for an activity.
     */
    public function requestRevision(Activity $activity, User $admin, string $feedback): Activity
    {
        return DB::transaction(function () use ($activity, $admin, $feedback) {
            $activity->update([
                'status' => ActivityStatus::NEEDS_REVISION,
                'verified_by_id' => $admin->id,
                'reviewed_at' => now(),
                'admin_feedback' => $feedback,
            ]);

            // Send notification
            Notification::send(
                $activity->fellow,
                Notification::TYPE_ACTIVITY_NEEDS_REVISION,
                'Activity Needs Revision',
                "Your {$activity->type->label()} \"{$activity->title}\" needs some changes: {$feedback}",
                [
                    'action_url' => route('fellow.activities.edit', $activity),
                    'action_label' => 'Edit Activity',
                    'priority' => Notification::PRIORITY_HIGH,
                ]
            );

            return $activity->fresh();
        });
    }

    /**
     * Submit a revised activity.
     */
    public function submitRevision(Activity $activity, array $data): Activity
    {
        if ($activity->status !== ActivityStatus::NEEDS_REVISION) {
            throw new \Exception("Activity is not pending revision.");
        }

        $activity->update([
            'title' => $data['title'] ?? $activity->title,
            'description' => $data['description'] ?? $activity->description,
            'proof_url' => $data['proof_url'] ?? $activity->proof_url,
            'proof_files' => $data['proof_files'] ?? $activity->proof_files,
            'metadata' => $data['metadata'] ?? $activity->metadata,
            'status' => ActivityStatus::PENDING,
            'submitted_at' => now(),
            'admin_feedback' => null, // Clear previous feedback
        ]);

        return $activity->fresh();
    }

    /**
     * Update weekly progress when activity is approved.
     */
    protected function updateWeeklyProgress(Activity $activity): void
    {
        $pillar = $activity->type->pillar();
        
        if (!$pillar) {
            return;
        }

        // Get or create weekly progress
        $progress = WeeklyProgress::getOrCreateForCurrentWeek(
            $activity->fellow,
            $activity->track
        );

        // Add activity to the appropriate pillar
        $progress->addActivity(
            $pillar,
            $activity->id,
            $activity->points_earned
        );

        // Check if pillar is now complete
        $pillarActivitiesField = strtolower($pillar) . '_activities';
        $pillarCompletedField = strtolower($pillar) . '_completed';

        if (!$progress->$pillarCompletedField && count($progress->$pillarActivitiesField ?? []) >= 1) {
            $progress->update([
                $pillarCompletedField => true,
            ]);
        }
    }

    /**
     * Calculate suggested points for an activity.
     */
    public function calculateSuggestedPoints(Activity $activity): int
    {
        $basePoints = $activity->type->defaultPoints();

        // Quality multipliers
        $multiplier = 1.0;

        // Has proof URL
        if (!empty($activity->proof_url)) {
            $multiplier += 0.1;
        }

        // Has detailed description (> 200 chars)
        if (strlen($activity->description ?? '') > 200) {
            $multiplier += 0.1;
        }

        // Has proof files
        if (!empty($activity->proof_files)) {
            $multiplier += 0.1;
        }

        // Fellow's track tier bonus
        $fellowTrack = $activity->fellow->fellowTracks()
            ->where('track_id', $activity->track_id)
            ->first();

        if ($fellowTrack) {
            // Higher tier fellows should produce higher quality work
            $tierMultiplier = match($fellowTrack->tier) {
                'professional' => 1.1,
                'elite' => 1.2,
                default => 1.0,
            };
            $multiplier *= $tierMultiplier;
        }

        return (int) round($basePoints * $multiplier);
    }

    /**
     * Get activity feed for a fellow.
     */
    public function getFeed(User $fellow, int $limit = 20): array
    {
        $activities = $fellow->activities()
            ->with(['track'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'type' => $activity->type->value,
                'type_label' => $activity->type->label(),
                'type_icon' => $activity->type->icon(),
                'title' => $activity->title,
                'status' => $activity->status->value,
                'status_label' => $activity->status->label(),
                'status_color' => $activity->status->color(),
                'points' => $activity->points_earned,
                'track' => $activity->track->name,
                'pillar' => $activity->type->pillar(),
                'created_at' => $activity->created_at->diffForHumans(),
                'reviewed_at' => $activity->reviewed_at?->diffForHumans(),
            ];
        })->toArray();
    }

    /**
     * Get pending activities for admin review.
     */
    public function getPendingForReview(array $filters = []): array
    {
        $query = Activity::pending()
            ->with(['fellow', 'track'])
            ->orderBy('created_at', 'asc'); // Oldest first for fair review

        if (!empty($filters['track_id'])) {
            $query->where('track_id', $filters['track_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = $filters['per_page'] ?? 15;
        $activities = $query->paginate($perPage);

        return [
            'activities' => $activities->items(),
            'total' => $activities->total(),
            'per_page' => $activities->perPage(),
            'current_page' => $activities->currentPage(),
            'last_page' => $activities->lastPage(),
        ];
    }

    /**
     * Bulk approve activities.
     */
    public function bulkApprove(array $activityIds, User $admin, array $pointsMap = []): int
    {
        $count = 0;

        DB::transaction(function () use ($activityIds, $admin, $pointsMap, &$count) {
            foreach ($activityIds as $activityId) {
                $activity = Activity::find($activityId);
                
                if (!$activity || $activity->status !== ActivityStatus::PENDING) {
                    continue;
                }

                $points = $pointsMap[$activityId] ?? $this->calculateSuggestedPoints($activity);
                $this->approve($activity, $admin, $points);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Get activity statistics for dashboard.
     */
    public function getAdminStatistics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'pending_count' => Activity::pending()->count(),
            'approved_today' => Activity::approved()
                ->whereDate('reviewed_at', $today)
                ->count(),
            'approved_this_week' => Activity::approved()
                ->where('reviewed_at', '>=', $thisWeek)
                ->count(),
            'approved_this_month' => Activity::approved()
                ->where('reviewed_at', '>=', $thisMonth)
                ->count(),
            'total_points_awarded_today' => Activity::approved()
                ->whereDate('reviewed_at', $today)
                ->sum('points_earned'),
            'average_review_time_hours' => $this->calculateAverageReviewTime(),
            'by_status' => $this->activityRepository->countByStatus(),
            'by_type' => $this->activityRepository->countByType(),
        ];
    }

    /**
     * Calculate average review time in hours.
     */
    protected function calculateAverageReviewTime(): float
    {
        $activities = Activity::whereNotNull('reviewed_at')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        if ($activities->isEmpty()) {
            return 0;
        }

        $totalHours = $activities->sum(function ($activity) {
            return $activity->created_at->diffInHours($activity->reviewed_at);
        });

        return round($totalHours / $activities->count(), 1);
    }

    /**
     * Get activities that need follow-up.
     */
    public function getNeedingFollowUp(): array
    {
        // Activities pending for more than 3 days
        $stale = Activity::pending()
            ->where('created_at', '<', now()->subDays(3))
            ->with(['fellow', 'track'])
            ->get();

        // Activities needing revision for more than 7 days
        $unrevised = Activity::needsRevision()
            ->where('reviewed_at', '<', now()->subDays(7))
            ->with(['fellow', 'track'])
            ->get();

        return [
            'stale_pending' => $stale,
            'stale_revision' => $unrevised,
        ];
    }
}
