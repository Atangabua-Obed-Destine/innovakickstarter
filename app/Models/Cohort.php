<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Cohort Model
 * 
 * Represents a time-bound group of fellows going through a track together.
 * 
 * Business Rules:
 * - Each cohort belongs to exactly ONE track
 * - A fellow can be in ONE cohort per track (but multiple cohorts across tracks)
 * - Status lifecycle: draft → upcoming → active → completed → archived
 * - Cohorts have defined start/end dates and optional enrollment windows
 * 
 * @property string $id UUID primary key
 * @property string $name Cohort name (e.g., "Cohort 8")
 * @property string $slug URL-friendly identifier
 * @property string|null $description Description of this cohort
 * @property string $track_id Track UUID
 * @property \Carbon\Carbon $start_date When cohort begins
 * @property \Carbon\Carbon $end_date When cohort ends
 * @property \Carbon\Carbon|null $enrollment_opens_at When enrollment starts
 * @property \Carbon\Carbon|null $enrollment_closes_at Enrollment deadline
 * @property int $max_fellows Maximum capacity
 * @property int $min_fellows Minimum to run
 * @property string $status draft|upcoming|active|completed|archived|cancelled
 * @property array|null $settings Custom settings JSON
 * @property array|null $milestones Key dates JSON
 * @property int $fellows_count Enrolled fellows count
 * @property float $avg_score Average Career Capital score
 * @property int $completion_rate Percentage completed (0-100)
 * @property int $activities_count Total activities
 * @property int|null $created_by Admin who created
 * 
 * @property-read Track $track
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $fellows
 * @property-read \Illuminate\Database\Eloquent\Collection|CohortFellow[] $cohortFellows
 */
class Cohort extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CANCELLED = 'cancelled';

    // All valid statuses
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_UPCOMING,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_ARCHIVED,
        self::STATUS_CANCELLED,
    ];

    // Statuses that can accept new fellows
    public const ENROLLABLE_STATUSES = [
        self::STATUS_UPCOMING,
        self::STATUS_ACTIVE,
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'track_id',
        'start_date',
        'end_date',
        'enrollment_opens_at',
        'enrollment_closes_at',
        'max_fellows',
        'min_fellows',
        'status',
        'settings',
        'milestones',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_opens_at' => 'date',
        'enrollment_closes_at' => 'date',
        'max_fellows' => 'integer',
        'min_fellows' => 'integer',
        'fellows_count' => 'integer',
        'avg_score' => 'decimal:2',
        'completion_rate' => 'integer',
        'activities_count' => 'integer',
        'settings' => 'array',
        'milestones' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'max_fellows' => 50,
        'min_fellows' => 10,
        'fellows_count' => 0,
        'avg_score' => 0.00,
        'completion_rate' => 0,
        'activities_count' => 0,
    ];

    // =========================================================================
    // BOOT METHODS
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cohort) {
            // Auto-generate slug if not provided
            if (empty($cohort->slug)) {
                $cohort->slug = static::generateUniqueSlug($cohort->name, $cohort->track_id);
            }
        });

        static::updating(function ($cohort) {
            // Update slug if name changed
            if ($cohort->isDirty('name') && !$cohort->isDirty('slug')) {
                $cohort->slug = static::generateUniqueSlug($cohort->name, $cohort->track_id, $cohort->id);
            }
        });
    }

    /**
     * Generate a unique slug for the cohort
     */
    public static function generateUniqueSlug(string $name, string $trackId, ?string $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->where('track_id', $trackId)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the track this cohort belongs to
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the admin who created this cohort
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all fellows enrolled in this cohort
     */
    public function fellows(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_fellows', 'cohort_id', 'fellow_id')
            ->using(CohortFellow::class)
            ->withPivot([
                'id', 'status', 'enrolled_at', 'completed_at', 'dropped_at',
                'drop_reason', 'cohort_score', 'activities_completed',
                'interviews_completed', 'weeks_active', 'rank', 'enrolled_by', 'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Get enrolled (active) fellows only
     */
    public function activeFellows(): BelongsToMany
    {
        return $this->fellows()
            ->wherePivotIn('status', ['enrolled', 'active']);
    }

    /**
     * Get completed fellows
     */
    public function completedFellows(): BelongsToMany
    {
        return $this->fellows()
            ->wherePivot('status', 'completed');
    }

    /**
     * Get the cohort_fellows pivot records directly
     */
    public function cohortFellows(): HasMany
    {
        return $this->hasMany(CohortFellow::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Only active cohorts
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Only upcoming cohorts
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    /**
     * Scope: Only completed cohorts
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Cohorts that can accept new enrollments
     */
    public function scopeEnrollable(Builder $query): Builder
    {
        return $query->whereIn('status', self::ENROLLABLE_STATUSES)
            ->whereColumn('fellows_count', '<', 'max_fellows');
    }

    /**
     * Scope: Currently in enrollment period
     */
    public function scopeEnrollmentOpen(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where(function ($q) use ($today) {
            $q->where(function ($q2) use ($today) {
                // Has enrollment window and we're in it
                $q2->whereNotNull('enrollment_opens_at')
                    ->where('enrollment_opens_at', '<=', $today)
                    ->where('enrollment_closes_at', '>=', $today);
            })->orWhere(function ($q2) use ($today) {
                // No enrollment window, check if before start date
                $q2->whereNull('enrollment_opens_at')
                    ->where('start_date', '>=', $today);
            });
        });
    }

    /**
     * Scope: Filter by track
     */
    public function scopeForTrack(Builder $query, string $trackId): Builder
    {
        return $query->where('track_id', $trackId);
    }

    /**
     * Scope: Currently running (between start and end date)
     */
    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Scope: Not archived or cancelled
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_ARCHIVED, self::STATUS_CANCELLED]);
    }

    // =========================================================================
    // COMPUTED PROPERTIES
    // =========================================================================

    /**
     * Check if cohort is currently active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if cohort is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_UPCOMING;
    }

    /**
     * Check if cohort is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if cohort can accept new fellows
     */
    public function canEnroll(): bool
    {
        if (!in_array($this->status, self::ENROLLABLE_STATUSES)) {
            return false;
        }

        if ($this->fellows_count >= $this->max_fellows) {
            return false;
        }

        // Check enrollment window if set
        if ($this->enrollment_opens_at && $this->enrollment_closes_at) {
            $today = Carbon::today();
            if ($today->lt($this->enrollment_opens_at) || $today->gt($this->enrollment_closes_at)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if cohort is currently running
     */
    public function isCurrentlyRunning(): bool
    {
        $today = Carbon::today();
        return $today->gte($this->start_date) && $today->lte($this->end_date);
    }

    /**
     * Check if cohort has started
     */
    public function hasStarted(): bool
    {
        return Carbon::today()->gte($this->start_date);
    }

    /**
     * Check if cohort has ended
     */
    public function hasEnded(): bool
    {
        return Carbon::today()->gt($this->end_date);
    }

    /**
     * Get days remaining in cohort
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }
        return max(0, Carbon::today()->diffInDays($this->end_date, false));
    }

    /**
     * Get days until cohort starts
     */
    public function getDaysUntilStartAttribute(): int
    {
        if ($this->hasStarted()) {
            return 0;
        }
        return Carbon::today()->diffInDays($this->start_date, false);
    }

    /**
     * Get total duration in weeks
     */
    public function getDurationWeeksAttribute(): int
    {
        return $this->start_date->diffInWeeks($this->end_date);
    }

    /**
     * Get current week number (1-indexed)
     */
    public function getCurrentWeekAttribute(): int
    {
        if (!$this->hasStarted()) {
            return 0;
        }
        return min(
            $this->start_date->diffInWeeks(Carbon::today()) + 1,
            $this->duration_weeks
        );
    }

    /**
     * Get progress percentage (based on time elapsed)
     */
    public function getProgressPercentageAttribute(): int
    {
        if (!$this->hasStarted()) {
            return 0;
        }
        if ($this->hasEnded()) {
            return 100;
        }
        
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(Carbon::today());
        
        return min(100, (int) round(($elapsedDays / max(1, $totalDays)) * 100));
    }

    /**
     * Get spots remaining for enrollment
     */
    public function getSpotsRemainingAttribute(): int
    {
        return max(0, $this->max_fellows - $this->fellows_count);
    }

    /**
     * Get fill percentage
     */
    public function getFillPercentageAttribute(): int
    {
        if ($this->max_fellows === 0) {
            return 0;
        }
        return min(100, (int) round(($this->fellows_count / $this->max_fellows) * 100));
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_UPCOMING => 'blue',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_COMPLETED => 'purple',
            self::STATUS_ARCHIVED => 'gray',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ARCHIVED => 'Archived',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    // =========================================================================
    // ACTIONS
    // =========================================================================

    /**
     * Enroll a fellow in this cohort
     * 
     * @param User $fellow The fellow to enroll
     * @param User|null $enrolledBy The admin enrolling the fellow
     * @param string|null $notes Optional notes
     * @return CohortFellow The created pivot record
     * @throws \Exception If enrollment not allowed
     */
    public function enrollFellow(User $fellow, ?User $enrolledBy = null, ?string $notes = null): CohortFellow
    {
        // Validation
        if (!$this->canEnroll()) {
            throw new \Exception('This cohort is not accepting new enrollments.');
        }

        // Check if already enrolled
        if ($this->fellows()->where('fellow_id', $fellow->id)->exists()) {
            throw new \Exception('Fellow is already enrolled in this cohort.');
        }

        // Check if fellow is in another cohort for the same track
        $existingCohort = Cohort::whereHas('fellows', function ($q) use ($fellow) {
            $q->where('fellow_id', $fellow->id)
                ->whereIn('cohort_fellows.status', ['enrolled', 'active']);
        })
            ->where('track_id', $this->track_id)
            ->first();

        if ($existingCohort) {
            throw new \Exception("Fellow is already enrolled in {$existingCohort->name} for this track.");
        }

        // Create enrollment
        $cohortFellow = CohortFellow::create([
            'cohort_id' => $this->id,
            'fellow_id' => $fellow->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'enrolled_by' => $enrolledBy?->id,
            'notes' => $notes,
        ]);

        // Update count
        $this->increment('fellows_count');

        // Ensure fellow is also enrolled in the track
        $this->ensureFellowInTrack($fellow);

        return $cohortFellow;
    }

    /**
     * Remove a fellow from this cohort
     * 
     * @param User $fellow The fellow to remove
     * @param string $reason Reason for removal
     * @return bool
     */
    public function removeFellow(User $fellow, string $reason = 'Removed by admin'): bool
    {
        $cohortFellow = CohortFellow::where('cohort_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$cohortFellow) {
            return false;
        }

        $cohortFellow->update([
            'status' => 'removed',
            'dropped_at' => now(),
            'drop_reason' => $reason,
        ]);

        $this->decrement('fellows_count');

        return true;
    }

    /**
     * Mark fellow as completed
     */
    public function markFellowCompleted(User $fellow): bool
    {
        $cohortFellow = CohortFellow::where('cohort_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$cohortFellow) {
            return false;
        }

        $cohortFellow->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->updateCompletionRate();

        return true;
    }

    /**
     * Ensure fellow is enrolled in the track
     */
    protected function ensureFellowInTrack(User $fellow): void
    {
        $exists = FellowTrack::where('fellow_id', $fellow->id)
            ->where('track_id', $this->track_id)
            ->exists();

        if (!$exists) {
            FellowTrack::create([
                'fellow_id' => $fellow->id,
                'track_id' => $this->track_id,
                'cohort_id' => $this->id,
                'is_primary' => true, // First track is primary
            ]);
        } else {
            // Update to link to this cohort
            FellowTrack::where('fellow_id', $fellow->id)
                ->where('track_id', $this->track_id)
                ->update(['cohort_id' => $this->id]);
        }
    }

    /**
     * Transition cohort to next status
     */
    public function transitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_DRAFT => [self::STATUS_UPCOMING, self::STATUS_CANCELLED],
            self::STATUS_UPCOMING => [self::STATUS_ACTIVE, self::STATUS_CANCELLED, self::STATUS_DRAFT],
            self::STATUS_ACTIVE => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            self::STATUS_COMPLETED => [self::STATUS_ARCHIVED],
            self::STATUS_ARCHIVED => [], // Terminal
            self::STATUS_CANCELLED => [self::STATUS_ARCHIVED, self::STATUS_DRAFT],
        ];

        if (!isset($validTransitions[$this->status])) {
            return false;
        }

        if (!in_array($newStatus, $validTransitions[$this->status])) {
            throw new \Exception("Cannot transition from {$this->status} to {$newStatus}");
        }

        $this->update(['status' => $newStatus]);

        return true;
    }

    /**
     * Update cohort statistics
     */
    public function updateStatistics(): void
    {
        // Get active/enrolled fellows
        $fellows = $this->cohortFellows()
            ->whereIn('status', ['enrolled', 'active', 'completed'])
            ->get();

        // Update average score
        if ($fellows->count() > 0) {
            $this->avg_score = $fellows->avg('cohort_score');
        }

        // Update completion rate
        $this->updateCompletionRate();

        $this->save();
    }

    /**
     * Update completion rate
     */
    protected function updateCompletionRate(): void
    {
        $total = $this->cohortFellows()
            ->whereIn('status', ['enrolled', 'active', 'completed'])
            ->count();

        $completed = $this->cohortFellows()
            ->where('status', 'completed')
            ->count();

        $this->completion_rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    /**
     * Calculate and update fellow rankings within cohort
     */
    public function updateRankings(): void
    {
        $fellows = $this->cohortFellows()
            ->whereIn('status', ['enrolled', 'active', 'completed'])
            ->orderByDesc('cohort_score')
            ->get();

        $rank = 1;
        foreach ($fellows as $fellow) {
            $fellow->update(['rank' => $rank++]);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Get summary for dashboards
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'track' => $this->track?->name,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'start_date' => $this->start_date->format('M j, Y'),
            'end_date' => $this->end_date->format('M j, Y'),
            'duration_weeks' => $this->duration_weeks,
            'current_week' => $this->current_week,
            'progress_percentage' => $this->progress_percentage,
            'fellows_count' => $this->fellows_count,
            'max_fellows' => $this->max_fellows,
            'spots_remaining' => $this->spots_remaining,
            'fill_percentage' => $this->fill_percentage,
            'avg_score' => $this->avg_score,
            'completion_rate' => $this->completion_rate,
            'can_enroll' => $this->canEnroll(),
        ];
    }

    /**
     * Get the route key name for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'id'; // Can change to 'slug' if preferred
    }
}
