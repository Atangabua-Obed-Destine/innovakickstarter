<?php

namespace App\Console\Commands;

use App\Services\StandingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/** Evaluate the weekly bar for a finished week (default: last week). Safe to re-run: evaluated weeks are skipped. */
class EvaluateWeeklyStanding extends Command
{
    protected $signature = 'standing:evaluate {--week= : Any date in the week to evaluate (default: last week)}';

    protected $description = 'Evaluate the weekly bar for every fellow (warnings, penalty fees, holds)';

    public function handle(StandingService $standing): int
    {
        $week = $this->option('week') ? Carbon::parse($this->option('week')) : now()->subWeek();
        $stats = $standing->evaluateWeek($week);
        $this->info('Week of ' . $standing->weekStart($week)->toDateString() . ': ' . json_encode($stats));

        return self::SUCCESS;
    }
}
