<?php

namespace App\Models;

use App\Enums\CurriculumStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fellow Curriculum Progress Model
 * 
 * Tracks an individual fellow's progress on a specific curriculum activity.
 * This is the core table connecting fellows to their structured learning journey.
 * 
 * Business Rules:
 * - One record per fellow per curriculum activity
 * - Status follows lifecycle: locked → available → in_progress → submitted → 
 *   peer_review → under_review → completed/rejected/overdue
 * - Deadlines are absolute (calculated from fellow's track enrollment + activity's relative days)
 * - Late submissions get penalty deducted from points
 * - Rejected submissions can be resubmitted (attempt_number increments)
 * - Peer review precedes mentor review when requires_peer_review is true
 * - mock_interview activities auto-link to interview_sessions
 * - Freestyle activities can be linked to satisfy curriculum requirements
 * 
 * @property string $id UUID
 * @property int $fellow_id FK to users
 * @property string $curriculum_activity_id FK to track_curriculum_activities
 * @property CurriculumStatus $status
 * @property \Carbon\Carbon|null $deadline_at
 * @property \Carbon\Carbon|null $grace_deadline_at
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $submitted_at
 * @property \Carbon\Carbon|null $completed_at
 * @property array|null $evidence
 * @property string|null $submission_notes
 * @property int|null $reviewer_id FK to users
 * @property string|null $review_notes
 * @property \Carbon\Carbon|null $reviewed_at
 * @property array|null $rubric_scores
 * @property int $score_awarded
 * @property int $points_awarded
 * @property bool $late_penalty_applied
 * @property int $attempt_number
 * @property int|null $peer_reviewer_id FK to users
 * @property string|null $peer_review_notes
 * @property \Carbon\Carbon|null $peer_reviewed_at
 * @property int|null $peer_review_score
 * @property string|null $linked_activity_id FK to activities
 * @property string|null $linked_interview_id FK to interview_sessions
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FellowCurriculumProgress extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'fellow_curriculum_progress';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fellow_id',
        'curriculum_activity_id',
        'status',
        'deadline_at',
        'grace_deadline_at',
        'started_at',
        'submitted_at',
        'completed_at',
        'evidence',
        'submission_notes',
        'reviewer_id',
        'review_notes',
        'reviewed_at',
        'rubric_scores',
        'score_awarded',
        'points_awarded',
        'late_penalty_applied',
        'attempt_number',
        'peer_reviewer_id',
        'peer_review_notes',
        'peer_reviewed_at',
        'peer_review_score',
        'linked_activity_id',
        'linked_interview_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => CurriculumStatus::class,
            'deadline_at' => 'datetime',
            'grace_deadline_at' => 'datetime',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'peer_reviewed_at' => 'datetime',
            'evidence' => 'array',
            'rubric_scores' => 'array',
            'score_awarded' => 'integer',
            'points_awarded' => 'integer',
            'late_penalty_applied' => 'boolean',
            'attempt_number' => 'integer',
            'peer_review_score' => 'integer',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'locked',
        'score_awarded' => 0,
        'points_awarded' => 0,
        'late_penalty_applied' => false,
        'attempt_number' => 1,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow working on this activity.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the curriculum activity being tracked.
     */
    public function curriculumActivity(): BelongsTo
    {
        return $this->belongsTo(TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    /**
     * Get the reviewer (mentor/admin).
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the multiple pod peer reviews for this submission.
     */
    public function peerReviews()
    {
        return $this->hasMany(FellowActivityPeerReview::class, 'progress_id');
    }

    /**
     * Get the linked freestyle activity (if any).
     */
    public function linkedActivity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'linked_activity_id');
    }

    /**
     * Get the linked interview session (if any).
     */
    public function linkedInterview(): BelongsTo
    {
        return $this->belongsTo(InterviewSession::class, 'linked_interview_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to a specific fellow.
     */
    public function scopeForFellow($query, $fellowId)
    {
        return $query->where('fellow_id', $fellowId);
    }

    /**
     * Scope to a specific status.
     */
    public function scopeOfStatus($query, CurriculumStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope to completed activities.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', CurriculumStatus::COMPLETED->value);
    }

    /**
     * Scope to activities pending review.
     */
    public function scopePendingReview($query)
    {
        return $query->whereIn('status', [
            CurriculumStatus::SUBMITTED->value,
            CurriculumStatus::UNDER_REVIEW->value,
        ]);
    }

    /**
     * Scope to activities pending peer review.
     */
    public function scopePendingPeerReview($query)
    {
        return $query->where('status', CurriculumStatus::PEER_REVIEW->value);
    }

    /**
     * Scope to overdue activities.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', CurriculumStatus::OVERDUE->value);
    }

    /**
     * Scope to activities past deadline but not yet marked overdue.
     */
    public function scopePastDeadline($query)
    {
        return $query->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->whereNotIn('status', [
                CurriculumStatus::COMPLETED->value,
                CurriculumStatus::LOCKED->value,
            ]);
    }

    /**
     * Scope to active (non-terminal) progress.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            CurriculumStatus::COMPLETED->value,
            CurriculumStatus::LOCKED->value,
        ]);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getEvidenceUrlAttribute()
    {
        return is_array($this->evidence) ? ($this->evidence['evidence_url'] ?? null) : null;
    }

    public function getEvidenceTextAttribute()
    {
        return is_array($this->evidence) ? ($this->evidence['evidence_text'] ?? null) : null;
    }

    public function getEvidenceFilesAttribute()
    {
        return is_array($this->evidence) ? ($this->evidence['evidence_files'] ?? []) : [];
    }

    public function getReflectionAttribute()
    {
        return $this->submission_notes;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? 'Unknown';
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status?->badgeClass() ?? '';
    }

    /**
     * Get status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return $this->status?->icon() ?? '';
    }

    /**
     * Check if submission is past deadline.
     */
    public function getIsPastDeadlineAttribute(): bool
    {
        if (!$this->deadline_at) {
            return false;
        }

        return now()->isAfter($this->deadline_at);
    }

    /**
     * Check if still within grace period.
     */
    public function getIsWithinGracePeriodAttribute(): bool
    {
        if (!$this->deadline_at || !$this->grace_deadline_at) {
            return false;
        }

        return now()->isAfter($this->deadline_at) && now()->isBefore($this->grace_deadline_at);
    }

    /**
     * Get days remaining until deadline.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline_at) {
            return null;
        }

        $days = (int) now()->diffInDays($this->deadline_at, false);
        return $days;
    }

    /**
     * Get formatted time remaining.
     */
    public function getTimeRemainingAttribute(): string
    {
        if (!$this->deadline_at) {
            return 'No deadline';
        }

        if ($this->status === CurriculumStatus::COMPLETED) {
            return 'Completed';
        }

        $days = $this->days_remaining;

        if ($days < 0) {
            $overdueDays = abs($days);
            return "{$overdueDays} day" . ($overdueDays !== 1 ? 's' : '') . ' overdue';
        }

        if ($days === 0) {
            return 'Due today';
        }

        return "{$days} day" . ($days !== 1 ? 's' : '') . ' left';
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Transition to in-progress status.
     */
    public function markInProgress(): self
    {
        $this->update([
            'status' => CurriculumStatus::IN_PROGRESS,
            'started_at' => $this->started_at ?? now(),
        ]);

        return $this;
    }

    /**
     * Submit evidence for review.
     */
    public function submit(array $evidence, ?string $notes = null): self
    {
        $curriculumActivity = $this->curriculumActivity;
        $targetStatus = $curriculumActivity->requires_peer_review
            ? CurriculumStatus::PEER_REVIEW
            : CurriculumStatus::SUBMITTED;

        $this->update([
            'status' => $targetStatus,
            'evidence' => $evidence,
            'submission_notes' => $notes,
            'submitted_at' => now(),
            'late_penalty_applied' => $this->is_past_deadline,
        ]);

        return $this;
    }

    /**
     * Complete one peer review and advance to mentor review if all are done.
     */
    public function completePeerReview(User $peerReviewer, string $notes, int $score): self
    {
        $review = $this->peerReviews()->where('reviewer_id', $peerReviewer->id)->first();
        if ($review) {
            $review->update([
                'status' => 'completed',
                'notes' => $notes,
                'score' => $score,
                'reviewed_at' => now(),
            ]);
        }

        // Check if all peer reviews are completed or bypassed
        $pendingCount = $this->peerReviews()->where('status', 'pending')->count();
        if ($pendingCount === 0) {
            // Average the score
            $avgScore = (int) round($this->peerReviews()->where('status', 'completed')->avg('score') ?? 0);
            
            $this->update([
                'status' => CurriculumStatus::SUBMITTED,
                'peer_reviewed_at' => now(),
                'peer_review_score' => $avgScore,
            ]);
        }

        return $this;
    }

    /**
     * Bypass peer review manually.
     */
    public function bypassPeerReview(string $reason): self
    {
        $this->peerReviews()->where('status', 'pending')->update([
            'status' => 'bypassed'
        ]);

        $this->update([
            'status' => CurriculumStatus::SUBMITTED,
            'peer_review_bypass_reason' => $reason,
            'peer_reviewed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Start mentor/admin review.
     */
    public function startReview(User $reviewer): self
    {
        $this->update([
            'status' => CurriculumStatus::UNDER_REVIEW,
            'reviewer_id' => $reviewer->id,
        ]);

        return $this;
    }

    /**
     * Approve the submission.
     */
    public function approve(User $reviewer, array $rubricScores, string $notes, int $pointsOverride = null): self
    {
        $curriculumActivity = $this->curriculumActivity;
        $basePoints = $pointsOverride ?? $curriculumActivity->points;

        // Apply late penalty if applicable
        if ($this->late_penalty_applied) {
            $basePoints = (int) round($basePoints * (1 - ($curriculumActivity->late_penalty_percent / 100)));
        }

        // Calculate score from rubric
        $totalScore = $this->calculateRubricScore($rubricScores, $curriculumActivity->evaluation_rubric);

        $this->update([
            'status' => CurriculumStatus::COMPLETED,
            'reviewer_id' => $reviewer->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'completed_at' => now(),
            'rubric_scores' => $rubricScores,
            'score_awarded' => $totalScore,
            'points_awarded' => $basePoints,
        ]);

        return $this;
    }

    /**
     * Reject the submission.
     */
    public function reject(User $reviewer, string $notes, array $rubricScores = null): self
    {
        $this->update([
            'status' => CurriculumStatus::REJECTED,
            'reviewer_id' => $reviewer->id,
            'review_notes' => $notes,
            'reviewed_at' => now(),
            'rubric_scores' => $rubricScores,
            'attempt_number' => $this->attempt_number + 1,
        ]);

        return $this;
    }

    /**
     * Undo an approval or rejection.
     */
    public function undoReview(): self
    {
        // If it was rejected, we decrement the attempt number
        $newAttempt = $this->status === CurriculumStatus::REJECTED 
            ? max(1, $this->attempt_number - 1) 
            : $this->attempt_number;

        $this->update([
            'status' => CurriculumStatus::UNDER_REVIEW,
            // We keep reviewer_id so they know who was reviewing it
            'review_notes' => null,
            'reviewed_at' => null,
            'completed_at' => null,
            'rubric_scores' => null,
            'score_awarded' => 0,
            'points_awarded' => 0,
            'attempt_number' => $newAttempt,
        ]);

        return $this;
    }

    /**
     * Mark as overdue.
     */
    public function markOverdue(): self
    {
        if (!$this->status->isTerminal() && $this->status !== CurriculumStatus::LOCKED) {
            $this->update([
                'status' => CurriculumStatus::OVERDUE,
            ]);
        }

        return $this;
    }

    /**
     * Calculate weighted rubric score.
     */
    protected function calculateRubricScore(array $scores, ?array $rubric): int
    {
        if (empty($rubric) || empty($scores)) {
            // If no rubric, average the scores
            return !empty($scores) ? (int) round(array_sum($scores) / count($scores)) : 0;
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($rubric as $criterion => $config) {
            $weight = $config['weight'] ?? 0;
            $score = $scores[$criterion] ?? 0;
            $weightedSum += ($score * $weight);
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? (int) round($weightedSum / $totalWeight) : 0;
    }

    /**
     * Check if this submission can be resubmitted.
     */
    public function canResubmit(): bool
    {
        return in_array($this->status, [
            CurriculumStatus::REJECTED,
            CurriculumStatus::OVERDUE,
        ]);
    }

    /**
     * Check if this progress is actionable by the fellow.
     */
    public function canSubmit(): bool
    {
        return $this->status->canSubmit();
    }
}
