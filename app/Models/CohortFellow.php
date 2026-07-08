<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CohortFellow Pivot Model
 * 
 * Represents the enrollment of a fellow in a cohort, tracking their
 * progress and status throughout the cohort period.
 * 
 * @property string $id UUID primary key
 * @property string $cohort_id Cohort UUID
 * @property int $fellow_id User ID
 * @property string $status enrolled|active|completed|dropped|removed
 * @property \Carbon\Carbon $enrolled_at When enrolled
 * @property \Carbon\Carbon|null $completed_at When completed
 * @property \Carbon\Carbon|null $dropped_at When dropped/removed
 * @property string|null $drop_reason Reason if dropped
 * @property float $cohort_score Score during this cohort
 * @property int $activities_completed Activities count
 * @property int $interviews_completed Interviews count
 * @property int $weeks_active Active weeks count
 * @property int|null $rank Position in cohort
 * @property int|null $enrolled_by Admin who enrolled
 * @property string|null $notes Admin notes
 * 
 * @property-read Cohort $cohort
 * @property-read User $fellow
 * @property-read User|null $enrolledByUser
 */
class CohortFellow extends Pivot
{
    use HasUuids, SoftDeletes;

    // Status constants
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_DROPPED = 'dropped';
    public const STATUS_REMOVED = 'removed';

    public const STATUSES = [
        self::STATUS_ENROLLED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_DROPPED,
        self::STATUS_REMOVED,
    ];

    // Active statuses (for counting enrolled fellows)
    public const ACTIVE_STATUSES = [
        self::STATUS_ENROLLED,
        self::STATUS_ACTIVE,
    ];

    protected $table = 'cohort_fellows';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cohort_id',
        'fellow_id',
        'status',
        'enrolled_at',
        'completed_at',
        'dropped_at',
        'drop_reason',
        'cohort_score',
        'activities_completed',
        'interviews_completed',
        'weeks_active',
        'rank',
        'enrolled_by',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'dropped_at' => 'datetime',
        'cohort_score' => 'decimal:2',
        'activities_completed' => 'integer',
        'interviews_completed' => 'integer',
        'weeks_active' => 'integer',
        'rank' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_ENROLLED,
        'cohort_score' => 0.00,
        'activities_completed' => 0,
        'interviews_completed' => 0,
        'weeks_active' => 0,
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the cohort
     */
    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    /**
     * Get the fellow (user)
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the admin who enrolled this fellow
     */
    public function enrolledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Active enrollments only
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /**
     * Scope: Completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Dropped or removed
     */
    public function scopeDropped($query)
    {
        return $query->whereIn('status', [self::STATUS_DROPPED, self::STATUS_REMOVED]);
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Check if enrollment is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    /**
     * Check if enrollment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ENROLLED => 'blue',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_COMPLETED => 'purple',
            self::STATUS_DROPPED => 'yellow',
            self::STATUS_REMOVED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ENROLLED => 'Enrolled',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_DROPPED => 'Dropped',
            self::STATUS_REMOVED => 'Removed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get days enrolled
     */
    public function getDaysEnrolledAttribute(): int
    {
        if (!$this->enrolled_at) {
            return 0;
        }
        
        $endDate = $this->completed_at ?? $this->dropped_at ?? now();
        return $this->enrolled_at->diffInDays($endDate);
    }

    // =========================================================================
    // ACTIONS
    // =========================================================================

    /**
     * Mark as active (started participating)
     */
    public function markActive(): bool
    {
        if ($this->status !== self::STATUS_ENROLLED) {
            return false;
        }
        
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): bool
    {
        if (!in_array($this->status, self::ACTIVE_STATUSES)) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as dropped (voluntary withdrawal)
     */
    public function markDropped(string $reason = 'Voluntary withdrawal'): bool
    {
        if (!in_array($this->status, self::ACTIVE_STATUSES)) {
            return false;
        }
        
        return $this->update([
            'status' => self::STATUS_DROPPED,
            'dropped_at' => now(),
            'drop_reason' => $reason,
        ]);
    }

    /**
     * Increment activity count
     */
    public function recordActivity(): void
    {
        $this->increment('activities_completed');
    }

    /**
     * Increment interview count
     */
    public function recordInterview(): void
    {
        $this->increment('interviews_completed');
    }

    /**
     * Update cohort score
     */
    public function updateScore(float $score): void
    {
        $this->update(['cohort_score' => $score]);
        $this->cohort?->updateRankings();
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get summary for lists/tables
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'fellow_id' => $this->fellow_id,
            'fellow_name' => $this->fellow?->name,
            'fellow_email' => $this->fellow?->email,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'enrolled_at' => $this->enrolled_at?->format('M j, Y'),
            'days_enrolled' => $this->days_enrolled,
            'cohort_score' => $this->cohort_score,
            'activities_completed' => $this->activities_completed,
            'interviews_completed' => $this->interviews_completed,
            'rank' => $this->rank,
        ];
    }
}
