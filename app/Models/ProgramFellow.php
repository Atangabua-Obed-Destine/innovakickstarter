<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Carbon\Carbon;

/**
 * ProgramFellow Pivot Model
 * 
 * Represents the enrollment of a fellow in a program, tracking their
 * status, milestones, certificate, and alumni outcomes.
 * 
 * Unlike a regular pivot, this stores significant data:
 * - Enrollment lifecycle (enrolled → active → completed)
 * - Milestone completion progress
 * - Certificate issuance
 * - Alumni outcome tracking (employment, salary)
 * 
 * @property string $id UUID primary key
 * @property string $program_id Program UUID
 * @property int $fellow_id User ID
 * @property string $status enrolled|active|completed|dropped|removed
 * @property \Carbon\Carbon $enrolled_at When enrolled
 * @property \Carbon\Carbon|null $activated_at When became active
 * @property \Carbon\Carbon|null $completed_at When graduated
 * @property \Carbon\Carbon|null $dropped_at When left
 * @property string|null $drop_reason Reason for leaving
 * @property bool $certificate_issued Certificate issued
 * @property \Carbon\Carbon|null $certificate_issued_at When issued
 * @property string|null $certificate_number Certificate ID
 * @property array|null $milestones_completed Milestone progress
 * @property string|null $employment_status Alumni employment status
 * @property string|null $employer_name Company name
 * @property string|null $job_title Position
 * @property float|null $starting_salary First salary
 * @property string|null $salary_currency Currency
 * @property \Carbon\Carbon|null $job_started_at Job start date
 * @property int|null $enrolled_by Admin who enrolled
 * @property string|null $notes Admin notes
 * 
 * @property-read Program $program
 * @property-read User $fellow
 * @property-read User $enrolledByUser
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ProgramFellow extends Pivot
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'program_fellows';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

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

    // Active statuses (for counting)
    public const ACTIVE_STATUSES = [
        self::STATUS_ENROLLED,
        self::STATUS_ACTIVE,
    ];

    // Employment statuses
    public const EMPLOYMENT_EMPLOYED = 'employed';
    public const EMPLOYMENT_FREELANCING = 'freelancing';
    public const EMPLOYMENT_FURTHER_EDUCATION = 'further_education';
    public const EMPLOYMENT_SEEKING = 'seeking';
    public const EMPLOYMENT_OTHER = 'other';

    public const EMPLOYMENT_STATUSES = [
        self::EMPLOYMENT_EMPLOYED,
        self::EMPLOYMENT_FREELANCING,
        self::EMPLOYMENT_FURTHER_EDUCATION,
        self::EMPLOYMENT_SEEKING,
        self::EMPLOYMENT_OTHER,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'program_id',
        'fellow_id',
        'status',
        'enrolled_at',
        'activated_at',
        'completed_at',
        'dropped_at',
        'drop_reason',
        'certificate_issued',
        'certificate_issued_at',
        'certificate_number',
        'milestones_completed',
        'employment_status',
        'employer_name',
        'job_title',
        'starting_salary',
        'salary_currency',
        'job_started_at',
        'enrolled_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'activated_at' => 'datetime',
            'completed_at' => 'datetime',
            'dropped_at' => 'datetime',
            'certificate_issued' => 'boolean',
            'certificate_issued_at' => 'datetime',
            'milestones_completed' => 'array',
            'starting_salary' => 'decimal:2',
            'job_started_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'status' => self::STATUS_ENROLLED,
        'certificate_issued' => false,
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Get the program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
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
    // MILESTONE METHODS
    // =========================================================================

    /**
     * Mark a milestone as completed
     */
    public function completeMilestone(string $key, ?string $notes = null): void
    {
        $completed = $this->milestones_completed ?? [];
        
        $completed[$key] = [
            'completed' => true,
            'completed_at' => now()->toIso8601String(),
            'notes' => $notes,
        ];

        $this->update(['milestones_completed' => $completed]);
    }

    /**
     * Check if a specific milestone is completed
     */
    public function hasMilestoneCompleted(string $key): bool
    {
        $completed = $this->milestones_completed ?? [];
        return isset($completed[$key]) && $completed[$key]['completed'] === true;
    }

    /**
     * Get milestone completion percentage
     */
    public function getMilestoneCompletionPercentage(): int
    {
        $program = $this->program;
        if (!$program || empty($program->milestones)) {
            return 0;
        }

        $total = count($program->milestones);
        $completed = collect($this->milestones_completed ?? [])
            ->filter(fn($m) => $m['completed'] ?? false)
            ->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    /**
     * Get completed milestones count
     */
    public function getCompletedMilestonesCountAttribute(): int
    {
        return collect($this->milestones_completed ?? [])
            ->filter(fn($m) => $m['completed'] ?? false)
            ->count();
    }

    // =========================================================================
    // ALUMNI TRACKING METHODS
    // =========================================================================

    /**
     * Update employment outcome
     */
    public function updateEmploymentOutcome(array $data): void
    {
        $this->update([
            'employment_status' => $data['employment_status'] ?? $this->employment_status,
            'employer_name' => $data['employer_name'] ?? $this->employer_name,
            'job_title' => $data['job_title'] ?? $this->job_title,
            'starting_salary' => $data['starting_salary'] ?? $this->starting_salary,
            'salary_currency' => $data['salary_currency'] ?? $this->salary_currency,
            'job_started_at' => $data['job_started_at'] ?? $this->job_started_at,
        ]);

        // Recalculate program employment rate
        $this->program?->calculateEmploymentRate();
    }

    /**
     * Is this an employed alumni?
     */
    public function isEmployed(): bool
    {
        return in_array($this->employment_status, [
            self::EMPLOYMENT_EMPLOYED,
            self::EMPLOYMENT_FREELANCING,
        ]);
    }

    /**
     * Get formatted salary
     */
    public function getFormattedSalaryAttribute(): ?string
    {
        if (!$this->starting_salary) {
            return null;
        }

        $currency = $this->salary_currency ?? 'XAF';
        return number_format($this->starting_salary, 0) . ' ' . $currency;
    }

    // =========================================================================
    // STATUS METHODS
    // =========================================================================

    /**
     * Check if enrollment is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES);
    }

    /**
     * Check if graduated
     */
    public function isGraduated(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if dropped/removed
     */
    public function hasLeft(): bool
    {
        return in_array($this->status, [self::STATUS_DROPPED, self::STATUS_REMOVED]);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ENROLLED => 'blue',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_COMPLETED => 'purple',
            self::STATUS_DROPPED => 'yellow',
            self::STATUS_REMOVED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ENROLLED => 'Enrolled',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_COMPLETED => 'Graduated',
            self::STATUS_DROPPED => 'Dropped',
            self::STATUS_REMOVED => 'Removed',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get employment status label
     */
    public function getEmploymentLabelAttribute(): ?string
    {
        if (!$this->employment_status) {
            return null;
        }

        return match($this->employment_status) {
            self::EMPLOYMENT_EMPLOYED => 'Employed',
            self::EMPLOYMENT_FREELANCING => 'Freelancing',
            self::EMPLOYMENT_FURTHER_EDUCATION => 'Further Education',
            self::EMPLOYMENT_SEEKING => 'Job Seeking',
            self::EMPLOYMENT_OTHER => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->employment_status)),
        };
    }

    // =========================================================================
    // DURATION HELPERS
    // =========================================================================

    /**
     * Get days enrolled in program
     */
    public function getDaysEnrolledAttribute(): int
    {
        $endDate = $this->completed_at ?? $this->dropped_at ?? now();
        return $this->enrolled_at->diffInDays($endDate);
    }

    /**
     * Get weeks enrolled
     */
    public function getWeeksEnrolledAttribute(): int
    {
        return (int) ceil($this->days_enrolled / 7);
    }

    // =========================================================================
    // DISPLAY HELPERS
    // =========================================================================

    /**
     * Get array representation for API/views
     */
    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'program_name' => $this->program?->name,
            'fellow_id' => $this->fellow_id,
            'fellow_name' => $this->fellow?->name,
            'fellow_email' => $this->fellow?->email,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'enrolled_at' => $this->enrolled_at?->format('M d, Y'),
            'activated_at' => $this->activated_at?->format('M d, Y'),
            'completed_at' => $this->completed_at?->format('M d, Y'),
            'dropped_at' => $this->dropped_at?->format('M d, Y'),
            'drop_reason' => $this->drop_reason,
            'days_enrolled' => $this->days_enrolled,
            'weeks_enrolled' => $this->weeks_enrolled,
            'certificate_issued' => $this->certificate_issued,
            'certificate_number' => $this->certificate_number,
            'milestone_completion' => $this->getMilestoneCompletionPercentage(),
            'completed_milestones' => $this->completed_milestones_count,
            'employment_status' => $this->employment_status,
            'employment_label' => $this->employment_label,
            'employer_name' => $this->employer_name,
            'job_title' => $this->job_title,
            'formatted_salary' => $this->formatted_salary,
        ];
    }
}
