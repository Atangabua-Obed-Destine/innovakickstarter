<?php

namespace App\Console\Commands;

use App\Services\ReviewPartnerService;
use Illuminate\Console\Command;

/** Pair fellows waiting at collaborative activities; flag long waits to the team. */
class MatchReviewPartners extends Command
{
    protected $signature = 'curriculum:match-review-partners';

    protected $description = 'Pair fellows waiting for a review partner and alert admins about long waits';

    public function handle(ReviewPartnerService $partners): int
    {
        $result = $partners->matchWaiting();
        $this->info("Paired {$result['paired']}, alerted the team about {$result['alerted']} fellow(s).");

        return self::SUCCESS;
    }
}
