<?php

namespace App\Models;

use App\Enums\InterviewType;
use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * InterviewSession Model
 * 
 * Represents both AI-powered and human mock interviews.
 * This is a flagship feature - interview readiness is 25% of Career Capital.
 * 
 * @property string $id UUID
 * @property int $fellow_id
 * @property string $track_id UUID
 * @property int|null $interviewer_id
 * @property InterviewType $type
 * @property InterviewMode $mode
 * @property InterviewStatus $status
 * @property float|null $score
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class InterviewSession extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
        'track_id',
        'interviewer_id',
        'type',
        'mode',
        'difficulty',
        'title',
        'description',
        'duration_minutes',
        'target_duration',
        'scheduled_at',
        'started_at',
        'completed_at',
        'status',
        'score',
        'rubric_scores',
        'percentile',
        'questions',
        'responses',
        'transcript',
        'ai_feedback',
        'interviewer_notes',
        'fellow_feedback',
        'interviewer_rating',
        'communication_metrics',
        'video_url',
        'audio_url',
        'points_earned',
        'meeting_link',
        'cancellation_reason',
        'is_practice',
        'technical_score',
        'communication_score',
        'problem_solving_score',
        'overall_score',
        'feedback',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'internal_notes',
        'filler_word_count',
        'speaking_pace_wpm',
        'confidence_score',
        'difficulty_level',
        'curriculum_activity_id',
        'curriculum_progress_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => InterviewType::class,
            'mode' => InterviewMode::class,
            'status' => InterviewStatus::class,
            'score' => 'decimal:2',
            'rubric_scores' => 'array',
            'percentile' => 'integer',
            'questions' => 'array',
            'responses' => 'array',
            'transcript' => 'array',
            'ai_feedback' => 'array',
            'communication_metrics' => 'array',
            'duration_minutes' => 'integer',
            'target_duration' => 'integer',
            'interviewer_rating' => 'integer',
            'points_earned' => 'integer',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_practice' => 'boolean',
            'technical_score' => 'decimal:2',
            'communication_score' => 'decimal:2',
            'problem_solving_score' => 'decimal:2',
            'overall_score' => 'decimal:2',
            'confidence_score' => 'decimal:2',
            'filler_word_count' => 'integer',
            'speaking_pace_wpm' => 'integer',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'pending',
        'difficulty' => 'medium',
        'target_duration' => 30,
        'points_earned' => 0,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow being interviewed.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track for this interview.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the human interviewer (null for AI).
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * Get the linked curriculum activity (if this interview was created from curriculum).
     */
    public function curriculumActivity(): BelongsTo
    {
        return $this->belongsTo(\App\Models\TrackCurriculumActivity::class, 'curriculum_activity_id');
    }

    /**
     * Get the linked curriculum progress record.
     */
    public function curriculumProgress(): BelongsTo
    {
        return $this->belongsTo(\App\Models\FellowCurriculumProgress::class, 'curriculum_progress_id');
    }

    /**
     * Check if this interview is linked to a curriculum activity.
     */
    public function isLinkedToCurriculum(): bool
    {
        return !is_null($this->curriculum_activity_id) && !is_null($this->curriculum_progress_id);
    }

    /**
     * Alias: Get/set mentor_id as interviewer_id for backward compatibility.
     */
    public function getMentorIdAttribute(): ?int
    {
        return $this->interviewer_id;
    }

    public function setMentorIdAttribute($value): void
    {
        $this->attributes['interviewer_id'] = $value;
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }

    /**
     * Get type short label.
     */
    public function getTypeShortLabelAttribute(): string
    {
        return $this->type->shortLabel();
    }

    /**
     * Get mode label.
     */
    public function getModeLabelAttribute(): string
    {
        return $this->mode->label();
    }

    /**
     * Get mode icon.
     */
    public function getModeIconAttribute(): string
    {
        return $this->mode->icon();
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status->badgeClass();
    }

    /**
     * Check if this is an AI interview.
     */
    public function getIsAiAttribute(): bool
    {
        return $this->mode === InterviewMode::AI;
    }

    /**
     * Check if this is a human interview.
     */
    public function getIsHumanAttribute(): bool
    {
        return $this->mode === InterviewMode::HUMAN;
    }

    /**
     * Get formatted score.
     */
    public function getFormattedScoreAttribute(): string
    {
        if ($this->score === null) {
            return 'N/A';
        }
        return number_format($this->score, 0) . '/100';
    }

    /**
     * Get score color class based on score.
     */
    public function getScoreColorAttribute(): string
    {
        if ($this->score === null) return 'gray';
        if ($this->score >= 90) return 'green';
        if ($this->score >= 75) return 'blue';
        if ($this->score >= 60) return 'yellow';
        return 'red';
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return $this->target_duration . ' min (target)';
        }
        return $this->duration_minutes . ' min';
    }

    /**
     * Get rubric scores as formatted array.
     */
    public function getFormattedRubricScoresAttribute(): array
    {
        if (empty($this->rubric_scores)) {
            return [];
        }

        $formatted = [];
        foreach ($this->rubric_scores as $key => $value) {
            $formatted[] = [
                'key' => $key,
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'score' => $value,
                'percentage' => $value, // Assuming 0-100 scale
            ];
        }
        return $formatted;
    }

    /**
     * Get communication insights.
     */
    public function getCommunicationInsightsAttribute(): array
    {
        if (empty($this->communication_metrics)) {
            return [];
        }

        return [
            'filler_words' => [
                'value' => $this->communication_metrics['filler_words_per_min'] ?? 0,
                'label' => 'Filler Words/Min',
                'benchmark' => 8,
                'good' => ($this->communication_metrics['filler_words_per_min'] ?? 0) < 5,
            ],
            'speaking_pace' => [
                'value' => $this->communication_metrics['speaking_pace'] ?? 0,
                'label' => 'Words/Min',
                'benchmark' => 150,
                'good' => ($this->communication_metrics['speaking_pace'] ?? 0) >= 120 && 
                          ($this->communication_metrics['speaking_pace'] ?? 0) <= 180,
            ],
            'confidence_score' => [
                'value' => $this->communication_metrics['confidence_score'] ?? 0,
                'label' => 'Confidence',
                'benchmark' => 75,
                'good' => ($this->communication_metrics['confidence_score'] ?? 0) >= 75,
            ],
        ];
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to AI interviews.
     */
    public function scopeAi($query)
    {
        return $query->where('mode', InterviewMode::AI);
    }

    /**
     * Scope to human interviews.
     */
    public function scopeHuman($query)
    {
        return $query->where('mode', InterviewMode::HUMAN);
    }

    /**
     * Scope to completed interviews.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', InterviewStatus::COMPLETED);
    }

    /**
     * Scope to scheduled interviews.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', InterviewStatus::SCHEDULED);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, InterviewType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to upcoming scheduled interviews.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', InterviewStatus::SCHEDULED);
    }

    /**
     * Scope to this week's interviews.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Order by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if interview is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === InterviewStatus::COMPLETED;
    }

    /**
     * Check if interview can be started.
     */
    public function canStart(): bool
    {
        return $this->status->canStart();
    }

    /**
     * Check if interview can be cancelled.
     */
    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    /**
     * Start the interview.
     */
    public function start(): void
    {
        $this->status = InterviewStatus::IN_PROGRESS;
        $this->started_at = now();
        $this->save();
    }

    /**
     * Complete the interview.
     */
    public function complete(
        float $score,
        array $rubricScores = [],
        array $feedback = [],
        ?array $transcript = null
    ): void {
        $this->status = InterviewStatus::COMPLETED;
        $this->completed_at = now();
        $this->score = $score;
        $this->overall_score = $score;
        $this->rubric_scores = $rubricScores;
        $this->ai_feedback = $feedback;
        $this->transcript = $transcript;
        
        if ($this->started_at) {
            $this->duration_minutes = $this->started_at->diffInMinutes(now());
        }

        // Calculate points based on type and mode
        $this->points_earned = $this->mode === InterviewMode::AI 
            ? $this->type->defaultAiPoints()
            : $this->type->defaultHumanPoints();
        
        $this->save();
    }

    /**
     * Cancel the interview.
     */
    public function cancel(?string $reason = null): void
    {
        $this->status = InterviewStatus::CANCELLED;
        $this->cancellation_reason = $reason;
        $this->save();
    }

    /**
     * Mark as no-show.
     */
    public function markNoShow(): void
    {
        $this->status = InterviewStatus::NO_SHOW;
        $this->save();
    }

    /**
     * Get average score for fellow in this interview type.
     */
    public static function getAverageScoreForFellow(int $fellowId, InterviewType $type): float
    {
        return static::where('fellow_id', $fellowId)
            ->where('type', $type)
            ->where('status', InterviewStatus::COMPLETED)
            ->avg('score') ?? 0;
    }

    /**
     * Get count for fellow this week.
     */
    public static function getWeeklyCountForFellow(int $fellowId, InterviewType $type): int
    {
        return static::where('fellow_id', $fellowId)
            ->where('type', $type)
            ->where('status', InterviewStatus::COMPLETED)
            ->whereBetween('completed_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->count();
    }
}
