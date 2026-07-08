<?php

namespace App\Repositories;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Enums\UserRole;
use App\Models\AdminSetting;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\User;
use App\Repositories\Contracts\InterviewRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interview Repository Implementation
 * 
 * Handles all interview session database operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class InterviewRepository extends BaseRepository implements InterviewRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function model(): string
    {
        return InterviewSession::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getForFellow(User $fellow, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->where('fellow_id', $fellow->id)
            ->with(['track', 'mentor']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['mode'])) {
            $query->where('mode', $filters['mode']);
        }

        if (!empty($filters['track_id'])) {
            $query->where('track_id', $filters['track_id']);
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
    public function getByStatus(InterviewStatus $status): Collection
    {
        return $this->model
            ->where('status', $status)
            ->with(['fellow', 'track', 'mentor'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(InterviewType $type): Collection
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
    public function getByMode(InterviewMode $mode): Collection
    {
        return $this->model
            ->where('mode', $mode)
            ->with(['fellow', 'track'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getScheduled(): Collection
    {
        return $this->model
            ->scheduled()
            ->with(['fellow', 'track', 'mentor'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getUpcomingForFellow(User $fellow): Collection
    {
        return $this->model
            ->where('fellow_id', $fellow->id)
            ->whereIn('status', [InterviewStatus::PENDING, InterviewStatus::SCHEDULED])
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>=', now());
            })
            ->with(['track', 'mentor'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getForMentor(User $mentor, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->where('mentor_id', $mentor->id)
            ->with(['fellow', 'track']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'scheduled_at';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * {@inheritDoc}
     */
    public function getCompletedForFellow(User $fellow, int $limit = 10): Collection
    {
        return $this->model
            ->where('fellow_id', $fellow->id)
            ->completed()
            ->with(['track', 'mentor'])
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getWeeklyCount(User $fellow, InterviewMode $mode): int
    {
        return $this->model
            ->where('fellow_id', $fellow->id)
            ->where('mode', $mode)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function canScheduleMore(User $fellow, InterviewMode $mode): bool
    {
        $currentCount = $this->getWeeklyCount($fellow, $mode);

        $limit = $mode === InterviewMode::AI
            ? AdminSetting::get('weekly_ai_interviews_limit', 5)
            : AdminSetting::get('weekly_human_interviews_limit', 2);

        return $currentCount < $limit;
    }

    /**
     * {@inheritDoc}
     */
    public function getFellowStatistics(User $fellow): array
    {
        $interviews = $fellow->interviewSessions;
        $completed = $interviews->where('status', InterviewStatus::COMPLETED);

        return [
            'total' => $interviews->count(),
            'completed' => $completed->count(),
            'scheduled' => $interviews->where('status', InterviewStatus::SCHEDULED)->count(),
            'pending' => $interviews->where('status', InterviewStatus::PENDING)->count(),
            'cancelled' => $interviews->where('status', InterviewStatus::CANCELLED)->count(),
            'average_score' => round($completed->avg('overall_score') ?? 0, 1),
            'best_score' => $completed->max('overall_score') ?? 0,
            'by_type' => $this->getTypeBreakdown($fellow),
            'by_mode' => [
                'ai' => $interviews->where('mode', InterviewMode::AI)->count(),
                'human' => $interviews->where('mode', InterviewMode::HUMAN)->count(),
            ],
            'this_week' => [
                'ai' => $this->getWeeklyCount($fellow, InterviewMode::AI),
                'human' => $this->getWeeklyCount($fellow, InterviewMode::HUMAN),
            ],
            'can_schedule' => [
                'ai' => $this->canScheduleMore($fellow, InterviewMode::AI),
                'human' => $this->canScheduleMore($fellow, InterviewMode::HUMAN),
            ],
        ];
    }

    /**
     * Get interview type breakdown for a fellow.
     */
    protected function getTypeBreakdown(User $fellow): array
    {
        $breakdown = [];

        foreach (InterviewType::cases() as $type) {
            $typeInterviews = $fellow->interviewSessions()
                ->where('type', $type)
                ->completed()
                ->get();

            if ($typeInterviews->isNotEmpty()) {
                $breakdown[$type->value] = [
                    'label' => $type->label(),
                    'count' => $typeInterviews->count(),
                    'average_score' => round($typeInterviews->avg('overall_score') ?? 0, 1),
                    'best_score' => $typeInterviews->max('overall_score') ?? 0,
                ];
            }
        }

        return $breakdown;
    }

    /**
     * {@inheritDoc}
     */
    public function getAverageScore(User $fellow, ?InterviewType $type = null): float
    {
        $query = $fellow->interviewSessions()->completed();

        if ($type) {
            $query->where('type', $type);
        }

        return round($query->avg('overall_score') ?? 0, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function getScoreProgression(User $fellow, int $limit = 10): Collection
    {
        return $fellow->interviewSessions()
            ->completed()
            ->orderBy('completed_at')
            ->limit($limit)
            ->get(['id', 'type', 'overall_score', 'completed_at'])
            ->map(function ($interview) {
                return [
                    'date' => $interview->completed_at->format('M d'),
                    'type' => $interview->type->label(),
                    'score' => $interview->overall_score,
                ];
            });
    }

    /**
     * {@inheritDoc}
     */
    public function schedule(User $fellow, Track $track, InterviewType $type, InterviewMode $mode, array $data = []): InterviewSession
    {
        return $this->model->create([
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'type' => $type,
            'mode' => $mode,
            'status' => isset($data['scheduled_at']) ? InterviewStatus::SCHEDULED : InterviewStatus::PENDING,
            'mentor_id' => $data['mentor_id'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? $type->defaultDuration(),
            'difficulty_level' => $data['difficulty_level'] ?? 'intermediate',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function start(InterviewSession $interview): InterviewSession
    {
        $interview->start();
        return $interview->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function complete(InterviewSession $interview, array $scores, ?string $feedback = null): InterviewSession
    {
        $overallScore = collect($scores)->avg() ?? 0;

        $interview->update([
            'status' => InterviewStatus::COMPLETED,
            'completed_at' => now(),
            'rubric_scores' => $scores,
            'overall_score' => round($overallScore, 1),
            'feedback' => $feedback,
        ]);

        return $interview->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function cancel(InterviewSession $interview, string $reason): InterviewSession
    {
        $interview->cancel($reason);
        return $interview->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function getNeedingReminder(): Collection
    {
        // Interviews scheduled within the next 24 hours
        return $this->model
            ->scheduled()
            ->whereBetween('scheduled_at', [
                now(),
                now()->addHours(24),
            ])
            ->whereNull('reminder_sent_at')
            ->with(['fellow', 'track', 'mentor'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getOverdueScheduled(): Collection
    {
        // Interviews that were scheduled but never started
        return $this->model
            ->where('status', InterviewStatus::SCHEDULED)
            ->where('scheduled_at', '<', now()->subHours(2))
            ->with(['fellow', 'track', 'mentor'])
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableMentors(InterviewType $type): Collection
    {
        return User::where('role', UserRole::MENTOR)
            ->where('is_available_for_interviews', true)
            ->whereJsonContains('interview_types', $type->value)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeStatistics(): array
    {
        $stats = [];

        foreach (InterviewType::cases() as $type) {
            $typeInterviews = $this->model
                ->where('type', $type)
                ->completed()
                ->get();

            $stats[$type->value] = [
                'label' => $type->label(),
                'total' => $typeInterviews->count(),
                'average_score' => round($typeInterviews->avg('overall_score') ?? 0, 1),
                'highest_score' => $typeInterviews->max('overall_score') ?? 0,
                'lowest_score' => $typeInterviews->min('overall_score') ?? 0,
            ];
        }

        return $stats;
    }

    /**
     * Get today's scheduled interviews.
     */
    public function getTodayScheduled(): Collection
    {
        return $this->model
            ->scheduled()
            ->whereDate('scheduled_at', today())
            ->with(['fellow', 'track', 'mentor'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get interview trends for analytics.
     */
    public function getTrends(int $months = 6): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $monthInterviews = $this->model
                ->completed()
                ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                ->get();

            $trends[] = [
                'month' => $date->format('M Y'),
                'total' => $monthInterviews->count(),
                'ai' => $monthInterviews->where('mode', InterviewMode::AI)->count(),
                'human' => $monthInterviews->where('mode', InterviewMode::HUMAN)->count(),
                'average_score' => round($monthInterviews->avg('overall_score') ?? 0, 1),
            ];
        }

        return $trends;
    }

    /**
     * Get interviews for weekly progress.
     */
    public function getForWeeklyProgress(User $fellow, Track $track): Collection
    {
        return $this->model
            ->where('fellow_id', $fellow->id)
            ->where('track_id', $track->id)
            ->completed()
            ->whereBetween('completed_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->get();
    }
}
