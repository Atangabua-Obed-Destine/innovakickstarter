<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Fee Payment Model
 *
 * Represents a single payment against a fee. Payments can be
 * admin-entered (auto-verified) or fellow-uploaded (needs verification).
 *
 * @property string $id UUID
 * @property string $verify_uuid
 * @property string|null $receipt_number
 * @property string|null $reference
 * @property string $fee_id
 * @property string|null $installment_id
 * @property int $fellow_id
 * @property float $amount
 * @property string $method
 * @property \Carbon\Carbon $payment_date
 * @property string $source
 * @property string|null $receipt_path
 * @property string|null $notes
 * @property string $status
 * @property int|null $verified_by
 * @property \Carbon\Carbon|null $verified_at
 * @property int|null $rejected_by
 * @property \Carbon\Carbon|null $rejected_at
 * @property string|null $rejection_reason
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class FeePayment extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** Status constants */
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    /** Source constants */
    public const SOURCE_ADMIN = 'admin_entry';
    public const SOURCE_FELLOW = 'fellow_upload';

    /** Payment method constants */
    public const METHOD_CASH = 'cash';
    public const METHOD_MTN_MOMO = 'mtn_momo';
    public const METHOD_ORANGE_MONEY = 'orange_money';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    protected $fillable = [
        'verify_uuid',
        'receipt_number',
        'reference',
        'fee_id',
        'installment_id',
        'fellow_id',
        'amount',
        'method',
        'payment_date',
        'source',
        'receipt_path',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'payment_date' => 'date',
            'verified_at'  => 'datetime',
            'rejected_at'  => 'datetime',
        ];
    }

    protected $attributes = [
        'source' => 'fellow_upload',
        'status' => 'submitted',
    ];

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (!$payment->verify_uuid) {
                $payment->verify_uuid = (string) Str::uuid();
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(FeeInstallment::class, 'installment_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Whether payment has been verified.
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Whether payment was rejected.
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Whether payment is pending verification.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * Human-readable method label.
     */
    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            self::METHOD_CASH          => 'Cash',
            self::METHOD_MTN_MOMO      => 'MTN Mobile Money',
            self::METHOD_ORANGE_MONEY  => 'Orange Money',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            default                    => ucfirst(str_replace('_', ' ', $this->method)),
        };
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'Pending',
            self::STATUS_VERIFIED  => 'Approved',
            self::STATUS_REJECTED  => 'Rejected',
            default                => ucfirst($this->status),
        };
    }

    /**
     * CSS color for status badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUBMITTED => 'amber',
            self::STATUS_VERIFIED  => 'green',
            self::STATUS_REJECTED  => 'red',
            default                => 'dark',
        };
    }

    /**
     * Human-readable source label.
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_ADMIN  => 'Admin Entry',
            self::SOURCE_FELLOW => 'Fellow Upload',
            default             => ucfirst($this->source),
        };
    }

    /**
     * Formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2) . ' CFA';
    }

    /**
     * Public verification URL.
     */
    public function getVerifyUrlAttribute(): string
    {
        return url('/receipt/verify/' . $this->verify_uuid);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFromAdmin($query)
    {
        return $query->where('source', self::SOURCE_ADMIN);
    }

    public function scopeFromFellow($query)
    {
        return $query->where('source', self::SOURCE_FELLOW);
    }

    /**
     * Available payment methods for dropdowns.
     */
    public static function paymentMethods(): array
    {
        return [
            self::METHOD_CASH          => 'Cash',
            self::METHOD_MTN_MOMO      => 'MTN Mobile Money',
            self::METHOD_ORANGE_MONEY  => 'Orange Money',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
        ];
    }
}
