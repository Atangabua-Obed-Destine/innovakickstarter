<?php

namespace App\Console\Commands;

use App\Services\AttestationService;
use Illuminate\Console\Command;

/**
 * Command to close internships whose approved window has passed.
 *
 * Marks them completed, notifies the fellow and opens a draft attestation
 * for admin review. Also backfills drafts for internships that were
 * completed before attestations existed.
 *
 * Schedule: daily
 */
class CompleteExpiredInternships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internships:complete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete internships past their end date and open their attestation drafts';

    /**
     * Execute the console command.
     */
    public function handle(AttestationService $attestations): int
    {
        $completed = $attestations->syncCompleted();

        $this->info("Internships completed: {$completed}. Attestation drafts are up to date.");

        return self::SUCCESS;
    }
}
