<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fee Installment Model
 *
 * Represents a single installment within an installment-based fee.
 * Each installment has its own due date and tracks its own payment status.
 *
 * @property string $id UUID
 * @property string $fee_id
 * @property int $sequence
 * @property float $amount_due
 * @property float $amount_paid
 * @property \Carbon\Carbon $due_date
 * @property string $status
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class FeeInstallment extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** Status constants */
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_DUE = 'due';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'fee_id',
        'sequence',
        'amount_due',
        'amount_paid',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_due'  => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'due_date'    => 'date',
            'sequence'    => 'integer',
        ];
    }

    protected $attributes = [
        'amount_paid' => 0.00,
        'status'      => 'upcoming',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'installment_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Remaining balance for this installment.
     */
    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    /**
     * Whether this installment is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_PAID) {
            return false;
        }

        return now()->greaterThan($this->due_date) && $this->balance > 0;
    }

    /**
     * Human-readable label (e.g., "1st Installment").
     */
    public function getLabelAttribute(): string
    {
        $ordinal = match ($this->sequence) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => $this->sequence . 'th',
        };
        return $ordinal . ' Installment';
    }

    /**
     * Status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPCOMING => 'Upcoming',
            self::STATUS_DUE     => 'Due',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_PAID    => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            default              => ucfirst($this->status),
        };
    }

    /**
     * Status color for badges.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_UPCOMING => 'dark',
            self::STATUS_DUE     => 'blue',
            self::STATUS_PARTIAL => 'amber',
            self::STATUS_PAID    => 'green',
            self::STATUS_OVERDUE => 'red',
            default              => 'dark',
        };
    }
}
