<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Accountability Pair Model
 * 
 * Represents a pairing of two fellows for mutual peer review
 * and accountability within the same track and cohort.
 * 
 * Business Rules:
 * - Fellows are auto-paired within the same track and cohort
 * - Pairs rotate every milestone
 * - Both fellows earn bonus Collaborate points when partner completes on time
 * - Partners review each other's submissions before mentor review
 * - Only one active pair per fellow per track at a time
 * 
 * @property string $id UUID
 * @property int $fellow_a_id FK to users
 * @property int $fellow_b_id FK to users
 * @property string $track_id FK to tracks
 * @property string|null $cohort_id FK to cohorts
 * @property string|null $milestone_id FK to track_milestones
 * @property bool $is_active
 * @property \Carbon\Carbon $paired_at
 * @property \Carbon\Carbon|null $unpaired_at
 * @property int $reviews_exchanged
 * @property int $bonus_points_earned
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AccountabilityPair extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'accountability_pairs';

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
        'fellow_a_id',
        'fellow_b_id',
        'track_id',
        'cohort_id',
        'milestone_id',
        'is_active',
        'paired_at',
        'unpaired_at',
        'reviews_exchanged',
        'bonus_points_earned',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'paired_at' => 'datetime',
            'unpaired_at' => 'datetime',
            'reviews_exchanged' => 'integer',
            'bonus_points_earned' => 'integer',
        ];
    }

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'is_active' => true,
        'reviews_exchanged' => 0,
        'bonus_points_earned' => 0,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the first fellow in the pair.
     */
    public function fellowA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_a_id');
    }

    /**
     * Get the second fellow in the pair.
     */
    public function fellowB(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_b_id');
    }

    /**
     * Get the track.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the cohort.
     */
    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    /**
     * Get the current milestone.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(TrackMilestone::class, 'milestone_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to active pairs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to pairs involving a specific fellow.
     */
    public function scopeForFellow($query, $fellowId)
    {
        return $query->where(function ($q) use ($fellowId) {
            $q->where('fellow_a_id', $fellowId)
              ->orWhere('fellow_b_id', $fellowId);
        });
    }

    /**
     * Scope to a specific track.
     */
    public function scopeForTrack($query, string $trackId)
    {
        return $query->where('track_id', $trackId);
    }

    /**
     * Scope to a specific cohort.
     */
    public function scopeForCohort($query, string $cohortId)
    {
        return $query->where('cohort_id', $cohortId);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get the partner for a given fellow.
     */
    public function getPartner(User $fellow): ?User
    {
        if ($fellow->id == $this->fellow_a_id) {
            return $this->fellowB;
        }

        if ($fellow->id == $this->fellow_b_id) {
            return $this->fellowA;
        }

        return null;
    }

    /**
     * Check if a fellow is part of this pair.
     */
    public function includesFellow(User $fellow): bool
    {
        return $fellow->id == $this->fellow_a_id || $fellow->id == $this->fellow_b_id;
    }

    /**
     * Record a peer review exchange.
     */
    public function recordReview(): self
    {
        $this->increment('reviews_exchanged');
        return $this;
    }

    /**
     * Award bonus points to both fellows.
     */
    public function awardBonusPoints(int $points): self
    {
        $this->increment('bonus_points_earned', $points);
        return $this;
    }

    /**
     * Deactivate this pair (e.g., on milestone rotation).
     */
    public function deactivate(): self
    {
        $this->update([
            'is_active' => false,
            'unpaired_at' => now(),
        ]);

        return $this;
    }

    /**
     * Find the active accountability partner for a fellow in a track.
     */
    public static function findPartnerFor(User $fellow, string $trackId): ?User
    {
        $pair = static::active()
            ->forFellow($fellow->id)
            ->forTrack($trackId)
            ->first();

        return $pair?->getPartner($fellow);
    }
}
