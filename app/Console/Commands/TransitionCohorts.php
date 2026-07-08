<?php

namespace App\Console\Commands;

use App\Models\Cohort;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command to automatically transition cohort statuses based on dates.
 * 
 * This command should be scheduled to run daily to:
 * - Transition 'upcoming' cohorts to 'active' on their start_date
 * - Transition 'active' cohorts to 'completed' after their end_date
 * - Notify admins of cohorts reaching milestones
 * 
 * Schedule: 0 0 * * * (daily at midnight)
 */
class TransitionCohorts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cohorts:transition 
                            {--dry-run : Preview changes without executing them}
                            {--force : Force transition even for cohorts with issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically transition cohort statuses based on their dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $today = Carbon::today();
        
        $this->info('Cohort Status Transition Job');
        $this->info('Date: ' . $today->format('Y-m-d'));
        $this->info('Mode: ' . ($dryRun ? 'DRY RUN' : 'LIVE'));
        $this->newLine();

        $transitioned = 0;
        $errors = [];

        // 1. Transition UPCOMING → ACTIVE on start_date
        $this->info('Checking upcoming cohorts ready to start...');
        $upcomingToActive = Cohort::where('status', Cohort::STATUS_UPCOMING)
            ->where('start_date', '<=', $today)
            ->get();

        foreach ($upcomingToActive as $cohort) {
            $message = "  → [{$cohort->name}] Start date: {$cohort->start_date->format('Y-m-d')}";
            
            // Check minimum fellows requirement
            if (!$force && $cohort->fellows_count < $cohort->min_fellows) {
                $this->warn("{$message} - SKIPPED (only {$cohort->fellows_count}/{$cohort->min_fellows} fellows)");
                $errors[] = "{$cohort->name}: Below minimum fellows ({$cohort->fellows_count}/{$cohort->min_fellows})";
                continue;
            }

            if ($dryRun) {
                $this->info("{$message} - WOULD ACTIVATE");
            } else {
                try {
                    $cohort->transitionTo(Cohort::STATUS_ACTIVE);
                    $this->info("{$message} - ACTIVATED ✓");
                    $transitioned++;
                    
                    Log::info("Cohort auto-transitioned to active", [
                        'cohort_id' => $cohort->id,
                        'cohort_name' => $cohort->name,
                        'fellows_count' => $cohort->fellows_count,
                    ]);
                } catch (\Exception $e) {
                    $this->error("{$message} - FAILED: {$e->getMessage()}");
                    $errors[] = "{$cohort->name}: {$e->getMessage()}";
                }
            }
        }

        if ($upcomingToActive->isEmpty()) {
            $this->line('  No upcoming cohorts ready to start.');
        }

        $this->newLine();

        // 2. Transition ACTIVE → COMPLETED after end_date
        $this->info('Checking active cohorts that have ended...');
        $activeToCompleted = Cohort::where('status', Cohort::STATUS_ACTIVE)
            ->where('end_date', '<', $today)
            ->get();

        foreach ($activeToCompleted as $cohort) {
            $message = "  → [{$cohort->name}] End date: {$cohort->end_date->format('Y-m-d')}";
            
            if ($dryRun) {
                $this->info("{$message} - WOULD COMPLETE");
            } else {
                try {
                    // Update statistics before completing
                    $cohort->updateStatistics();
                    $cohort->updateRankings();
                    
                    $cohort->transitionTo(Cohort::STATUS_COMPLETED);
                    $this->info("{$message} - COMPLETED ✓");
                    $transitioned++;
                    
                    Log::info("Cohort auto-transitioned to completed", [
                        'cohort_id' => $cohort->id,
                        'cohort_name' => $cohort->name,
                        'fellows_count' => $cohort->fellows_count,
                        'completion_rate' => $cohort->completion_rate,
                        'avg_score' => $cohort->avg_score,
                    ]);
                } catch (\Exception $e) {
                    $this->error("{$message} - FAILED: {$e->getMessage()}");
                    $errors[] = "{$cohort->name}: {$e->getMessage()}";
                }
            }
        }

        if ($activeToCompleted->isEmpty()) {
            $this->line('  No active cohorts have ended.');
        }

        $this->newLine();

        // 3. Update active cohort statistics
        $this->info('Updating statistics for active cohorts...');
        $activeCohorts = Cohort::where('status', Cohort::STATUS_ACTIVE)->get();
        
        foreach ($activeCohorts as $cohort) {
            if ($dryRun) {
                $this->line("  → [{$cohort->name}] - WOULD UPDATE STATS");
            } else {
                $cohort->updateStatistics();
                $cohort->updateRankings();
                $this->line("  → [{$cohort->name}] - Updated (Avg: {$cohort->avg_score}%, Completion: {$cohort->completion_rate}%)");
            }
        }

        if ($activeCohorts->isEmpty()) {
            $this->line('  No active cohorts to update.');
        }

        $this->newLine();

        // 4. Check for cohorts with enrollment closing soon
        $this->info('Checking enrollment deadlines...');
        $enrollmentClosingSoon = Cohort::where('status', Cohort::STATUS_UPCOMING)
            ->whereNotNull('enrollment_closes_at')
            ->whereBetween('enrollment_closes_at', [$today, $today->copy()->addDays(3)])
            ->get();

        foreach ($enrollmentClosingSoon as $cohort) {
            $daysLeft = $today->diffInDays($cohort->enrollment_closes_at);
            $this->warn("  → [{$cohort->name}] Enrollment closes in {$daysLeft} day(s) ({$cohort->fellows_count}/{$cohort->max_fellows} enrolled)");
        }

        if ($enrollmentClosingSoon->isEmpty()) {
            $this->line('  No enrollment deadlines in next 3 days.');
        }

        $this->newLine();

        // Summary
        $this->info('─────────────────────────────────────');
        $this->info('SUMMARY');
        $this->info("  Cohorts transitioned: {$transitioned}");
        $this->info("  Errors: " . count($errors));
        
        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
