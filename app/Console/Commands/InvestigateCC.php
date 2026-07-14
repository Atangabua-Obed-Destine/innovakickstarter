<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FellowTrack;
use App\Services\CareerCapitalCalculator;

class InvestigateCC extends Command
{
    protected $signature = 'app:recalc-cc';
    protected $description = 'Recalculate Career Capital scores for all fellows across all tracks';

    public function handle()
    {
        $calc = app(CareerCapitalCalculator::class);
        $fellowTracks = FellowTrack::with(['fellow', 'track'])->get();
        
        $this->info("Found {$fellowTracks->count()} fellow track enrollments. Recalculating...");
        
        $bar = $this->output->createProgressBar($fellowTracks->count());
        $bar->start();

        foreach ($fellowTracks as $ft) {
            if ($ft->fellow && $ft->track) {
                $calc->updateScore($ft->fellow, $ft->track);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("All Career Capital scores have been successfully recalculated to the new math curve!");
    }
}
