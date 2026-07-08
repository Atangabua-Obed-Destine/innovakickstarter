<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Fee Model
 *
 * Represents a fee assigned to a fellow. Fees can be one-time or split
 * into installments. They are polymorphically linked to a billable
 * entity (InternshipProfile, Cohort, Program, Track).
 *
 * @property string $id UUID
 * @property string $reference
 * @property int $fellow_id
 * @property string|null $billable_type
 * @property string|null $billable_id
 * @property string $title
 * @property string|null $description
 * @property float $amount_total
 * @property float $amount_paid
 * @property string $currency
 * @property string $plan_type
 * @property int|null $installments_count
 * @property string|null $installment_cadence
 * @property \Carbon\Carbon $first_due_date
 * @property \Carbon\Carbon $final_due_date
 * @property int|null $grace_period_hours
 * @property string $status
 * @property int|null $assigned_by
 * @property \Carbon\Carbon|null $assigned_at
 * @property int|null $waived_by
 * @property \Carbon\Carbon|null $waived_at
 * @property string|null $waived_reason
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class Fee extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** Status constants */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_WAIVED = 'waived';

    /** Plan type constants */
    public const PLAN_ONE_TIME = 'one_time';
    public const PLAN_INSTALLMENTS = 'installments';

    protected $fillable = [
        'reference',
        'fellow_id',
        'billable_type',
        'billable_id',
        'title',
        'description',
        'amount_total',
        'amount_paid',
        'currency',
        'plan_type',
        'installments_count',
        'installment_cadence',
        'first_due_date',
        'final_due_date',
        'grace_period_hours',
        'status',
        'assigned_by',
        'assigned_at',
        'waived_by',
        'waived_at',
        'waived_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_total'      => 'decimal:2',
            'amount_paid'       => 'decimal:2',
            'installments_count'=> 'integer',
            'grace_period_hours'=> 'integer',
            'first_due_date'    => 'date',
            'final_due_date'    => 'date',
            'assigned_at'       => 'datetime',
            'waived_at'         => 'datetime',
        ];
    }

    protected $attributes = [
        'amount_paid' => 0.00,
        'currency'    => 'XAF',
        'plan_type'   => 'one_time',
        'status'      => 'active',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function assignedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function waivedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class)->orderBy('sequence');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->orderByDesc('payment_date');
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->where('status', FeePayment::STATUS_VERIFIED);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Remaining balance.
     */
    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount_total - (float) $this->amount_paid);
    }

    /**
     * Whether the fee is past its final due date.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, [self::STATUS_PAID, self::STATUS_WAIVED])) {
            return false;
        }

        $grace = $this->grace_period_hours ?? 48;
        $deadline = $this->final_due_date->copy()->addHours($grace);

        return now()->greaterThan($deadline) && $this->balance > 0;
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE         => 'Active',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_PAID           => 'Fully Paid',
            self::STATUS_OVERDUE        => 'Overdue',
            self::STATUS_WAIVED         => 'Waived',
            default                     => ucfirst($this->status),
        };
    }

    /**
     * CSS color class for the status badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE         => 'blue',
            self::STATUS_PARTIALLY_PAID => 'amber',
            self::STATUS_PAID           => 'green',
            self::STATUS_OVERDUE        => 'red',
            self::STATUS_WAIVED         => 'dark',
            default                     => 'dark',
        };
    }

    /**
     * Formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->amount_total, 2) . ' ' . $this->currency;
    }

    /**
     * Formatted paid amount.
     */
    public function getFormattedPaidAttribute(): string
    {
        return number_format((float) $this->amount_paid, 2) . ' ' . $this->currency;
    }

    /**
     * Formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    /**
     * Billable label (human-readable).
     */
    public function getBillableLabelAttribute(): string
    {
        if (!$this->billable_type) {
            return 'General Fee';
        }

        return match ($this->billable_type) {
            'App\\Models\\InternshipProfile' => 'Internship',
            'App\\Models\\Cohort'            => 'Cohort: ' . ($this->billable?->name ?? 'N/A'),
            'App\\Models\\Program'           => 'Program: ' . ($this->billable?->name ?? 'N/A'),
            'App\\Models\\Track'             => 'Track: ' . ($this->billable?->name ?? 'N/A'),
            default                          => class_basename($this->billable_type),
        };
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', self::STATUS_PARTIALLY_PAID);
    }

    public function scopeWaived($query)
    {
        return $query->where('status', self::STATUS_WAIVED);
    }

    public function scopeForFellow($query, int $fellowId)
    {
        return $query->where('fellow_id', $fellowId);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_PARTIALLY_PAID, self::STATUS_OVERDUE]);
    }

    /**
     * Scope to fees that are overdue past grace period.
     */
    public function scopeOverduePastGrace($query)
    {
        return $query->where('status', '!=', self::STATUS_PAID)
            ->where('status', '!=', self::STATUS_WAIVED)
            ->whereRaw('DATE_ADD(final_due_date, INTERVAL COALESCE(grace_period_hours, 48) HOUR) < NOW()')
            ->where('amount_paid', '<', \DB::raw('amount_total'));
    }
}
