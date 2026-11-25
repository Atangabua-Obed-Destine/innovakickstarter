<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A verifiable certificate for one skill of a track. */
class SkillCertificate extends Model
{
    use HasUuids;

    protected $fillable = ['fellow_id', 'track_id', 'skill_key', 'skill_label', 'activities_completed', 'points_earned', 'issued_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function title(): string
    {
        return "{$this->skill_label} · {$this->track?->name}";
    }

    /** Short code shown on the certificate (first block of the id). */
    public function code(): string
    {
        return strtoupper(substr(str_replace('-', '', $this->id), 0, 10));
    }

    /** LinkedIn "Add to profile" link for this certificate. */
    public function linkedInUrl(): string
    {
        return 'https://www.linkedin.com/profile/add?' . http_build_query([
            'startTask' => 'CERTIFICATION_NAME',
            'name' => "{$this->skill_label} ({$this->track?->name})",
            'organizationName' => 'I-NNOVA Kickstarter',
            'issueYear' => $this->issued_at->year,
            'issueMonth' => $this->issued_at->month,
            'certUrl' => route('certificates.show', $this),
            'certId' => $this->code(),
        ]);
    }
}
