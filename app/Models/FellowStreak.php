<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fellow Streak Model
 * 
 * Tracks a fellow's consecutive weekly completion streak for a specific track.
 * Streaks are based on completing all 4 weekly pillars (Build, Brand, Interview, Collaborate).
 * 
 * Business Rules:
 * - Streak increments when all 4 pillars are completed in a week
 * - Streak resets to 0 when a week passes without all pillars
 * - Multiplier tiers: 2 weeks = 1.1x, 4 weeks = 1.25x, 8 weeks = 1.5x
 * - One streak record per fellow per track
 * - Integrates with weekly_progress table for pillar tracking
 * 
 * @property string $id UUID
 * @property int $fellow_id FK to users
 * @property string $track_id FK to tracks
 * @property int $current_streak
 * @property int $longest_streak
 * @property float $multiplier
 * @property \Carbon\Carbon|null $last_completed_week
 * @property \Carbon\Carbon|null $streak_broken_at
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class FellowStreak extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'fellow_streaks';

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
        'current_streak',
        'longest_streak',
        'multiplier',
        'last_completed_week',
        'streak_broken_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'multiplier' => 'decimal:2',
            'last_completed_week' => 'date',
            'streak_broken_at' => 'datetime',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'current_streak' => 0,
        'longest_streak' => 0,
        'multiplier' => 1.00,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow.
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

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the streak tier label.
     */
    public function getStreakTierAttribute(): string
    {
        return match(true) {
            $this->current_streak >= 8 => 'Diamond',
            $this->current_streak >= 4 => 'Unstoppable',
            $this->current_streak >= 2 => 'On Fire',
            default => 'Building',
        };
    }

    /**
     * Get the streak tier icon.
     */
    public function getStreakTierIconAttribute(): string
    {
        return match(true) {
            $this->current_streak >= 8 => '💎',
            $this->current_streak >= 4 => '⚡',
            $this->current_streak >= 2 => '🔥',
            default => '🌱',
        };
    }

    /**
     * Get the streak tier color.
     */
    public function getStreakTierColorAttribute(): string
    {
        return match(true) {
            $this->current_streak >= 8 => 'purple',
            $this->current_streak >= 4 => 'blue',
            $this->current_streak >= 2 => 'orange',
            default => 'gray',
        };
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Increment the streak (called when all pillars completed for the week).
     */
    public function incrementStreak(\Carbon\Carbon $weekStart): self
    {
        $this->current_streak++;
        $this->longest_streak = max($this->longest_streak, $this->current_streak);
        $this->multiplier = $this->calculateMultiplier();
        $this->last_completed_week = $weekStart;
        $this->streak_broken_at = null;
        $this->save();

        return $this;
    }

    /**
     * Break the streak (called when a week passes without all pillars).
     */
    public function breakStreak(): self
    {
        $this->current_streak = 0;
        $this->multiplier = 1.00;
        $this->streak_broken_at = now();
        $this->save();

        return $this;
    }

    /**
     * Calculate the points multiplier based on current streak.
     */
    public function calculateMultiplier(): float
    {
        return match(true) {
            $this->current_streak >= 8 => 1.50,
            $this->current_streak >= 4 => 1.25,
            $this->current_streak >= 2 => 1.10,
            default => 1.00,
        };
    }

    /**
     * Get the number of weeks until the next multiplier tier.
     */
    public function weeksToNextTier(): ?int
    {
        return match(true) {
            $this->current_streak >= 8 => null, // Already at max
            $this->current_streak >= 4 => 8 - $this->current_streak,
            $this->current_streak >= 2 => 4 - $this->current_streak,
            default => 2 - $this->current_streak,
        };
    }
}
