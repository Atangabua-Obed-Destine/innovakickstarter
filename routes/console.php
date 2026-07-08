<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands
|--------------------------------------------------------------------------
*/

// Transition cohort statuses daily at midnight
Schedule::command('cohorts:transition')
    ->dailyAt('00:00')
    ->appendOutputTo(storage_path('logs/cohort-transitions.log'));
