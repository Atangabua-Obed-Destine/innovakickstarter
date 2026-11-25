<?php

namespace App\Services;

use App\Models\FellowTrack;
use App\Models\TypingRaceEntry;
use App\Models\TypingSession;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Built-in typing trainer: lessons from the home row to real course code, a daily
 * drill, a 1-minute speed test, and leagues for the live races.
 * Texts: database/data/typing/texts.json.
 */
class TypingService
{
    public const LEAGUES = ['bronze' => ['label' => 'Bronze', 'icon' => '🥉', 'max' => 25], 'silver' => ['label' => 'Silver', 'icon' => '🥈', 'max' => 45], 'gold' => ['label' => 'Gold', 'icon' => '🥇', 'max' => 999]];

    public function texts(): array
    {
        return Cache::remember('typing-texts:' . @filemtime(database_path('data/typing/texts.json')), 3600,
            fn () => json_decode((string) file_get_contents(database_path('data/typing/texts.json')), true) ?? []);
    }

    public function lessons(): array
    {
        return $this->texts()['lessons'] ?? [];
    }

    public function lesson(string $key): ?array
    {
        return collect($this->lessons())->firstWhere('key', $key);
    }

    /** Code languages for the fellow's track (Data Science: python and sql). */
    public function codeFor(User $fellow): array
    {
        $slug = FellowTrack::where('fellow_id', $fellow->id)->where('is_primary', true)->first()?->track?->slug;
        $langs = $this->texts()['track_code'][$slug] ?? ['python'];
        $code = $this->texts()['code'] ?? [];

        return collect($langs)->flatMap(fn ($l) => array_map(fn ($c) => ['lang' => $l, 'code' => $c], $code[$l] ?? []))->all();
    }

    protected function sentences(int $n): array
    {
        return collect($this->texts()['sentences'] ?? [])->shuffle()->take($n)->values()->all();
    }

    /** A long text for the 1-minute test (enough for fast typists). */
    public function testText(): string
    {
        return implode(' ', $this->sentences(9));
    }

    /** The daily drill: sentences plus real code from the fellow's track. */
    public function drillText(User $fellow): array
    {
        $code = collect($this->codeFor($fellow))->shuffle()->take(2);

        return array_values(array_filter([
            ['kind' => 'text', 'text' => implode(' ', $this->sentences(3))],
            ...$code->map(fn ($c) => ['kind' => 'code', 'lang' => $c['lang'], 'text' => $c['code']])->all(),
            ['kind' => 'text', 'text' => implode(' ', $this->sentences(2))],
        ]));
    }

    public function raceText(string $league): string
    {
        return implode(' ', $this->sentences($league === 'gold' ? 3 : 2));
    }

    public function save(User $fellow, string $mode, array $data): TypingSession
    {
        $session = TypingSession::create([
            'fellow_id' => $fellow->id, 'mode' => $mode, 'lesson_key' => $data['lesson_key'] ?? null,
            'wpm' => round(min(250, max(0, (float) $data['wpm'])), 1), 'accuracy' => round(min(100, max(0, (float) $data['accuracy'])), 1),
            'chars' => (int) ($data['chars'] ?? 0), 'seconds' => (int) ($data['seconds'] ?? 0),
        ]);
        app(FellowActivityService::class)->record($fellow, 'typing');

        return $session;
    }

    /** Average speed of recent timed typing (not lessons), for leagues. */
    public function recentWpm(User|int $fellow): ?float
    {
        $id = $fellow instanceof User ? $fellow->id : $fellow;
        $wpm = TypingSession::where('fellow_id', $id)->whereIn('mode', ['test', 'drill', 'race'])->latest('id')->limit(5)->pluck('wpm');

        return $wpm->isEmpty() ? null : round($wpm->avg(), 1);
    }

    public function league(User|int $fellow): string
    {
        $wpm = $this->recentWpm($fellow) ?? 0;

        return $wpm < self::LEAGUES['bronze']['max'] ? 'bronze' : ($wpm < self::LEAGUES['silver']['max'] ? 'silver' : 'gold');
    }

    public function stats(User $fellow): array
    {
        $sessions = TypingSession::where('fellow_id', $fellow->id)->orderBy('id')->get();
        $timed = $sessions->whereIn('mode', ['test', 'drill', 'race']);
        $firstTest = $sessions->firstWhere('mode', 'test');
        $passed = $sessions->where('mode', 'lesson')->where('accuracy', '>=', app(FoundationService::class)->targets()['accuracy'])->pluck('lesson_key')->unique()->values()->all();

        return [
            'best' => $timed->sortByDesc('wpm')->first(),
            'recent' => $this->recentWpm($fellow),
            'first' => $firstTest,
            'league' => $this->league($fellow),
            'sessions' => $sessions->count(),
            'days' => $sessions->map(fn ($s) => $s->created_at->toDateString())->unique()->count(),
            'drill_today' => $sessions->where('mode', 'drill')->contains(fn ($s) => $s->created_at->isToday()),
            'lessons_passed' => $passed,
            'chart' => $timed->take(-20)->map(fn ($s) => ['d' => $s->created_at->format('j M'), 'wpm' => $s->wpm, 'acc' => $s->accuracy])->values()->all(),
        ];
    }

    /** This week's boards: fastest per league, most improved, most races won. */
    public function leaderboards(): array
    {
        $since = now()->startOfWeek();
        $week = TypingSession::whereIn('mode', ['test', 'drill', 'race'])->where('created_at', '>=', $since)->with([])->get()->groupBy('fellow_id');
        $names = User::whereIn('id', $week->keys())->pluck('name', 'id');
        $first = fn ($id) => explode(' ', trim((string) ($names[$id] ?? '')))[0];

        $fastest = [];
        foreach ($week as $id => $rows) {
            $fastest[$this->league($id)][] = ['name' => $first($id), 'wpm' => round($rows->max('wpm')), 'id' => $id];
        }
        foreach ($fastest as $l => $rows) {
            $fastest[$l] = collect($rows)->sortByDesc('wpm')->take(5)->values()->all();
        }

        $improved = $week->map(function (Collection $rows, $id) use ($since, $first) {
            $before = TypingSession::where('fellow_id', $id)->whereIn('mode', ['test', 'drill', 'race'])->where('created_at', '<', $since)->orderBy('id')->limit(3)->avg('wpm');

            return $before ? ['name' => $first($id), 'gain' => round($rows->max('wpm') - $before), 'id' => $id] : null;
        })->filter(fn ($r) => $r && $r['gain'] > 0)->sortByDesc('gain')->take(5)->values()->all();

        $wins = TypingRaceEntry::where('place', 1)->where('created_at', '>=', $since)->get()->groupBy('fellow_id')
            ->map(fn ($r, $id) => ['name' => explode(' ', trim((string) User::find($id)?->name))[0], 'wins' => $r->count(), 'id' => $id])->sortByDesc('wins')->take(5)->values()->all();

        return ['fastest' => $fastest, 'improved' => $improved, 'wins' => $wins];
    }
}
