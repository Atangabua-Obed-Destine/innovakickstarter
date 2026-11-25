<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\PracticeChallenge;
use App\Services\PracticeService;
use App\Support\LessonMarkdown;
use App\Support\TrackExperience;
use Illuminate\Http\Request;

/** Practice Lab: short self-checking SQL, Python and quiz challenges for the fellow's track. */
class PracticeController extends Controller
{
    public function __construct(protected PracticeService $practice) {}

    public function index(Request $request)
    {
        $track = $this->track($request);
        abort_unless($track, 404);

        $challenges = $this->practice->challenges($track);
        $solved = $this->practice->solvedIds($request->user(), $track);

        return view('fellow.practice.index', [
            'track' => $track,
            'experience' => TrackExperience::for($track),
            'daily' => $this->practice->dailyChallenge($track),
            'stats' => $this->practice->stats($request->user(), $track),
            'leaderboard' => $this->practice->leaderboard($track),
            'bySkill' => $challenges->groupBy(fn ($c) => $c->skill ?: 'general'),
            'solved' => $solved,
            'weeklyCase' => app(\App\Services\DetectiveService::class)->caseOfTheWeek($track),
            'flashLeft' => app(\App\Services\FlashReviewService::class)->todaysCards($request->user(), $track)->count(),
        ]);
    }

    public function show(Request $request, PracticeChallenge $challenge)
    {
        $this->authorizeChallenge($request, $challenge);
        $experience = TrackExperience::for($challenge->track);
        $solved = $this->practice->hasSolved($request->user(), $challenge);

        return view('fellow.practice.show', [
            'challenge' => $challenge,
            'experience' => $experience,
            'solved' => $solved,
            'explanation' => $solved ? $challenge->explanation : null,
            'isDaily' => $this->practice->dailyChallenge($challenge->track)?->id === $challenge->id,
            'practiceConfig' => $challenge->track->experience['practice'] ?? [],
            'next' => $this->practice->challenges($challenge->track)
                ->first(fn ($c) => $c->id !== $challenge->id && !$this->practice->hasSolved($request->user(), $c)),
        ]);
    }

    public function check(Request $request, PracticeChallenge $challenge)
    {
        $this->authorizeChallenge($request, $challenge);
        $data = $request->validate([
            'canonical' => ['nullable', 'string', 'max:200000'],
            'option' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $result = $this->practice->check($request->user(), $challenge, $data);
        $result['explanation_html'] = $result['explanation'] ? (string) LessonMarkdown::render($result['explanation']) : null;
        unset($result['explanation']);

        return response()->json($result);
    }

    public function reveal(Request $request, PracticeChallenge $challenge)
    {
        $this->authorizeChallenge($request, $challenge);
        $explanation = $this->practice->reveal($request->user(), $challenge);
        abort_unless($explanation !== null, 403, 'Try a few more times first.');

        return response()->json(['explanation_html' => (string) LessonMarkdown::render($explanation)]);
    }

    protected function track(Request $request)
    {
        return $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
    }

    protected function authorizeChallenge(Request $request, PracticeChallenge $challenge): void
    {
        abort_unless($challenge->is_active && $request->user()->isEnrolledIn($challenge->track_id), 403);
    }
}
