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
 * Program Model
 * 
 * Represents an administrative grouping of fellows across all tracks.
 * Programs are used for managing cohorts of fellows who joined together,
 * independent of their specific career tracks.
 * 
 * Examples:
 * - "IKS Fellowship 2025" - All fellows who joined the 2025 intake
 * - "Mastercard Scholars Program Batch 3" - Sponsored fellows
 * - "Tech Talent Accelerator Q1 2026" - Quarterly intake
 * 
 * Conceptual Separation:
 * - Program = WHO you joined with (administrative, funding, certificates)
 * - Track = WHAT you're learning (career path)
 * - Cohort = WHEN you're learning it in that track (time-bound group)
 * 
 * Business Rules:
 * - A fellow belongs to exactly ONE program
 * - A program spans ALL tracks (cross-track grouping)
 * - Programs have their own lifecycle independent of cohorts
 * - Programs can issue certificates upon completion
 * - Programs track alumni outcomes (employment, salary, etc.)
 * 
 * @property string $id UUID primary key
 * @property string $name Program name
 * @property string $slug URL-friendly identifier
 * @property string|null $description Full description
 * @property string|null $short_description Brief tagline
 * @property string|null $logo_url Program logo
 * @property string|null $banner_url Banner image
 * @property string $color Brand color (hex)
 * @property \Carbon\Carbon $start_date Official start
 * @property \Carbon\Carbon $end_date Official end
 * @property \Carbon\Carbon|null $enrollment_opens_at When enrollment opens
 * @property \Carbon\Carbon|null $enrollment_closes_at Enrollment deadline
 * @property \Carbon\Carbon|null $graduation_date Ceremony date
 * @property int $max_fellows Maximum capacity
 * @property int $min_fellows Minimum to run
 * @property string $status draft|upcoming|enrolling|active|graduated|archived
 * @property string|null $sponsor_name Funding organization
 * @property string|null $sponsor_logo_url Sponsor logo
 * @property string|null $funding_type scholarship|grant|self-funded|hybrid
 * @property array|null $milestones Program milestones (JSON)
 * @property array|null $settings Custom settings (JSON)
 * @property bool $has_certificate Issues certificates
 * @property string|null $certificate_template Template ID
 * @property bool $track_alumni_outcomes Track outcomes
 * @property int $fellows_count Enrolled count
 * @property int $graduated_count Completed count
 * @property int $dropped_count Left count
 * @property float $avg_completion_rate Average milestone completion
 * @property float|null $employment_rate Graduate employment rate
 * @property int|null $created_by Admin who created
 * 
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $fellows
 * @property-read \Illuminate\Database\Eloquent\Collection|ProgramFellow[] $programFellows
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class Program extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    // =========================================================================
    // STATUS CONSTANTS
    // =========================================================================

    public const STATUS_DRAFT = 'draft';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ENROLLING = 'enrolling';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_GRADUATED = 'graduated';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_UPCOMING,
        self::STATUS_ENROLLING,
        self::STATUS_ACTIVE,
        self::STATUS_GRADUATED,
        self::STATUS_ARCHIVED,
    ];

    /**
     * Get statuses as an associative array (value => label).
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_UPCOMING  => 'Upcoming',
            self::STATUS_ENROLLING => 'Enrolling',
            self::STATUS_ACTIVE    => 'Active',
            self::STATUS_GRADUATED => 'Graduated',
            self::STATUS_ARCHIVED  => 'Archived',
        ];
    }

    // Statuses that can accept new fellows
    public const ENROLLABLE_STATUSES = [
        self::STATUS_ENROLLING,
        self::STATUS_UPCOMING,
    ];

    // Funding types
    public const FUNDING_SCHOLARSHIP = 'scholarship';
    public const FUNDING_GRANT = 'grant';
    public const FUNDING_SELF_FUNDED = 'self-funded';
    public const FUNDING_HYBRID = 'hybrid';

    public const FUNDING_TYPES = [
        self::FUNDING_SCHOLARSHIP,
        self::FUNDING_GRANT,
        self::FUNDING_SELF_FUNDED,
        self::FUNDING_HYBRID,
    ];

    // =========================================================================
    // MODEL CONFIGURATION
    // =========================================================================

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'logo_url',
        'banner_url',
        'color',
        'start_date',
        'end_date',
        'enrollment_opens_at',
        'enrollment_closes_at',
        'graduation_date',
        'max_fellows',
        'min_fellows',
        'status',
        'sponsor_name',
        'sponsor_logo_url',
        'funding_type',
        'milestones',
        'settings',
        'has_certificate',
        'certificate_template',
        'track_alumni_outcomes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_opens_at' => 'date',
        'enrollment_closes_at' => 'date',
        'graduation_date' => 'date',
        'max_fellows' => 'integer',
        'min_fellows' => 'integer',
        'fellows_count' => 'integer',
        'graduated_count' => 'integer',
        'dropped_count' => 'integer',
        'avg_completion_rate' => 'decimal:2',
        'employment_rate' => 'decimal:2',
        'milestones' => 'array',
        'settings' => 'array',
        'has_certificate' => 'boolean',
        'track_alumni_outcomes' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'color' => '#6366f1',
        'max_fellows' => 100,
        'min_fellows' => 10,
        'fellows_count' => 0,
        'graduated_count' => 0,
        'dropped_count' => 0,
        'avg_completion_rate' => 0.00,
        'has_certificate' => true,
        'track_alumni_outcomes' => true,
    ];

    // =========================================================================
    // BOOT METHODS
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($program) {
            if (empty($program->slug)) {
                $program->slug = static::generateUniqueSlug($program->name);
            }
        });

        static::updating(function ($program) {
            if ($program->isDirty('name') && !$program->isDirty('slug')) {
                $program->slug = static::generateUniqueSlug($program->name, $program->id);
            }
        });
    }

    /**
     * Generate a unique slug for the program
     */
    public static function generateUniqueSlug(string $name, ?string $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
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
     * Get the admin who created this program
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all fellows enrolled in this program
     */
    public function fellows(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_fellows', 'program_id', 'fellow_id')
            ->using(ProgramFellow::class)
            ->withPivot([
                'id', 'status', 'enrolled_at', 'activated_at', 'completed_at', 
                'dropped_at', 'drop_reason', 'certificate_issued', 'certificate_issued_at',
                'certificate_number', 'milestones_completed', 'employment_status',
                'employer_name', 'job_title', 'enrolled_by', 'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Get all program-fellow pivot records
     */
    public function programFellows(): HasMany
    {
        return $this->hasMany(ProgramFellow::class, 'program_id');
    }

    /**
     * Get active/enrolled fellows only
     */
    public function activeFellows(): BelongsToMany
    {
        return $this->fellows()
            ->wherePivotIn('status', ['enrolled', 'active']);
    }

    /**
     * Get graduated fellows
     */
    public function graduates(): BelongsToMany
    {
        return $this->fellows()
            ->wherePivot('status', 'completed');
    }

    /**
     * Get employed alumni
     */
    public function employedAlumni(): BelongsToMany
    {
        return $this->fellows()
            ->wherePivot('status', 'completed')
            ->wherePivotIn('employment_status', ['employed', 'freelancing']);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    /**
     * Scope: Active programs
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Currently enrolling
     */
    public function scopeEnrolling(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENROLLING);
    }

    /**
     * Scope: Upcoming programs
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UPCOMING);
    }

    /**
     * Scope: Graduated programs
     */
    public function scopeGraduated(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_GRADUATED);
    }

    /**
     * Scope: Programs accepting enrollment
     */
    public function scopeAcceptingEnrollment(Builder $query): Builder
    {
        return $query->whereIn('status', self::ENROLLABLE_STATUSES);
    }

    /**
     * Scope: Filter by funding type
     */
    public function scopeByFunding(Builder $query, string $type): Builder
    {
        return $query->where('funding_type', $type);
    }

    /**
     * Scope: Sponsored programs
     */
    public function scopeSponsored(Builder $query): Builder
    {
        return $query->whereNotNull('sponsor_name');
    }

    /**
     * Scope: Programs starting within a date range
     */
    public function scopeStartingBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('start_date', [$from, $to]);
    }

    /**
     * Scope: Search by name or description
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('sponsor_name', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // ENROLLMENT HELPERS
    // =========================================================================

    /**
     * Check if program can accept new fellows
     */
    public function canEnroll(): bool
    {
        // Check status
        if (!in_array($this->status, self::ENROLLABLE_STATUSES)) {
            return false;
        }

        // Check capacity
        if ($this->fellows_count >= $this->max_fellows) {
            return false;
        }

        // Check enrollment window
        if ($this->enrollment_opens_at && $this->enrollment_closes_at) {
            $today = Carbon::today();
            if ($today->lt($this->enrollment_opens_at) || $today->gt($this->enrollment_closes_at)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enroll a fellow in this program
     * 
     * @param User $fellow The fellow to enroll
     * @param User|null $enrolledBy The admin enrolling the fellow
     * @param string|null $notes Optional notes
     * @return ProgramFellow The created pivot record
     * @throws \Exception If enrollment not allowed
     */
    public function enrollFellow(User $fellow, ?User $enrolledBy = null, ?string $notes = null): ProgramFellow
    {
        // Validation
        if (!$this->canEnroll()) {
            throw new \Exception('This program is not accepting new enrollments.');
        }

        // Check if already enrolled
        if ($this->fellows()->where('fellow_id', $fellow->id)->exists()) {
            throw new \Exception('Fellow is already enrolled in this program.');
        }

        // Check if fellow is in another active program
        $existingProgram = Program::whereHas('fellows', function ($q) use ($fellow) {
            $q->where('fellow_id', $fellow->id)
                ->whereIn('program_fellows.status', ['enrolled', 'active']);
        })
            ->whereIn('status', [self::STATUS_ENROLLING, self::STATUS_ACTIVE])
            ->first();

        if ($existingProgram) {
            throw new \Exception("Fellow is already enrolled in {$existingProgram->name}.");
        }

        // Create enrollment
        $programFellow = ProgramFellow::create([
            'program_id' => $this->id,
            'fellow_id' => $fellow->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
            'enrolled_by' => $enrolledBy?->id,
            'notes' => $notes,
        ]);

        // Update counts
        $this->increment('fellows_count');

        // Update user's current program
        $fellow->update(['current_program_id' => $this->id]);

        return $programFellow;
    }

    /**
     * Remove a fellow from this program
     * 
     * @param User $fellow The fellow to remove
     * @param string $reason Reason for removal
     * @return bool
     */
    public function removeFellow(User $fellow, string $reason = 'Removed by admin'): bool
    {
        $programFellow = ProgramFellow::where('program_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$programFellow) {
            return false;
        }

        $programFellow->update([
            'status' => 'removed',
            'dropped_at' => now(),
            'drop_reason' => $reason,
        ]);

        $this->decrement('fellows_count');
        $this->increment('dropped_count');

        // Clear user's current program
        if ($fellow->current_program_id === $this->id) {
            $fellow->update(['current_program_id' => null]);
        }

        return true;
    }

    /**
     * Graduate a fellow (mark as completed)
     * 
     * @param User $fellow The fellow to graduate
     * @return ProgramFellow
     */
    public function graduateFellow(User $fellow): ProgramFellow
    {
        $programFellow = ProgramFellow::where('program_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->firstOrFail();

        $programFellow->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->decrement('fellows_count');
        $this->increment('graduated_count');

        return $programFellow;
    }

    /**
     * Issue certificate to a graduated fellow
     */
    public function issueCertificate(User $fellow): ProgramFellow
    {
        $programFellow = ProgramFellow::where('program_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $certificateNumber = $this->generateCertificateNumber($fellow);

        $programFellow->update([
            'certificate_issued' => true,
            'certificate_issued_at' => now(),
            'certificate_number' => $certificateNumber,
        ]);

        return $programFellow;
    }

    /**
     * Generate unique certificate number
     */
    protected function generateCertificateNumber(User $fellow): string
    {
        $prefix = Str::upper(Str::substr(Str::slug($this->name), 0, 3));
        $year = $this->graduation_date?->year ?? now()->year;
        $sequence = str_pad($this->graduated_count, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$sequence}";
    }

    // =========================================================================
    // MILESTONE HELPERS
    // =========================================================================

    /**
     * Get milestones as structured array
     */
    public function getMilestonesAttribute($value): array
    {
        return json_decode($value, true) ?? $this->getDefaultMilestones();
    }

    /**
     * Get default program milestones
     */
    public static function getDefaultMilestones(): array
    {
        return [
            [
                'key' => 'onboarding',
                'title' => 'Complete Onboarding',
                'description' => 'Attend orientation and complete profile setup',
                'order' => 1,
                'required' => true,
            ],
            [
                'key' => 'track_selection',
                'title' => 'Select Primary Track',
                'description' => 'Choose your primary career track',
                'order' => 2,
                'required' => true,
            ],
            [
                'key' => 'first_activity',
                'title' => 'Submit First Activity',
                'description' => 'Complete and submit your first Career Capital activity',
                'order' => 3,
                'required' => true,
            ],
            [
                'key' => 'first_interview',
                'title' => 'Complete First Interview',
                'description' => 'Complete your first mock interview',
                'order' => 4,
                'required' => true,
            ],
            [
                'key' => 'capstone_project',
                'title' => 'Capstone Project',
                'description' => 'Complete your capstone project',
                'order' => 5,
                'required' => true,
            ],
            [
                'key' => 'graduation',
                'title' => 'Graduation Requirements',
                'description' => 'Meet all graduation requirements',
                'order' => 6,
                'required' => true,
            ],
        ];
    }

    /**
     * Check if all required milestones are completed for a fellow
     */
    public function fellowCompletedAllMilestones(User $fellow): bool
    {
        $programFellow = ProgramFellow::where('program_id', $this->id)
            ->where('fellow_id', $fellow->id)
            ->first();

        if (!$programFellow) {
            return false;
        }

        $completed = $programFellow->milestones_completed ?? [];
        $required = collect($this->milestones)
            ->where('required', true)
            ->pluck('key');

        foreach ($required as $key) {
            if (!isset($completed[$key]) || !$completed[$key]['completed']) {
                return false;
            }
        }

        return true;
    }

    // =========================================================================
    // DATE & STATUS HELPERS
    // =========================================================================

    /**
     * Check if program is currently running
     */
    public function isCurrentlyRunning(): bool
    {
        $today = Carbon::today();
        return $today->gte($this->start_date) && $today->lte($this->end_date);
    }

    /**
     * Check if program has started
     */
    public function hasStarted(): bool
    {
        return Carbon::today()->gte($this->start_date);
    }

    /**
     * Check if program has ended
     */
    public function hasEnded(): bool
    {
        return Carbon::today()->gt($this->end_date);
    }

    /**
     * Get days remaining in program
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }
        if (!$this->hasStarted()) {
            return $this->start_date->diffInDays($this->end_date);
        }
        return max(0, Carbon::today()->diffInDays($this->end_date, false));
    }

    /**
     * Get days until program starts
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
     * Is enrollment currently open?
     */
    public function getIsEnrollmentOpenAttribute(): bool
    {
        if (!in_array($this->status, self::ENROLLABLE_STATUSES)) {
            return false;
        }

        if ($this->enrollment_opens_at && $this->enrollment_closes_at) {
            $today = Carbon::today();
            return $today->gte($this->enrollment_opens_at) && $today->lte($this->enrollment_closes_at);
        }

        return true;
    }

    // =========================================================================
    // STATISTICS HELPERS
    // =========================================================================

    /**
     * Calculate and update statistics
     */
    public function recalculateStats(): void
    {
        $this->update([
            'fellows_count' => $this->programFellows()
                ->whereIn('status', ['enrolled', 'active'])
                ->count(),
            'graduated_count' => $this->programFellows()
                ->where('status', 'completed')
                ->count(),
            'dropped_count' => $this->programFellows()
                ->whereIn('status', ['dropped', 'removed'])
                ->count(),
        ]);
    }

    /**
     * Calculate employment rate for graduates
     */
    public function calculateEmploymentRate(): ?float
    {
        $graduates = $this->programFellows()
            ->where('status', 'completed')
            ->count();

        if ($graduates === 0) {
            return null;
        }

        $employed = $this->programFellows()
            ->where('status', 'completed')
            ->whereIn('employment_status', ['employed', 'freelancing'])
            ->count();

        $rate = ($employed / $graduates) * 100;
        
        $this->update(['employment_rate' => $rate]);
        
        return $rate;
    }

    // =========================================================================
    // STATUS TRANSITIONS
    // =========================================================================

    /**
     * Transition program to a new status
     */
    public function transitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_DRAFT => [self::STATUS_UPCOMING],
            self::STATUS_UPCOMING => [self::STATUS_ENROLLING, self::STATUS_DRAFT],
            self::STATUS_ENROLLING => [self::STATUS_ACTIVE, self::STATUS_UPCOMING],
            self::STATUS_ACTIVE => [self::STATUS_GRADUATED, self::STATUS_ENROLLING],
            self::STATUS_GRADUATED => [self::STATUS_ARCHIVED],
            self::STATUS_ARCHIVED => [],
        ];

        if (!in_array($newStatus, $validTransitions[$this->status] ?? [])) {
            throw new \Exception("Cannot transition from {$this->status} to {$newStatus}");
        }

        $this->update(['status' => $newStatus]);

        // Handle side effects
        if ($newStatus === self::STATUS_ACTIVE) {
            // Activate all enrolled fellows
            $this->programFellows()
                ->where('status', 'enrolled')
                ->update(['status' => 'active', 'activated_at' => now()]);
        }

        return true;
    }

    // =========================================================================
    // DISPLAY HELPERS
    // =========================================================================

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_UPCOMING => 'blue',
            self::STATUS_ENROLLING => 'yellow',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_GRADUATED => 'purple',
            self::STATUS_ARCHIVED => 'dark',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_ENROLLING => 'Enrolling',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_GRADUATED => 'Graduated',
            self::STATUS_ARCHIVED => 'Archived',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get funding type label
     */
    public function getFundingLabelAttribute(): ?string
    {
        if (!$this->funding_type) {
            return null;
        }

        return match($this->funding_type) {
            self::FUNDING_SCHOLARSHIP => 'Scholarship',
            self::FUNDING_GRANT => 'Grant',
            self::FUNDING_SELF_FUNDED => 'Self-Funded',
            self::FUNDING_HYBRID => 'Hybrid',
            default => ucfirst($this->funding_type),
        };
    }

    /**
     * Get formatted date range
     */
    public function getDateRangeAttribute(): string
    {
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Get array representation for API/views
     */
    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'color' => $this->color,
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,
            'sponsor_name' => $this->sponsor_name,
            'funding_label' => $this->funding_label,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'date_range' => $this->date_range,
            'graduation_date' => $this->graduation_date?->format('Y-m-d'),
            'duration_weeks' => $this->duration_weeks,
            'current_week' => $this->current_week,
            'progress_percentage' => $this->progress_percentage,
            'days_remaining' => $this->days_remaining,
            'fellows_count' => $this->fellows_count,
            'graduated_count' => $this->graduated_count,
            'spots_remaining' => $this->spots_remaining,
            'max_fellows' => $this->max_fellows,
            'has_certificate' => $this->has_certificate,
            'can_enroll' => $this->canEnroll(),
            'is_enrollment_open' => $this->is_enrollment_open,
            'employment_rate' => $this->employment_rate,
        ];
    }
}
