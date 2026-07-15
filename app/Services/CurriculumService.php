<?php

namespace App\Services;

use App\Enums\BadgeType;
use App\Enums\CurriculumStatus;
use App\Enums\DifficultyLevel;
use App\Enums\InterviewMode;
use App\Enums\InterviewType;
use App\Models\FellowBadge;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowStreak;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Repositories\Contracts\CurriculumRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Curriculum Service
 * 
 * Core business logic for the Track Curriculum System.
 * Manages the lifecycle of milestones, activities, and fellow progress.
 * Handles initialization, submission, review, and completion workflows.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class CurriculumService
{
    public function __construct(
        protected CurriculumRepositoryInterface $curriculumRepository,
        protected StreakService $streakService,
        protected CareerCapitalCalculator $calculator
    ) {}

    // ==========================================
    // MILESTONE MANAGEMENT (Admin)
    // ==========================================

    /**
     * Get the full curriculum for a track with milestones and activities.
     */
    public function getTrackCurriculum(Track $track): Collection
    {
        return $this->curriculumRepository->getMilestonesForTrack($track);
    }

    /**
     * Create a new milestone.
     */
    public function createMilestone(Track $track, array $data, User $creator): TrackMilestone
    {
        $data['created_by'] = $creator->id;

        return DB::transaction(function () use ($track, $data) {
            return $this->curriculumRepository->createMilestone($track, $data);
        });
    }

    /**
     * Update a milestone.
     */
    public function updateMilestone(string $milestoneId, array $data): TrackMilestone
    {
        return $this->curriculumRepository->updateMilestone($milestoneId, $data);
    }

    /**
     * Delete a milestone (with safety checks).
     */
    public function deleteMilestone(string $milestoneId): bool
    {
        return $this->curriculumRepository->deleteMilestone($milestoneId);
    }

    /**
     * Reorder milestones.
     */
    public function reorderMilestones(Track $track, array $orderedIds): void
    {
        $this->curriculumRepository->reorderMilestones($track, $orderedIds);
    }

    // ==========================================
    // CURRICULUM ACTIVITY MANAGEMENT (Admin)
    // ==========================================

    /**
     * Create a curriculum activity within a milestone.
     */
    public function createCurriculumActivity(array $data, User $creator): TrackCurriculumActivity
    {
        $data['created_by'] = $creator->id;

        // Resolve track_id from milestone if not set
        if (empty($data['track_id']) && !empty($data['milestone_id'])) {
            $milestone = TrackMilestone::findOrFail($data['milestone_id']);
            $data['track_id'] = $milestone->track_id;
        }

        return DB::transaction(function () use ($data) {
            return $this->curriculumRepository->createActivity($data);
        });
    }

    /**
     * Update a curriculum activity.
     */
    public function updateCurriculumActivity(string $activityId, array $data): TrackCurriculumActivity
    {
        return $this->curriculumRepository->updateActivity($activityId, $data);
    }

    /**
     * Delete a curriculum activity.
     */
    public function deleteCurriculumActivity(string $activityId): bool
    {
        return $this->curriculumRepository->deleteActivity($activityId);
    }

    /**
     * Reorder activities within a milestone.
     */
    public function reorderActivities(string $milestoneId, array $orderedIds): void
    {
        $this->curriculumRepository->reorderActivities($milestoneId, $orderedIds);
    }

    // ==========================================
    // FELLOW PROGRESS MANAGEMENT
    // ==========================================

    /**
     * Initialize curriculum progress for a fellow in a track.
     * Creates progress records for all available activities
     * based on milestone gating and prerequisites.
     */
    public function initializeFellowCurriculum(User $fellow, Track $track): int
    {
        $milestones = $this->curriculumRepository->getMilestonesForTrack($track);
        $initialized = 0;

        DB::transaction(function () use ($fellow, $milestones, &$initialized) {
            foreach ($milestones as $milestone) {
                // Check if this milestone is unlocked for the fellow
                if (!$milestone->isUnlockedFor($fellow)) {
                    continue;
                }

                foreach ($milestone->curriculumActivities as $activity) {
                    // Check prerequisites
                    if (!$activity->prerequisitesMet($fellow)) {
                        // Create locked progress record
                        FellowCurriculumProgress::firstOrCreate(
                            [
                                'fellow_id' => $fellow->id,
                                'curriculum_activity_id' => $activity->id,
                            ],
                            [
                                'status' => CurriculumStatus::LOCKED,
                            ]
                        );
                    } else {
                        // Create available progress record with deadline
                        $this->curriculumRepository->getOrCreateProgress($fellow, $activity);
                    }
                    $initialized++;
                }
            }

            // Initialize streak record
            $this->streakService->getOrCreateStreak($fellow, $milestones->first()?->track);
        });

        return $initialized;
    }

    /**
     * Start working on a curriculum activity.
     * For mock_interview activities, this also creates an interview session.
     *
     * @return FellowCurriculumProgress|array Returns progress, or ['progress' => ..., 'interview' => ...] for interviews
     */
    public function startActivity(User $fellow, string $activityId): FellowCurriculumProgress|array
    {
        $activity = TrackCurriculumActivity::findOrFail($activityId);

        // Verify fellow is enrolled in the track
        if (!$fellow->isEnrolledIn($activity->track_id)) {
            throw new \Exception('Fellow is not enrolled in this track.');
        }

        // Check prerequisites
        if (!$activity->isAvailableFor($fellow)) {
            throw new \Exception('This activity is not yet available. Complete prerequisites first.');
        }

        $progress = $this->curriculumRepository->getOrCreateProgress($fellow, $activity);

        if (!$progress->status->canStart()) {
            throw new \Exception("Cannot start this activity. Current status: {$progress->status->label()}");
        }

        $progress->markInProgress();

        // For mock_interview activities, create an interview session
        if ($activity->requiresInterviewSession()) {
            $interview = $this->createLinkedInterview($fellow, $activity, $progress);
            return ['progress' => $progress, 'interview' => $interview];
        }

        return $progress;
    }

    /**
     * Create a linked interview session for a mock_interview curriculum activity.
     */
    public function createLinkedInterview(
        User $fellow,
        TrackCurriculumActivity $activity,
        FellowCurriculumProgress $progress
    ): InterviewSession {
        $config = $activity->interview_config ?? [];
        $track = Track::findOrFail($activity->track_id);

        $interviewType = InterviewType::tryFrom($config['type'] ?? 'behavioral') ?? InterviewType::BEHAVIORAL;
        $interviewMode = InterviewMode::tryFrom($config['mode'] ?? 'ai') ?? InterviewMode::AI;
        $difficulty = $config['difficulty'] ?? 'intermediate';

        // AI interviews start immediately; human/peer need scheduling
        $isAI = $interviewMode === InterviewMode::AI;

        $interview = InterviewSession::create([
            'fellow_id' => $fellow->id,
            'track_id' => $activity->track_id,
            'type' => $interviewType,
            'mode' => $interviewMode,
            'status' => $isAI
                ? \App\Enums\InterviewStatus::IN_PROGRESS
                : \App\Enums\InterviewStatus::PENDING,
            'scheduled_at' => now(),
            'started_at' => $isAI ? now() : null,
            'duration_minutes' => $interviewType->defaultDuration(),
            'difficulty_level' => $difficulty,
            'title' => "Curriculum: {$activity->title}",
            'description' => "Interview session for curriculum activity: {$activity->title}",
            'curriculum_activity_id' => $activity->id,
            'curriculum_progress_id' => $progress->id,
        ]);

        // Link the interview to the progress record
        $progress->update(['linked_interview_id' => $interview->id]);

        return $interview;
    }

    /**
     * Launch another interview attempt for a curriculum activity.
     * Used when the fellow wants to retry (if multiple sessions are allowed).
     */
    public function launchInterviewAttempt(User $fellow, string $activityId): InterviewSession
    {
        $activity = TrackCurriculumActivity::findOrFail($activityId);

        if (!$activity->requiresInterviewSession()) {
            throw new \Exception('This activity does not require an interview session.');
        }

        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->firstOrFail();

        if (!in_array($progress->status, [CurriculumStatus::IN_PROGRESS, CurriculumStatus::REJECTED])) {
            throw new \Exception("Cannot launch interview in current status: {$progress->status->label()}");
        }

        // Prevent launching if there's already an active (non-completed) session
        $activeSession = InterviewSession::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->whereIn('status', [
                \App\Enums\InterviewStatus::SCHEDULED->value,
                \App\Enums\InterviewStatus::IN_PROGRESS->value,
                \App\Enums\InterviewStatus::PENDING->value,
            ])
            ->first();

        if ($activeSession) {
            throw new \Exception('You already have an active interview session. Complete or cancel it before starting a new one.');
        }

        return $this->createLinkedInterview($fellow, $activity, $progress);
    }

    /**
     * Handle the completion of a linked interview session.
     * Called when the interview system completes an interview that's linked to a curriculum activity.
     */
    public function handleInterviewCompletion(InterviewSession $interview): void
    {
        if (!$interview->curriculum_progress_id) {
            return; // Not linked to curriculum
        }

        DB::transaction(function () use ($interview) {
            // Lock the progress row to prevent concurrent completion race conditions
            $progress = FellowCurriculumProgress::lockForUpdate()
                ->find($interview->curriculum_progress_id);
            if (!$progress) {
                return;
            }

            $activity = $progress->curriculumActivity;
            if (!$activity) {
                return;
            }

            $config = $activity->interview_config ?? [];
            $minScore = $config['min_score'] ?? 70;
            $requiredCount = $config['count'] ?? 1;

            // Get all completed interviews for this curriculum activity
            $completedInterviews = InterviewSession::where('fellow_id', $progress->fellow_id)
                ->where('curriculum_activity_id', $activity->id)
                ->where('status', 'completed')
                ->orderByDesc('overall_score')
                ->get();

            $bestScore = $completedInterviews->max(fn($i) => $i->overall_score ?? $i->score ?? 0) ?? 0;
            $completedSessionCount = $completedInterviews->count();
            $passed = $bestScore >= $minScore && $completedSessionCount >= $requiredCount;

            // Update the progress with the best interview score
            $progress->update([
                'linked_interview_id' => $completedInterviews->first()?->id,
            ]);

            // If passed and activity is still in_progress, auto-submit for review
            if ($passed && $progress->status === CurriculumStatus::IN_PROGRESS) {
                $progress->submit(
                    [
                        'evidence_text' => "Mock interview completed. Best score: {$bestScore}%. Sessions completed: {$completedSessionCount}/{$requiredCount}.",
                    ],
                    "Auto-submitted via interview system. Interview type: " . ($config['type'] ?? 'behavioral')
                );

                $progress->update([
                    'score_awarded' => round($bestScore),
                ]);

                Log::info("Curriculum activity auto-submitted after interview completion", [
                    'fellow_id' => $progress->fellow_id,
                    'activity_id' => $activity->id,
                    'interview_id' => $interview->id,
                    'best_score' => $bestScore,
                    'sessions' => $completedSessionCount,
                ]);
            }
        });
    }

    /**
     * Get interview sessions linked to a curriculum activity for a fellow.
     */
    public function getLinkedInterviews(User $fellow, TrackCurriculumActivity $activity): Collection
    {
        return InterviewSession::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Submit a completed curriculum activity for review.
     * For mock_interview activities, validates that interview requirements are met.
     */
    public function submitActivity(User $fellow, string $progressId, array $data): FellowCurriculumProgress
    {
        $progress = FellowCurriculumProgress::with('curriculumActivity')->findOrFail($progressId);

        // Verify ownership
        if ($progress->fellow_id !== $fellow->id) {
            throw new \Exception('You can only submit your own activities.');
        }

        if (!$progress->status->canSubmit()) {
            throw new \Exception("Cannot submit in current status: {$progress->status->label()}");
        }

        $activity = $progress->curriculumActivity;

        // For mock_interview activities, validate interview completion
        if ($activity->requiresInterviewSession()) {
            $config = $activity->interview_config ?? [];
            $minScore = $config['min_score'] ?? 70;
            $requiredCount = $config['count'] ?? 1;

            $completedInterviews = InterviewSession::where('fellow_id', $fellow->id)
                ->where('curriculum_activity_id', $activity->id)
                ->where('status', 'completed')
                ->get();

            $bestScore = $completedInterviews->max(fn($i) => $i->overall_score ?? $i->score ?? 0) ?? 0;

            if ($completedInterviews->count() < $requiredCount) {
                throw new \Exception(
                    "You need to complete {$requiredCount} interview session(s). " .
                    "Completed: {$completedInterviews->count()}"
                );
            }

            if ($bestScore < $minScore) {
                throw new \Exception(
                    "Your best interview score ({$bestScore}%) doesn't meet the minimum required ({$minScore}%). " .
                    "Try another interview session to improve your score."
                );
            }

            // Auto-fill evidence with interview results
            $data['evidence_text'] = ($data['evidence_text'] ?? '') .
                "\n\n[Auto-generated] Mock interview completed. " .
                "Best score: {$bestScore}%. Sessions: {$completedInterviews->count()}/{$requiredCount}.";
        }

        return DB::transaction(function () use ($progress, $data, $activity, $fellow) {
            $notes = $data['reflection'] ?? $data['submission_notes'] ?? null;
            unset($data['reflection'], $data['submission_notes']);

            $progress->submit($data, $notes);

            // If it requires peer review, create peer review requests for pod members
            if ($activity->requires_peer_review) {
                $pod = $fellow->activeMentorshipPod();
                $podMembers = collect();
                
                if ($pod) {
                    $podMembers = $pod->activeMembers()
                        ->where('fellow_id', '!=', $fellow->id)
                        ->get();
                }
                
                if ($podMembers->isEmpty()) {
                    // Bypass automatically if no pod members available
                    $progress->bypassPeerReview('No active pod members available for peer review.');
                } else {
                    foreach ($podMembers as $member) {
                        \App\Models\FellowActivityPeerReview::create([
                            'progress_id' => $progress->id,
                            'reviewer_id' => $member->fellow_id,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            return $progress->fresh();
        });
    }

    /**
     * Complete peer review for a submission.
     */
    public function completePeerReview(
        User $reviewer,
        string $progressId,
        int $rating,
        ?string $feedback = null
    ): FellowCurriculumProgress {
        $progress = FellowCurriculumProgress::findOrFail($progressId);

        if ($progress->status !== CurriculumStatus::PEER_REVIEW) {
            throw new \Exception('This submission is not awaiting peer review.');
        }

        // Verify reviewer is assigned to this
        $review = $progress->peerReviews()->where('reviewer_id', $reviewer->id)->first();

        if (!$review) {
            throw new \Exception('You are not assigned to peer review this submission.');
        }

        if ($review->status !== 'pending') {
            throw new \Exception('You have already completed or bypassed this peer review.');
        }

        return DB::transaction(function () use ($progress, $reviewer, $rating, $feedback) {
            $progress->completePeerReview($reviewer, $feedback ?? '', $rating);
            return $progress->fresh();
        });
    }

    /**
     * Review and approve/reject a submission (admin/mentor).
     */
    public function reviewSubmission(
        User $reviewer,
        string $progressId,
        string $decision,
        array $data = []
    ): FellowCurriculumProgress {
        $progress = FellowCurriculumProgress::with(['curriculumActivity', 'fellow'])->findOrFail($progressId);

        if (!in_array($progress->status, [CurriculumStatus::UNDER_REVIEW, CurriculumStatus::SUBMITTED])) {
            throw new \Exception('This submission is not pending review.');
        }

        return DB::transaction(function () use ($progress, $reviewer, $decision, $data) {
            if ($decision === 'approve') {
                $points = $data['points'] ?? $progress->curriculumActivity->points;
                $rubricScores = $data['rubric_scores'] ?? null;

                $progress->approve($reviewer, $rubricScores ?? [], $data['feedback'] ?? '', $points);

                // Check for milestone completion
                $this->checkMilestoneCompletion($progress->fellow, $progress->curriculumActivity);

                // Update streak
                $track = $progress->curriculumActivity->track;
                if ($track) {
                    $this->streakService->recordCompletion($progress->fellow, $track);
                }

                // Unlock dependent activities
                $this->unlockDependentActivities($progress->fellow, $progress->curriculumActivity);

                // Recalculate Career Capital
                $this->recalculateCareerCapital($progress->fellow, $progress->curriculumActivity->track);

            } elseif ($decision === 'reject') {
                $progress->reject($reviewer, $data['feedback'] ?? 'Submission does not meet requirements.');
            }

            return $progress->fresh();
        });
    }

    /**
     * Check if a milestone has been fully completed and award badge.
     */
    protected function checkMilestoneCompletion(User $fellow, TrackCurriculumActivity $activity): void
    {
        $milestone = $activity->milestone;
        if (!$milestone) {
            return;
        }

        if ($milestone->isCompletedBy($fellow)) {
            // Award milestone badge if not already awarded
            if (!$this->curriculumRepository->hasBadge($fellow, BadgeType::MILESTONE->value, $milestone->id)) {
                FellowBadge::createMilestoneBadge($fellow, $milestone);

                Log::info("Milestone badge awarded", [
                    'fellow_id' => $fellow->id,
                    'milestone_id' => $milestone->id,
                    'milestone_title' => $milestone->title,
                ]);
            }

            // Unlock the next milestone
            $this->unlockNextMilestone($fellow, $milestone);

            // Check for full track completion
            $this->checkTrackCompletion($fellow, $milestone->track);
        }
    }

    /**
     * Unlock the next milestone's activities for a fellow.
     */
    protected function unlockNextMilestone(User $fellow, TrackMilestone $completedMilestone): void
    {
        $dependentMilestones = TrackMilestone::where('unlock_after_milestone_id', $completedMilestone->id)
            ->where('is_active', true)
            ->get();

        foreach ($dependentMilestones as $nextMilestone) {
            foreach ($nextMilestone->curriculumActivities as $activity) {
                if ($activity->prerequisitesMet($fellow)) {
                    $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                        ->where('curriculum_activity_id', $activity->id)
                        ->first();

                    if ($progress && $progress->status === CurriculumStatus::LOCKED) {
                        $progress->update([
                            'status' => CurriculumStatus::AVAILABLE,
                            'deadline_at' => $activity->calculateDeadlineFor($fellow),
                            'grace_deadline_at' => $activity->calculateGraceDeadlineFor($fellow),
                        ]);
                    } elseif (!$progress) {
                        $this->curriculumRepository->getOrCreateProgress($fellow, $activity);
                    }
                }
            }
        }
    }

    /**
     * Unlock activities that depend on a completed activity.
     */
    public function unlockDependentActivities(User $fellow, TrackCurriculumActivity $completedActivity): void
    {
        $dependentActivities = collect();

        // 1. Chain children
        $dependentActivities = $dependentActivities->merge($completedActivity->chainChildren);

        // 2. Next sequential sibling
        $nextSequential = TrackCurriculumActivity::where('milestone_id', $completedActivity->milestone_id)
            ->where('sequence_order', '>', $completedActivity->sequence_order)
            ->where('is_active', true)
            ->orderBy('sequence_order')
            ->first();

        if ($nextSequential && $nextSequential->is_sequential) {
            $dependentActivities->push($nextSequential);
        }

        // 3. Explicit prerequisites
        $prereqActivities = TrackCurriculumActivity::whereJsonContains('prerequisites', $completedActivity->id)
            ->where('is_active', true)
            ->get();

        $dependentActivities = $dependentActivities->merge($prereqActivities);

        foreach ($dependentActivities->unique('id') as $child) {
            if ($child->prerequisitesMet($fellow)) {
                $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                    ->where('curriculum_activity_id', $child->id)
                    ->first();

                if ($progress && $progress->status === CurriculumStatus::LOCKED) {
                    $progress->update([
                        'status' => CurriculumStatus::AVAILABLE,
                        'deadline_at' => $child->calculateDeadlineFor($fellow),
                        'grace_deadline_at' => $child->calculateGraceDeadlineFor($fellow),
                    ]);
                }
            }
        }
    }

    /**
     * Check if fellow has completed the entire track curriculum.
     */
    protected function checkTrackCompletion(User $fellow, Track $track): void
    {
        $requiredActivities = TrackCurriculumActivity::where('track_id', $track->id)
            ->where('is_active', true)
            ->where('is_required', true)
            ->count();

        $completedRequired = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->whereHas('curriculumActivity', function ($q) use ($track) {
                $q->where('track_id', $track->id)
                    ->where('is_required', true);
            })
            ->count();

        if ($requiredActivities > 0 && $completedRequired >= $requiredActivities) {
            // Award track completion badge
            if (!$this->curriculumRepository->hasBadge($fellow, BadgeType::TRACK_COMPLETION->value, $track->id)) {
                FellowBadge::createTrackCompletionBadge($fellow, $track);

                Log::info("Track completion badge awarded", [
                    'fellow_id' => $fellow->id,
                    'track_id' => $track->id,
                ]);
            }
        }
    }

    /**
     * Recalculate Career Capital score after curriculum completion.
     */
    protected function recalculateCareerCapital(User $fellow, Track $track): void
    {
        try {
            $this->calculator->updateScore($fellow, $track);
        } catch (\Exception $e) {
            Log::warning("Failed to recalculate Career Capital", [
                'fellow_id' => $fellow->id,
                'track_id' => $track->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ==========================================
    // FELLOW DASHBOARD DATA
    // ==========================================

    /**
     * Get complete curriculum dashboard data for a fellow.
     */
    public function getFellowCurriculumDashboard(User $fellow, Track $track): array
    {
        $milestones = $this->curriculumRepository->getMilestonesForTrack($track);
        $progress = $this->curriculumRepository->getFellowProgress($fellow, $track);
        $stats = $this->curriculumRepository->getFellowCurriculumStats($fellow, $track);
        $streak = $this->curriculumRepository->getOrCreateStreak($fellow, $track);
        $badges = $this->curriculumRepository->getFellowBadges($fellow);

        // Map progress to activities for easy lookup
        $progressMap = $progress->keyBy('curriculum_activity_id');

        // Build milestone data with progress info
        $milestonesWithProgress = $milestones->map(function ($milestone) use ($fellow, $progressMap) {
            $activities = $milestone->curriculumActivities->map(function ($activity) use ($progressMap) {
                $activityProgress = $progressMap->get($activity->id);
                return [
                    'activity' => $activity,
                    'progress' => $activityProgress,
                    'status' => $activityProgress?->status ?? CurriculumStatus::LOCKED,
                ];
            });

            return [
                'milestone' => $milestone,
                'activities' => $activities,
                'is_unlocked' => $milestone->isUnlockedFor($fellow),
                'is_completed' => $milestone->isCompletedBy($fellow),
                'completion_pct' => $milestone->getCompletionPercentage($fellow),
            ];
        });

        return [
            'milestones' => $milestones,
            'milestonesWithProgress' => $milestonesWithProgress,
            'progress' => $progress,
            'stats' => $stats,
            'totalPoints' => $stats['total_points'] ?? 0,
            'streak' => $streak,
            'badges' => $badges,
            'track' => $track,
        ];
    }

    /**
     * Get pending review items for a mentor/admin.
     */
    public function getReviews(int $perPage = 15, ?Track $track = null, string $statusFilter = 'pending'): LengthAwarePaginator
    {
        return $this->curriculumRepository->getReviews($perPage, $track, $statusFilter);
    }

    /**
     * Get peer review assignments for a fellow.
     */
    public function getPeerReviewsForFellow(User $fellow, ?Track $track = null): Collection
    {
        return $this->curriculumRepository->getPendingPeerReviews($fellow, $track);
    }

    /**
     * Get curriculum analytics for admin dashboard.
     */
    public function getAdminCurriculumAnalytics(Track $track): array
    {
        $milestones = $this->curriculumRepository->getMilestonesForTrack($track);
        $overdue = $this->curriculumRepository->getOverdueProgress($track);
        $completionRate = $this->curriculumRepository->getTrackCompletionRate($track);
        $rankings = $this->curriculumRepository->getCurriculumRankings($track);
        $streakLeaderboard = $this->curriculumRepository->getStreakLeaderboard($track);
        $recentCompletions = $this->curriculumRepository->getRecentCompletions(10);

        // Per-milestone stats
        $milestoneStats = $milestones->map(function ($milestone) {
            return [
                'milestone' => $milestone,
                'avg_completion_time' => $this->curriculumRepository->getAverageCompletionTime($milestone->id),
                'activity_count' => $milestone->curriculumActivities->count(),
            ];
        });

        return [
            'track' => $track,
            'milestones' => $milestoneStats,
            'overdue_count' => $overdue->count(),
            'overdue_items' => $overdue->take(20),
            'completion_rate' => $completionRate,
            'rankings' => $rankings,
            'streak_leaderboard' => $streakLeaderboard,
            'recent_completions' => $recentCompletions,
        ];
    }

    /**
     * Process overdue activities (called by scheduler).
     */
    public function processOverdueActivities(): int
    {
        $overdue = $this->curriculumRepository->getOverdueProgress();
        $processed = 0;

        foreach ($overdue as $progress) {
            if ($progress->grace_deadline_at && now()->greaterThan($progress->grace_deadline_at)) {
                $progress->markOverdue();
                $processed++;
            } elseif ($progress->deadline_at && now()->greaterThan($progress->deadline_at)) {
                // Within grace period — just log for now
                Log::info("Activity past deadline, in grace period", [
                    'progress_id' => $progress->id,
                    'fellow_id' => $progress->fellow_id,
                    'deadline' => $progress->deadline_at,
                    'grace_deadline' => $progress->grace_deadline_at,
                ]);
            }
        }

        return $processed;
    }
}
