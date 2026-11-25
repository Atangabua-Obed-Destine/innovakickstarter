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

// Complete internships past their end date and open attestation drafts
Schedule::command('internships:complete-expired')
    ->dailyAt('00:10')
    ->appendOutputTo(storage_path('logs/internship-completions.log'));

// Pair fellows waiting for a review partner (collaborative activities); flag long waits to admins
Schedule::command('curriculum:match-review-partners')->dailyAt('07:00')->withoutOverlapping();
