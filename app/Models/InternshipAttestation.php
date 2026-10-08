<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Internship Attestation Model
 *
 * A verifiable, bilingual attestation issued to a fellow once their
 * internship has ended. The `snapshot` holds a frozen copy of the record
 * printed on the document; it is never recomputed after issue.
 *
 * Lifecycle: draft → issued → revoked | superseded (by a reissued version)
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $reference
 * @property string|null $verification_code
 * @property int $internship_profile_id
 * @property int $fellow_id
 * @property string|null $track_id
 * @property string $status
 * @property int $version
 * @property string|null $mention
 * @property string|null $suggested_mention
 * @property array|null $snapshot
 * @property string|null $content_hash
 *
 * @author IKS Engineering Team
 * @version 1.0
 */
class InternshipAttestation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_SUPERSEDED = 'superseded';

    /** Mentions, best first: key => [English, French] */
    public const MENTIONS = [
        'excellent' => ['Excellent', 'Excellent'],
        'very_good' => ['Very Good', 'Très Bien'],
        'good' => ['Good', 'Bien'],
        'satisfactory' => ['Satisfactory', 'Passable'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'reference',
        'verification_code',
        'internship_profile_id',
        'fellow_id',
        'track_id',
        'status',
        'version',
        'supersedes_id',
        'mention',
        'suggested_mention',
        'remarks_en',
        'remarks_fr',
        'snapshot',
        'content_hash',
        'signatory_name',
        'signatory_title',
        'pdf_path',
        'issued_at',
        'issued_by',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'version' => 'integer',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $attestation) {
            if (empty($attestation->uuid)) {
                $attestation->uuid = Str::uuid()->toString();
            }
        });
    }

    // ─── Relationships ───────────────────────────────────────────

    public function internshipProfile(): BelongsTo
    {
        return $this->belongsTo(InternshipProfile::class);
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id')->withTrashed();
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * The earlier version this attestation replaces.
     */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    /**
     * The newer version that replaced this attestation, if any.
     */
    public function supersededBy()
    {
        return $this->hasOne(self::class, 'supersedes_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    // ─── Accessors & helpers ─────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    /**
     * English label of the awarded (or suggested) mention.
     */
    public function getMentionEnAttribute(): ?string
    {
        return self::MENTIONS[$this->mention][0] ?? null;
    }

    /**
     * French label of the awarded (or suggested) mention.
     */
    public function getMentionFrAttribute(): ?string
    {
        return self::MENTIONS[$this->mention][1] ?? null;
    }

    /**
     * Short fingerprint printed on the document and shown on verification.
     */
    public function getFingerprintAttribute(): ?string
    {
        return $this->content_hash
            ? strtoupper(implode('-', str_split(substr($this->content_hash, 0, 12), 4)))
            : null;
    }

    /**
     * Public verification URL (the QR code target).
     */
    public function getVerifyUrlAttribute(): string
    {
        return route('attestation.verify', $this->uuid);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_REVOKED => 'Revoked',
            self::STATUS_SUPERSEDED => 'Superseded',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
            self::STATUS_ISSUED => 'bg-green-600/20 text-green-400 border-green-500/30',
            self::STATUS_REVOKED => 'bg-red-600/20 text-red-400 border-red-500/30',
            default => 'bg-dark-600/40 text-dark-300 border-dark-500/30',
        };
    }
}
