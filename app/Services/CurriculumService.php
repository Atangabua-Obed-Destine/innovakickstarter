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
                    // Role-specific work in a shared chapter (pathway tracks) only for fellows it applies to.
                    if (!app(PathwayService::class)->activityApplies($fellow, $activity)) {
                        continue;
                    }
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
        app(FellowActivityService::class)->record($fellow, 'start');

        // For mock_interview activities, create an interview session
        if ($activity->requiresInterviewSession()) {
            $interview = $this->createLinkedInterview($fellow, $activity, $progress);
            return ['progress' => $progress, 'interview' => $interview];
        }

        return $progress;
    }

    /**
     * Create the interview for a mock_interview activity. AI interviews are planned by the
     * interview engine and open in the room; human interviews wait (PENDING) until an admin
     * assigns a mentor, a time and a meeting link.
     */
    public function createLinkedInterview(
        User $fellow,
        TrackCurriculumActivity $activity,
        FellowCurriculumProgress $progress
    ): InterviewSession {
        $config = $activity->interview_config ?? [];
        $mode = InterviewMode::tryFrom((string) ($config['mode'] ?? 'ai'));

        if (!in_array($mode, [InterviewMode::HUMAN, InterviewMode::PEER], true)) {
            $interview = app(\App\Services\Interview\InterviewEngine::class)->createForActivity($fellow, $activity, $progress);
            $progress->update(['linked_interview_id' => $interview->id]);

            return $interview;
        }

        $type = InterviewType::tryFrom((string) ($config['type'] ?? '')) ?? InterviewType::TECHNICAL_CODING;
        $interview = InterviewSession::create([
            'fellow_id' => $fellow->id,
            'track_id' => $activity->track_id,
            'type' => $type,
            'mode' => $mode,
            'status' => \App\Enums\InterviewStatus::PENDING,
            'scheduled_at' => null,
            'target_duration' => (int) ($config['minutes'] ?? 45),
            'difficulty_level' => $config['difficulty'] ?? 'intermediate',
            'title' => $activity->title,
            'description' => 'Interview with a mentor for: ' . $activity->title,
            'curriculum_activity_id' => $activity->id,
            'curriculum_progress_id' => $progress->id,
        ]);
        $progress->update(['linked_interview_id' => $interview->id]);

        foreach (app(InterviewService::class)->team()->filter(fn ($u) => $u->isAdmin()) as $admin) {
            try {
                \App\Models\Notification::send($admin, \App\Models\Notification::TYPE_INTERVIEW_SCHEDULED, 'An interview needs a mentor and a time',
                    "{$fellow->name} is ready for \"{$activity->title}\". Assign a mentor, a time and a meeting link.",
                    ['action_url' => route('admin.interviews.show', $interview), 'action_label' => 'Schedule it', 'data' => ['interview_id' => $interview->id]]);
            } catch (\Throwable $e) {
                Log::warning('Interview scheduling alert not sent', ['error' => $e->getMessage()]);
            }
        }

        return $interview;
    }

    /** Hours a fellow waits after a failed interview before a retake (with new questions). */
    public const INTERVIEW_RETAKE_HOURS = 24;

    /** Failed attempts after which the team is told and the fellow is asked to book a mentor. */
    public const INTERVIEW_FAILS_BEFORE_MENTOR = 3;

    /** Pass mark of a mock interview activity: interview_config.min_score, else the blueprint chapter's, else 70. */
    public function interviewPassMark(TrackCurriculumActivity $activity): int
    {
        $config = (array) ($activity->interview_config ?? []);
        if (isset($config['min_score']) && is_numeric($config['min_score'])) {
            return (int) $config['min_score'];
        }
        $blueprints = app(\App\Services\Interview\BlueprintService::class);
        $chapter = $blueprints->chapter($blueprints->forTrack($activity->track), $activity->milestone, $activity);

        return !empty($chapter['matched']) ? (int) ($chapter['pass_mark'] ?? 70) : 70;
    }

    /**
     * Interviews that count for an activity: completed, scored, not practice. Abandoned,
     * cancelled and not-scored (scoring failed) sessions never count.
     */
    public function countedInterviews(int $fellowId, string $activityId, ?\Carbon\CarbonInterface $since = null): \Illuminate\Support\Collection
    {
        return InterviewSession::where('fellow_id', $fellowId)
            ->where('curriculum_activity_id', $activityId)
            ->when($since, fn ($q) => $q->where('completed_at', '>', $since))
            ->where('status', \App\Enums\InterviewStatus::COMPLETED->value)
            ->whereNotNull('score')
            ->where(fn ($q) => $q->where('is_practice', false)->orWhereNull('is_practice'))
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * Where a fellow stands on a mock interview activity: attempts, best score, whether a
     * retake is allowed yet, and whether they should book a mentor session.
     */
    public function interviewStanding(User $fellow, TrackCurriculumActivity $activity): array
    {
        $pass = $this->interviewPassMark($activity);
        $required = max(1, (int) ($activity->interview_config['count'] ?? 1));
        // After a reviewer sends the activity back, only interviews taken since then count.
        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)->where('curriculum_activity_id', $activity->id)->first();
        $since = $progress?->status === CurriculumStatus::REJECTED ? $progress->reviewed_at : null;
        $counted = $this->countedInterviews($fellow->id, $activity->id, $since);
        $passedCount = $counted->filter(fn ($i) => (float) $i->score >= $pass)->count();
        $failed = $counted->filter(fn ($i) => (float) $i->score < $pass);
        $last = $counted->first();
        $retakeAt = $last && (float) $last->score < $pass && $last->completed_at
            ? $last->completed_at->copy()->addHours(self::INTERVIEW_RETAKE_HOURS) : null;

        return [
            'pass_mark' => $pass,
            'required' => $required,
            'counted' => $counted->count(),
            'passed_count' => $passedCount,
            'best_score' => $counted->max(fn ($i) => (float) $i->score),
            'passed' => $passedCount >= $required,
            'failed_count' => $failed->count(),
            'retake_at' => $retakeAt && $retakeAt->isFuture() ? $retakeAt : null,
            'needs_mentor' => $failed->count() >= self::INTERVIEW_FAILS_BEFORE_MENTOR,
        ];
    }

    /**
     * Launch another interview attempt for a curriculum activity. A failed attempt can be
     * retaken after 24 hours, with new questions.
     */
    public function launchInterviewAttempt(User $fellow, string $activityId): InterviewSession
    {
        $activity = TrackCurriculumActivity::with(['milestone', 'track'])->findOrFail($activityId);

        if (!$activity->requiresInterviewSession()) {
            throw new \Exception('This activity does not require an interview session.');
        }

        $progress = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->firstOrFail();

        if (!in_array($progress->status, [CurriculumStatus::IN_PROGRESS, CurriculumStatus::REJECTED, CurriculumStatus::OVERDUE], true)) {
            throw new \Exception("Cannot launch interview in current status: {$progress->status->label()}");
        }

        // Close an AI attempt that was left open too long, then refuse if one is still active.
        $engine = app(\App\Services\Interview\InterviewEngine::class);
        $active = InterviewSession::where('fellow_id', $fellow->id)
            ->where('curriculum_activity_id', $activity->id)
            ->whereIn('status', [
                \App\Enums\InterviewStatus::SCHEDULED->value,
                \App\Enums\InterviewStatus::IN_PROGRESS->value,
                \App\Enums\InterviewStatus::PENDING->value,
            ])
            ->get()
            ->reject(fn ($s) => $engine->isExpired($s) && $engine->expire($s));

        if ($active->isNotEmpty()) {
            throw new \Exception('You already have an active interview session. Complete or cancel it before starting a new one.');
        }

        $standing = $this->interviewStanding($fellow, $activity);
        if ($standing['passed']) {
            throw new \Exception('You already passed this interview.');
        }
        if ($standing['retake_at']) {
            throw new \Exception('You can retake this interview from ' . $standing['retake_at']->format('D j M, g:i A')
                . ' (24 hours after your last attempt), with new questions. Use the time to work on the next steps in your scorecard.');
        }

        return $this->createLinkedInterview($fellow, $activity, $progress);
    }

    /**
     * Called once a linked interview has its final 0-100 score. Pass (score >= the activity's
     * pass mark, enough counted sessions) submits the activity to the human review queue with
     * the interview as evidence. A third failed attempt alerts the team.
     */
    public function handleInterviewCompletion(InterviewSession $interview): void
    {
        if (!$interview->curriculum_progress_id || $interview->is_practice || $interview->score === null
            || $interview->status !== \App\Enums\InterviewStatus::COMPLETED) {
            return;
        }

        $alertTeam = false;
        DB::transaction(function () use ($interview, &$alertTeam) {
            $progress = FellowCurriculumProgress::lockForUpdate()->find($interview->curriculum_progress_id);
            $activity = $progress?->curriculumActivity;
            if (!$progress || !$activity) {
                return;
            }

            $standing = $this->interviewStanding($progress->fellow, $activity);
            $counted = $this->countedInterviews($progress->fellow_id, $activity->id,
                $progress->status === CurriculumStatus::REJECTED ? $progress->reviewed_at : null);
            $best = $counted->sortByDesc(fn ($i) => (float) $i->score)->first();
            $progress->update(['linked_interview_id' => $best?->id ?? $interview->id]);

            $passedThis = (float) $interview->score >= $standing['pass_mark'];
            if (!$passedThis && $standing['failed_count'] === self::INTERVIEW_FAILS_BEFORE_MENTOR) {
                $alertTeam = true;
            }

            if ($standing['passed'] && in_array($progress->status, [CurriculumStatus::IN_PROGRESS, CurriculumStatus::REJECTED, CurriculumStatus::OVERDUE], true)) {
                $score = round((float) $best->score, 1);
                $evidence = [
                    'evidence_text' => "Mock interview passed: {$score}/100 (pass mark {$standing['pass_mark']}). "
                        . 'Hire signal: ' . \App\Services\Interview\BlueprintService::signalLabel($best->hire_signal) . '. '
                        . 'Integrity: ' . ($best->integrity_level ?? 'n/a') . '. Sessions counted: ' . $standing['counted'] . '.',
                    'evidence_url' => route('admin.interviews.show', $best),
                    'interview' => [
                        'id' => $best->id,
                        'score' => $score,
                        'hire_signal' => $best->hire_signal,
                        'integrity_level' => $best->integrity_level,
                        'mode' => $best->mode?->value,
                        'attempts' => $standing['counted'],
                    ],
                ];
                $progress->submit($evidence, 'Submitted automatically after a passed mock interview. Review the transcript, scorecard and integrity report.');
                $progress->update(['score_awarded' => (int) round($score)]);

                Log::info('Curriculum interview passed and sent to review', [
                    'fellow_id' => $progress->fellow_id, 'activity_id' => $activity->id, 'interview_id' => $best->id, 'score' => $score,
                ]);
            }
        });

        if ($alertTeam) {
            $fellow = $interview->fellow;
            foreach (app(InterviewService::class)->team() as $member) {
                try {
                    \App\Models\Notification::send($member, \App\Models\Notification::TYPE_SYSTEM_ANNOUNCEMENT, 'A fellow failed a mock interview 3 times',
                        "{$fellow->name} has not passed \"" . ($interview->title ?: 'a mock interview') . '" after ' . self::INTERVIEW_FAILS_BEFORE_MENTOR . ' attempts. Please offer a mentor session.',
                        ['action_url' => $member->isAdmin() ? route('admin.interviews.show', $interview) : route('mentor.dashboard'),
                            'action_label' => 'Open', 'priority' => \App\Models\Notification::PRIORITY_HIGH, 'data' => ['interview_id' => $interview->id, 'fellow_id' => $fellow->id]]);
                } catch (\Throwable $e) {
                    Log::warning('Interview team alert not sent', ['error' => $e->getMessage()]);
                }
            }
            try {
                \App\Models\Notification::send($fellow, \App\Models\Notification::TYPE_SYSTEM_ANNOUNCEMENT, 'Book a session with a mentor',
                    'You have tried this interview 3 times. Book a session with a mentor: they will go through your scorecards with you before your next attempt.',
                    ['action_url' => route('help.index'), 'action_label' => 'Ask for a mentor session']);
            } catch (\Throwable $e) {
                Log::warning('Interview mentor advice not sent', ['error' => $e->getMessage()]);
            }
        }
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

        // For mock_interview activities, validate interview completion (scored, non-practice sessions only).
        if ($activity->requiresInterviewSession()) {
            $standing = $this->interviewStanding($fellow, $activity);

            if ($standing['passed_count'] < $standing['required']) {
                throw new \Exception(
                    "You need {$standing['required']} passed interview session(s) with at least {$standing['pass_mark']}/100. " .
                    "Passed so far: {$standing['passed_count']}."
                );
            }

            $data['evidence_text'] = ($data['evidence_text'] ?? '') .
                "\n\n[Auto-generated] Mock interview passed. Best score: " . round((float) $standing['best_score'], 1) . "/100. Sessions: {$standing['counted']}.";
        }

        if ($activity->requires_video && empty($data['video']['id'])) {
            throw new \Exception('This project needs your Show & Tell video: publish it on YouTube as Public and paste the link.');
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

            app(FellowActivityService::class)->record($fellow, 'submit');

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

        $reviewedAttempt = (int) ($progress->attempt_number ?: 1);

        return DB::transaction(function () use ($progress, $reviewer, $decision, $data, $reviewedAttempt) {
            if ($decision === 'approve') {
                // Never more than the activity is worth.
                $points = min((int) ($data['points'] ?? $progress->curriculumActivity->points), (int) $progress->curriculumActivity->points);
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

                // Share projects and Brand posts with the track's community feed
                app(TrackFeedService::class)->workApproved($progress->fresh(['curriculumActivity', 'fellow']));

                // A skill whose required activities are all approved earns a certificate
                app(SkillCertificateService::class)->checkAndAward($progress->fellow, $progress->curriculumActivity->track);

            } elseif ($decision === 'reject') {
                $progress->reject($reviewer, $data['feedback'] ?? 'Submission does not meet requirements.');
            }

            app(\App\Services\AiReview\AiReviewService::class)->recordHumanDecision(
                $progress->id,
                $reviewedAttempt,
                $reviewer,
                $decision,
                $decision === 'approve' ? (int) $progress->score_awarded : null,
            );

            return $progress->fresh();
        });
    }

    /**
     * Undo a review decision.
     */
    public function undoReview(string $progressId): FellowCurriculumProgress
    {
        $progress = FellowCurriculumProgress::with(['curriculumActivity', 'fellow'])->findOrFail($progressId);

        if (!in_array($progress->status, [CurriculumStatus::COMPLETED, CurriculumStatus::REJECTED])) {
            throw new \Exception('This submission is not in a reviewed state.');
        }

        return DB::transaction(function () use ($progress) {
            $progress->undoReview();

            // Recalculate Career Capital
            $this->recalculateCareerCapital($progress->fellow, $progress->curriculumActivity->track);

            // Send notification
            $notificationService = app()->make(\App\Services\NotificationService::class);
            $notificationService->send(
                $progress->fellow,
                \App\Models\Notification::TYPE_ACTIVITY_REVIEW_REVERTED,
                'Review Status Reverted',
                "Your review status for '{$progress->curriculumActivity->title}' was reverted by an admin. It is back under review."
            );

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
                $this->sendChapterDebrief($fellow, $milestone);
                app(TrackFeedService::class)->chapterCompleted($fellow, $milestone);

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
     * Story tracks: when a chapter (milestone) is completed, the fellow's
     * "manager" sends the chapter debrief and points to the next chapter.
     */
    protected function sendChapterDebrief(User $fellow, TrackMilestone $milestone): void
    {
        $experience = \App\Support\TrackExperience::for($milestone->track);
        $manager = $experience?->manager();
        $debrief = $milestone->story['debrief'] ?? null;
        if (!$manager || !$debrief) {
            return;
        }

        $next = TrackMilestone::where('track_id', $milestone->track_id)
            ->where('is_active', true)
            ->where('sequence_order', '>', $milestone->sequence_order)
            ->orderBy('sequence_order')
            ->first();
        $nextLine = $next ? ' Next: ' . ($next->story['chapter'] ?? $next->title) . '.' : '';

        \App\Models\Notification::send(
            $fellow,
            \App\Models\Notification::TYPE_ACHIEVEMENT,
            "Message from {$manager['name']}",
            trim($debrief) . $nextLine,
            ['action_url' => route('curriculum.index'), 'action_label' => 'Open your journey']
        );
    }

    /**
     * Unlock the next milestone's activities for a fellow.
     */
    protected function unlockNextMilestone(User $fellow, TrackMilestone $completedMilestone): void
    {
        $pathways = app(PathwayService::class);
        $dependentMilestones = $pathways->hasPathways($completedMilestone->track)
            // Pathway tracks: any chapter of this fellow's that is now unlocked (core, major or minor).
            ? $pathways->milestones($fellow, $completedMilestone->track)
                ->filter(fn ($m) => $m->sequence_order > $completedMilestone->sequence_order && $m->isUnlockedFor($fellow))
            : TrackMilestone::where('unlock_after_milestone_id', $completedMilestone->id)
                ->where('is_active', true)
                ->get();

        foreach ($dependentMilestones as $nextMilestone) {
            foreach ($nextMilestone->curriculumActivities as $activity) {
                if ($activity->prerequisitesMet($fellow) && $pathways->activityApplies($fellow, $activity)) {
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

                    if ($child->is_collaborative && !app(SquadService::class)->isSquadChapter($child->milestone)) {
                        app(ReviewPartnerService::class)->matchFellow($fellow, $child);
                    }
                }
            }
        }
    }

    /**
     * Check if fellow has completed the entire track curriculum.
     */
    protected function checkTrackCompletion(User $fellow, Track $track): void
    {
        // Core + the fellow's own pathway (minors and catch-up lessons don't count) for pathway tracks.
        $requiredIds = app(PathwayService::class)->requiredActivityIds($fellow, $track);
        $requiredActivities = $requiredIds->count();

        $completedRequired = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->where('status', CurriculumStatus::COMPLETED->value)
            ->whereIn('curriculum_activity_id', $requiredIds)
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

        // Pathway tracks: show the core, the fellow's pathway and minors; other pathways are offered separately.
        $pathways = app(PathwayService::class);
        $pathwayInfo = null;
        if ($pathways->hasPathways($track)) {
            $all = $milestones;
            $milestones = $all->each(fn ($m) => $m->setAttribute('pathway_role', $pathways->role($fellow, $m)))
                ->filter(fn ($m) => $m->pathway_role !== null)->values();
            $pathwayInfo = [
                'definitions' => $pathways->definitions($track),
                'major' => $pathways->major($fellow, $track),
                'minors' => $pathways->minors($fellow, $track),
                'core_complete' => $pathways->coreComplete($fellow, $track),
                'other_chapters' => $all->filter(fn ($m) => $m->pathway_role === null)->values(),
            ];
        }
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
            'pathwayInfo' => $pathwayInfo,
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
