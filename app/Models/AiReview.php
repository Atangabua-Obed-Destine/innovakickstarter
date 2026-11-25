<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI review of one curriculum submission attempt.
 *
 * The AI never decides on its own in phase 1: `recommended_decision` is what
 * it would do (approve / revise / escalate). Once a human reviews the same
 * attempt, `human_decision` is stored next to it for agreement statistics.
 */
class AiReview extends Model
{
    use HasUuids;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public const MODE_OFF = 'off';
    public const MODE_SHADOW = 'shadow';
    public const MODE_ASSIST = 'assist';

    public const MODES = [
        self::MODE_OFF => 'Off',
        self::MODE_SHADOW => 'Shadow (AI reviews silently; humans decide)',
        self::MODE_ASSIST => 'Assist (AI draft shown to the reviewer)',
    ];

    public const DECISION_APPROVE = 'approve';
    public const DECISION_REVISE = 'revise';
    public const DECISION_ESCALATE = 'escalate';

    protected $fillable = [
        'progress_id', 'curriculum_activity_id', 'fellow_id', 'attempt_number',
        'status', 'mode', 'model', 'prompt_version',
        'evidence', 'checks', 'integrity', 'criteria', 'weighted_score', 'confidence',
        'flags', 'requirements_met', 'missing_requirements', 'authenticity_concern',
        'summary', 'feedback_for_fellow', 'notes_for_reviewer',
        'recommended_decision', 'decision_reasons',
        'revealed_before_decision', 'human_decision', 'human_reviewer_id', 'human_score', 'human_decided_at',
        'input_tokens', 'output_tokens', 'duration_ms', 'error',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'checks' => 'array',
            'integrity' => 'array',
            'criteria' => 'array',
            'flags' => 'array',
            'missing_requirements' => 'array',
            'decision_reasons' => 'array',
            'weighted_score' => 'float',
            'requirements_met' => 'boolean',
            'revealed_before_decision' => 'boolean',
            'human_decided_at' => 'datetime',
        ];
    }

    public function progress(): BelongsTo
    {
        return $this->belongsTo(FellowCurriculumProgress::class, 'progress_id');
    }

    public function curriculumActivity(): BelongsTo
    {
        return $this->belongsTo(TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function humanReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'human_reviewer_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Did the AI recommendation match the human decision? Null when there is
     * nothing to compare (no human decision yet, or the AI escalated).
     */
    public function getAgreesAttribute(): ?bool
    {
        if (!$this->human_decision || !$this->isCompleted() || $this->recommended_decision === self::DECISION_ESCALATE) {
            return null;
        }

        $aiApproves = $this->recommended_decision === self::DECISION_APPROVE;

        return $aiApproves === ($this->human_decision === 'approve');
    }

    public function getDecisionLabelAttribute(): string
    {
        return match ($this->recommended_decision) {
            self::DECISION_APPROVE => 'Approve',
            self::DECISION_REVISE => 'Return for revision',
            self::DECISION_ESCALATE => 'Needs a human',
            default => '—',
        };
    }

    public function getDecisionBadgeClassAttribute(): string
    {
        return match ($this->recommended_decision) {
            self::DECISION_APPROVE => 'bg-green-500/20 text-green-400 border-green-500/30',
            self::DECISION_REVISE => 'bg-red-500/20 text-red-400 border-red-500/30',
            self::DECISION_ESCALATE => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            default => 'bg-dark-700 text-dark-300 border-dark-600',
        };
    }
}
