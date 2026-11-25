<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Services\FoundationService;
use App\Services\TypingService;
use Illuminate\Http\Request;

/** Base Camp: the Foundations check and short catch-up modules. */
class BaseCampController extends Controller
{
    public function __construct(protected FoundationService $foundations) {}

    public function index(Request $request)
    {
        return view('fellow.base-camp.index', [
            'status' => $this->foundations->status($request->user()),
            'targets' => $this->foundations->targets(),
            'checkTaken' => $this->foundations->checkTaken($request->user()),
            'areas' => $this->foundations->areas()->keyBy('key'),
            'upcoming' => ($track = $request->user()->primaryTrack?->track) ? app(\App\Services\FoundationLinkService::class)->upcoming($request->user(), $track) : [],
        ]);
    }

    /** The 15-minute Foundations check: typing test, then each area. */
    public function check(Request $request, TypingService $typing)
    {
        $areas = $this->foundations->areas()->map(fn ($a) => [
            'key' => $a->key, 'title' => $a->title, 'icon' => $a->icon, 'summary' => $a->summary,
            'passage' => $a->content['check']['passage'] ?? null,
            'questions' => FoundationService::publicQuestions($a->content['check']['questions'] ?? []),
        ])->values();

        return view('fellow.base-camp.check', ['areas' => $areas, 'typingText' => $typing->testText(), 'targets' => $this->foundations->targets(),
            'status' => collect($this->foundations->status($request->user()))->keyBy('key')]);
    }

    public function submit(Request $request, string $area, string $kind, ?string $lesson = null)
    {
        abort_unless(in_array($kind, ['check', 'test', 'lesson'], true), 404);
        $data = $request->validate(['answers' => ['required', 'array'], 'answers.*' => ['nullable', 'integer', 'min:0', 'max:3']]);

        return response()->json($this->foundations->grade($request->user(), $this->foundations->area($area), $kind, $lesson, $data['answers']));
    }

    public function lesson(Request $request, string $area, string $lesson)
    {
        $model = $this->foundations->area($area);
        $lessons = collect($model->content['lessons'] ?? []);
        $current = $lessons->firstWhere('key', $lesson);
        abort_unless($current, 404);
        $index = $lessons->search(fn ($l) => $l['key'] === $lesson);

        return view('fellow.base-camp.lesson', [
            'area' => $model, 'lesson' => $current, 'practice' => FoundationService::publicQuestions($current['practice'] ?? []),
            'next' => $lessons->get($index + 1), 'position' => $index + 1, 'count' => $lessons->count(),
        ]);
    }

    public function test(Request $request, string $area)
    {
        $model = $this->foundations->area($area);

        return view('fellow.base-camp.test', ['area' => $model, 'passage' => $model->content['test']['passage'] ?? null,
            'questions' => FoundationService::publicQuestions($model->content['test']['questions'] ?? []), 'targets' => $this->foundations->targets()]);
    }
}
