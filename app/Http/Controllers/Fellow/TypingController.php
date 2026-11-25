<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Services\FoundationService;
use App\Services\TypingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Built-in typing trainer: lessons, daily drill, 1-minute test. */
class TypingController extends Controller
{
    public function __construct(protected TypingService $typing) {}

    public function index(Request $request)
    {
        return view('fellow.typing.index', [
            'lessons' => $this->typing->lessons(),
            'stats' => $this->typing->stats($request->user()),
            'targets' => app(FoundationService::class)->targets(),
            'leagues' => TypingService::LEAGUES,
            'boards' => $this->typing->leaderboards(),
        ]);
    }

    public function practice(Request $request, string $mode, ?string $lesson = null)
    {
        abort_unless(in_array($mode, ['lesson', 'test', 'drill'], true), 404);
        $fellow = $request->user();
        $lessonData = $lesson ? $this->typing->lesson($lesson) : null;
        abort_if($mode === 'lesson' && !$lessonData, 404);
        $parts = match ($mode) {
            'test' => [['kind' => 'text', 'text' => $this->typing->testText()]],
            'drill' => $this->typing->drillText($fellow),
            'lesson' => array_map(fn ($d) => ['kind' => 'text', 'text' => $d], $lessonData['drills']),
        };
        $keys = array_column($this->typing->lessons(), 'key');
        $pos = $lesson ? array_search($lesson, $keys, true) : false;

        return view('fellow.typing.practice', [
            'mode' => $mode, 'lesson' => $lessonData, 'parts' => $parts,
            'targets' => app(FoundationService::class)->targets(), 'stats' => $this->typing->stats($fellow),
            'nextLesson' => $pos !== false && isset($keys[$pos + 1]) ? $this->typing->lesson($keys[$pos + 1]) : null,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['lesson', 'test', 'drill'])],
            'lesson_key' => ['nullable', 'string', 'max:40'],
            'wpm' => ['required', 'numeric', 'min:0', 'max:250'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100'],
            'chars' => ['required', 'integer', 'min:1', 'max:20000'],
            'seconds' => ['required', 'integer', 'min:3', 'max:1800'],
        ]);
        // A human can't type more than ~20 characters a second.
        abort_if($data['chars'] / max(1, $data['seconds']) > 20, 422, 'That result is not possible.');
        $this->typing->save($request->user(), $data['mode'], $data);
        $stats = $this->typing->stats($request->user());

        return response()->json(['ok' => true, 'best' => $stats['best']?->wpm, 'league' => $stats['league'], 'recent' => $stats['recent']]);
    }
}
