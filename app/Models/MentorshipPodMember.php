<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mentorship Pod Member (pivot with timestamps)
 *
 * @property string $id UUID
 * @property string $pod_id
 * @property int $fellow_id
 * @property \Carbon\Carbon $joined_at
 * @property \Carbon\Carbon|null $left_at
 * @property bool $is_active
 *
 * @author IKS Engineering Team
 */
class MentorshipPodMember extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'pod_id',
        'fellow_id',
        'joined_at',
        'left_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected $attributes = [
        'is_active' => true,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pod(): BelongsTo
    {
        return $this->belongsTo(MentorshipPod::class, 'pod_id');
    }

    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
