<?php

namespace App\Services;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Models\AdminSetting;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\Track;
use App\Models\User;
use App\Models\WeeklyProgress;
use App\Repositories\Contracts\InterviewRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Interview Service
 * 
 * Handles all business logic related to interview sessions:
 * - Scheduling AI and human interviews
 * - Managing interview lifecycle
 * - Scoring and feedback
 * - Weekly limits enforcement
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class InterviewService
{
    public function __construct(
        protected InterviewRepositoryInterface $interviewRepository,
        protected CareerCapitalCalculator $calculator,
        protected ?AIInterviewService $aiService = null
    ) {
        $this->aiService = $aiService ?? app(AIInterviewService::class);
    }

    /**
     * Schedule a new interview session.
     */
    public function schedule(
        User $fellow,
        Track $track,
        InterviewType $type,
        InterviewMode $mode,
        array $options = []
    ): InterviewSession {
        // Validate fellow is enrolled in track
        if (!$fellow->isEnrolledIn($track)) {
            throw new \Exception("Fellow is not enrolled in this track.");
        }

        // Check weekly limits
        if (!$this->canSchedule($fellow, $mode)) {
            $limit = $this->getWeeklyLimit($mode);
            throw new \Exception("Weekly {$mode->label()} interview limit ({$limit}) reached.");
        }

        // Create the interview session
        $interview = InterviewSession::create([
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'type' => $type,
            'mode' => $mode,
            'status' => isset($options['scheduled_at']) 
                ? InterviewStatus::SCHEDULED 
                : InterviewStatus::PENDING,
            'mentor_id' => $options['mentor_id'] ?? null,
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'duration_minutes' => $options['duration_minutes'] ?? $type->defaultDuration(),
            'difficulty_level' => $options['difficulty_level'] ?? 'intermediate',
            'notes' => $options['notes'] ?? null,
        ]);

        // Send notification if scheduled for later
        if ($interview->scheduled_at) {
            Notification::send(
                $fellow,
                Notification::TYPE_INTERVIEW_SCHEDULED,
                'Interview Scheduled',
                "Your {$type->label()} interview is scheduled for " . 
                    $interview->scheduled_at->format('M j, Y \a\t g:i A'),
                [
                    'action_url' => route('interviews.show', $interview),
                    'action_label' => 'View Details',
                    'data' => ['interview_id' => $interview->id],
                ]
            );
        }

        return $interview;
    }

    /**
     * Start an interview session (for AI interviews).
     */
    public function start(InterviewSession $interview): InterviewSession
    {
        if ($interview->status !== InterviewStatus::PENDING && 
            $interview->status !== InterviewStatus::SCHEDULED) {
            throw new \Exception("Interview cannot be started in its current state.");
        }

        $interview->update([
            'status' => InterviewStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        return $interview->fresh();
    }

    /**
     * Complete an interview with scores.
     */
    public function complete(
        InterviewSession $interview,
        array $scores,
        ?string $feedback = null,
        array $communicationMetrics = []
    ): InterviewSession {
        return DB::transaction(function () use ($interview, $scores, $feedback, $communicationMetrics) {
            // Calculate overall score from rubric scores
            $overallScore = $this->calculateOverallScore($scores);

            // Update interview
            $interview->update([
                'status' => InterviewStatus::COMPLETED,
                'completed_at' => now(),
                'rubric_scores' => $scores,
                'score' => $overallScore,
                'overall_score' => $overallScore,
                'feedback' => $feedback,
                'filler_word_count' => $communicationMetrics['filler_word_count'] ?? null,
                'speaking_pace_wpm' => $communicationMetrics['speaking_pace_wpm'] ?? null,
                'confidence_score' => $communicationMetrics['confidence_score'] ?? null,
            ]);

            // Update weekly progress
            $this->updateWeeklyProgress($interview);

            // Recalculate Career Capital score
            $this->calculator->updateScore($interview->fellow, $interview->track);

            // If this interview is linked to a curriculum activity, handle auto-progression
            if ($interview->isLinkedToCurriculum()) {
                try {
                    $curriculumService = app(CurriculumService::class);
                    $curriculumService->handleInterviewCompletion($interview);
                } catch (\Exception $e) {
                    Log::warning("Failed to handle curriculum interview completion", [
                        'interview_id' => $interview->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Send completion notification
            Notification::send(
                $interview->fellow,
                Notification::TYPE_INTERVIEW_COMPLETED,
                'Interview Completed',
                "Your {$interview->type->label()} interview has been scored: {$overallScore}%",
                [
                    'action_url' => $interview->isLinkedToCurriculum()
                        ? route('curriculum.activity.show', $interview->curriculum_activity_id)
                        : route('interviews.show', $interview),
                    'action_label' => $interview->isLinkedToCurriculum() ? 'View Activity' : 'View Results',
                    'data' => [
                        'interview_id' => $interview->id,
                        'score' => $overallScore,
                        'curriculum_linked' => $interview->isLinkedToCurriculum(),
                    ],
                ]
            );

            return $interview->fresh();
        });
    }

    /**
     * Calculate overall score from rubric scores.
     */
    protected function calculateOverallScore(array $scores): float
    {
        if (empty($scores)) {
            return 0;
        }

        // Get rubric criteria weights
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($scores as $criterion => $scoreData) {
            $weight = $scoreData['weight'] ?? 1;
            $score = $scoreData['score'] ?? $scoreData;
            
            $weightedSum += $score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 1) : 0;
    }

    /**
     * Update weekly progress for interview completion.
     */
    protected function updateWeeklyProgress(InterviewSession $interview): void
    {
        $progress = WeeklyProgress::getOrCreateForCurrentWeek(
            $interview->fellow,
            $interview->track
        );

        // Interviews contribute to the BUILD pillar
        $progress->addActivity(
            'build',
            $interview->id,
            (int) ($interview->overall_score / 10) // Convert to points
        );
    }

    /**
     * Cancel an interview.
     */
    public function cancel(InterviewSession $interview, string $reason): InterviewSession
    {
        if (!in_array($interview->status, [
            InterviewStatus::PENDING,
            InterviewStatus::SCHEDULED,
        ])) {
            throw new \Exception("Cannot cancel interview in its current state.");
        }

        $interview->update([
            'status' => InterviewStatus::CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        // Notify mentor if human interview
        if ($interview->mentor_id && $interview->mentor) {
            Notification::send(
                $interview->mentor,
                Notification::TYPE_SYSTEM_ANNOUNCEMENT,
                'Interview Cancelled',
                "Interview with {$interview->fellow->name} has been cancelled: {$reason}",
            );
        }

        return $interview->fresh();
    }

    /**
     * Reschedule an interview.
     */
    public function reschedule(
        InterviewSession $interview,
        \DateTime $newTime,
        ?string $reason = null
    ): InterviewSession {
        $oldTime = $interview->scheduled_at;

        $interview->update([
            'scheduled_at' => $newTime,
            'status' => InterviewStatus::SCHEDULED,
            'notes' => $interview->notes . "\n[Rescheduled] " . ($reason ?? 'No reason provided'),
        ]);

        // Notify fellow
        Notification::send(
            $interview->fellow,
            Notification::TYPE_INTERVIEW_SCHEDULED,
            'Interview Rescheduled',
            "Your interview has been moved to " . $newTime->format('M j, Y \a\t g:i A'),
        );

        return $interview->fresh();
    }

    /**
     * Check if fellow can schedule more interviews this week.
     */
    public function canSchedule(User $fellow, InterviewMode $mode): bool
    {
        $currentCount = $this->getWeeklyCount($fellow, $mode);
        $limit = $this->getWeeklyLimit($mode);

        return $currentCount < $limit;
    }

    /**
     * Get weekly interview count for a fellow.
     */
    public function getWeeklyCount(User $fellow, InterviewMode $mode): int
    {
        return $fellow->interviewSessions()
            ->where('mode', $mode)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->count();
    }

    /**
     * Get weekly limit for an interview mode.
     */
    public function getWeeklyLimit(InterviewMode $mode): int
    {
        return $mode === InterviewMode::AI
            ? AdminSetting::get('weekly_ai_interviews_limit', 5)
            : AdminSetting::get('weekly_human_interviews_limit', 2);
    }

    /**
     * Get remaining interviews for this week.
     */
    public function getRemainingThisWeek(User $fellow): array
    {
        return [
            'ai' => [
                'used' => $this->getWeeklyCount($fellow, InterviewMode::AI),
                'limit' => $this->getWeeklyLimit(InterviewMode::AI),
                'remaining' => max(0, $this->getWeeklyLimit(InterviewMode::AI) - 
                    $this->getWeeklyCount($fellow, InterviewMode::AI)),
            ],
            'human' => [
                'used' => $this->getWeeklyCount($fellow, InterviewMode::HUMAN),
                'limit' => $this->getWeeklyLimit(InterviewMode::HUMAN),
                'remaining' => max(0, $this->getWeeklyLimit(InterviewMode::HUMAN) - 
                    $this->getWeeklyCount($fellow, InterviewMode::HUMAN)),
            ],
        ];
    }

    /**
     * Get interview statistics for a fellow.
     */
    public function getFellowStatistics(User $fellow): array
    {
        $interviews = $fellow->interviewSessions;
        $completed = $interviews->where('status', InterviewStatus::COMPLETED);

        return [
            'total' => $interviews->count(),
            'completed' => $completed->count(),
            'average_score' => round($completed->avg('overall_score') ?? 0, 1),
            'best_score' => $completed->max('overall_score') ?? 0,
            'worst_score' => $completed->min('overall_score') ?? 0,
            'by_type' => $this->getTypeBreakdown($completed),
            'score_trend' => $this->getScoreTrend($fellow),
            'this_week' => $this->getRemainingThisWeek($fellow),
            'strengths' => $this->analyzeStrengths($completed),
            'areas_to_improve' => $this->analyzeWeaknesses($completed),
        ];
    }

    /**
     * Get interview breakdown by type.
     */
    protected function getTypeBreakdown($interviews): array
    {
        $breakdown = [];

        foreach (InterviewType::cases() as $type) {
            $typeInterviews = $interviews->where('type', $type);
            
            if ($typeInterviews->isNotEmpty()) {
                $breakdown[$type->value] = [
                    'label' => $type->label(),
                    'count' => $typeInterviews->count(),
                    'average' => round($typeInterviews->avg('overall_score') ?? 0, 1),
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Get score trend (last 10 interviews).
     */
    protected function getScoreTrend(User $fellow): array
    {
        return $fellow->interviewSessions()
            ->completed()
            ->orderBy('completed_at')
            ->limit(10)
            ->get()
            ->map(fn ($i) => [
                'date' => $i->completed_at->format('M d'),
                'score' => $i->overall_score,
                'type' => $i->type->label(),
            ])
            ->toArray();
    }

    /**
     * Analyze fellow's interview strengths.
     */
    protected function analyzeStrengths($interviews): array
    {
        $criteriaScores = [];

        foreach ($interviews as $interview) {
            if (empty($interview->rubric_scores)) continue;

            foreach ($interview->rubric_scores as $criterion => $scoreData) {
                $score = $scoreData['score'] ?? $scoreData;
                
                if (!isset($criteriaScores[$criterion])) {
                    $criteriaScores[$criterion] = ['total' => 0, 'count' => 0];
                }
                
                $criteriaScores[$criterion]['total'] += $score;
                $criteriaScores[$criterion]['count']++;
            }
        }

        // Calculate averages and find top 3
        $averages = [];
        foreach ($criteriaScores as $criterion => $data) {
            $averages[$criterion] = $data['count'] > 0 
                ? $data['total'] / $data['count'] 
                : 0;
        }

        arsort($averages);
        
        return array_slice(array_keys($averages), 0, 3);
    }

    /**
     * Analyze areas needing improvement.
     */
    protected function analyzeWeaknesses($interviews): array
    {
        $criteriaScores = [];

        foreach ($interviews as $interview) {
            if (empty($interview->rubric_scores)) continue;

            foreach ($interview->rubric_scores as $criterion => $scoreData) {
                $score = $scoreData['score'] ?? $scoreData;
                
                if (!isset($criteriaScores[$criterion])) {
                    $criteriaScores[$criterion] = ['total' => 0, 'count' => 0];
                }
                
                $criteriaScores[$criterion]['total'] += $score;
                $criteriaScores[$criterion]['count']++;
            }
        }

        // Calculate averages and find bottom 3
        $averages = [];
        foreach ($criteriaScores as $criterion => $data) {
            $averages[$criterion] = $data['count'] > 0 
                ? $data['total'] / $data['count'] 
                : 0;
        }

        asort($averages);
        
        // Only return criteria with scores below 70%
        return array_filter(
            array_slice(array_keys($averages), 0, 3),
            fn ($criterion) => $averages[$criterion] < 70
        );
    }

    /**
     * Generate AI interview questions based on type and difficulty.
     */
    public function generateQuestions(
        InterviewType $type,
        string $difficulty = 'intermediate',
        ?Track $track = null,
        ?User $fellow = null
    ): array {
        return $this->aiService->generateQuestions($type, $difficulty, $track, $fellow);
    }

    /**
     * Evaluate a candidate's interview response.
     */
    public function evaluateResponse(
        string $question,
        string $response,
        InterviewType $type,
        string $difficulty = 'intermediate'
    ): array {
        return $this->aiService->evaluateResponse($question, $response, $type, $difficulty);
    }

    /**
     * Generate a complete interview session with personalized questions.
     */
    public function generateInterviewSession(
        User $fellow,
        Track $track,
        InterviewType $type,
        string $difficulty = 'intermediate',
        int $questionCount = 5
    ): array {
        return $this->aiService->generateInterviewSession($fellow, $track, $type, $difficulty, $questionCount);
    }

    /**
     * Get upcoming interviews for dashboard.
     */
    public function getUpcoming(User $fellow, int $limit = 5): array
    {
        return $fellow->interviewSessions()
            ->whereIn('status', [InterviewStatus::PENDING, InterviewStatus::SCHEDULED])
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '>=', now());
            })
            ->with(['track', 'mentor'])
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'type' => $i->type->label(),
                'mode' => $i->mode->label(),
                'track' => $i->track->name,
                'scheduled_at' => $i->scheduled_at?->format('M j, Y g:i A'),
                'mentor' => $i->mentor?->name,
            ])
            ->toArray();
    }

    /**
     * Send interview reminders.
     */
    public function sendReminders(): int
    {
        $interviews = InterviewSession::scheduled()
            ->whereBetween('scheduled_at', [now(), now()->addHours(24)])
            ->whereNull('reminder_sent_at')
            ->get();

        $count = 0;

        foreach ($interviews as $interview) {
            Notification::send(
                $interview->fellow,
                Notification::TYPE_INTERVIEW_REMINDER,
                'Interview Reminder',
                "Your {$interview->type->label()} interview is coming up " .
                    $interview->scheduled_at->diffForHumans(),
                ['priority' => Notification::PRIORITY_HIGH]
            );

            $interview->update(['reminder_sent_at' => now()]);
            $count++;
        }

        return $count;
    }
}
