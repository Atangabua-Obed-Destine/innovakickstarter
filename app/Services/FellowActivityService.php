<?php

namespace App\Services;

use App\Models\FellowActivityDay;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Records what a fellow actually does each day (not logins: real learning actions).
 * Feeds the weekly bar (StandingService) and early-warning signals (SupportService).
 */
class FellowActivityService
{
    /** Practising and reviewing. */
    public const PRACTICE = ['review', 'quick_check', 'practice', 'detective', 'typing', 'explain', 'base_camp'];

    /** Moving through the curriculum. */
    public const PROGRESS = ['step', 'start', 'submit', 'interview'];

    /** Anything else that still counts as an active day. */
    public const OTHER = ['mentor', 'orientation', 'help', 'race'];

    public function record(?User $fellow, string $kind): void
    {
        if (!$fellow || !in_array($kind, [...self::PRACTICE, ...self::PROGRESS, ...self::OTHER], true)) {
            return;
        }
        try {
            DB::transaction(function () use ($fellow, $kind) {
                $row = FellowActivityDay::where('fellow_id', $fellow->id)->whereDate('day', now()->toDateString())->lockForUpdate()->first()
                    ?? new FellowActivityDay(['fellow_id' => $fellow->id, 'day' => now()->toDateString(), 'kinds' => []]);
                $kinds = $row->kinds ?? [];
                $kinds[$kind] = ($kinds[$kind] ?? 0) + 1;
                $row->kinds = $kinds;
                $row->save();
            });
        } catch (Throwable $e) {
            report($e); // never let tracking break the action itself
        }
    }

    /** @return Collection<int, FellowActivityDay> */
    public function days(User|int $fellow, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $id = $fellow instanceof User ? $fellow->id : $fellow;

        return FellowActivityDay::where('fellow_id', $id)->whereDate('day', '>=', $from->toDateString())->whereDate('day', '<=', $to->toDateString())->orderBy('day')->get();
    }

    /** Active days, practice days and curriculum actions in a period. */
    public function summary(User|int $fellow, CarbonInterface $from, CarbonInterface $to): array
    {
        $days = $this->days($fellow, $from, $to);
        $practice = $days->filter(fn ($d) => array_intersect(array_keys($d->kinds ?? []), self::PRACTICE))->count();
        $progress = $days->sum(fn ($d) => array_sum(array_intersect_key($d->kinds ?? [], array_flip(self::PROGRESS))));

        return ['active_days' => $days->count(), 'practice_days' => $practice, 'progress_actions' => (int) $progress,
            'by_day' => $days->mapWithKeys(fn ($d) => [$d->day->toDateString() => $d->kinds])->all()];
    }

    public function lastActiveDay(User|int $fellow): ?CarbonInterface
    {
        $id = $fellow instanceof User ? $fellow->id : $fellow;

        return FellowActivityDay::where('fellow_id', $id)->max('day') ? \Carbon\Carbon::parse(FellowActivityDay::where('fellow_id', $id)->max('day')) : null;
    }
}
