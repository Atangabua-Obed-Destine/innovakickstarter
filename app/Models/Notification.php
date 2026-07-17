<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification Model
 * 
 * In-app notifications for all user types.
 * 
 * Notification Types:
 * - activity_approved/rejected: Activity status change
 * - tier_promotion: Fellow reached new tier
 * - weekly_reminder: 4-pillar completion reminder
 * - interview_scheduled: Interview appointment
 * - recruiter_view: Recruiter viewed profile
 * - recruiter_contact: Recruiter requested contact
 * - system_announcement: Platform-wide announcements
 * 
 * @property string $id UUID
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string|null $body
 * @property array|null $data
 * @property bool $is_read
 * @property Carbon|null $read_at
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class Notification extends Model
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
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'action_url',
        'action_text',
        'data',
        'is_read',
        'read_at',
        'priority',
        'expires_at',
        'category',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'is_read' => false,
        'priority' => 'normal',
    ];

    // ==========================================
    // CONSTANTS
    // ==========================================

    // Notification Types
    public const TYPE_ACTIVITY_APPROVED = 'activity_approved';
    public const TYPE_ACTIVITY_REJECTED = 'activity_rejected';
    public const TYPE_ACTIVITY_NEEDS_REVISION = 'activity_needs_revision';
    public const TYPE_ACTIVITY_REVIEW_REVERTED = 'activity_review_reverted';
    public const TYPE_TIER_PROMOTION = 'tier_promotion';
    public const TYPE_TIER_DEMOTION = 'tier_demotion';
    public const TYPE_WEEKLY_REMINDER = 'weekly_reminder';
    public const TYPE_SCORE_UPDATE = 'score_update';
    public const TYPE_INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const TYPE_INTERVIEW_REMINDER = 'interview_reminder';
    public const TYPE_INTERVIEW_COMPLETED = 'interview_completed';
    public const TYPE_RECRUITER_VIEW = 'recruiter_view';
    public const TYPE_RECRUITER_CONTACT = 'recruiter_contact';
    public const TYPE_RECRUITER_SHORTLIST = 'recruiter_shortlist';
    public const TYPE_NEW_MESSAGE = 'new_message';
    public const TYPE_ACHIEVEMENT = 'achievement';
    public const TYPE_SYSTEM_ANNOUNCEMENT = 'system_announcement';

    // Priority Levels
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the user who received this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // BACKWARD COMPATIBILITY ACCESSORS
    // ==========================================

    /**
     * Alias: 'body' reads from 'message' column for backward compatibility.
     */
    public function getBodyAttribute(): ?string
    {
        return $this->attributes['message'] ?? null;
    }

    /**
     * Alias: setting 'body' writes to 'message' column.
     */
    public function setBodyAttribute($value): void
    {
        $this->attributes['message'] = $value;
    }

    /**
     * Alias: 'action_label' reads from 'action_text' column.
     */
    public function getActionLabelAttribute(): ?string
    {
        return $this->attributes['action_text'] ?? null;
    }

    /**
     * Alias: setting 'action_label' writes to 'action_text' column.
     */
    public function setActionLabelAttribute($value): void
    {
        $this->attributes['action_text'] = $value;
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get notification icon.
     */
    public function getIconAttribute($value): string
    {
        if ($value) return $value;

        return match($this->type) {
            self::TYPE_ACTIVITY_APPROVED => 'check-circle',
            self::TYPE_ACTIVITY_REJECTED => 'x-circle',
            self::TYPE_ACTIVITY_NEEDS_REVISION => 'pencil',
            self::TYPE_TIER_PROMOTION => 'arrow-up-circle',
            self::TYPE_TIER_DEMOTION => 'arrow-down-circle',
            self::TYPE_WEEKLY_REMINDER => 'clock',
            self::TYPE_SCORE_UPDATE => 'chart-bar',
            self::TYPE_INTERVIEW_SCHEDULED, self::TYPE_INTERVIEW_REMINDER => 'calendar',
            self::TYPE_INTERVIEW_COMPLETED => 'video-camera',
            self::TYPE_RECRUITER_VIEW => 'eye',
            self::TYPE_RECRUITER_CONTACT => 'phone',
            self::TYPE_RECRUITER_SHORTLIST => 'star',
            self::TYPE_NEW_MESSAGE => 'mail',
            self::TYPE_ACHIEVEMENT => 'badge-check',
            self::TYPE_SYSTEM_ANNOUNCEMENT => 'speakerphone',
            default => 'bell',
        };
    }

    /**
     * Get notification color.
     */
    public function getColorAttribute($value): string
    {
        if ($value) return $value;

        return match($this->type) {
            self::TYPE_ACTIVITY_APPROVED, self::TYPE_TIER_PROMOTION => 'green',
            self::TYPE_ACTIVITY_REJECTED, self::TYPE_TIER_DEMOTION => 'red',
            self::TYPE_ACTIVITY_NEEDS_REVISION => 'yellow',
            self::TYPE_WEEKLY_REMINDER, self::TYPE_INTERVIEW_REMINDER => 'orange',
            self::TYPE_INTERVIEW_SCHEDULED => 'blue',
            self::TYPE_INTERVIEW_COMPLETED => 'purple',
            self::TYPE_RECRUITER_VIEW, self::TYPE_RECRUITER_CONTACT, 
            self::TYPE_RECRUITER_SHORTLIST => 'teal',
            self::TYPE_ACHIEVEMENT => 'yellow',
            self::TYPE_SYSTEM_ANNOUNCEMENT => 'indigo',
            default => 'gray',
        };
    }

    /**
     * Get time ago.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if notification is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get priority badge class.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-500',
            self::PRIORITY_NORMAL => 'bg-blue-500',
            self::PRIORITY_HIGH => 'bg-orange-500',
            self::PRIORITY_URGENT => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ACTIVITY_APPROVED => 'Activity Approved',
            self::TYPE_ACTIVITY_REJECTED => 'Activity Rejected',
            self::TYPE_ACTIVITY_NEEDS_REVISION => 'Revision Needed',
            self::TYPE_TIER_PROMOTION => 'Tier Promotion',
            self::TYPE_TIER_DEMOTION => 'Tier Demotion',
            self::TYPE_WEEKLY_REMINDER => 'Weekly Reminder',
            self::TYPE_SCORE_UPDATE => 'Score Update',
            self::TYPE_INTERVIEW_SCHEDULED => 'Interview Scheduled',
            self::TYPE_INTERVIEW_REMINDER => 'Interview Reminder',
            self::TYPE_INTERVIEW_COMPLETED => 'Interview Completed',
            self::TYPE_RECRUITER_VIEW => 'Profile Viewed',
            self::TYPE_RECRUITER_CONTACT => 'Contact Request',
            self::TYPE_RECRUITER_SHORTLIST => 'Shortlisted',
            self::TYPE_NEW_MESSAGE => 'New Message',
            self::TYPE_ACHIEVEMENT => 'Achievement Unlocked',
            self::TYPE_SYSTEM_ANNOUNCEMENT => 'Announcement',
            default => 'Notification',
        };
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Mark as read.
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to urgent notifications.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    /**
     * Scope to high priority.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Scope to non-expired notifications.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to recent notifications.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Order by most recent.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Send a notification to a user.
     */
    public static function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        array $options = []
    ): self {
        return static::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $body,
            'icon' => $options['icon'] ?? null,
            'color' => $options['color'] ?? null,
            'action_url' => $options['action_url'] ?? null,
            'action_text' => $options['action_label'] ?? $options['action_text'] ?? null,
            'data' => $options['data'] ?? null,
            'priority' => $options['priority'] ?? self::PRIORITY_NORMAL,
            'expires_at' => $options['expires_at'] ?? null,
        ]);
    }

    /**
     * Send activity approved notification.
     */
    public static function sendActivityApproved(User $fellow, Activity $activity): self
    {
        return static::send(
            $fellow,
            self::TYPE_ACTIVITY_APPROVED,
            'Activity Approved!',
            "Your {$activity->type->label()} \"{$activity->title}\" has been approved and added to your Career Capital.",
            [
                'action_url' => route('fellow.activities.show', $activity),
                'action_label' => 'View Activity',
                'data' => ['activity_id' => $activity->id],
            ]
        );
    }

    /**
     * Send activity rejected notification.
     */
    public static function sendActivityRejected(User $fellow, Activity $activity, string $reason): self
    {
        return static::send(
            $fellow,
            self::TYPE_ACTIVITY_REJECTED,
            'Activity Needs Attention',
            "Your {$activity->type->label()} \"{$activity->title}\" was not approved. Reason: {$reason}",
            [
                'action_url' => route('fellow.activities.edit', $activity),
                'action_label' => 'Edit Activity',
                'data' => ['activity_id' => $activity->id, 'reason' => $reason],
                'priority' => self::PRIORITY_HIGH,
            ]
        );
    }

    /**
     * Send tier promotion notification.
     */
    public static function sendTierPromotion(User $fellow, string $previousTier, string $newTier, float $score): self
    {
        return static::send(
            $fellow,
            self::TYPE_TIER_PROMOTION,
            '🎉 Tier Promotion!',
            "Congratulations! You've been promoted from {$previousTier} to {$newTier} with a score of {$score}%!",
            [
                'action_url' => route('dashboard'),
                'action_label' => 'View Dashboard',
                'data' => [
                    'previous_tier' => $previousTier,
                    'new_tier' => $newTier,
                    'score' => $score,
                ],
                'priority' => self::PRIORITY_HIGH,
            ]
        );
    }

    /**
     * Send weekly reminder notification.
     */
    public static function sendWeeklyReminder(User $fellow, array $incompletePillars): self
    {
        $pillarsText = implode(', ', array_map('ucfirst', $incompletePillars));
        
        return static::send(
            $fellow,
            self::TYPE_WEEKLY_REMINDER,
            '⏰ Weekly Progress Reminder',
            "You have incomplete pillars this week: {$pillarsText}. Complete them to keep your score from freezing!",
            [
                'action_url' => route('fellow.progress'),
                'action_label' => 'View Progress',
                'data' => ['incomplete_pillars' => $incompletePillars],
                'priority' => self::PRIORITY_HIGH,
                'expires_at' => now()->endOfWeek(),
            ]
        );
    }

    /**
     * Send recruiter view notification.
     */
    public static function sendRecruiterView(User $fellow, User $recruiter): self
    {
        return static::send(
            $fellow,
            self::TYPE_RECRUITER_VIEW,
            'Profile Viewed',
            "A recruiter from {$recruiter->company_name} viewed your profile.",
            [
                'data' => ['recruiter_id' => $recruiter->id],
            ]
        );
    }

    /**
     * Send recruiter shortlist notification.
     */
    public static function sendRecruiterShortlist(User $fellow, User $recruiter): self
    {
        return static::send(
            $fellow,
            self::TYPE_RECRUITER_SHORTLIST,
            '⭐ You\'ve Been Shortlisted!',
            "A recruiter from {$recruiter->company_name} has added you to their shortlist.",
            [
                'data' => ['recruiter_id' => $recruiter->id],
                'priority' => self::PRIORITY_HIGH,
            ]
        );
    }

    /**
     * Send system announcement.
     */
    public static function sendAnnouncement(
        string $title,
        string $body,
        ?string $actionUrl = null,
        ?string $actionLabel = null
    ): void {
        $users = User::all();
        
        foreach ($users as $user) {
            static::send(
                $user,
                self::TYPE_SYSTEM_ANNOUNCEMENT,
                $title,
                $body,
                [
                    'action_url' => $actionUrl,
                    'action_label' => $actionLabel,
                    'priority' => self::PRIORITY_HIGH,
                ]
            );
        }
    }

    /**
     * Get unread count for a user.
     */
    public static function getUnreadCount(User $user): int
    {
        return static::where('user_id', $user->id)
            ->unread()
            ->valid()
            ->count();
    }

    /**
     * Mark all as read for a user.
     */
    public static function markAllAsRead(User $user): int
    {
        return static::where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete old read notifications.
     */
    public static function deleteOld(int $days = 90): int
    {
        return static::read()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
