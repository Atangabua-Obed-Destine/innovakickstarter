<?php

namespace App\Repositories;

use App\Enums\ActivityStatus;
use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Track;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Activity Repository Implementation
 * 
 * Handles all activity-related database operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ActivityRepository extends BaseRepository implements ActivityRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return Activity::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getForFellow(User $fellow, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->where('fellow_id', $fellow->id)
            ->with(['track', 'reviewedBy']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['track_id'])) {
            $query->where('track_id', $filters['track_id']);
        }

        if (!empty($filters['pillar'])) {
            $query->whereHas('type', function ($q) use ($filters) {
                // Filter by pillar based on activity type
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * {@inheritDoc}
     */
    public function getByStatus(ActivityStatus $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->with(['fellow', 'track'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingForReview(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->pending()
            ->with(['fellow', 'track'])
            ->orderBy('created_at', 'asc') // Oldest first for fair review
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(ActivityType $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->with(['fellow', 'track'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getForTrack(Track $track, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->where('track_id', $track->id)
            ->with(['fellow']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Only show approved by default
        if (!isset($filters['status'])) {
            $query->approved();
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * {@inheritDoc}
     */
    public function getByPillar(string $pillar): Collection
    {
        $pillar = strtolower($pillar);
        
        $types = collect(ActivityType::cases())
            ->filter(fn ($type) => $type->pillar() === $pillar)
            ->map(fn ($type) => $type->value)
            ->toArray();

        return $this->model
            ->whereIn('type', $types)
            ->approved()
            ->with(['fellow', 'track'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentApproved(int $limit = 10): Collection
    {
        return $this->model
            ->approved()
            ->with(['fellow', 'track'])
            ->orderByDesc('reviewed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getThisWeek(?User $fellow = null): Collection
    {
        $query = $this->model
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->with(['fellow', 'track']);

        if ($fellow) {
            $query->where('fellow_id', $fellow->id);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getFellowStatistics(User $fellow): array
    {
        $activities = $fellow->activities;

        return [
            'total' => $activities->count(),
            'approved' => $activities->where('status', ActivityStatus::APPROVED)->count(),
            'pending' => $activities->where('status', ActivityStatus::PENDING)->count(),
            'rejected' => $activities->where('status', ActivityStatus::REJECTED)->count(),
            'needs_revision' => $activities->where('status', ActivityStatus::NEEDS_REVISION)->count(),
            'total_points' => $activities->where('status', ActivityStatus::APPROVED)->sum('points_earned'),
            'by_type' => $this->countByTypeForFellow($fellow),
            'by_pillar' => $this->getGroupedByPillar($fellow),
            'this_week' => $this->getThisWeek($fellow)->count(),
            'this_month' => $fellow->activities()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackStatistics(Track $track): array
    {
        $activities = $track->activities;

        return [
            'total' => $activities->count(),
            'approved' => $activities->where('status', ActivityStatus::APPROVED)->count(),
            'pending' => $activities->where('status', ActivityStatus::PENDING)->count(),
            'average_points' => round($activities->where('status', ActivityStatus::APPROVED)->avg('points_earned') ?? 0, 2),
            'by_type' => $track->activities()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'this_week' => $activities->where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => $activities
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function countByStatus(): array
    {
        $counts = [];

        foreach (ActivityStatus::cases() as $status) {
            $counts[$status->value] = $this->model
                ->where('status', $status)
                ->count();
        }

        return $counts;
    }

    /**
     * {@inheritDoc}
     */
    public function countByType(): array
    {
        $counts = [];

        foreach (ActivityType::cases() as $type) {
            $counts[$type->value] = $this->model
                ->where('type', $type)
                ->approved()
                ->count();
        }

        return $counts;
    }

    /**
     * Count activities by type for a specific fellow.
     */
    protected function countByTypeForFellow(User $fellow): array
    {
        $counts = [];

        foreach (ActivityType::cases() as $type) {
            $counts[$type->value] = $fellow->activities()
                ->where('type', $type)
                ->approved()
                ->count();
        }

        return $counts;
    }

    /**
     * {@inheritDoc}
     */
    public function search(string $term, array $filters = []): Collection
    {
        $query = $this->model
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->with(['fellow', 'track']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['track_id'])) {
            $query->where('track_id', $filters['track_id']);
        }

        return $query->orderByDesc('created_at')->limit(50)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getDueForFollowUp(int $days = 7): Collection
    {
        return $this->model
            ->needsRevision()
            ->where('updated_at', '<', now()->subDays($days))
            ->with(['fellow', 'track'])
            ->orderBy('updated_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function approve(Activity $activity, User $admin, int $points, ?string $feedback = null): Activity
    {
        $activity->approve($admin, $points, $feedback);
        return $activity->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function reject(Activity $activity, User $admin, string $reason): Activity
    {
        $activity->reject($admin, $reason);
        return $activity->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function requestRevision(Activity $activity, User $admin, string $feedback): Activity
    {
        $activity->update([
            'status' => ActivityStatus::NEEDS_REVISION,
            'verified_by_id' => $admin->id,
            'reviewed_at' => now(),
            'admin_feedback' => $feedback,
        ]);

        return $activity->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function getPointsSummary(User $fellow): array
    {
        $activities = $fellow->activities()->approved()->get();

        $summary = [
            'total_points' => $activities->sum('points_earned'),
            'by_type' => [],
            'by_pillar' => [
                'learn' => 0,
                'build' => 0,
                'share' => 0,
                'connect' => 0,
            ],
            'by_month' => [],
        ];

        // Group by type
        foreach (ActivityType::cases() as $type) {
            $typePoints = $activities->where('type', $type)->sum('points_earned');
            if ($typePoints > 0) {
                $summary['by_type'][$type->value] = [
                    'label' => $type->label(),
                    'points' => $typePoints,
                    'count' => $activities->where('type', $type)->count(),
                ];
            }
        }

        // Group by pillar
        foreach ($activities as $activity) {
            $pillar = $activity->type->pillar();
            $summary['by_pillar'][$pillar] += $activity->points_earned;
        }

        // Group by month (last 6 months)
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthActivities = $activities->filter(function ($activity) use ($month) {
                return $activity->reviewed_at && 
                       $activity->reviewed_at->format('Y-m') === $month->format('Y-m');
            });

            $summary['by_month'][$month->format('M Y')] = $monthActivities->sum('points_earned');
        }

        return $summary;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupedByPillar(User $fellow): array
    {
        $activities = $fellow->activities()->approved()->get();

        $grouped = [
            'learn' => ['activities' => collect(), 'points' => 0, 'count' => 0],
            'build' => ['activities' => collect(), 'points' => 0, 'count' => 0],
            'share' => ['activities' => collect(), 'points' => 0, 'count' => 0],
            'connect' => ['activities' => collect(), 'points' => 0, 'count' => 0],
        ];

        foreach ($activities as $activity) {
            $pillar = $activity->type->pillar();
            $grouped[$pillar]['activities']->push($activity);
            $grouped[$pillar]['points'] += $activity->points_earned;
            $grouped[$pillar]['count']++;
        }

        return $grouped;
    }

    /**
     * Get recent activity for admin dashboard.
     */
    public function getRecentForDashboard(int $limit = 20): Collection
    {
        return $this->model
            ->with(['fellow', 'track', 'reviewedBy'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activities awaiting review count.
     */
    public function getPendingCount(): int
    {
        return $this->model->pending()->count();
    }

    /**
     * Get average review time (in hours).
     */
    public function getAverageReviewTime(): float
    {
        $activities = $this->model
            ->whereNotNull('reviewed_at')
            ->whereMonth('created_at', now()->month)
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
     * Get activities for weekly progress update.
     */
    public function getForWeeklyProgress(User $fellow, Track $track): array
    {
        $activities = $fellow->activities()
            ->where('track_id', $track->id)
            ->approved()
            ->whereBetween('reviewed_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->get();

        $result = [
            'learn' => [],
            'build' => [],
            'share' => [],
            'connect' => [],
        ];

        foreach ($activities as $activity) {
            $pillar = $activity->type->pillar();
            $result[$pillar][] = [
                'id' => $activity->id,
                'title' => $activity->title,
                'points' => $activity->points_earned,
            ];
        }

        return $result;
    }
}
