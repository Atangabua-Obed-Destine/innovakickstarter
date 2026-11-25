<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\DetectiveCase;
use App\Services\DetectiveService;
use App\Support\LessonMarkdown;
use App\Support\TrackExperience;
use Illuminate\Http\Request;

/** Weekly Data Detective cases. */
class DetectiveController extends Controller
{
    public function __construct(protected DetectiveService $detective) {}

    public function index(Request $request)
    {
        $track = $request->attributes->get('activeTrack')?->track ?? $request->user()->primaryTrack?->track;
        abort_unless($track, 404);
        $weekly = $this->detective->caseOfTheWeek($track);
        abort_unless($weekly, 404, 'No cases for this track yet.');

        return redirect()->route('detective.show', $weekly);
    }

    public function show(Request $request, DetectiveCase $case)
    {
        $this->authorizeCase($request, $case);
        $fellow = $request->user();
        $weekly = $this->detective->caseOfTheWeek($case->track);
        $solved = $this->detective->isSolved($fellow, $case);
        $revealed = $this->detective->revealed($fellow, $case);

        return view('fellow.detective.show', [
            'case' => $case,
            'experience' => TrackExperience::for($case->track),
            'isWeekly' => $weekly?->id === $case->id,
            'solved' => $solved,
            'correctKeys' => $this->detective->correctKeys($fellow, $case),
            'solutionHtml' => ($solved || $revealed) ? (string) LessonMarkdown::render($case->solution) : null,
            'solvers' => $weekly ? $this->detective->solvers($weekly) : collect(),
            'others' => $this->detective->cases($case->track)->reject(fn ($c) => $c->id === $case->id)
                ->map(fn ($c) => ['case' => $c, 'solved' => $this->detective->isSolved($fellow, $c), 'weekly' => $weekly?->id === $c->id]),
            'practiceConfig' => $case->track->experience['practice'] ?? [],
            'xpNow' => $weekly?->id === $case->id ? $case->xp : (int) round($case->xp / 2),
        ]);
    }

    public function answer(Request $request, DetectiveCase $case)
    {
        $this->authorizeCase($request, $case);
        $data = $request->validate(['key' => ['required', 'string', 'max:40'], 'value' => ['required', 'max:60']]);
        $result = $this->detective->answer($request->user(), $case, $data['key'], $data['value']);
        $result['solution_html'] = $result['solution'] ? (string) LessonMarkdown::render($result['solution']) : null;
        unset($result['solution']);

        return response()->json($result);
    }

    public function reveal(Request $request, DetectiveCase $case)
    {
        $this->authorizeCase($request, $case);
        $solution = $this->detective->reveal($request->user(), $case);
        abort_unless($solution !== null, 403, "This week's case can't be revealed; for other cases, try an answer first.");

        return response()->json(['solution_html' => (string) LessonMarkdown::render($solution)]);
    }

    protected function authorizeCase(Request $request, DetectiveCase $case): void
    {
        abort_unless($case->is_active && $request->user()->isEnrolledIn($case->track_id), 403);
    }
}
