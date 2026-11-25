<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\AiReview;
use App\Models\AuditLog;
use App\Models\FellowCurriculumProgress;
use App\Services\AiReview\AiReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * AI review of curriculum submissions: agreement dashboard, settings,
 * and actions from the review page (re-run, reveal).
 */
class AiReviewController extends Controller
{
    /** Bar an activity type must clear before AI decisions are trusted (phase 2). */
    public const READY_MIN_CASES = 30;
    public const READY_MIN_AGREEMENT = 90;

    public function __construct(protected AiReviewService $service)
    {
    }

    /**
     * How often the AI agrees with human reviewers, overall and by activity type.
     */
    public function index(Request $request): View
    {
        $reviews = AiReview::with(['curriculumActivity:id,title,type,points', 'fellow:id,name', 'humanReviewer:id,name'])
            ->latest()
            ->get();

        $byType = $reviews->groupBy(fn ($r) => $r->curriculumActivity?->type?->value ?? 'unknown')
            ->map(fn (Collection $group, $type) => ['type' => $type] + $this->stats($group))
            ->sortByDesc('total')
            ->values();

        return view('admin.ai-reviews.index', [
            'overall' => $this->stats($reviews),
            'byType' => $byType,
            'recent' => $reviews->take(40),
            'settings' => [
                'mode' => AdminSetting::get('ai_review_mode', 'shadow'),
                'pass_score' => (int) AdminSetting::get('ai_review_pass_score', 70),
                'criterion_floor' => (int) AdminSetting::get('ai_review_criterion_floor', 50),
                'revise_below' => (int) AdminSetting::get('ai_review_revise_below', 50),
                'similarity_threshold' => (int) AdminSetting::get('ai_review_similarity_threshold', 50),
                'human_from_points' => (int) AdminSetting::get('ai_review_human_from_points', 60),
            ],
            'configured' => !empty(config('services.openai.key')),
            'githubToken' => !empty(config('services.github.token')),
            'model' => config('services.openai.review_model'),
            'readyMinCases' => self::READY_MIN_CASES,
            'readyMinAgreement' => self::READY_MIN_AGREEMENT,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_review_mode' => 'required|in:off,shadow,assist',
            'ai_review_pass_score' => 'required|integer|min:50|max:100',
            'ai_review_criterion_floor' => 'required|integer|min:0|max:100',
            'ai_review_revise_below' => 'required|integer|min:0|max:100|lt:ai_review_pass_score',
            'ai_review_similarity_threshold' => 'required|integer|min:20|max:100',
            'ai_review_human_from_points' => 'required|integer|min:0|max:1000',
        ], [
            'ai_review_revise_below.lt' => 'The revision threshold must be below the approval score.',
        ]);

        $old = [];
        foreach ($validated as $key => $value) {
            $old[$key] = AdminSetting::get($key);
            AdminSetting::set($key, $key === 'ai_review_mode' ? $value : (int) $value, $request->user()->id);
        }

        AuditLog::create([
            'admin_id' => $request->user()->id,
            'action' => 'ai_review_settings_updated',
            'auditable_type' => AdminSetting::class,
            'auditable_id' => 'ai_review_settings',
            'justification' => 'AI review settings updated',
            'old_values' => $old,
            'new_values' => $validated,
        ]);

        return redirect()->route('admin.ai-reviews.index')->with('success', 'AI review settings saved.');
    }

    /**
     * Run (or re-run) the AI review of a submission now.
     */
    public function run(string $progressId): RedirectResponse
    {
        $progress = FellowCurriculumProgress::with('curriculumActivity')->findOrFail($progressId);

        if (!$this->service->shouldReview($progress)) {
            return back()->with('error', 'AI review is off for this activity.');
        }

        $review = $this->service->queue($progress, sync: true);

        return back()->with(
            $review?->isCompleted() ? 'success' : 'error',
            $review?->isCompleted() ? 'AI review completed.' : 'The AI review did not complete: ' . ($review?->error ?? 'unknown error')
        );
    }

    /**
     * Show the AI verdict before deciding (shadow mode). The review is then
     * left out of the agreement statistics, because the human saw it.
     */
    public function reveal(string $progressId): RedirectResponse
    {
        $progress = FellowCurriculumProgress::findOrFail($progressId);
        $review = $this->service->currentFor($progress);

        if ($review && !$review->human_decision) {
            $review->update(['revealed_before_decision' => true]);
        }

        return back();
    }

    /**
     * Agreement statistics for a set of reviews.
     */
    protected function stats(Collection $reviews): array
    {
        $completed = $reviews->where('status', AiReview::STATUS_COMPLETED);
        // Comparable: a human decided without seeing the AI verdict first.
        $decided = $reviews->filter(fn ($r) => $r->human_decision && !$r->revealed_before_decision && $r->isCompleted());
        $compared = $decided->filter(fn ($r) => $r->agrees !== null);
        $agreeing = $compared->filter(fn ($r) => $r->agrees === true);

        $falseApprovals = $decided->filter(fn ($r) => $r->recommended_decision === AiReview::DECISION_APPROVE && $r->human_decision === 'reject');
        $missedApprovals = $decided->filter(fn ($r) => $r->recommended_decision === AiReview::DECISION_REVISE && $r->human_decision === 'approve');
        $scoreGaps = $decided
            ->filter(fn ($r) => $r->human_decision === 'approve' && $r->human_score !== null && $r->weighted_score !== null)
            ->map(fn ($r) => abs($r->weighted_score - $r->human_score));

        $agreement = $compared->count() ? round($agreeing->count() * 100 / $compared->count(), 1) : null;

        return [
            'total' => $reviews->count(),
            'completed' => $completed->count(),
            'failed' => $reviews->where('status', AiReview::STATUS_FAILED)->count(),
            'pending' => $reviews->whereIn('status', [AiReview::STATUS_QUEUED, AiReview::STATUS_RUNNING])->count(),
            'approve' => $completed->where('recommended_decision', AiReview::DECISION_APPROVE)->count(),
            'revise' => $completed->where('recommended_decision', AiReview::DECISION_REVISE)->count(),
            'escalate' => $reviews->where('recommended_decision', AiReview::DECISION_ESCALATE)->count(),
            'decided' => $decided->count(),
            'compared' => $compared->count(),
            'agreement' => $agreement,
            'false_approvals' => $falseApprovals->count(),
            'missed_approvals' => $missedApprovals->count(),
            'avg_score_gap' => $scoreGaps->count() ? round($scoreGaps->avg(), 1) : null,
            'tokens' => (int) $reviews->sum('input_tokens') + (int) $reviews->sum('output_tokens'),
            'ready' => $compared->count() >= self::READY_MIN_CASES
                && $agreement !== null && $agreement >= self::READY_MIN_AGREEMENT
                && $falseApprovals->isEmpty(),
        ];
    }
}
