<?php

namespace App\Jobs;

use App\Models\AiReview;
use App\Services\AiReview\AiReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs the AI review of one submission attempt in the background.
 * Failures are recorded on the review (and the submission goes to a human);
 * the job itself does not retry, so a broken submission cannot loop.
 */
class RunAiReview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(public string $aiReviewId)
    {
    }

    public function handle(AiReviewService $service): void
    {
        $review = AiReview::find($this->aiReviewId);

        if ($review && in_array($review->status, [AiReview::STATUS_QUEUED, AiReview::STATUS_RUNNING], true)) {
            $service->run($review);
        }
    }

    public function failed(\Throwable $e): void
    {
        AiReview::whereKey($this->aiReviewId)->update([
            'status' => AiReview::STATUS_FAILED,
            'recommended_decision' => AiReview::DECISION_ESCALATE,
            'decision_reasons' => ['The AI review did not complete.'],
            'error' => mb_substr($e->getMessage(), 0, 2000),
        ]);
    }
}
