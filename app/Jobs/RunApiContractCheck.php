<?php

namespace App\Jobs;

use App\Models\FellowCurriculumProgress;
use App\Services\ApiContractService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Runs a backend project's API contract in the background: a sleeping free-tier host can take
 * a minute to wake, longer than a web request may last. The result goes to the cache (for the
 * "Check my API" button, which polls) and, for a submission, into the progress evidence.
 */
class RunApiContractCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 480;

    public function __construct(public string $progressId, public string $url, public string $token, public bool $forSubmission = false) {}

    public static function cacheKey(string $token): string
    {
        return 'api-contract:' . $token;
    }

    public function handle(ApiContractService $contracts): void
    {
        $progress = FellowCurriculumProgress::with('curriculumActivity')->find($this->progressId);
        $contract = $progress?->curriculumActivity?->api_contract;
        $result = is_array($contract) ? $contracts->run($contract, $this->url) : ['ok' => false, 'error' => 'This activity has no API checks.'];
        Cache::put(self::cacheKey($this->token), $result, now()->addHour());

        if ($this->forSubmission && $progress) {
            $evidence = (array) $progress->evidence;
            $evidence['contract'] = $result['ok'] ? $result : ['ok' => false, 'base' => $this->url, 'error' => $result['error'] ?? 'The check could not run.', 'checked_at' => now()->toIso8601String()];
            $progress->forceFill(['evidence' => $evidence])->save();
        }
    }

    public function failed(\Throwable $e): void
    {
        Cache::put(self::cacheKey($this->token), ['ok' => false, 'error' => 'The check stopped unexpectedly. Try again in a minute.'], now()->addHour());
    }
}
