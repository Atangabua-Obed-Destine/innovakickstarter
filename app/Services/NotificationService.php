<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Notification Service
 * 
 * Handles all notification-related operations including:
 * - Sending notifications
 * - Marking as read/unread
 * - Batch operations
 * - Notification preferences
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class NotificationService
{
    /**
     * Send a notification to a user.
     */
    public function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
        ]);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToMany(
        Collection $users,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): int {
        $count = 0;

        foreach ($users as $user) {
            $this->send($user, $type, $title, $message, $data, $actionUrl, $actionText);
            $count++;
        }

        return $count;
    }

    /**
     * Send notification to all users with a specific role.
     */
    public function sendToRole(
        string $role,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): int {
        $users = User::role($role)->get();
        return $this->sendToMany($users, $type, $title, $message, $data, $actionUrl, $actionText);
    }

    /**
     * Send a system announcement.
     */
    public function announce(string $title, string $message, ?string $actionUrl = null): int
    {
        $users = User::where('is_active', true)->get();
        
        return $this->sendToMany(
            $users,
            'system_announcement',
            $title,
            $message,
            ['is_announcement' => true],
            $actionUrl
        );
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): Notification
    {
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return $notification;
    }

    /**
     * Mark a notification as unread.
     */
    public function markAsUnread(Notification $notification): Notification
    {
        $notification->update(['read_at' => null]);
        return $notification;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Mark multiple notifications as read.
     */
    public function markManyAsRead(array $notificationIds, User $user): int
    {
        return Notification::whereIn('id', $notificationIds)
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Delete multiple notifications.
     */
    public function deleteMany(array $notificationIds, User $user): int
    {
        return Notification::whereIn('id', $notificationIds)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Delete all read notifications for a user.
     */
    public function deleteAllRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Delete old notifications.
     */
    public function deleteOld(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(User $user, int $limit = 10): Collection
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user with pagination.
     */
    public function getForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get notifications by type.
     */
    public function getByType(User $user, string $type, int $limit = 20): Collection
    {
        return Notification::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Check if user has unread notifications.
     */
    public function hasUnread(User $user): bool
    {
        return $this->getUnreadCount($user) > 0;
    }

    /**
     * Send activity-related notifications.
     */
    public function notifyActivitySubmitted(User $fellow, $activity): void
    {
        // Notify the fellow
        $this->send(
            $fellow,
            'activity_submitted',
            'Activity Submitted',
            "Your {$activity->type->label()} activity has been submitted for review.",
            ['activity_id' => $activity->id],
            route('activities.show', $activity),
            'View Activity'
        );

        // Notify admins
        $this->sendToRole(
            'admin',
            'activity_pending_review',
            'New Activity Pending Review',
            "{$fellow->name} submitted a {$activity->type->label()} activity.",
            [
                'activity_id' => $activity->id,
                'fellow_id' => $fellow->id,
            ],
            route('admin.activities.review', $activity),
            'Review Now'
        );
    }

    /**
     * Send activity approved notification.
     */
    public function notifyActivityApproved(User $fellow, $activity, int $pointsEarned): void
    {
        $this->send(
            $fellow,
            'activity_approved',
            'Activity Approved! 🎉',
            "Your activity has been approved! You earned {$pointsEarned} points.",
            [
                'activity_id' => $activity->id,
                'points_earned' => $pointsEarned,
            ],
            route('activities.show', $activity),
            'View Activity'
        );
    }

    /**
     * Send activity rejected notification.
     */
    public function notifyActivityRejected(User $fellow, $activity, ?string $feedback): void
    {
        $this->send(
            $fellow,
            'activity_rejected',
            'Activity Needs Attention',
            $feedback ?? 'Your activity was not approved. Please review and resubmit.',
            ['activity_id' => $activity->id],
            route('activities.show', $activity),
            'View Feedback'
        );
    }

    /**
     * Send tier change notification.
     */
    public function notifyTierChange(User $fellow, string $oldTier, string $newTier, bool $isPromotion): void
    {
        if ($isPromotion) {
            $this->send(
                $fellow,
                'tier_promotion',
                'Congratulations! You Leveled Up! 🚀',
                "You've been promoted from {$oldTier} to {$newTier}! Keep up the great work!",
                [
                    'old_tier' => $oldTier,
                    'new_tier' => $newTier,
                ],
                route('dashboard'),
                'View Progress'
            );
        } else {
            $this->send(
                $fellow,
                'tier_demotion',
                'Tier Update Notice',
                "Your tier has changed from {$oldTier} to {$newTier}. Stay active to climb back up!",
                [
                    'old_tier' => $oldTier,
                    'new_tier' => $newTier,
                ],
                route('dashboard'),
                'View Progress'
            );
        }
    }

    /**
     * Send interview reminder.
     */
    public function notifyInterviewReminder(User $fellow, $interview, int $minutesBefore): void
    {
        $timeLabel = $minutesBefore >= 60 
            ? ($minutesBefore / 60) . ' hour(s)' 
            : $minutesBefore . ' minutes';

        $this->send(
            $fellow,
            'interview_reminder',
            'Interview Reminder',
            "Your {$interview->type->label()} interview starts in {$timeLabel}.",
            [
                'interview_id' => $interview->id,
                'scheduled_at' => $interview->scheduled_at->toISOString(),
            ],
            $interview->meeting_link ?? route('interviews.show', $interview),
            'Join Interview'
        );
    }

    /**
     * Send weekly progress reminder.
     */
    public function notifyWeeklyProgressReminder(User $fellow, array $missingPillars): void
    {
        $pillarsText = implode(', ', $missingPillars);

        $this->send(
            $fellow,
            'weekly_progress_reminder',
            'Complete Your Weekly 4 Pillars',
            "Don't forget to submit your weekly progress! Missing: {$pillarsText}",
            ['missing_pillars' => $missingPillars],
            route('weekly-progress.submit'),
            'Submit Now'
        );
    }

    /**
     * Send recruiter interest notification to fellow.
     */
    public function notifyRecruiterInterest(User $fellow, User $recruiter, string $action): void
    {
        $messages = [
            'profile_view' => "{$recruiter->company_name} viewed your profile.",
            'shortlist' => "{$recruiter->company_name} added you to their shortlist! 🌟",
            'contact_request' => "{$recruiter->company_name} wants to connect with you!",
        ];

        $titles = [
            'profile_view' => 'Profile Viewed',
            'shortlist' => 'You\'ve Been Shortlisted!',
            'contact_request' => 'New Connection Request',
        ];

        $this->send(
            $fellow,
            "recruiter_{$action}",
            $titles[$action] ?? 'Recruiter Activity',
            $messages[$action] ?? "A recruiter is interested in your profile.",
            [
                'recruiter_id' => $recruiter->id,
                'company_name' => $recruiter->company_name,
                'action' => $action,
            ],
            route('profile.show'),
            'View Profile'
        );
    }

    /**
     * Get notification statistics for admin dashboard.
     */
    public function getStatistics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();

        return [
            'total_notifications' => Notification::count(),
            'sent_today' => Notification::where('created_at', '>=', $today)->count(),
            'sent_this_week' => Notification::where('created_at', '>=', $thisWeek)->count(),
            'unread_total' => Notification::whereNull('read_at')->count(),
            'read_rate' => $this->calculateReadRate(),
            'by_type' => Notification::where('created_at', '>=', $thisWeek)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Calculate overall read rate.
     */
    protected function calculateReadRate(): float
    {
        $total = Notification::count();
        
        if ($total === 0) {
            return 0;
        }

        $read = Notification::whereNotNull('read_at')->count();
        
        return round(($read / $total) * 100, 2);
    }
}
