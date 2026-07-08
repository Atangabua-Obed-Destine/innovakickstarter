<?php

namespace App\Repositories\Contracts;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interview Repository Interface
 * 
 * Defines specialized methods for interview session operations.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface InterviewRepositoryInterface extends RepositoryInterface
{
    /**
     * Get interviews for a fellow.
     */
    public function getForFellow(User $fellow, array $filters = []): LengthAwarePaginator;

    /**
     * Get interviews by status.
     */
    public function getByStatus(InterviewStatus $status): Collection;

    /**
     * Get interviews by type.
     */
    public function getByType(InterviewType $type): Collection;

    /**
     * Get interviews by mode (AI or Human).
     */
    public function getByMode(InterviewMode $mode): Collection;

    /**
     * Get scheduled interviews.
     */
    public function getScheduled(): Collection;

    /**
     * Get upcoming interviews for a fellow.
     */
    public function getUpcomingForFellow(User $fellow): Collection;

    /**
     * Get interviews for a mentor.
     */
    public function getForMentor(User $mentor, array $filters = []): LengthAwarePaginator;

    /**
     * Get completed interviews for a fellow.
     */
    public function getCompletedForFellow(User $fellow, int $limit = 10): Collection;

    /**
     * Get this week's interview count for a fellow.
     */
    public function getWeeklyCount(User $fellow, InterviewMode $mode): int;

    /**
     * Check if fellow can schedule more interviews this week.
     */
    public function canScheduleMore(User $fellow, InterviewMode $mode): bool;

    /**
     * Get interview statistics for a fellow.
     */
    public function getFellowStatistics(User $fellow): array;

    /**
     * Get average score for a fellow.
     */
    public function getAverageScore(User $fellow, ?InterviewType $type = null): float;

    /**
     * Get interview score progression for a fellow.
     */
    public function getScoreProgression(User $fellow, int $limit = 10): Collection;

    /**
     * Schedule a new interview.
     */
    public function schedule(User $fellow, Track $track, InterviewType $type, InterviewMode $mode, array $data = []): InterviewSession;

    /**
     * Start an interview session.
     */
    public function start(InterviewSession $interview): InterviewSession;

    /**
     * Complete an interview session with scores.
     */
    public function complete(InterviewSession $interview, array $scores, ?string $feedback = null): InterviewSession;

    /**
     * Cancel an interview.
     */
    public function cancel(InterviewSession $interview, string $reason): InterviewSession;

    /**
     * Get interviews needing reminder.
     */
    public function getNeedingReminder(): Collection;

    /**
     * Get interviews that need to be marked as missed.
     */
    public function getOverdueScheduled(): Collection;

    /**
     * Get available mentors for interview type.
     */
    public function getAvailableMentors(InterviewType $type): Collection;

    /**
     * Get interview type statistics.
     */
    public function getTypeStatistics(): array;
}
