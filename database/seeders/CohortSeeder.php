<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Track;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder for Cohort data
 * 
 * Creates sample cohorts for each track with various statuses
 * to demonstrate the cohort management system.
 */
class CohortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding cohorts...');

        // Get all active tracks
        $tracks = Track::where('is_active', true)->get();
        
        if ($tracks->isEmpty()) {
            $this->command->warn('No active tracks found. Please seed tracks first.');
            return;
        }

        // Get an admin user for created_by
        $admin = User::role('admin')->first();

        // Get fellows for enrollment
        $fellows = User::role('fellow')->inRandomOrder()->limit(100)->get();

        $cohortNumber = 1;
        $today = Carbon::today();

        foreach ($tracks as $track) {
            // Create a completed cohort (past)
            // Start as ACTIVE to allow enrollment, then change to COMPLETED
            $completedCohort = Cohort::create([
                'name' => "Cohort {$cohortNumber}",
                'description' => "This cohort has completed the program.",
                'track_id' => $track->id,
                'start_date' => $today->copy()->subMonths(6),
                'end_date' => $today->copy()->subMonths(3),
                'max_fellows' => 40,
                'min_fellows' => 10,
                'status' => Cohort::STATUS_ACTIVE, // Start as active for enrollment
                'created_by' => $admin?->id,
            ]);
            $cohortNumber++;

            // Enroll some fellows in completed cohort
            $completedFellows = $fellows->random(min(rand(5, 10), $fellows->count()));
            foreach ($completedFellows as $fellow) {
                try {
                    $completedCohort->enrollFellow($fellow, $admin);
                    // Mark as completed with random score
                    $completedCohort->cohortFellows()
                        ->where('fellow_id', $fellow->id)
                        ->update([
                            'status' => 'completed',
                            'completed_at' => $completedCohort->end_date,
                            'cohort_score' => rand(60, 95),
                            'activities_completed' => rand(10, 50),
                        ]);
                } catch (\Exception $e) {
                    // Fellow already in cohort for this track, skip
                }
            }
            
            // Now mark as completed
            $completedCohort->update(['status' => Cohort::STATUS_COMPLETED]);
            $completedCohort->updateStatistics();
            $completedCohort->updateRankings();

            // Create an active cohort (current)
            $activeCohort = Cohort::create([
                'name' => "Cohort {$cohortNumber}",
                'description' => "Currently running cohort with active fellows.",
                'track_id' => $track->id,
                'start_date' => $today->copy()->subWeeks(4),
                'end_date' => $today->copy()->addWeeks(8),
                'max_fellows' => 50,
                'min_fellows' => 10,
                'status' => Cohort::STATUS_ACTIVE,
                'created_by' => $admin?->id,
            ]);
            $cohortNumber++;

            // Enroll fellows in active cohort
            $activeFellowsList = $fellows->random(min(rand(10, 20), $fellows->count()));
            foreach ($activeFellowsList as $fellow) {
                try {
                    $activeCohort->enrollFellow($fellow, $admin);
                    // Update with progress
                    $activeCohort->cohortFellows()
                        ->where('fellow_id', $fellow->id)
                        ->update([
                            'status' => 'active',
                            'cohort_score' => rand(20, 85),
                            'activities_completed' => rand(3, 25),
                        ]);
                } catch (\Exception $e) {
                    // Fellow already in cohort for this track, skip
                }
            }
            $activeCohort->updateStatistics();
            $activeCohort->updateRankings();

            // Create an upcoming cohort
            $upcomingCohort = Cohort::create([
                'name' => "Cohort {$cohortNumber}",
                'description' => "Upcoming cohort open for enrollment.",
                'track_id' => $track->id,
                'start_date' => $today->copy()->addWeeks(4),
                'end_date' => $today->copy()->addWeeks(16),
                'enrollment_opens_at' => $today->copy()->subWeeks(2),
                'enrollment_closes_at' => $today->copy()->addWeeks(3),
                'max_fellows' => 35,
                'min_fellows' => 10,
                'status' => Cohort::STATUS_UPCOMING,
                'created_by' => $admin?->id,
            ]);
            $cohortNumber++;

            // Enroll some fellows in upcoming cohort
            $upcomingFellowsList = $fellows->random(min(rand(8, 20), $fellows->count()));
            foreach ($upcomingFellowsList as $fellow) {
                try {
                    $upcomingCohort->enrollFellow($fellow, $admin);
                } catch (\Exception $e) {
                    // Fellow already in cohort for this track, skip
                }
            }

            // Create a draft cohort (every other track)
            if ($tracks->search($track) % 2 === 0) {
                Cohort::create([
                    'name' => "Cohort {$cohortNumber}",
                    'description' => "Draft cohort still being configured.",
                    'track_id' => $track->id,
                    'start_date' => $today->copy()->addMonths(3),
                    'end_date' => $today->copy()->addMonths(6),
                    'max_fellows' => 40,
                    'min_fellows' => 15,
                    'status' => Cohort::STATUS_DRAFT,
                    'created_by' => $admin?->id,
                ]);
                $cohortNumber++;
            }

            $this->command->info("  Created cohorts for {$track->name}");
        }

        $totalCohorts = Cohort::count();
        $this->command->info("Seeded {$totalCohorts} cohorts successfully.");
    }
}
