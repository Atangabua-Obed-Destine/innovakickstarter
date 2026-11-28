<?php

namespace App\Http\Controllers;

use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Services\Interview\InterviewEngine;
use App\Services\InterviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The fellow's Interviews page, results pages and practice interviews.
 * AI interviews run in the room (InterviewRoomController); human interviews are
 * scheduled by admins and scored by mentors. Fellows do not schedule interviews.
 */
class InterviewController extends Controller
{
    public function __construct(
        protected InterviewService $interviewService,
        protected InterviewEngine $engine,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $sessions = InterviewSession::where('fellow_id', $user->id)->with(['track', 'interviewer', 'curriculumActivity'])->latest()->get();

        foreach ($sessions as $s) {
            if ($this->engine->isExpired($s)) {
                $this->engine->expire($s);
            }
        }

        $counted = $sessions->filter(fn ($s) => $s->status === InterviewStatus::COMPLETED && $s->score !== null && !$s->is_practice);
        $track = $this->track($request);

        return view('fellow.interviews.index', [
            'active' => $sessions->filter(fn ($s) => in_array($s->status, [InterviewStatus::IN_PROGRESS, InterviewStatus::PENDING, InterviewStatus::SCHEDULED], true))->values(),
            'past' => $sessions->filter(fn ($s) => in_array($s->status, [InterviewStatus::COMPLETED, InterviewStatus::CANCELLED, InterviewStatus::NO_SHOW], true))->values(),
            'stats' => [
                'completed' => $counted->count(),
                'average' => $counted->isNotEmpty() ? round($counted->avg(fn ($s) => (float) $s->score), 1) : null,
                'practice' => $sessions->where('is_practice', true)->where('status', InterviewStatus::COMPLETED)->count(),
            ],
            'track' => $track,
            'practiceMilestone' => $track ? $this->engine->currentMilestone($user, $track) : null,
            'practiceLeft' => max(0, InterviewEngine::PRACTICE_PER_DAY - $this->engine->practiceCountToday($user)),
        ]);
    }

    /** Fellows no longer schedule interviews: AI interviews start from the curriculum or practice. */
    public function create(): RedirectResponse
    {
        return redirect()->route('interviews.index')
            ->with('info', 'Interviews start from your chapter\'s mock interview activity, or as practice below. Interviews with a mentor are scheduled by the team.');
    }

    public function show(Request $request, InterviewSession $interview): View
    {
        $this->authorize('view', $interview);
        if ($this->engine->isExpired($interview)) {
            $this->engine->expire($interview);
        }
        $interview->load(['track', 'fellow', 'interviewer', 'curriculumActivity']);

        return view('fellow.interviews.show', [
            'interview' => $interview,
            'isEngine' => $this->engine->isEngine($interview),
            'canCancel' => $this->canCancel($interview),
            'standing' => $interview->curriculumActivity && !$interview->is_practice
                ? app(\App\Services\CurriculumService::class)->interviewStanding($interview->fellow, $interview->curriculumActivity) : null,
        ]);
    }

    /**
     * Start an older pending/scheduled AI session in the new room, or open the meeting
     * of a scheduled human interview.
     */
    public function start(Request $request, InterviewSession $interview): RedirectResponse
    {
        abort_unless($request->user()->id === $interview->fellow_id, 403);

        if ($interview->mode !== InterviewMode::AI) {
            return $interview->status === InterviewStatus::SCHEDULED && $interview->meeting_link
                ? redirect()->away($interview->meeting_link)
                : redirect()->route('interviews.show', $interview)->with('info', 'The team will share the time and the meeting link of this interview.');
        }

        if ($interview->status === InterviewStatus::IN_PROGRESS && $this->engine->isEngine($interview)) {
            return redirect()->route('interviews.room', $interview);
        }
        if (!in_array($interview->status, [InterviewStatus::PENDING, InterviewStatus::SCHEDULED], true)) {
            return redirect()->route('interviews.show', $interview)->with('error', 'This interview cannot be started.');
        }

        $activity = $interview->curriculumActivity?->loadMissing('milestone');
        $this->engine->prepare($interview, $request->user(), $interview->track, $activity?->milestone ?? $this->engine->currentMilestone($request->user(), $interview->track), $activity, (bool) $interview->is_practice);

        return redirect()->route('interviews.room', $interview);
    }

    public function cancel(Request $request, InterviewSession $interview): RedirectResponse
    {
        abort_unless($request->user()->id === $interview->fellow_id || $request->user()->isAdmin(), 403);

        if (!$this->canCancel($interview)) {
            return redirect()->route('interviews.show', $interview)->with('error', 'This interview cannot be cancelled.');
        }

        $this->interviewService->cancel($interview, (string) $request->input('reason', 'Cancelled by the fellow'));

        if ($interview->curriculum_activity_id) {
            return redirect()->route('curriculum.activity.show', $interview->curriculum_activity_id)
                ->with('success', 'Interview cancelled. It does not count; you can start a new attempt.');
        }

        return redirect()->route('interviews.index')->with('success', 'Interview cancelled.');
    }

    /** Practice interview for the fellow's own track and current chapter (POST only, 3 a day). */
    public function practice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $track = $this->track($request);
        if (!$track) {
            return redirect()->route('interviews.index')->with('error', 'Choose your track first: practice interviews follow your current chapter.');
        }
        if ($this->engine->practiceCountToday($user) >= InterviewEngine::PRACTICE_PER_DAY) {
            return redirect()->route('interviews.index')->with('error', 'You have used your ' . InterviewEngine::PRACTICE_PER_DAY . ' practice interviews for today. Come back tomorrow.');
        }
        $open = InterviewSession::where('fellow_id', $user->id)->where('is_practice', true)->where('status', InterviewStatus::IN_PROGRESS)->get()
            ->reject(fn ($s) => $this->engine->isExpired($s) && $this->engine->expire($s))->first();
        if ($open) {
            return redirect()->route('interviews.room', $open)->with('info', 'You already have a practice interview open.');
        }

        $session = $this->engine->createPractice($user, $track);

        return redirect()->route('interviews.room', $session);
    }

    /** Old links: practice used to start (and save a session) on a GET. */
    public function practiceLegacy(): RedirectResponse
    {
        return redirect()->route('interviews.index');
    }

    /** Old room links go to the new room. */
    public function aiRoom(InterviewSession $interview): RedirectResponse
    {
        return redirect()->route('interviews.room', $interview);
    }

    protected function track(Request $request): ?Track
    {
        $user = $request->user();
        $fellowTrack = $request->attributes->get('activeTrack') ?? $user->activeTrack() ?? $user->primaryTrack;

        return $fellowTrack?->track;
    }

    protected function canCancel(InterviewSession $interview): bool
    {
        if ($interview->mode === InterviewMode::AI) {
            // Once the questions have started, the fellow ends the interview (and it is scored);
            // it cannot be thrown away to retry straight away with new questions.
            if ($interview->status === InterviewStatus::IN_PROGRESS) {
                return !$this->engine->isEngine($interview) || empty($interview->plan['begun_at']);
            }

            return in_array($interview->status, [InterviewStatus::PENDING, InterviewStatus::SCHEDULED], true);
        }

        // Human interviews: while waiting for a time, or up to 1 hour before it.
        return $interview->status === InterviewStatus::PENDING
            || ($interview->status === InterviewStatus::SCHEDULED && $interview->scheduled_at && now()->lt($interview->scheduled_at->copy()->subHour()));
    }
}
