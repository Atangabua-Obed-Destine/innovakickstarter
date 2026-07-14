<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\CareerCapitalCategory;
use App\Enums\DifficultyLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Track Curriculum Activity Model
 * 
 * Represents an admin-defined activity within a track milestone.
 * These are the structured "assignments" that fellows must complete
 * as part of their track curriculum.
 * 
 * Business Rules:
 * - Activities belong to a milestone within a track
 * - Can be sequential (must complete previous) or parallel (any order within milestone)
 * - Deadlines are relative (days from fellow's track enrollment)
 * - Evidence requirements define what proof a fellow must submit
 * - Evaluation rubrics define how submissions are scored
 * - mock_interview type auto-integrates with interview_sessions table
 * - Activity chains link multi-part activities (Build → Test → Deploy)
 * 
 * @property string $id UUID
 * @property string $track_id FK to tracks
 * @property string $milestone_id FK to track_milestones
 * @property string $title
 * @property string $description
 * @property string|null $instructions
 * @property ActivityType $type
 * @property DifficultyLevel $difficulty_level
 * @property CareerCapitalCategory $career_capital_category
 * @property string|null $pillar
 * @property int $points
 * @property array|null $evaluation_rubric
 * @property int $sequence_order
 * @property bool $is_sequential
 * @property int|null $deadline_days
 * @property int $grace_period_days
 * @property int $late_penalty_percent
 * @property array|null $evidence_requirements
 * @property array|null $resources
 * @property array|null $prerequisites
 * @property string|null $chain_parent_id FK to self
 * @property bool $is_required
 * @property bool $is_collaborative
 * @property bool $requires_cross_track
 * @property bool $requires_peer_review
 * @property array|null $interview_config
 * @property int|null $created_by FK to users
 * @property bool $is_active
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class TrackCurriculumActivity extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'track_curriculum_activities';

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
        'track_id',
        'milestone_id',
        'title',
        'description',
        'instructions',
        'type',
        'difficulty_level',
        'career_capital_category',
        'pillar',
        'points',
        'evaluation_rubric',
        'sequence_order',
        'is_sequential',
        'deadline_days',
        'grace_period_days',
        'late_penalty_percent',
        'evidence_requirements',
        'resources',
        'prerequisites',
        'chain_parent_id',
        'is_required',
        'is_collaborative',
        'requires_cross_track',
        'requires_peer_review',
        'interview_config',
        'created_by',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'difficulty_level' => DifficultyLevel::class,
            'career_capital_category' => CareerCapitalCategory::class,
            'evaluation_rubric' => 'array',
            'evidence_requirements' => 'array',
            'resources' => 'array',
            'prerequisites' => 'array',
            'interview_config' => 'array',
            'sequence_order' => 'integer',
            'is_sequential' => 'boolean',
            'deadline_days' => 'integer',
            'grace_period_days' => 'integer',
            'late_penalty_percent' => 'integer',
            'points' => 'integer',
            'is_required' => 'boolean',
            'is_collaborative' => 'boolean',
            'requires_cross_track' => 'boolean',
            'requires_peer_review' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'difficulty_level' => 'beginner',
        'sequence_order' => 0,
        'is_sequential' => false,
        'grace_period_days' => 3,
        'late_penalty_percent' => 20,
        'points' => 10,
        'is_required' => true,
        'is_collaborative' => false,
        'requires_cross_track' => false,
        'requires_peer_review' => false,
        'is_active' => true,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $activity) {
            // Auto-set career capital category from type if not provided
            if (empty($activity->career_capital_category) && $activity->type) {
                $activity->career_capital_category = $activity->type->category();
            }
            // Auto-set pillar from type if not provided
            if (empty($activity->pillar) && $activity->type) {
                $activity->pillar = $activity->type->pillar();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the track this activity belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the milestone this activity belongs to.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TrackMilestone::class, 'milestone_id');
    }

    /**
     * Get the parent activity in a chain.
     */
    public function chainParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'chain_parent_id');
    }

    /**
     * Get child activities in a chain.
     */
    public function chainChildren(): HasMany
    {
        return $this->hasMany(self::class, 'chain_parent_id')
            ->orderBy('sequence_order');
    }

    /**
     * Get the discussion comments for this activity.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ActivityComment::class, 'curriculum_activity_id')
            ->whereNull('parent_id') // only fetch root comments by default
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all fellow progress records for this activity.
     */
    public function fellowProgress(): HasMany
    {
        return $this->hasMany(FellowCurriculumProgress::class, 'curriculum_activity_id');
    }

    /**
     * Get the admin who created this activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to active activities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to required activities.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to a specific milestone.
     */
    public function scopeForMilestone($query, string $milestoneId)
    {
        return $query->where('milestone_id', $milestoneId);
    }

    /**
     * Scope to a specific track.
     */
    public function scopeForTrack($query, string $trackId)
    {
        return $query->where('track_id', $trackId);
    }

    /**
     * Scope ordered by sequence.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    /**
     * Scope to a specific activity type.
     */
    public function scopeOfType($query, ActivityType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope to a specific difficulty level.
     */
    public function scopeOfDifficulty($query, DifficultyLevel $level)
    {
        return $query->where('difficulty_level', $level->value);
    }

    /**
     * Scope to chain root activities (no parent).
     */
    public function scopeChainRoots($query)
    {
        return $query->whereNull('chain_parent_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type?->label() ?? 'Unknown';
    }

    /**
     * Get difficulty badge class.
     */
    public function getDifficultyBadgeClassAttribute(): string
    {
        return $this->difficulty_level?->badgeClass() ?? '';
    }

    /**
     * Get the type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return $this->type?->icon() ?? '📌';
    }

    /**
     * Check if this activity is part of a chain.
     */
    public function getIsChainedAttribute(): bool
    {
        return $this->chain_parent_id !== null || $this->chainChildren()->exists();
    }

    /**
     * Get the full chain (all activities in sequence).
     */
    public function getChainAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->chain_parent_id) {
            // This is a child — get the root and all siblings
            return self::where('chain_parent_id', $this->chain_parent_id)
                ->orWhere('id', $this->chain_parent_id)
                ->orderBy('sequence_order')
                ->get();
        }

        // This is a root — get self and children
        return self::where('chain_parent_id', $this->id)
            ->orWhere('id', $this->id)
            ->orderBy('sequence_order')
            ->get();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this activity requires interview module integration.
     */
    public function requiresInterviewSession(): bool
    {
        return $this->type === ActivityType::MOCK_INTERVIEW;
    }

    /**
     * Calculate the absolute deadline for a fellow based on their enrollment date.
     */
    public function calculateDeadlineFor(User $fellow): ?\Carbon\Carbon
    {
        if ($this->deadline_days === null) {
            return null;
        }

        $fellowTrack = $fellow->fellowTracks()
            ->where('track_id', $this->track_id)
            ->first();

        if (!$fellowTrack) {
            return null;
        }

        return $fellowTrack->started_at->copy()->addDays($this->deadline_days);
    }

    /**
     * Calculate the grace deadline for a fellow.
     */
    public function calculateGraceDeadlineFor(User $fellow): ?\Carbon\Carbon
    {
        $deadline = $this->calculateDeadlineFor($fellow);

        if (!$deadline) {
            return null;
        }

        return $deadline->copy()->addDays($this->grace_period_days);
    }

    /**
     * Get the points after applying late penalty.
     */
    public function getLatePenaltyPoints(): int
    {
        return (int) round($this->points * (1 - ($this->late_penalty_percent / 100)));
    }

    /**
     * Check if prerequisite activities are completed by a fellow.
     */
    public function prerequisitesMet(User $fellow): bool
    {
        // Check activity-level prerequisites
        if (!empty($this->prerequisites)) {
            $completedCount = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                ->whereIn('curriculum_activity_id', $this->prerequisites)
                ->where('status', 'completed')
                ->count();

            if ($completedCount < count($this->prerequisites)) {
                return false;
            }
        }

        // Check sequential ordering within milestone
        if ($this->is_sequential && $this->sequence_order > 0) {
            $previousActivity = self::where('milestone_id', $this->milestone_id)
                ->where('sequence_order', '<', $this->sequence_order)
                ->where('is_active', true)
                ->orderByDesc('sequence_order')
                ->first();

            if ($previousActivity) {
                $previousCompleted = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                    ->where('curriculum_activity_id', $previousActivity->id)
                    ->where('status', 'completed')
                    ->exists();

                if (!$previousCompleted) {
                    return false;
                }
            }
        }

        // Check chain parent completion
        if ($this->chain_parent_id) {
            $parentCompleted = FellowCurriculumProgress::where('fellow_id', $fellow->id)
                ->where('curriculum_activity_id', $this->chain_parent_id)
                ->where('status', 'completed')
                ->exists();

            if (!$parentCompleted) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if this activity is available for a fellow (milestone unlocked + prerequisites met).
     */
    public function isAvailableFor(User $fellow): bool
    {
        // Milestone must be unlocked
        if (!$this->milestone->isUnlockedFor($fellow)) {
            return false;
        }

        // Activity prerequisites must be met
        return $this->prerequisitesMet($fellow);
    }

    /**
     * Get the number of fellows who have completed this activity.
     */
    public function getCompletionCount(): int
    {
        return $this->fellowProgress()->where('status', 'completed')->count();
    }

    /**
     * Get the average score for completed submissions.
     */
    public function getAverageScore(): float
    {
        return (float) $this->fellowProgress()
            ->where('status', 'completed')
            ->avg('score_awarded') ?? 0;
    }
}
