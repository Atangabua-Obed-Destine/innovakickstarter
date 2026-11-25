<?php

namespace App\Console\Commands;

use App\Services\SupportService;
use Illuminate\Console\Command;

class NudgeStrugglingFellows extends Command
{
    protected $signature = 'support:nudge';

    protected $description = 'Send kind, specific nudges to fellows showing struggle signals';

    public function handle(SupportService $support): int
    {
        $this->info($support->nudgeAll() . ' fellows nudged.');

        return self::SUCCESS;
    }
}
