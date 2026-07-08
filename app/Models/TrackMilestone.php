<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Track Milestone Model
 * 
 * Represents a phase/stage within a track curriculum.
 * Milestones group curriculum activities into logical sequences
 * like "Week 1-2: Foundation" or "Phase 3: Advanced Building".
 * 
 * Business Rules:
 * - Milestones are ordered by sequence_order within a track
 * - A milestone can require completion of a previous milestone (gating)
 * - Completing all required activities in a milestone awards a badge
 * - Milestones can have bonus points on completion
 * 
 * @property string $id UUID
 * @property string $track_id FK to tracks
 * @property string $title
 * @property string $description
 * @property string|null $short_description
 * @property int $sequence_order
 * @property string|null $unlock_after_milestone_id FK to self
 * @property int $estimated_duration_days
 * @property string|null $badge_name
 * @property string|null $badge_icon
 * @property string $badge_color
 * @property bool $is_required
 * @property bool $is_active
 * @property int $bonus_points
 * @property int|null $created_by FK to users
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class TrackMilestone extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'track_milestones';

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
        'title',
        'description',
        'short_description',
        'sequence_order',
        'unlock_after_milestone_id',
        'estimated_duration_days',
        'badge_name',
        'badge_icon',
        'badge_color',
        'is_required',
        'is_active',
        'bonus_points',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'estimated_duration_days' => 'integer',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'bonus_points' => 'integer',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the track this milestone belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the prerequisite milestone (gating).
     */
    public function prerequisiteMilestone(): BelongsTo
    {
        return $this->belongsTo(self::class, 'unlock_after_milestone_id');
    }

    /**
     * Get milestones that depend on this one.
     */
    public function dependentMilestones(): HasMany
    {
        return $this->hasMany(self::class, 'unlock_after_milestone_id');
    }

    /**
     * Get all curriculum activities in this milestone.
     */
    public function curriculumActivities(): HasMany
    {
        return $this->hasMany(TrackCurriculumActivity::class, 'milestone_id')
            ->orderBy('sequence_order');
    }

    /**
     * Get only active curriculum activities.
     */
    public function activeCurriculumActivities(): HasMany
    {
        return $this->hasMany(TrackCurriculumActivity::class, 'milestone_id')
            ->where('is_active', true)
            ->orderBy('sequence_order');
    }

    /**
     * Get the admin who created this milestone.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get badges awarded for this milestone.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(FellowBadge::class, 'milestone_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to active milestones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to required milestones.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to milestones in order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    /**
     * Scope to milestones for a specific track.
     */
    public function scopeForTrack($query, string $trackId)
    {
        return $query->where('track_id', $trackId);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the total number of activities in this milestone.
     */
    public function getTotalActivitiesAttribute(): int
    {
        return $this->curriculumActivities()->count();
    }

    /**
     * Get the number of required activities.
     */
    public function getRequiredActivitiesCountAttribute(): int
    {
        return $this->curriculumActivities()->where('is_required', true)->count();
    }

    /**
     * Get total points available in this milestone.
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->curriculumActivities()->sum('points') + $this->bonus_points;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if this milestone is the first in its track.
     */
    public function isFirst(): bool
    {
        return $this->unlock_after_milestone_id === null;
    }

    /**
     * Check if a fellow has completed this milestone.
     */
    public function isCompletedBy(User $fellow): bool
    {
        $requiredActivities = $this->curriculumActivities()
            ->where('is_required', true)
            ->where('is_active', true)
            ->pluck('id');

        if ($requiredActivities->isEmpty()) {
            return false;
        }

        $completedCount = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('curriculum_activity_id', $requiredActivities)
            ->where('status', 'completed')
            ->count();

        return $completedCount >= $requiredActivities->count();
    }

    /**
     * Check if this milestone is unlocked for a fellow.
     */
    public function isUnlockedFor(User $fellow): bool
    {
        // First milestone is always unlocked
        if ($this->isFirst()) {
            return true;
        }

        // Check if prerequisite milestone is completed
        if ($this->prerequisiteMilestone) {
            return $this->prerequisiteMilestone->isCompletedBy($fellow);
        }

        return true;
    }

    /**
     * Get fellow's completion percentage for this milestone.
     */
    public function getCompletionPercentage(User $fellow): float
    {
        $requiredActivities = $this->curriculumActivities()
            ->where('is_required', true)
            ->where('is_active', true)
            ->count();

        if ($requiredActivities === 0) {
            return 100.0;
        }

        $completedCount = FellowCurriculumProgress::where('fellow_id', $fellow->id)
            ->whereIn('curriculum_activity_id', $this->curriculumActivities()->pluck('id'))
            ->where('status', 'completed')
            ->count();

        return round(($completedCount / $requiredActivities) * 100, 1);
    }
}
