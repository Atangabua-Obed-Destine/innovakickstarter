<?php

namespace App\Console\Commands;

use App\Models\Cohort;
use App\Models\CohortFellow;
use App\Models\User;
use Illuminate\Console\Command;

class TestCohortQuery extends Command
{
    protected $signature = 'test:cohort-query {userId=2}';
    protected $description = 'Test the cohort query logic for a fellow';

    public function handle()
    {
        $userId = $this->argument('userId');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }
        
        $this->info("Testing cohort query for: {$user->name} (ID: {$user->id})");
        
        // Get primary track
        $primaryTrack = $user->primaryTrack?->load('track');
        
        if (!$primaryTrack) {
            $this->warn("No primary track found for this user");
            return 1;
        }
        
        $this->info("Primary Track ID: {$primaryTrack->track_id}");
        $this->info("Primary Track Name: {$primaryTrack->track?->name}");
        
        // Find cohort enrollment
        $cohortFellow = CohortFellow::whereHas('cohort', function($q) use ($primaryTrack) {
            $q->where('track_id', $primaryTrack->track_id)
              ->whereIn('status', [Cohort::STATUS_ACTIVE, Cohort::STATUS_UPCOMING]);
        })
            ->where('fellow_id', $user->id)
            ->whereIn('status', ['enrolled', 'active'])
            ->with(['cohort', 'cohort.track'])
            ->first();
        
        if ($cohortFellow && $cohortFellow->cohort) {
            $this->info("✅ COHORT FOUND!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Cohort Name', $cohortFellow->cohort->name],
                    ['Cohort Status', $cohortFellow->cohort->status],
                    ['Track', $cohortFellow->cohort->track?->name ?? 'N/A'],
                    ['Enrollment Status', $cohortFellow->status],
                    ['Cohort Size', $cohortFellow->cohort->fellows_count],
                ]
            );
        } else {
            $this->warn("❌ No cohort found for this user");
            
            // Debug: Check all cohort enrollments for this user
            $allEnrollments = CohortFellow::where('fellow_id', $user->id)->with('cohort')->get();
            $this->info("All enrollments for this user:");
            foreach ($allEnrollments as $e) {
                $this->line("  - {$e->cohort->name} (Status: {$e->cohort->status}, Enrollment: {$e->status})");
            }
        }
        
        return 0;
    }
}
