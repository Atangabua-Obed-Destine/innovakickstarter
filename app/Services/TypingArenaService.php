<?php

namespace App\Services;

use App\Models\TypingRace;
use App\Models\TypingRaceEntry;
use App\Models\TypingSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Live typing races by polling (no websocket server needed).
 *
 * Fellows join a race in their league (Bronze/Silver/Gold by recent speed). When a
 * second fellow joins, an 8-second countdown starts; up to 5 racers. If nobody else
 * comes within 15 seconds, you race the "ghost" of your own best speed. A race ends
 * when everyone has finished or after 150 seconds. Browsers poll the state about once
 * a second and post their progress; the server checks it is humanly possible.
 */
class TypingArenaService
{
    public const MAX_RACERS = 5;
    public const COUNTDOWN = 8;
    public const WAIT_FOR_OTHERS = 15;
    public const MAX_SECONDS = 150;

    public function __construct(protected TypingService $typing) {}

    public function join(User $fellow): TypingRace
    {
        $league = $this->typing->league($fellow);

        return DB::transaction(function () use ($fellow, $league) {
            // Already in a race that hasn't finished? Go back to it.
            $current = TypingRace::whereIn('id', TypingRaceEntry::where('fellow_id', $fellow->id)->whereNull('finished_at')->select('race_id'))
                ->where('status', '!=', 'finished')->where('created_at', '>=', now()->subMinutes(5))->latest()->first();
            if ($current) {
                return $current;
            }
            // Own league first; with a small community, a neighbouring league (never Bronze vs Gold) beats racing alone.
            $neighbours = ['bronze' => ['silver'], 'silver' => ['bronze', 'gold'], 'gold' => ['silver']][$league];
            $open = TypingRace::whereIn('league', [$league, ...$neighbours])->where('status', 'waiting')->where('created_at', '>=', now()->subSeconds(60))
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '>', now()->addSeconds(2)))
                ->withCount('entries')->lockForUpdate()->get()->filter(fn ($r) => $r->entries_count < self::MAX_RACERS && $r->ghost_wpm === null);
            $race = $open->firstWhere('league', $league) ?? $open->first();
            $race ??= TypingRace::create(['league' => $league, 'text' => $this->typing->raceText($league), 'status' => 'waiting']);
            TypingRaceEntry::firstOrCreate(['race_id' => $race->id, 'fellow_id' => $fellow->id]);
            $this->tick($race);

            return $race->fresh();
        });
    }

    /** Move the race along its lifecycle (called on every poll). */
    public function tick(TypingRace $race): void
    {
        $race->loadCount('entries');
        if ($race->status === 'waiting') {
            if (!$race->starts_at && $race->entries_count >= 2) {
                $race->update(['starts_at' => now()->addSeconds(self::COUNTDOWN)]);
            } elseif (!$race->starts_at && $race->entries_count === 1 && $race->created_at->lte(now()->subSeconds(self::WAIT_FOR_OTHERS))) {
                $solo = $race->entries()->first();
                $best = TypingSession::where('fellow_id', $solo->fellow_id)->whereIn('mode', ['test', 'drill', 'race'])->max('wpm');
                $ghost = $best ? round($best, 1) : ['bronze' => 18, 'silver' => 32, 'gold' => 50][$race->league];
                $race->update(['starts_at' => now()->addSeconds(5), 'ghost_wpm' => $ghost]);
            }
            if ($race->starts_at && $race->starts_at->lte(now())) {
                $race->update(['status' => 'running']);
            }
        }
        if ($race->status === 'running') {
            $allDone = $race->entries()->whereNull('finished_at')->doesntExist();
            if ($allDone || $race->starts_at->lte(now()->subSeconds(self::MAX_SECONDS))) {
                $this->finish($race);
            }
        }
    }

    protected function finish(TypingRace $race): void
    {
        $entries = $race->entries()->get()->sortBy(fn ($e) => [$e->finished_at ? 0 : 1, $e->finished_at?->getTimestampMs() ?? 0, -$e->typed])->values();
        foreach ($entries as $i => $e) {
            $e->update(['place' => $i + 1]);
        }
        $race->update(['status' => 'finished', 'finished_at' => now()]);
    }

    public function state(TypingRace $race, User $fellow): array
    {
        $this->tick($race);
        $race->refresh();
        $elapsed = $race->starts_at && $race->starts_at->isPast() ? round($race->starts_at->diffInMilliseconds(now()) / 1000, 1) : 0;
        $entries = $race->entries()->with('fellow:id,name')->get()->map(fn ($e) => [
            'name' => $e->fellow_id === $fellow->id ? 'You' : explode(' ', trim((string) $e->fellow?->name))[0],
            'me' => $e->fellow_id === $fellow->id,
            'typed' => $e->typed, 'wpm' => $e->wpm ?? ($elapsed > 0 ? round($e->typed / 5 / ($elapsed / 60)) : 0),
            'finished' => (bool) $e->finished_at, 'place' => $e->place,
        ])->values()->all();

        return [
            'id' => $race->id, 'status' => $race->status, 'league' => $race->league,
            'text' => $race->text, 'length' => mb_strlen($race->text),
            'starts_in' => $race->starts_at && $race->starts_at->isFuture() ? round(now()->diffInMilliseconds($race->starts_at) / 1000, 1) : ($race->starts_at ? 0 : null),
            'elapsed' => $elapsed, 'ghost_wpm' => $race->ghost_wpm,
            'waiting_for' => $race->starts_at ? 0 : max(0, self::WAIT_FOR_OTHERS - (int) $race->created_at->diffInSeconds(now())),
            'entries' => $entries,
        ];
    }

    public function progress(TypingRace $race, User $fellow, array $data): array
    {
        $entry = TypingRaceEntry::where('race_id', $race->id)->where('fellow_id', $fellow->id)->firstOrFail();
        $this->tick($race);
        $race->refresh();
        if ($race->status !== 'running' || $entry->finished_at) {
            return $this->state($race, $fellow);
        }
        $length = mb_strlen($race->text);
        $elapsed = max(0.5, $race->starts_at->diffInMilliseconds(now()) / 1000);
        // Humanly possible: no more than ~20 characters a second (240 wpm).
        $typed = (int) min($length, max($entry->typed, (int) ($data['typed'] ?? 0)), (int) ceil($elapsed * 20));
        $update = ['typed' => $typed, 'errors' => max(0, (int) ($data['errors'] ?? 0))];
        if (!empty($data['finished']) && $typed >= $length) {
            $wpm = round($length / 5 / ($elapsed / 60), 1);
            $accuracy = round(min(100, max(0, (float) ($data['accuracy'] ?? 0))), 1);
            $update += ['finished_at' => now(), 'wpm' => $wpm, 'accuracy' => $accuracy];
            $this->typing->save($fellow, 'race', ['wpm' => $wpm, 'accuracy' => $accuracy, 'chars' => $length, 'seconds' => (int) round($elapsed)]);
            app(FellowActivityService::class)->record($fellow, 'race');
        }
        $entry->update($update);

        return $this->state($race, $fellow);
    }

    /** Races a fellow can see on the lobby: people waiting in their league right now. */
    public function lobby(User $fellow): array
    {
        $league = $this->typing->league($fellow);
        $waiting = TypingRace::where('league', $league)->where('status', 'waiting')->where('created_at', '>=', now()->subSeconds(60))->whereNull('ghost_wpm')
            ->withCount('entries')->get()->sum('entries_count');
        $online = TypingRaceEntry::where('created_at', '>=', now()->subMinutes(10))->distinct('fellow_id')->count('fellow_id');

        return ['league' => $league, 'waiting' => $waiting, 'recently_racing' => $online];
    }
}
