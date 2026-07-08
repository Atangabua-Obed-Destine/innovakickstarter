<?php

namespace App\Models;

use App\Enums\BadgeType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Fellow Badge Model
 * 
 * Represents a digital badge earned by a fellow through the curriculum
 * and engagement systems. Badges are shareable on LinkedIn.
 * 
 * Badge Types:
 * - Milestone: Earned by completing a curriculum milestone
 * - Streak: Earned for maintaining weekly streak tiers
 * - Achievement: Special accomplishments (first submission, etc.)
 * - Track Completion: Completing entire track curriculum
 * - Power Week: Outstanding performance during Power Weeks
 * - Peer Champion: Top peer reviewer
 * 
 * @property string $id UUID
 * @property int $fellow_id FK to users
 * @property BadgeType $badge_type
 * @property string $badge_name
 * @property string $badge_icon
 * @property string $badge_color
 * @property string|null $badge_description
 * @property \Carbon\Carbon $earned_at
 * @property string|null $milestone_id FK to track_milestones
 * @property string|null $track_id FK to tracks
 * @property string|null $shareable_url
 * @property bool $is_shared
 * @property array|null $metadata
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FellowBadge extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'fellow_badges';

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
        'badge_type',
        'badge_name',
        'badge_icon',
        'badge_color',
        'badge_description',
        'earned_at',
        'milestone_id',
        'track_id',
        'shareable_url',
        'is_shared',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'badge_type' => BadgeType::class,
            'earned_at' => 'datetime',
            'is_shared' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'badge_icon' => '⭐',
        'badge_color' => '#8B5CF6',
        'is_shared' => false,
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $badge) {
            // Auto-generate shareable URL if badge type is shareable
            if (empty($badge->shareable_url) && $badge->badge_type?->isShareable()) {
                $badge->shareable_url = '/badges/verify/' . Str::uuid();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow who earned this badge.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the associated milestone (if milestone badge).
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TrackMilestone::class, 'milestone_id');
    }

    /**
     * Get the associated track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
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
     * Scope to a specific badge type.
     */
    public function scopeOfType($query, BadgeType $type)
    {
        return $query->where('badge_type', $type->value);
    }

    /**
     * Scope to shareable badges.
     */
    public function scopeShareable($query)
    {
        return $query->whereNotNull('shareable_url');
    }

    /**
     * Scope to shared badges.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope to recent badges.
     */
    public function scopeRecent($query, int $limit = 5)
    {
        return $query->orderByDesc('earned_at')->limit($limit);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get badge type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->badge_type?->label() ?? 'Badge';
    }

    /**
     * Check if this badge can be shared.
     */
    public function getIsShareableAttribute(): bool
    {
        return $this->badge_type?->isShareable() && !empty($this->shareable_url);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mark the badge as shared.
     */
    public function markShared(): self
    {
        $this->update(['is_shared' => true]);
        return $this;
    }

    /**
     * Create a milestone completion badge.
     */
    public static function createMilestoneBadge(User $fellow, TrackMilestone $milestone): self
    {
        return static::create([
            'fellow_id' => $fellow->id,
            'badge_type' => BadgeType::MILESTONE,
            'badge_name' => $milestone->badge_name ?? "Completed: {$milestone->title}",
            'badge_icon' => $milestone->badge_icon ?? '🏅',
            'badge_color' => $milestone->badge_color ?? '#8B5CF6',
            'badge_description' => "Completed all required activities in {$milestone->title}",
            'earned_at' => now(),
            'milestone_id' => $milestone->id,
            'track_id' => $milestone->track_id,
            'metadata' => [
                'milestone_title' => $milestone->title,
                'track_name' => $milestone->track->name ?? null,
            ],
        ]);
    }

    /**
     * Create a streak badge.
     */
    public static function createStreakBadge(User $fellow, FellowStreak $streak): self
    {
        $tierName = $streak->streak_tier;

        return static::create([
            'fellow_id' => $fellow->id,
            'badge_type' => BadgeType::STREAK,
            'badge_name' => "{$tierName} Streak",
            'badge_icon' => $streak->streak_tier_icon,
            'badge_color' => match($tierName) {
                'Diamond' => '#A855F7',
                'Unstoppable' => '#3B82F6',
                'On Fire' => '#F97316',
                default => '#6B7280',
            },
            'badge_description' => "{$streak->current_streak} consecutive weeks of completing all pillars",
            'earned_at' => now(),
            'track_id' => $streak->track_id,
            'metadata' => [
                'streak_count' => $streak->current_streak,
                'multiplier' => $streak->multiplier,
            ],
        ]);
    }

    /**
     * Create a track completion badge.
     */
    public static function createTrackCompletionBadge(User $fellow, Track $track): self
    {
        return static::create([
            'fellow_id' => $fellow->id,
            'badge_type' => BadgeType::TRACK_COMPLETION,
            'badge_name' => "{$track->name} Complete",
            'badge_icon' => '🎓',
            'badge_color' => $track->color ?? '#22C55E',
            'badge_description' => "Completed the entire {$track->name} track curriculum",
            'earned_at' => now(),
            'track_id' => $track->id,
            'metadata' => [
                'track_name' => $track->name,
                'track_slug' => $track->slug,
            ],
        ]);
    }
}
