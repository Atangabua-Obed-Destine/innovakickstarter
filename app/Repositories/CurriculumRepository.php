<?php

namespace App\Repositories;

use App\Enums\BadgeType;
use App\Enums\CurriculumStatus;
use App\Enums\DifficultyLevel;
use App\Models\AccountabilityPair;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Repositories\Contracts\CurriculumRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Curriculum Repository Implementation
 * 
 * Handles all data access for the curriculum system:
 * milestones, activities, progress, streaks, badges.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CurriculumRepository extends BaseRepository implements CurriculumRepositoryInterface
{
    /**
     * Get the model class name.
     * 
     * Note: This repository manages multiple models.
     * The base model is TrackMilestone for general CRUD,
     * but specific methods operate on their own model classes.
     */
    protected function model(): string
    {
        return TrackMilestone::class;
    }

    // ==========================================
    // MILESTONE OPERATIONS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getMilestonesForTrack(Track $track): Collection
    {
        return TrackMilestone::where('track_id', $track->id)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->with(['curriculumActivities' => function ($q) {
                $q->where('is_active', true)->orderBy('sequence_order');
            }])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getMilestoneWithActivities(string $milestoneId): TrackMilestone
    {
        return TrackMilestone::with([
            'curriculumActivities' => function ($q) {
                $q->orderBy('sequence_order');
            },
            'curriculumActivities.fellowProgress',
            'prerequisiteMilestone',
            'track',
        ])->findOrFail($milestoneId);
    }

    /**
     * {@inheritDoc}
     */
    public function createMilestone(Track $track, array $data): TrackMilestone
    {
        $data['track_id'] = $track->id;

        // Auto-set order if not provided
        if (!isset($data['sequence_order'])) {
            $data['sequence_order'] = TrackMilestone::where('track_id', $track->id)->max('sequence_order') + 1;
        }

        return TrackMilestone::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateMilestone(string $milestoneId, array $data): TrackMilestone
    {
        $milestone = TrackMilestone::findOrFail($milestoneId);
        $milestone->update($data);
        return $milestone->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMilestone(string $milestoneId): bool
    {
        $milestone = TrackMilestone::findOrFail($milestoneId);

        // Check if any fellows have started activities in this milestone
        $hasProgress = FellowCurriculumProgress::whereHas('curriculumActivity', function ($q) use ($milestoneId) {
            $q->where('milestone_id', $milestoneId);
        })->exists();

        if ($hasProgress) {
            // Soft-deactivate instead of delete
            $milestone->update(['is_active' => false]);
            return true;
        }

        return $milestone->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function reorderMilestones(Track $track, array $orderedIds): void
    {
        DB::transaction(function () use ($track, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                TrackMilestone::where('id', $id)
                    ->where('track_id', $track->id)
                    ->update(['sequence_order' => $index + 1]);
            }
        });
    }

    // ==========================================
    // CURRICULUM ACTIVITY OPERATIONS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getActivitiesForMilestone(string $milestoneId): Collection
    {
        return TrackCurriculumActivity::where('milestone_id', $milestoneId)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->with(['chainParent', 'chainChildren'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getActivitiesForTrack(Track $track, array $filters = []): Collection
    {
        $query = TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true);

        if (!empty($filters['milestone_id'])) {
            $query->where('milestone_id', $filters['milestone_id']);
        }

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty_level', $filters['difficulty']);
        }

        if (!empty($filters['type'])) {
            $query->where('activity_type', $filters['type']);
        }

        if (!empty($filters['required_only'])) {
            $query->where('is_required', true);
        }

        return $query->orderBy('sequence_order')
            ->with(['milestone'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function createActivity(array $data): TrackCurriculumActivity
    {
        // Auto-set order if not provided
        if (!isset($data['sequence_order'])) {
            $data['sequence_order'] = TrackCurriculumActivity::where('milestone_id', $data['milestone_id'])
                    ->max('sequence_order') + 1;
        }

        return TrackCurriculumActivity::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateActivity(string $activityId, array $data): TrackCurriculumActivity
    {
        $activity = TrackCurriculumActivity::findOrFail($activityId);
        $activity->update($data);
        return $activity->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteActivity(string $activityId): bool
    {
        $activity = TrackCurriculumActivity::findOrFail($activityId);

        // Check for existing progress
        $hasProgress = FellowCurriculumProgress::where('curriculum_activity_id', $activityId)
            ->exists();

        if ($hasProgress) {
            $activity->update(['is_active' => false]);
            return true;
        }

        return $activity->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function reorderActivities(string $milestoneId, array $orderedIds): void
    {
        DB::transaction(function () use ($milestoneId, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                TrackCurriculumActivity::where('id', $id)
                    ->where('milestone_id', $milestoneId)
                    ->update(['sequence_order' => $index + 1]);
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function getActivitiesByDifficulty(DifficultyLevel $level, ?Track $track = null): Collection
    {
        $query = TrackCurriculumActivity::where('difficulty_level', $level->value)
            ->where('is_active', true);

        if ($track) {
            $query->where('track_id', $track->id);
        }

        return $query->orderBy('sequence_order')->get();
    }

    // ==========================================
    // FELLOW PROGRESS OPERATIONS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getFellowProgress(User $fellow, Track $track): Collection
    {
        return FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            })
            ->with([
                'curriculumActivity.milestone',
                'curriculumActivity',
                'reviewer',
                'peerReviewer',
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFellowMilestoneProgress(User $fellow, string $milestoneId): Collection
    {
        return FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereHas('curriculumActivity', function ($q) use ($milestoneId) {
                $q->where('milestone_id', $milestoneId);
            })
            ->with(['curriculumActivity'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrCreateProgress(User $fellow, TrackCurriculumActivity $activity): FellowCurriculumProgress
    {
        return FellowCurriculumProgress::firstOrCreate(
            [
                'fellow_id' => $fellow->id,
                'curriculum_activity_id' => $activity->id,
            ],
            [
                'status' => CurriculumStatus::AVAILABLE,
                'deadline_at' => $activity->calculateDeadlineFor($fellow),
                'grace_deadline_at' => $activity->calculateGraceDeadlineFor($fellow),
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingReviews(int $perPage = 15, ?Track $track = null): LengthAwarePaginator
    {
        $query = FellowCurriculumProgress::whereIn('status', [
                CurriculumStatus::SUBMITTED->value,
                CurriculumStatus::UNDER_REVIEW->value,
            ])
            ->with([
                'fellow',
                'curriculumActivity.milestone',
                'curriculumActivity.track',
            ]);

        if ($track) {
            $query->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            });
        }

        return $query->orderBy('submitted_at', 'asc')->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingPeerReviews(User $fellow, ?Track $track = null): Collection
    {
        // Find progress records that need peer review from this fellow's accountability partner
        $partnerIds = AccountabilityPair::where(function ($q) use ($fellow) {
            $q->where('fellow_a_id', $fellow->id)
                ->orWhere('fellow_b_id', $fellow->id);
        })
            ->where('is_active', true)
            ->get()
            ->map(function ($pair) use ($fellow) {
                return $pair->getPartner($fellow)?->id;
            })
            ->filter();

        $query = FellowCurriculumProgress::where('status', CurriculumStatus::PEER_REVIEW->value)
            ->whereIn('fellow_id', $partnerIds)
            ->with(['fellow', 'curriculumActivity']);

        if ($track) {
            $query->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            });
        }

        return $query->orderBy('submitted_at', 'asc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getOverdueProgress(?Track $track = null): Collection
    {
        $query = FellowCurriculumProgress::whereIn('status', [
            CurriculumStatus::AVAILABLE->value,
            CurriculumStatus::IN_PROGRESS->value,
        ])
            ->where(function ($q) {
                $q->where('deadline_at', '<', now())
                    ->orWhere('grace_deadline_at', '<', now());
            })
            ->with(['fellow', 'curriculumActivity']);

        if ($track) {
            $query->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            });
        }

        return $query->orderBy('deadline_at', 'asc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFellowCurriculumStats(User $fellow, Track $track): array
    {
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            });

        $totalActivities = TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true)
            ->count();

        $completedCount = (clone $progress)->where('status', CurriculumStatus::COMPLETED->value)->count();
        $inProgressCount = (clone $progress)->where('status', CurriculumStatus::IN_PROGRESS->value)->count();
        $submittedCount = (clone $progress)->whereIn('status', [
            CurriculumStatus::SUBMITTED->value,
            CurriculumStatus::PEER_REVIEW->value,
            CurriculumStatus::UNDER_REVIEW->value,
        ])->count();
        $overdueCount = (clone $progress)->where('status', CurriculumStatus::OVERDUE->value)->count();

        $totalPoints = (clone $progress)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->sum('points_awarded');

        $avgScore = (clone $progress)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->avg('score_awarded');

        return [
            'total_activities' => $totalActivities,
            'completed' => $completedCount,
            'in_progress' => $inProgressCount,
            'submitted' => $submittedCount,
            'overdue' => $overdueCount,
            'not_started' => $totalActivities - $completedCount - $inProgressCount - $submittedCount - $overdueCount,
            'completion_percentage' => $totalActivities > 0 ? round(($completedCount / $totalActivities) * 100, 1) : 0,
            'total_points' => $totalPoints,
            'average_score' => $avgScore ? round($avgScore, 2) : null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getProgressByStatus(CurriculumStatus $status, ?Track $track = null): Collection
    {
        $query = FellowCurriculumProgress::where('status', $status->value)
            ->with(['fellow', 'curriculumActivity']);

        if ($track) {
            $query->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            });
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentCompletions(int $limit = 20): Collection
    {
        return FellowCurriculumProgress::where('status', CurriculumStatus::COMPLETED->value)
            ->with(['fellow', 'curriculumActivity.track', 'curriculumActivity.milestone'])
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // ==========================================
    // STREAK OPERATIONS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getOrCreateStreak(User $fellow, Track $track): FellowStreak
    {
        return FellowStreak::firstOrCreate(
            [
                'fellow_id' => $fellow->id,
                'track_id' => $track->id,
            ],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
                'multiplier' => 1.0,
                'is_active' => true,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveStreaks(?Track $track = null): Collection
    {
        $query = FellowStreak::where('is_active', true)
            ->where('current_streak', '>', 0)
            ->with(['fellow', 'track']);

        if ($track) {
            $query->where('track_id', $track->id);
        }

        return $query->orderBy('current_streak', 'desc')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getStreakLeaderboard(Track $track, int $limit = 10): Collection
    {
        return FellowStreak::where('track_id', $track->id)
            ->where('current_streak', '>', 0)
            ->with(['fellow'])
            ->orderBy('current_streak', 'desc')
            ->limit($limit)
            ->get();
    }

    // ==========================================
    // BADGE OPERATIONS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getFellowBadges(User $fellow): Collection
    {
        return FellowBadge::where('fellow_id', $fellow->id)
            ->orderBy('earned_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function awardBadge(array $data): FellowBadge
    {
        $data['earned_at'] = $data['earned_at'] ?? now();
        return FellowBadge::create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function hasBadge(User $fellow, string $badgeType, ?string $referenceId = null): bool
    {
        $query = FellowBadge::where('fellow_id', $fellow->id)
            ->where('badge_type', $badgeType);

        if ($referenceId) {
            $query->where(function ($q) use ($referenceId) {
                $q->where('milestone_id', $referenceId)
                    ->orWhere('track_id', $referenceId);
            });
        }

        return $query->exists();
    }

    // ==========================================
    // ANALYTICS & REPORTS
    // ==========================================

    /**
     * {@inheritDoc}
     */
    public function getTrackCompletionRate(Track $track): float
    {
        $totalActivities = TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true)
            ->where('is_required', true)
            ->count();

        if ($totalActivities === 0) {
            return 0.0;
        }

        // Count distinct fellows and their completed required activities
        $fellowCount = $track->fellowTracks()->count();
        if ($fellowCount === 0) {
            return 0.0;
        }

        $totalCompleted = FellowCurriculumProgress::where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id)
                    ->where('is_required', true);
            })
            ->count();

        return round(($totalCompleted / ($totalActivities * $fellowCount)) * 100, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function getAverageCompletionTime(string $milestoneId): ?float
    {
        $completedProgress = FellowCurriculumProgress::where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', function ($q) use ($milestoneId) {
                $q->where('milestone_id', $milestoneId);
            })
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->get();

        if ($completedProgress->isEmpty()) {
            return null;
        }

        $totalHours = $completedProgress->sum(function ($progress) {
            return $progress->started_at->diffInHours($progress->completed_at);
        });

        return round($totalHours / $completedProgress->count(), 1);
    }

    /**
     * {@inheritDoc}
     */
    public function getCurriculumRankings(Track $track, int $limit = 20): Collection
    {
        return FellowCurriculumProgress::where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id);
            })
            ->selectRaw('fellow_id, COUNT(*) as completed_count, SUM(points_awarded) as total_points, AVG(score_awarded) as avg_score')
            ->groupBy('fellow_id')
            ->orderByDesc('total_points')
            ->with(['fellow'])
            ->limit($limit)
            ->get();
    }
}
