<?php

namespace App\Jobs;

use App\Models\InterviewSession;
use App\Services\Interview\InterviewScorer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Scores a finished AI interview in the background (the AI call waits on rate limits).
 * A failure marks the session "scoring failed": no score is ever invented.
 */
class ScoreInterview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 420;

    public function __construct(public string $interviewId) {}

    public function handle(InterviewScorer $scorer): void
    {
        $session = InterviewSession::find($this->interviewId);
        if ($session) {
            $scorer->score($session);
        }
    }

    public function failed(\Throwable $e): void
    {
        $session = InterviewSession::find($this->interviewId);
        if ($session && $session->scoring_status !== 'done') {
            app(InterviewScorer::class)->markFailed($session, $e->getMessage());
        }
    }
}
