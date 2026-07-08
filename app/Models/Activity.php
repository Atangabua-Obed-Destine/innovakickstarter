<?php

namespace App\Models;

use App\Enums\ActivityType;
use App\Enums\ActivityStatus;
use App\Enums\CareerCapitalCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Activity Model
 * 
 * Represents any fellow activity that earns Career Capital points.
 * Activities go through an approval workflow before points are awarded.
 * 
 * @property string $id UUID
 * @property int $fellow_id
 * @property string|null $track_id UUID
 * @property ActivityType $type
 * @property CareerCapitalCategory $category
 * @property string $title
 * @property ActivityStatus $status
 * @property int $points_earned
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class Activity extends Model
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
        'type',
        'category',
        'title',
        'description',
        'summary',
        'url',
        'demo_url',
        'github_url',
        'video_url',
        'thumbnail_url',
        'images',
        'impact_metrics',
        'tech_stack',
        'points_earned',
        'points_requested',
        'pillar',
        'pillar_week',
        'status',
        'verified_by_id',
        'admin_feedback',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'is_featured',
        'is_public',
        'problem',
        'solution',
        'outcome',
        'proof_url',
        'proof_files',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'category' => CareerCapitalCategory::class,
            'status' => ActivityStatus::class,
            'images' => 'array',
            'impact_metrics' => 'array',
            'tech_stack' => 'array',
            'proof_files' => 'array',
            'metadata' => 'array',
            'points_earned' => 'integer',
            'points_requested' => 'integer',
            'pillar_week' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'pending',
        'points_earned' => 0,
        'is_featured' => false,
        'is_public' => true,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Activity $activity) {
            if (empty($activity->submitted_at)) {
                $activity->submitted_at = now();
            }
            if (empty($activity->category) && $activity->type) {
                $activity->category = $activity->type->category()->value;
            }
            if (empty($activity->pillar) && $activity->type) {
                $activity->pillar = $activity->type->pillar();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow who submitted this activity.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track this activity belongs to.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the admin who verified this activity.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
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
     * Get type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return $this->type->icon();
    }

    /**
     * Get type color.
     */
    public function getTypeColorAttribute(): string
    {
        return $this->type->color();
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
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category->label();
    }

    /**
     * Get primary URL for display.
     */
    public function getPrimaryUrlAttribute(): ?string
    {
        return $this->demo_url ?? $this->url ?? $this->github_url;
    }

    /**
     * Check if activity has case study format.
     */
    public function getHasCaseStudyAttribute(): bool
    {
        return !empty($this->problem) && !empty($this->solution);
    }

    /**
     * Get formatted impact metrics.
     */
    public function getFormattedImpactAttribute(): array
    {
        if (empty($this->impact_metrics)) {
            return [];
        }

        $formatted = [];
        foreach ($this->impact_metrics as $key => $value) {
            $formatted[] = [
                'key' => $key,
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => is_numeric($value) ? number_format($value) : $value,
            ];
        }
        return $formatted;
    }

    /**
     * Get time since submission.
     */
    public function getTimeSinceSubmissionAttribute(): string
    {
        return $this->submitted_at->diffForHumans();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to pending activities.
     */
    public function scopePending($query)
    {
        return $query->where('status', ActivityStatus::PENDING);
    }

    /**
     * Scope to approved activities.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', ActivityStatus::APPROVED);
    }

    /**
     * Scope to rejected activities.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', ActivityStatus::REJECTED);
    }

    /**
     * Scope to featured activities.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to public activities.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, ActivityType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by category.
     */
    public function scopeOfCategory($query, CareerCapitalCategory $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by pillar.
     */
    public function scopeOfPillar($query, string $pillar)
    {
        return $query->where('pillar', $pillar);
    }

    /**
     * Scope to activities for a specific week.
     */
    public function scopeForWeek($query, $weekStart)
    {
        return $query->where('pillar_week', $weekStart);
    }

    /**
     * Scope to recent activities.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('submitted_at', '>=', now()->subDays($days));
    }

    /**
     * Order by newest first.
     */
    public function scopeNewest($query)
    {
        return $query->orderByDesc('submitted_at');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if activity is pending.
     */
    public function isPending(): bool
    {
        return $this->status === ActivityStatus::PENDING;
    }

    /**
     * Check if activity is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === ActivityStatus::APPROVED;
    }

    /**
     * Check if activity can be edited.
     */
    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    /**
     * Check if activity can be resubmitted.
     */
    public function canResubmit(): bool
    {
        return $this->status->canResubmit();
    }

    /**
     * Approve the activity.
     */
    public function approve(User $admin, int $points, ?string $feedback = null): void
    {
        $this->status = ActivityStatus::APPROVED;
        $this->verified_by_id = $admin->id;
        $this->points_earned = $points;
        $this->admin_feedback = $feedback;
        $this->reviewed_at = now();
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Reject the activity.
     */
    public function reject(User $admin, string $feedback): void
    {
        $this->status = ActivityStatus::REJECTED;
        $this->verified_by_id = $admin->id;
        $this->admin_feedback = $feedback;
        $this->reviewed_at = now();
        $this->save();
    }

    /**
     * Request revision.
     */
    public function requestRevision(User $admin, string $feedback): void
    {
        $this->status = ActivityStatus::NEEDS_REVISION;
        $this->verified_by_id = $admin->id;
        $this->admin_feedback = $feedback;
        $this->reviewed_at = now();
        $this->save();
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(): void
    {
        $this->is_featured = !$this->is_featured;
        $this->save();
    }
}
