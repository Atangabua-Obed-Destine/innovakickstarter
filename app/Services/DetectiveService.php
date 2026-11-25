<?php

namespace App\Services;

use App\Models\DetectiveAnswer;
use App\Models\DetectiveCase;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Weekly Data Detective cases. One case per week for the whole track (rotating);
 * other cases stay open for practice at half XP and can be revealed after a try.
 */
class DetectiveService
{
    public function cases(Track $track): Collection
    {
        return DetectiveCase::where('track_id', $track->id)->where('is_active', true)->orderBy('sort')->get();
    }

    public function caseOfTheWeek(Track $track): ?DetectiveCase
    {
        $cases = $this->cases($track);
        if ($cases->isEmpty()) {
            return null;
        }
        // Week 0 is the week the cases were first loaded, so fellows start with the easiest case.
        $anchor = \Carbon\Carbon::parse($cases->min('created_at'))->startOfWeek();
        $week = max(0, intdiv((int) $anchor->diffInDays(now()->startOfWeek()), 7));

        return $cases->values()[$week % $cases->count()];
    }

    /** Same canonical form as practice_canon.py: floor(x * 100 + 0.5) / 100, whole numbers as integers. */
    public static function canonicalNumber(float $x): string
    {
        $r = floor($x * 100 + 0.5) / 100;

        return $r == floor($r) && abs($r) < 1e15 ? (string) (int) $r : json_encode($r);
    }

    /** @return array{correct: bool, solved: bool, xp: int, solution: ?string} */
    public function answer(User $fellow, DetectiveCase $case, string $key, $value): array
    {
        app(FellowActivityService::class)->record($fellow, 'detective');
        $question = collect($case->questions)->firstWhere('key', $key);
        abort_unless($question, 404);
        $expected = $case->answer_key[$key] ?? null;

        if (($question['kind'] ?? '') === 'number') {
            $clean = str_replace([' ', ',', 'FCFA', 'XAF', '%'], '', trim((string) $value));
            $correct = is_numeric($clean) && is_string($expected) && hash_equals($expected, hash('sha256', self::canonicalNumber((float) $clean)));
        } else {
            $correct = is_numeric($value) && (int) $value === (int) $expected;
        }

        return DB::transaction(function () use ($fellow, $case, $key, $correct) {
            $wasSolved = $this->isSolved($fellow, $case);
            $row = DetectiveAnswer::create(['fellow_id' => $fellow->id, 'case_id' => $case->id, 'question_key' => $key, 'correct' => $correct,
                'xp_awarded' => 0, 'answered_on' => now()->toDateString()]);
            $solved = $this->isSolved($fellow, $case);
            $xp = 0;
            if ($solved && !$wasSolved && !$this->revealed($fellow, $case)) {
                $isWeekly = $this->caseOfTheWeek($case->track)?->id === $case->id;
                $xp = $isWeekly ? (int) $case->xp : (int) round($case->xp / 2);
                $row->update(['xp_awarded' => $xp]);
                if ($isWeekly) {
                    app(TrackFeedService::class)->record($fellow, $case->track_id, 'detective',
                        explode(' ', trim($fellow->name))[0] . " cracked this week's case: {$case->title}", null, null,
                        "case:{$case->id}:" . now()->startOfWeek()->toDateString());
                }
            }

            return ['correct' => $correct, 'solved' => $solved, 'xp' => $xp, 'solution' => $solved ? $case->solution : null];
        });
    }

    public function correctKeys(User $fellow, DetectiveCase $case): Collection
    {
        return DetectiveAnswer::where('fellow_id', $fellow->id)->where('case_id', $case->id)->where('correct', true)->pluck('question_key')->unique()->values();
    }

    public function isSolved(User $fellow, DetectiveCase $case): bool
    {
        return $this->correctKeys($fellow, $case)->intersect(collect($case->questions)->pluck('key'))->count() === count($case->questions);
    }

    public function revealed(User $fellow, DetectiveCase $case): bool
    {
        return DetectiveAnswer::where('fellow_id', $fellow->id)->where('case_id', $case->id)->where('question_key', '__reveal')->exists();
    }

    /** Cases other than this week's can be revealed (no XP afterwards) once the fellow has tried an answer. */
    public function reveal(User $fellow, DetectiveCase $case): ?string
    {
        $isWeekly = $this->caseOfTheWeek($case->track)?->id === $case->id;
        $tried = DetectiveAnswer::where('fellow_id', $fellow->id)->where('case_id', $case->id)->exists();
        if ($isWeekly || !$tried) {
            return null;
        }
        if (!$this->revealed($fellow, $case)) {
            DetectiveAnswer::create(['fellow_id' => $fellow->id, 'case_id' => $case->id, 'question_key' => '__reveal', 'correct' => false,
                'xp_awarded' => 0, 'answered_on' => now()->toDateString()]);
        }

        return $case->solution;
    }

    /** Who cracked this week's case, first solvers first. */
    public function solvers(DetectiveCase $case, int $limit = 10): Collection
    {
        return DetectiveAnswer::with('fellow:id,name')->where('case_id', $case->id)->where('xp_awarded', '>', 0)
            ->where('created_at', '>=', now()->startOfWeek())->orderBy('created_at')->limit($limit)->get()
            ->map(fn ($a) => ['name' => explode(' ', trim($a->fellow?->name ?? 'A fellow'))[0], 'at' => $a->created_at]);
    }
}
