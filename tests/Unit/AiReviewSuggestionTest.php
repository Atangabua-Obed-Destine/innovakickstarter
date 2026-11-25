<?php

namespace Tests\Unit;

use App\Models\AiReview;
use PHPUnit\Framework\TestCase;

class AiReviewSuggestionTest extends TestCase
{
    protected function review(string $decision, ?float $score, string $status = AiReview::STATUS_COMPLETED): AiReview
    {
        return (new AiReview())->forceFill(['status' => $status, 'recommended_decision' => $decision, 'weighted_score' => $score]);
    }

    public function test_suggested_decision_maps_to_the_review_form(): void
    {
        $this->assertSame('approve', $this->review(AiReview::DECISION_APPROVE, 82)->suggested_decision);
        $this->assertSame('reject', $this->review(AiReview::DECISION_REVISE, 34)->suggested_decision);
        $this->assertNull($this->review(AiReview::DECISION_ESCALATE, 60)->suggested_decision);
        $this->assertSame('Reject (send back to redo)', $this->review(AiReview::DECISION_REVISE, 34)->decision_label);
    }

    public function test_suggested_points_follow_the_score_and_never_exceed_the_activity(): void
    {
        $this->assertSame(8, $this->review(AiReview::DECISION_APPROVE, 82)->suggestedPoints(10));
        $this->assertSame(10, $this->review(AiReview::DECISION_APPROVE, 100)->suggestedPoints(10));
        $this->assertSame(1, $this->review(AiReview::DECISION_APPROVE, 2)->suggestedPoints(10), 'An approval is worth at least 1 point.');
        $this->assertSame(0, $this->review(AiReview::DECISION_REVISE, 34)->suggestedPoints(10), 'Sent back to redo: no points.');
        $this->assertSame(6, $this->review(AiReview::DECISION_ESCALATE, 60)->suggestedPoints(10));
        $this->assertNull($this->review(AiReview::DECISION_APPROVE, null)->suggestedPoints(10));
        $this->assertNull($this->review(AiReview::DECISION_APPROVE, 80, 'queued')->suggestedPoints(10));
    }
}
