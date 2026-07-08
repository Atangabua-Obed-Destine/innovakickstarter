<?php

namespace App\Models;

use App\Enums\FellowType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Internship Profile Model
 * 
 * Stores internship-specific details for academic and corporate fellows.
 * Created during onboarding when a fellow identifies as an academic
 * or corporate intern.
 * 
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $type
 * @property string $institution_name
 * @property string|null $department
 * @property string|null $academic_level
 * @property string|null $student_id
 * @property string $supervisor_name
 * @property string $supervisor_email
 * @property string|null $supervisor_phone
 * @property string|null $internship_letter_path
 * @property string $duration_type
 * @property int|null $predefined_duration_months
 * @property \Carbon\Carbon|null $start_date
 * @property \Carbon\Carbon|null $end_date
 * @property string $status
 * @property string|null $notes
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class InternshipProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'institution_name',
        'department',
        'academic_level',
        'student_id',
        'supervisor_name',
        'supervisor_email',
        'supervisor_phone',
        'internship_letter_path',
        'duration_type',
        'predefined_duration_months',
        'start_date',
        'end_date',
        'status',
        'notes',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'approved_start_date',
        'approved_end_date',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'predefined_duration_months' => 'integer',
            'reviewed_at' => 'datetime',
            'approved_start_date' => 'date',
            'approved_end_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /** Status constants */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_NEEDS_REVISION = 'needs_revision';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_WITHDRAWN = 'withdrawn';

    /** Duration type constants */
    public const DURATION_PREDEFINED = 'predefined';
    public const DURATION_CUSTOM = 'custom';

    /** Available predefined durations in months */
    public const PREDEFINED_DURATIONS = [1, 2, 3, 6, 12];

    /** Available academic levels */
    public const ACADEMIC_LEVELS = [
        'diploma' => 'Diploma',
        'hnd' => 'HND',
        'btec' => 'BTEC',
        'bachelor' => 'Bachelor\'s Degree',
        'master' => 'Master\'s Degree',
        'phd' => 'PhD / Doctorate',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $profile) {
            if (empty($profile->uuid)) {
                $profile->uuid = Str::uuid()->toString();
            }
        });
    }

    // ─── Relationships ───────────────────────────────────────────

    /**
     * Get the fellow (user) this internship profile belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for clarity.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Admin who last reviewed this profile.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Accessors ───────────────────────────────────────────────

    /**
     * Get calculated duration in months.
     */
    public function getDurationMonthsAttribute(): ?int
    {
        if ($this->duration_type === self::DURATION_PREDEFINED) {
            return $this->predefined_duration_months;
        }

        if ($this->start_date && $this->end_date) {
            return (int) $this->start_date->diffInMonths($this->end_date);
        }

        return null;
    }

    /**
     * Get human-readable duration string.
     */
    public function getDurationLabelAttribute(): string
    {
        $months = $this->duration_months;

        if (!$months) {
            return 'Not specified';
        }

        return $months === 1 ? '1 month' : "{$months} months";
    }

    /**
     * Whether this is an academic internship.
     */
    public function getIsAcademicAttribute(): bool
    {
        return $this->type === FellowType::ACADEMIC->value;
    }

    /**
     * Whether this is a corporate internship.
     */
    public function getIsCorporateAttribute(): bool
    {
        return $this->type === FellowType::CORPORATE->value;
    }

    // ─── Progress accessors (based on approved dates) ───────────

    /**
     * Total approved duration in days.
     */
    public function getTotalDaysAttribute(): ?int
    {
        if (!$this->approved_start_date || !$this->approved_end_date) {
            return null;
        }
        return max(1, (int) $this->approved_start_date->diffInDays($this->approved_end_date) + 1);
    }

    /**
     * Days elapsed since approved start (clamped to [0, total]).
     */
    public function getDaysElapsedAttribute(): ?int
    {
        if (!$this->approved_start_date) {
            return null;
        }
        $today = now()->startOfDay();
        if ($today->lt($this->approved_start_date)) {
            return 0;
        }
        $elapsed = (int) $this->approved_start_date->diffInDays($today) + 1;
        return $this->total_days ? min($elapsed, $this->total_days) : $elapsed;
    }

    /**
     * Days remaining until approved end (0 if past).
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->approved_end_date) {
            return null;
        }
        $today = now()->startOfDay();
        if ($today->gt($this->approved_end_date)) {
            return 0;
        }
        return (int) $today->diffInDays($this->approved_end_date);
    }

    /**
     * Progress percent 0-100.
     */
    public function getProgressPercentAttribute(): ?int
    {
        if (!$this->total_days || !$this->days_elapsed) {
            return $this->approved_start_date ? 0 : null;
        }
        return min(100, (int) round(($this->days_elapsed / $this->total_days) * 100));
    }

    /**
     * Whether the approved window has passed.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->approved_end_date
            && now()->startOfDay()->gt($this->approved_end_date);
    }

    /**
     * Whether the fellow is currently in-window.
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if (!$this->approved_start_date || !$this->approved_end_date) {
            return false;
        }
        $today = now()->startOfDay();
        return $today->betweenIncluded($this->approved_start_date, $this->approved_end_date);
    }

    /**
     * Whether admin has cleared the fellow to use the platform.
     */
    public function getIsClearedAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_ACTIVE,
            self::STATUS_COMPLETED,
        ], true);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    /**
     * Scope to academic internships.
     */
    public function scopeAcademic($query)
    {
        return $query->where('type', FellowType::ACADEMIC->value);
    }

    /**
     * Scope to corporate internships.
     */
    public function scopeCorporate($query)
    {
        return $query->where('type', FellowType::CORPORATE->value);
    }

    /**
     * Scope by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to active internships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
