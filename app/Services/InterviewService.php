<?php

namespace App\Services;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\User;
use App\Models\WeeklyProgress;
use App\Services\Interview\BlueprintService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Interview lifecycle outside the AI room: what happens once a session has a score
 * (weekly progress, Career Capital, curriculum review), human interviews (scheduled by
 * admins, scored by mentors) and cancellations.
 *
 * Practice sessions never touch Career Capital, weekly progress or the curriculum.
 */
class InterviewService
{
    public function __construct(protected CareerCapitalCalculator $calculator) {}

    /**
     * A session just received its 0-100 score (AI scoring, a mentor, or a reviewer).
     */
    public function recordScored(InterviewSession $interview): void
    {
        if ($interview->status !== InterviewStatus::COMPLETED || $interview->score === null) {
            return;
        }
        $fellow = $interview->fellow;
        $score = (float) $interview->score;

        if (!$interview->is_practice) {
            $this->safely('weekly progress', fn () => $this->updateWeeklyProgress($interview));
            $this->safely('career capital', fn () => $this->calculator->updateScore($fellow, $interview->track));
            if ($interview->isLinkedToCurriculum()) {
                $this->safely('curriculum', fn () => app(CurriculumService::class)->handleInterviewCompletion($interview->fresh()));
            }
        }

        $this->safely('notification', fn () => Notification::send(
            $fellow,
            Notification::TYPE_INTERVIEW_COMPLETED,
            $interview->is_practice ? 'Practice interview scored' : 'Interview scored',
            ($interview->title ?: 'Your mock interview') . ': ' . rtrim(rtrim(number_format($score, 1), '0'), '.') . '/100'
                . ($interview->hire_signal ? ' (' . BlueprintService::signalLabel($interview->hire_signal) . ')' : '') . '.',
            [
                'action_url' => route('interviews.show', $interview),
                'action_label' => 'See your scorecard',
                'data' => ['interview_id' => $interview->id, 'score' => $score],
            ]
        ));
    }

    protected function updateWeeklyProgress(InterviewSession $interview): void
    {
        if ($interview->is_practice) {
            return;
        }
        $progress = WeeklyProgress::getOrCreateForCurrentWeek($interview->fellow, $interview->track);
        $progress->addActivity('build', $interview->id, (int) round((float) $interview->score / 10));
    }

    // ------------------------------------------------------------------ human interviews

    /**
     * An admin assigns the mentor, the time and the meeting link: the session is scheduled.
     */
    public function scheduleHuman(InterviewSession $interview, User $mentor, \DateTimeInterface $at, ?string $meetingLink): InterviewSession
    {
        if (!in_array($interview->mode, [InterviewMode::HUMAN, InterviewMode::PEER], true)) {
            throw new \RuntimeException('Only human interviews are scheduled with a mentor.');
        }
        if (!in_array($interview->status, [InterviewStatus::PENDING, InterviewStatus::SCHEDULED], true)) {
            throw new \RuntimeException('This interview is no longer open for scheduling.');
        }

        $interview->update([
            'interviewer_id' => $mentor->id,
            'scheduled_at' => $at,
            'meeting_link' => $meetingLink,
            'status' => InterviewStatus::SCHEDULED,
        ]);

        $when = \Carbon\Carbon::instance($at)->format('D j M Y, g:i A');
        $this->safely('notification', fn () => Notification::send($interview->fellow, Notification::TYPE_INTERVIEW_SCHEDULED, 'Interview scheduled',
            "Your interview \"{$interview->title}\" with {$mentor->name} is on {$when}.",
            ['action_url' => route('interviews.show', $interview), 'action_label' => 'View details', 'data' => ['interview_id' => $interview->id]]));
        $this->safely('notification', fn () => Notification::send($mentor, Notification::TYPE_INTERVIEW_SCHEDULED, 'You have an interview to run',
            "Interview \"{$interview->title}\" with {$interview->fellow->name} on {$when}.",
            ['action_url' => route('mentor.interviews.review', $interview), 'action_label' => 'Open the interview', 'data' => ['interview_id' => $interview->id]]));

        return $interview->fresh();
    }

    /**
     * The mentor records four 1-10 scores and feedback. score = average x 10 (0-100);
     * overall_score is on the same 0-100 scale. Then the curriculum is updated.
     */
    public function completeHuman(InterviewSession $interview, User $mentor, array $data): InterviewSession
    {
        $interview = DB::transaction(function () use ($interview, $mentor, $data) {
            $locked = InterviewSession::lockForUpdate()->findOrFail($interview->id);
            if (!in_array($locked->status, [InterviewStatus::SCHEDULED, InterviewStatus::IN_PROGRESS], true)) {
                throw new \RuntimeException('This interview is not open: it was already completed, cancelled or marked as a no-show.');
            }

            $parts = ['technical_score', 'communication_score', 'problem_solving_score', 'overall_score'];
            $score = round(array_sum(array_map(fn ($k) => (int) $data[$k], $parts)) / 4 * 10, 1);
            $bp = app(BlueprintService::class)->forTrack($locked->track);
            $signal = app(BlueprintService::class)->hireSignal($bp, $score);
            $pass = (int) ($locked->curriculumActivity
                ? app(CurriculumService::class)->interviewPassMark($locked->curriculumActivity)
                : 70);

            $locked->forceFill([
                'status' => InterviewStatus::COMPLETED,
                'completed_at' => now(),
                'started_at' => $locked->started_at ?? $locked->scheduled_at,
                'score' => $score,
                'overall_score' => (int) round($score),
                'technical_score' => (int) $data['technical_score'],
                'communication_score' => (int) $data['communication_score'],
                'problem_solving_score' => (int) $data['problem_solving_score'],
                'rubric_scores' => [
                    'technical' => (int) $data['technical_score'] * 10,
                    'communication' => (int) $data['communication_score'] * 10,
                    'problem_solving' => (int) $data['problem_solving_score'] * 10,
                    'overall_impression' => (int) $data['overall_score'] * 10,
                ],
                'hire_signal' => $signal,
                'scoring_status' => 'done',
                'scorecard' => [
                    'version' => 1, 'by' => 'mentor', 'mentor' => $mentor->name, 'overall' => $score, 'hire_signal' => $signal,
                    'pass_mark' => $pass, 'passed' => $score >= $pass,
                    'dimensions' => collect(['technical_score' => 'Technical', 'communication_score' => 'Communication', 'problem_solving_score' => 'Problem solving', 'overall_score' => 'Overall impression'])
                        ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'score' => (int) $data[$key] * 10, 'out_of_10' => (int) $data[$key], 'evidence' => [], 'comment' => ''])->values()->all(),
                    'summary' => $data['feedback'],
                    'strengths' => array_values(array_filter(preg_split('/\r?\n/', (string) ($data['strengths'] ?? '')))),
                    'improvements' => array_values(array_filter(preg_split('/\r?\n/', (string) ($data['areas_for_improvement'] ?? '')))),
                    'scored_at' => now()->toIso8601String(),
                ],
                'feedback' => $data['feedback'],
                'strengths' => $data['strengths'] ?? null,
                'areas_for_improvement' => $data['areas_for_improvement'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'interviewer_notes' => $data['feedback'],
                'points_earned' => $locked->is_practice ? 0 : $locked->type->defaultHumanPoints(),
                'duration_minutes' => $locked->duration_minutes ?? $locked->target_duration,
            ])->save();

            return $locked;
        });

        $this->recordScored($interview->fresh());

        return $interview->fresh();
    }

    public function markNoShow(InterviewSession $interview, ?string $notes): InterviewSession
    {
        if (!in_array($interview->status, [InterviewStatus::SCHEDULED, InterviewStatus::IN_PROGRESS, InterviewStatus::PENDING], true)) {
            throw new \RuntimeException('Only an open interview can be marked as a no-show.');
        }
        $interview->update([
            'status' => InterviewStatus::NO_SHOW,
            'completed_at' => now(),
            'interviewer_notes' => $notes ?: 'Fellow did not attend the session.',
        ]);

        $this->safely('notification', fn () => Notification::send($interview->fellow, Notification::TYPE_INTERVIEW_COMPLETED, 'Missed interview',
            "You were marked absent for \"{$interview->title}\". You can request a new interview from the activity page.",
            ['action_url' => $interview->curriculum_activity_id ? route('curriculum.activity.show', $interview->curriculum_activity_id) : route('interviews.index')]));

        return $interview->fresh();
    }

    /** Start an old pending/scheduled AI session (sessions from the engine start in the room). */
    public function start(InterviewSession $interview): InterviewSession
    {
        if (!in_array($interview->status, [InterviewStatus::PENDING, InterviewStatus::SCHEDULED], true)) {
            throw new \RuntimeException('Interview cannot be started in its current state.');
        }
        $interview->update(['status' => InterviewStatus::IN_PROGRESS, 'started_at' => now()]);

        return $interview->fresh();
    }

    public function cancel(InterviewSession $interview, string $reason): InterviewSession
    {
        $cancellable = [InterviewStatus::PENDING, InterviewStatus::SCHEDULED];
        if ($interview->mode === InterviewMode::AI) {
            $cancellable[] = InterviewStatus::IN_PROGRESS;
        }
        if (!in_array($interview->status, $cancellable, true)) {
            throw new \RuntimeException('Cannot cancel interview in its current state.');
        }

        $interview->update(['status' => InterviewStatus::CANCELLED, 'cancellation_reason' => $reason]);

        if ($interview->interviewer) {
            $this->safely('notification', fn () => Notification::send($interview->interviewer, Notification::TYPE_SYSTEM_ANNOUNCEMENT, 'Interview cancelled',
                "The interview with {$interview->fellow->name} was cancelled: {$reason}", ['action_url' => route('mentor.interviews')]));
        }

        return $interview->fresh();
    }

    /** Admins and mentors (for alerts about a fellow). */
    public function team()
    {
        return User::whereIn('role', ['admin', 'mentor'])->where(fn ($q) => $q->whereNull('is_active')->orWhere('is_active', true))->get();
    }

    protected function safely(string $what, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::warning("Interview: {$what} not updated", ['error' => mb_substr($e->getMessage(), 0, 300)]);
        }
    }
}
