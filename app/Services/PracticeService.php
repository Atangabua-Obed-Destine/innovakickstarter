<?php

namespace App\Services;

use App\Models\PracticeAttempt;
use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Practice Lab: daily challenge, answer checking, XP, practice streak and the
 * weekly leaderboard. Practice XP is separate from Career Capital.
 */
class PracticeService
{
    public const DAILY_BONUS = 0.5; // +50% XP for solving today's challenge today
    public const ATTEMPTS_BEFORE_REVEAL = 3;

    /** Today's challenge: the same for every fellow of the track, rotating daily. */
    public function dailyChallenge(Track $track, ?CarbonInterface $day = null): ?PracticeChallenge
    {
        $challenges = $this->challenges($track);
        if ($challenges->isEmpty()) {
            return null;
        }
        $day ??= now();
        $index = (int) floor($day->copy()->startOfDay()->getTimestamp() / 86400) % $challenges->count();

        return $challenges->values()[$index];
    }

    public function challenges(Track $track): Collection
    {
        return PracticeChallenge::where('track_id', $track->id)->where('is_active', true)->orderBy('sort')->orderBy('slug')->get();
    }

    /**
     * Check an answer. $payload: ['canonical' => string] for SQL/Python, ['option' => int] for quizzes.
     *
     * @return array{correct: bool, xp: int, first_solve: bool, explanation: ?string, hint: ?string, attempts: int, can_reveal: bool}
     */
    public function check(User $fellow, PracticeChallenge $challenge, array $payload): array
    {
        app(FellowActivityService::class)->record($fellow, 'practice');
        $correct = match ($challenge->kind) {
            'quiz' => isset($payload['option']) && (int) $payload['option'] === (int) $challenge->answer_index,
            default => is_string($payload['canonical'] ?? null) && $challenge->expected_hash !== null
                && hash_equals($challenge->expected_hash, hash('sha256', $payload['canonical'])),
        };

        return DB::transaction(function () use ($fellow, $challenge, $correct) {
            $alreadySolved = $this->hasSolved($fellow, $challenge);
            $isDaily = $this->dailyChallenge($challenge->track)?->id === $challenge->id;
            $xp = 0;
            if ($correct && !$alreadySolved) {
                $xp = (int) round($challenge->xp * ($isDaily ? 1 + self::DAILY_BONUS : 1));
            }

            PracticeAttempt::create([
                'challenge_id' => $challenge->id,
                'fellow_id' => $fellow->id,
                'correct' => $correct,
                'xp_awarded' => $xp,
                'was_daily' => $isDaily,
                'practiced_on' => now()->toDateString(),
            ]);

            if ($correct) {
                app(TrackFeedService::class)->practiceStreak($fellow, $challenge->track_id, $this->stats($fellow, $challenge->track)['streak']);
            }

            $attempts = PracticeAttempt::where('challenge_id', $challenge->id)->where('fellow_id', $fellow->id)->count();
            $canReveal = $correct || $alreadySolved || $attempts >= self::ATTEMPTS_BEFORE_REVEAL;

            return [
                'correct' => $correct,
                'xp' => $xp,
                'first_solve' => $correct && !$alreadySolved,
                'explanation' => $correct || $alreadySolved ? $challenge->explanation : null,
                'hint' => $correct ? null : $challenge->hint,
                'attempts' => $attempts,
                'can_reveal' => $canReveal,
            ];
        });
    }

    /** After enough tries a fellow may see the explanation (no XP). */
    public function reveal(User $fellow, PracticeChallenge $challenge): ?string
    {
        $attempts = PracticeAttempt::where('challenge_id', $challenge->id)->where('fellow_id', $fellow->id)->count();

        return $attempts >= self::ATTEMPTS_BEFORE_REVEAL || $this->hasSolved($fellow, $challenge) ? $challenge->explanation : null;
    }

    public function hasSolved(User $fellow, PracticeChallenge $challenge): bool
    {
        return PracticeAttempt::where('challenge_id', $challenge->id)->where('fellow_id', $fellow->id)->where('correct', true)->exists();
    }

    /**
     * Every source of practice XP: [table, card/challenge table, foreign key, day column].
     * Each row counts toward XP, the practice streak (correct answers) and the leaderboard.
     */
    public const XP_SOURCES = [
        ['practice_attempts', 'practice_challenges', 'challenge_id', 'practiced_on'],
        ['flash_answers', 'flash_cards', 'card_id', 'answered_on'],
        ['detective_answers', 'detective_cases', 'case_id', 'answered_on'],
        ['explanations', 'track_curriculum_activities', 'curriculum_activity_id', 'answered_on'],
    ];

    /** Union of all XP rows for a track: fellow_id, xp, day, correct. */
    public function ledger(Track $track): \Illuminate\Database\Query\Builder
    {
        $query = null;
        foreach (self::XP_SOURCES as [$table, $parent, $fk, $day]) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                continue;
            }
            $part = DB::table($table)->join("{$parent} as p", 'p.id', '=', "{$table}.{$fk}")->where('p.track_id', $track->id)
                ->selectRaw("{$table}.fellow_id as fellow_id, {$table}.xp_awarded as xp, {$table}.{$day} as day, {$table}.correct as correct");
            $query = $query ? $query->unionAll($part) : $part;
        }

        return DB::query()->fromSub($query, 'ledger');
    }

    /** @return array{xp: int, solved: int, total: int, streak: int, practiced_today: bool, week_xp: int} */
    public function stats(User $fellow, Track $track): array
    {
        $mine = fn () => $this->ledger($track)->where('fellow_id', $fellow->id);

        $days = $mine()->where('correct', true)->distinct()->orderByDesc('day')->pluck('day')
            ->map(fn ($d) => \Carbon\Carbon::parse($d)->toDateString())->unique()->values();
        $streak = 0;
        $cursor = now()->startOfDay();
        if ($days->first() !== $cursor->toDateString()) {
            $cursor = $cursor->subDay(); // yesterday still counts until today ends
        }
        foreach ($days as $day) {
            if ($day === $cursor->toDateString()) {
                $streak++;
                $cursor = $cursor->subDay();
            } elseif ($day < $cursor->toDateString()) {
                break;
            }
        }

        $ids = PracticeChallenge::where('track_id', $track->id)->pluck('id');

        return [
            'xp' => (int) $mine()->sum('xp'),
            'solved' => PracticeAttempt::where('fellow_id', $fellow->id)->whereIn('challenge_id', $ids)->where('correct', true)->distinct('challenge_id')->count('challenge_id'),
            'total' => PracticeChallenge::where('track_id', $track->id)->where('is_active', true)->count(),
            'streak' => $streak,
            'practiced_today' => $days->first() === now()->toDateString(),
            'week_xp' => (int) $mine()->where('day', '>=', now()->startOfWeek()->toDateString())->sum('xp'),
        ];
    }

    /** This week's top fellows by practice XP in the track (challenges, reviews, cases, explanations). */
    public function leaderboard(Track $track, int $limit = 10): Collection
    {
        return $this->ledger($track)
            ->join('users as u', 'u.id', '=', 'ledger.fellow_id')
            ->where('ledger.day', '>=', now()->startOfWeek()->toDateString())
            ->groupBy('ledger.fellow_id', 'u.name')
            ->havingRaw('SUM(ledger.xp) > 0')
            ->selectRaw('ledger.fellow_id as fellow_id, u.name as name, SUM(ledger.xp) as xp')
            ->orderByDesc('xp')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $parts = preg_split('/\s+/', trim($row->name));
                $row->display_name = $parts[0] . (isset($parts[1]) ? ' ' . mb_substr($parts[1], 0, 1) . '.' : '');

                return $row;
            });
    }

    /** Ids of challenges the fellow has solved. */
    public function solvedIds(User $fellow, Track $track): Collection
    {
        return PracticeAttempt::where('fellow_id', $fellow->id)->where('correct', true)
            ->whereIn('challenge_id', PracticeChallenge::where('track_id', $track->id)->pluck('id'))
            ->distinct()->pluck('challenge_id');
    }
}
