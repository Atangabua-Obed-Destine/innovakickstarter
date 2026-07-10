<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Mentorship Pod Model
 *
 * Represents a small team of fellows (2–4) within a track,
 * led by a Pod Lead who sets the pod's identity. Members see
 * each other's Career Capital scores and progress.
 *
 * @property string $id UUID
 * @property string $track_id
 * @property int $lead_id
 * @property string|null $name
 * @property string|null $nickname
 * @property string|null $emoji
 * @property string|null $color
 * @property string|null $description
 * @property bool $is_active
 * @property int $max_members
 * @property int $created_by
 * @property \Carbon\Carbon|null $closed_at
 *
 * @author IKS Engineering Team
 */
class MentorshipPod extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'track_id',
        'lead_id',
        'name',
        'nickname',
        'emoji',
        'color',
        'description',
        'is_active',
        'max_members',
        'created_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_members' => 'integer',
            'closed_at' => 'datetime',
        ];
    }

    protected $attributes = [
        'is_active' => true,
        'max_members' => 4,
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(MentorshipPodMember::class, 'pod_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(MentorshipPodMember::class, 'pod_id')
            ->where('is_active', true);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrack($query, string $trackId)
    {
        return $query->where('track_id', $trackId);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return ($this->emoji ? $this->emoji . ' ' : '') . $this->name;
        }

        return 'Unnamed Pod';
    }

    public function getActiveMemberCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    // ==========================================
    // HELPERS
    // ==========================================

    public function isFull(): bool
    {
        return $this->active_member_count >= $this->max_members;
    }

    public function isNamed(): bool
    {
        return !empty($this->name);
    }

    public function isLead(User $user): bool
    {
        return $this->lead_id === $user->id;
    }

    public function hasMember(User $user): bool
    {
        return $this->activeMembers()->where('fellow_id', $user->id)->exists();
    }

    /**
     * Close the pod (admin action).
     */
    public function close(): void
    {
        $this->update([
            'is_active' => false,
            'closed_at' => now(),
        ]);

        // Deactivate all memberships
        $this->activeMembers()->update([
            'is_active' => false,
            'left_at' => now(),
        ]);
    }
}
