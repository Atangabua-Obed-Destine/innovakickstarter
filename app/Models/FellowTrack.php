<?php

namespace App\Models;

use App\Enums\Tier;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FellowTrack Model (Pivot with extra data)
 * 
 * Represents a fellow's enrollment in a specific track.
 * Stores the Career Capital score, tier, and category breakdowns.
 * 
 * Business Rules:
 * - Only ONE is_primary = true per fellow
 * - Sum of effort_allocation across all tracks = 100%
 * 
 * @property string $id UUID
 * @property int $fellow_id
 * @property string $track_id UUID
 * @property float $score
 * @property string $tier
 * @property bool $is_primary
 * @property int $effort_allocation
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FellowTrack extends Model
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
        'score',
        'tier',
        'technical_score',
        'interview_score',
        'portfolio_score',
        'collaboration_score',
        'learning_score',
        'is_primary',
        'effort_allocation',
        'total_points_earned',
        'started_at',
        'last_active_at',
        'tier_promoted_at',
        'status',
        'motivation',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'technical_score' => 'decimal:2',
            'interview_score' => 'decimal:2',
            'portfolio_score' => 'decimal:2',
            'collaboration_score' => 'decimal:2',
            'learning_score' => 'decimal:2',
            'is_primary' => 'boolean',
            'effort_allocation' => 'integer',
            'total_points_earned' => 'integer',
            'started_at' => 'datetime',
            'last_active_at' => 'datetime',
            'tier_promoted_at' => 'datetime',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'score' => 0.00,
        'tier' => 'rookie',
        'technical_score' => 0.00,
        'interview_score' => 0.00,
        'portfolio_score' => 0.00,
        'collaboration_score' => 0.00,
        'learning_score' => 0.00,
        'is_primary' => false,
        'effort_allocation' => 100,
        'total_points_earned' => 0,
    ];

    /** Enrollment status constants */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Scope to only approved enrollments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to enrollments that need admin action.
     */
    public function scopeAwaitingReview($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_NEEDS_REVISION]);
    }

    /**
     * Whether this enrollment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Whether this enrollment is still waiting on admin review.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_NEEDS_REVISION], true);
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow who owns this track enrollment.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Admin who last reviewed this enrollment.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get activities for this fellow in this track.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'track_id', 'track_id')
            ->where('fellow_id', $this->fellow_id);
    }

    /**
     * Get interview sessions for this fellow in this track.
     */
    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class, 'track_id', 'track_id')
            ->where('fellow_id', $this->fellow_id);
    }

    /**
     * Get audit logs for score changes.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'track_id', 'track_id')
            ->where('fellow_id', $this->fellow_id);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get tier as enum.
     */
    public function getTierEnumAttribute(): Tier
    {
        return Tier::from($this->tier);
    }

    /**
     * Get tier label.
     */
    public function getTierLabelAttribute(): string
    {
        return $this->tierEnum->label();
    }

    /**
     * Get tier color.
     */
    public function getTierColorAttribute(): string
    {
        return $this->tierEnum->hexColor();
    }

    /**
     * Get tier icon.
     */
    public function getTierIconAttribute(): string
    {
        return $this->tierEnum->icon();
    }

    /**
     * Get progress to next tier as percentage.
     */
    public function getProgressToNextTierAttribute(): float
    {
        $currentTier = $this->tierEnum;
        
        if ($currentTier === Tier::ELITE) {
            return 100.0;
        }

        $range = $currentTier->defaultRange();
        // Since max is calculated as next_min - 0.1 in defaultRange, we add 0.1 to get the exact next min
        $nextTierMin = $range['max'] + 0.1;

        $rangeSize = $nextTierMin - $range['min'];
        
        if ($rangeSize <= 0) return 0.0; // Safety fallback
        
        $progress = $this->score - $range['min'];

        return min(100, max(0, ($progress / $rangeSize) * 100));
    }

    /**
     * Get formatted score.
     */
    public function getFormattedScoreAttribute(): string
    {
        return number_format($this->score, 1) . '%';
    }

    /**
     * Get score breakdown as array.
     */
    public function getScoreBreakdownAttribute(): array
    {
        return [
            'technical' => [
                'score' => $this->technical_score,
                'label' => 'Technical',
                'color' => '#8B5CF6',
            ],
            'interview' => [
                'score' => $this->interview_score,
                'label' => 'Interview',
                'color' => '#3B82F6',
            ],
            'portfolio' => [
                'score' => $this->portfolio_score,
                'label' => 'Portfolio',
                'color' => '#14B8A6',
            ],
            'collaboration' => [
                'score' => $this->collaboration_score,
                'label' => 'Collaboration',
                'color' => '#22C55E',
            ],
            'learning' => [
                'score' => $this->learning_score,
                'label' => 'Learning',
                'color' => '#F59E0B',
            ],
        ];
    }

    /**
     * Get days in track.
     */
    public function getDaysInTrackAttribute(): int
    {
        return $this->started_at ? $this->started_at->diffInDays(now()) : 0;
    }

    /**
     * Get weeks in track.
     */
    public function getWeeksInTrackAttribute(): int
    {
        return $this->started_at ? $this->started_at->diffInWeeks(now()) : 0;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to primary tracks only.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to secondary tracks.
     */
    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }

    /**
     * Scope by tier.
     */
    public function scopeOfTier($query, Tier $tier)
    {
        return $query->where('tier', $tier->value);
    }

    /**
     * Scope to elite fellows.
     */
    public function scopeElite($query)
    {
        return $query->where('tier', Tier::ELITE->value);
    }

    /**
     * Scope by minimum score.
     */
    public function scopeMinScore($query, float $score)
    {
        return $query->where('score', '>=', $score);
    }

    /**
     * Order by score descending.
     */
    public function scopeTopPerformers($query)
    {
        return $query->orderByDesc('score');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if fellow is in a specific tier.
     */
    public function isInTier(Tier $tier): bool
    {
        return $this->tier === $tier->value;
    }

    /**
     * Check if score improved from previous.
     */
    public function hasImprovedFrom(float $previousScore): bool
    {
        return $this->score > $previousScore;
    }

    /**
     * Get points needed to reach next tier.
     */
    public function getPointsToNextTier(): float
    {
        $currentTier = $this->tierEnum;
        
        if ($currentTier === Tier::ELITE) {
            return 0.0; // Already at top
        }

        $range = $currentTier->defaultRange();
        $nextTierMin = $range['max'] + 0.1;

        return max(0, $nextTierMin - $this->score);
    }

    /**
     * Update last active timestamp.
     */
    public function touchLastActive(): void
    {
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Record tier promotion.
     */
    public function recordTierPromotion(Tier $newTier): void
    {
        $this->tier = $newTier->value;
        $this->tier_promoted_at = now();
        $this->save();
    }
}
