<?php

namespace App\Console\Commands;

use App\Services\StandingService;
use Illuminate\Console\Command;

/** Tell every fellow about the weekly bar (run once when it launches, or after changing the rules). */
class AnnounceWeeklyBar extends Command
{
    protected $signature = 'standing:announce';

    protected $description = 'Notify all fellows about the weekly bar and when it starts';

    public function handle(StandingService $standing): int
    {
        $this->info($standing->announce() . ' fellows notified.');

        return self::SUCCESS;
    }
}
