<?php

namespace App\Models;

use App\Enums\TrackCategory;
use App\Enums\CareerCapitalCategory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Track Model
 * 
 * Represents a career track like Full-Stack Engineering, Product Management, etc.
 * Each track has its own scoring rubric defining weight distribution
 * across Career Capital categories.
 * 
 * @property string $id UUID
 * @property string $name
 * @property string $slug
 * @property TrackCategory $category
 * @property array $scoring_rubric
 * @property bool $is_active
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class Track extends Model
{
    use HasFactory, HasUuids;

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
        'name',
        'slug',
        'category',
        'description',
        'short_description',
        'icon',
        'color',
        'scoring_rubric',
        'requirements',
        'outcomes',
        'is_active',
        'is_featured',
        'order',
        'fellows_count',
        'avg_score',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'category' => TrackCategory::class,
            'scoring_rubric' => 'array',
            'requirements' => 'array',
            'outcomes' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'integer',
            'fellows_count' => 'integer',
            'avg_score' => 'decimal:2',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Track $track) {
            if (empty($track->slug)) {
                $track->slug = Str::slug($track->name);
            }
            if (empty($track->scoring_rubric)) {
                $track->scoring_rubric = CareerCapitalCategory::defaultRubric();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all fellow enrollments for this track.
     */
    public function fellowTracks(): HasMany
    {
        return $this->hasMany(FellowTrack::class, 'track_id');
    }

    /**
     * Get all fellows enrolled in this track.
     */
    public function fellows()
    {
        return $this->belongsToMany(User::class, 'fellow_tracks', 'track_id', 'fellow_id')
            ->withPivot(['score', 'tier', 'is_primary', 'effort_allocation'])
            ->withTimestamps();
    }

    /**
     * Get all activities in this track.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'track_id');
    }

    /**
     * Get all interview sessions for this track.
     */
    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class, 'track_id');
    }

    // --- Curriculum System Relationships ---

    /**
     * Get all curriculum milestones for this track.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(TrackMilestone::class, 'track_id');
    }

    /**
     * Get ordered active milestones.
     */
    public function activeMilestones(): HasMany
    {
        return $this->milestones()->where('is_active', true)->orderBy('sequence_order');
    }

    /**
     * Get all curriculum activities for this track.
     */
    public function curriculumActivities(): HasMany
    {
        return $this->hasMany(TrackCurriculumActivity::class, 'track_id');
    }

    /**
     * Get accountability pairs for this track.
     */
    public function accountabilityPairs(): HasMany
    {
        return $this->hasMany(AccountabilityPair::class, 'track_id');
    }

    /**
     * Get mentorship pods for this track.
     */
    public function mentorshipPods(): HasMany
    {
        return $this->hasMany(MentorshipPod::class, 'track_id');
    }

    /**
     * Get all badges awarded in this track.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(FellowBadge::class, 'track_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get weight for a specific category.
     */
    public function getCategoryWeight(CareerCapitalCategory $category): int
    {
        return $this->scoring_rubric[$category->value] ?? $category->defaultWeight();
    }

    /**
     * Get all category weights formatted for display.
     */
    public function getFormattedRubricAttribute(): array
    {
        $formatted = [];
        foreach (CareerCapitalCategory::cases() as $category) {
            $formatted[] = [
                'category' => $category,
                'label' => $category->label(),
                'weight' => $this->getCategoryWeight($category),
                'color' => $category->hexColor(),
            ];
        }
        return $formatted;
    }

    /**
     * Get track badge color classes.
     */
    public function getBadgeClassAttribute(): string
    {
        return "bg-[{$this->color}]/20 text-[{$this->color}] border-[{$this->color}]/30";
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to active tracks only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to featured tracks.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to technical tracks.
     */
    public function scopeTechnical($query)
    {
        return $query->where('category', TrackCategory::TECHNICAL);
    }

    /**
     * Order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Validate that scoring rubric sums to 100.
     */
    public function validateRubric(): bool
    {
        return CareerCapitalCategory::validateRubric($this->scoring_rubric);
    }

    /**
     * Update fellow count (called when fellows enroll/unenroll).
     */
    public function updateFellowsCount(): void
    {
        $this->fellows_count = $this->fellowTracks()->count();
        $this->save();
    }

    /**
     * Update average score.
     */
    public function updateAverageScore(): void
    {
        $this->avg_score = $this->fellowTracks()->avg('score') ?? 0;
        $this->save();
    }

    /**
     * Get tier distribution for this track.
     */
    public function getTierDistribution(): array
    {
        return $this->fellowTracks()
            ->selectRaw('tier, COUNT(*) as count')
            ->groupBy('tier')
            ->pluck('count', 'tier')
            ->toArray();
    }
}
