<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * 
 * IMMUTABLE audit trail for all Career Capital changes.
 * This table is critical for transparency and compliance.
 * 
 * Every score change records:
 * - Who: Admin who made the change
 * - When: Timestamp
 * - What: Previous → New score
 * - Why: Justification (minimum 10 chars)
 * 
 * @property string $id UUID
 * @property int $fellow_id
 * @property string|null $track_id UUID
 * @property int $admin_id
 * @property string $action
 * @property float|null $previous_score
 * @property float|null $new_score
 * @property string $justification
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AuditLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * Disable timestamps (only created_at, immutable).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'fellow_id',
        'track_id',
        'admin_id',
        'action',
        'category',
        'previous_score',
        'new_score',
        'score_delta',
        'previous_tier',
        'new_tier',
        'old_values',
        'new_values',
        'changed_fields',
        'justification',
        'related_activity_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'previous_score' => 'decimal:2',
            'new_score' => 'decimal:2',
            'score_delta' => 'decimal:2',
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_fields' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (AuditLog $log) {
            $log->created_at = now();
            
            // Calculate score delta
            if ($log->previous_score !== null && $log->new_score !== null) {
                $log->score_delta = $log->new_score - $log->previous_score;
            }

            // Capture IP and user agent
            if (request()) {
                $log->ip_address = $log->ip_address ?? request()->ip();
                $log->user_agent = $log->user_agent ?? request()->userAgent();
            }
        });

        // Prevent updates
        static::updating(function () {
            throw new \Exception('Audit logs are immutable and cannot be updated.');
        });

        // Prevent deletes
        static::deleting(function () {
            throw new \Exception('Audit logs are immutable and cannot be deleted.');
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the fellow whose data was changed.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track related to this change.
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class, 'track_id');
    }

    /**
     * Get the admin who made the change.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the related activity if applicable.
     */
    public function relatedActivity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'related_activity_id');
    }

    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get formatted score change.
     */
    public function getFormattedScoreChangeAttribute(): string
    {
        if ($this->previous_score === null || $this->new_score === null) {
            return 'N/A';
        }

        $delta = $this->score_delta >= 0 ? '+' . $this->score_delta : $this->score_delta;
        return "{$this->previous_score}% → {$this->new_score}% ({$delta})";
    }

    /**
     * Check if score increased.
     */
    public function getScoreIncreasedAttribute(): bool
    {
        return $this->score_delta !== null && $this->score_delta > 0;
    }

    /**
     * Check if tier changed.
     */
    public function getTierChangedAttribute(): bool
    {
        return $this->previous_tier !== null && 
               $this->new_tier !== null && 
               $this->previous_tier !== $this->new_tier;
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'score_adjusted' => 'Score Adjusted',
            'tier_changed' => 'Tier Changed',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get action color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'blue',
            'approved', 'score_adjusted' => 'green',
            'rejected', 'deleted' => 'red',
            'tier_changed' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'career_capital' => 'Career Capital',
            'activity' => 'Activity',
            'interview' => 'Interview',
            'profile' => 'Profile',
            'track_switch' => 'Track Switch',
            default => ucfirst($this->category ?? 'General'),
        };
    }

    /**
     * Get relative time.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope by action.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope by category.
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to score changes.
     */
    public function scopeScoreChanges($query)
    {
        return $query->whereNotNull('score_delta');
    }

    /**
     * Scope to tier promotions.
     */
    public function scopeTierPromotions($query)
    {
        return $query->where('action', 'tier_changed')
            ->whereColumn('new_tier', '>', 'previous_tier');
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Order by most recent.
     */
    public function scopeNewest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Create a score adjustment log.
     */
    public static function logScoreAdjustment(
        User $fellow,
        Track $track,
        User $admin,
        float $previousScore,
        float $newScore,
        string $justification,
        ?Activity $relatedActivity = null
    ): self {
        return static::create([
            'auditable_type' => FellowTrack::class,
            'auditable_id' => $fellow->fellowTracks()->where('track_id', $track->id)->first()?->id,
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'admin_id' => $admin->id,
            'action' => 'score_adjusted',
            'category' => 'career_capital',
            'previous_score' => $previousScore,
            'new_score' => $newScore,
            'justification' => $justification,
            'related_activity_id' => $relatedActivity?->id,
        ]);
    }

    /**
     * Create a tier change log.
     */
    public static function logTierChange(
        User $fellow,
        Track $track,
        User $admin,
        string $previousTier,
        string $newTier,
        float $score,
        string $justification
    ): self {
        return static::create([
            'auditable_type' => FellowTrack::class,
            'auditable_id' => $fellow->fellowTracks()->where('track_id', $track->id)->first()?->id,
            'fellow_id' => $fellow->id,
            'track_id' => $track->id,
            'admin_id' => $admin->id,
            'action' => 'tier_changed',
            'category' => 'career_capital',
            'previous_tier' => $previousTier,
            'new_tier' => $newTier,
            'new_score' => $score,
            'justification' => $justification,
        ]);
    }

    /**
     * Create an activity approval log.
     */
    public static function logActivityApproval(
        Activity $activity,
        User $admin,
        string $action,
        string $justification
    ): self {
        return static::create([
            'auditable_type' => Activity::class,
            'auditable_id' => $activity->id,
            'fellow_id' => $activity->fellow_id,
            'track_id' => $activity->track_id,
            'admin_id' => $admin->id,
            'action' => $action,
            'category' => 'activity',
            'justification' => $justification,
        ]);
    }
}
