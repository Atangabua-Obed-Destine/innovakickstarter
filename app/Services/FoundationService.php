<?php

namespace App\Services;

use App\Models\AdminSetting;
use App\Models\FoundationArea;
use App\Models\FoundationResult;
use App\Models\TypingSession;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Base Camp: a 15-minute Foundations check (typing, computer basics, maths, English,
 * spreadsheets) and short catch-up modules for the areas that need a boost. It never
 * blocks the curriculum: it runs alongside Chapters 1-2.
 *
 * Answers stay on the server. The check shows only scores; the module test shows which
 * questions were wrong but not the right answer (so a retake still means something);
 * lesson practice explains answers (that is where learning happens).
 */
class FoundationService
{
    public function targets(): array
    {
        return [
            'pass' => (int) AdminSetting::get('foundations_pass_percent', 70),
            'wpm' => (int) AdminSetting::get('foundations_typing_wpm', 25),
            'accuracy' => (int) AdminSetting::get('foundations_typing_accuracy', 90),
        ];
    }

    /** @return Collection<int, FoundationArea> */
    public function areas(): Collection
    {
        return FoundationArea::where('is_active', true)->orderBy('sort')->get();
    }

    public function area(string $key): FoundationArea
    {
        return FoundationArea::where('key', $key)->where('is_active', true)->firstOrFail();
    }

    /** Questions for a page: no answers, no explanations. */
    public static function publicQuestions(array $questions): array
    {
        return array_map(fn ($q) => ['key' => $q['key'], 'question' => $q['question'], 'options' => $q['options']], $questions);
    }

    public function typingBest(User $fellow): ?TypingSession
    {
        $t = $this->targets();

        return TypingSession::where('fellow_id', $fellow->id)->where('mode', 'test')
            ->orderByRaw('(wpm >= ? AND accuracy >= ?) DESC', [$t['wpm'], $t['accuracy']])->orderByDesc('wpm')->first();
    }

    /** Status per area, typing first. */
    public function status(User $fellow): array
    {
        $t = $this->targets();
        $results = FoundationResult::where('fellow_id', $fellow->id)->get()->groupBy('area');
        $best = $this->typingBest($fellow);
        $out = [[
            'key' => 'typing', 'title' => 'Typing', 'icon' => '⌨️', 'summary' => 'Typing fast and accurately, with all ten fingers.',
            'checked' => (bool) $best, 'best' => $best ? round($best->wpm) . ' wpm · ' . round($best->accuracy) . '%' : null,
            'ready' => $best && $best->wpm >= $t['wpm'] && $best->accuracy >= $t['accuracy'],
            'target' => "{$t['wpm']} words per minute at {$t['accuracy']}% accuracy", 'lessons' => [], 'lessons_done' => [], 'test_passed' => false,
        ]];
        foreach ($this->areas() as $area) {
            $rows = $results->get($area->key, collect());
            $check = $rows->where('kind', 'check')->sortByDesc('percent')->first();
            $test = $rows->where('kind', 'test')->sortByDesc('percent')->first();
            $bestPercent = max((float) ($check?->percent ?? 0), (float) ($test?->percent ?? 0));
            $out[] = [
                'key' => $area->key, 'title' => $area->title, 'icon' => $area->icon, 'summary' => $area->summary,
                'checked' => (bool) $check, 'best' => $check || $test ? round($bestPercent) . '%' : null,
                'ready' => $bestPercent >= $t['pass'],
                'target' => "{$t['pass']}% on the check or the module test",
                'lessons' => array_map(fn ($l) => ['key' => $l['key'], 'title' => $l['title'], 'minutes' => $l['minutes'] ?? 8], $area->content['lessons'] ?? []),
                'lessons_done' => $rows->where('kind', 'lesson')->pluck('item_key')->unique()->values()->all(),
                'test_passed' => (bool) $rows->where('kind', 'test')->firstWhere('passed', true),
            ];
        }

        return $out;
    }

    public function checkTaken(User $fellow): bool
    {
        return collect($this->status($fellow))->every(fn ($a) => $a['checked']);
    }

    /** Titles of areas still to strengthen ("skills check" when it hasn't been taken). */
    public function gaps(User $fellow): array
    {
        if (FoundationArea::where('is_active', true)->doesntExist()) {
            return [];
        }
        $status = collect($this->status($fellow));
        if ($status->contains(fn ($a) => !$a['checked'])) {
            return ['skills check'];
        }

        return $status->reject(fn ($a) => $a['ready'])->pluck('title')->map(fn ($t) => mb_strtolower($t))->values()->all();
    }

    /**
     * Grade answers for an area's check, a lesson's practice or the module test.
     *
     * @return array{score: int, total: int, percent: float, passed: bool, results: array}
     */
    public function grade(User $fellow, FoundationArea $area, string $kind, ?string $lessonKey, array $answers): array
    {
        $questions = match ($kind) {
            'check' => $area->content['check']['questions'] ?? [],
            'test' => $area->content['test']['questions'] ?? [],
            'lesson' => collect($area->content['lessons'] ?? [])->firstWhere('key', $lessonKey)['practice'] ?? [],
        };
        abort_if(!$questions, 404);

        $results = [];
        $score = 0;
        foreach ($questions as $q) {
            $given = $answers[$q['key']] ?? null;
            $right = $given !== null && (int) $given === (int) $q['answer'];
            $score += $right ? 1 : 0;
            $results[$q['key']] = match ($kind) {
                'lesson' => ['correct' => $right, 'answer' => (int) $q['answer'], 'explain' => $q['explain'] ?? null],
                'test' => ['correct' => $right],
                default => [],
            };
        }
        $total = count($questions);
        $percent = round($score / max(1, $total) * 100, 1);
        $passed = $kind === 'lesson' ? true : $percent >= ($kind === 'test' ? max(80, $this->targets()['pass']) : $this->targets()['pass']);

        FoundationResult::create(['fellow_id' => $fellow->id, 'area' => $area->key, 'kind' => $kind, 'item_key' => $lessonKey,
            'score' => $score, 'total' => $total, 'percent' => $percent, 'passed' => $passed]);
        if ($kind !== 'check') {
            app(FellowActivityService::class)->record($fellow, 'base_camp');
        }

        return ['score' => $score, 'total' => $total, 'percent' => $percent, 'passed' => $passed, 'results' => $kind === 'check' ? [] : $results];
    }
}
