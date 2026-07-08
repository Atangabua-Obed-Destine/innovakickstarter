<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

/**
 * Audit Service
 * 
 * Handles comprehensive audit logging for the platform.
 * Tracks all significant actions for compliance and debugging.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AuditService
{
    /**
     * Log an action.
     */
    public function log(
        string $action,
        ?User $user = null,
        ?Model $auditable = null,
        ?string $description = null,
        array $metadata = []
    ): AuditLog {
        // Determine whether user is admin or fellow
        $adminId = null;
        $fellowId = null;
        if ($user) {
            if ($user->hasRole('admin')) {
                $adminId = $user->id;
            }
            if ($user->hasRole('fellow')) {
                $fellowId = $user->id;
            }
            // Recruiters/mentors go into admin_id as the "actor"
            if (!$adminId && !$fellowId) {
                $adminId = $user->id;
            }
        }

        return AuditLog::create([
            'admin_id' => $adminId,
            'fellow_id' => $fellowId ?? ($metadata['fellow_id'] ?? null),
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'justification' => $description ?? ucfirst(str_replace('_', ' ', $action)),
            'old_values' => $metadata['old_values'] ?? null,
            'new_values' => $metadata['new_values'] ?? array_diff_key($metadata, array_flip(['old_values', 'new_values', 'fellow_id'])) ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log a model creation.
     */
    public function logCreated(Model $model, ?User $user = null, ?string $description = null): AuditLog
    {
        return $this->log(
            'created',
            $user ?? auth()->user(),
            $model,
            $description ?? class_basename($model) . ' created',
            ['new_values' => $model->toArray()]
        );
    }

    /**
     * Log a model update.
     */
    public function logUpdated(Model $model, array $oldValues, ?User $user = null, ?string $description = null): AuditLog
    {
        return $this->log(
            'updated',
            $user ?? auth()->user(),
            $model,
            $description ?? class_basename($model) . ' updated',
            [
                'old_values' => $oldValues,
                'new_values' => $model->getChanges(),
            ]
        );
    }

    /**
     * Log a model deletion.
     */
    public function logDeleted(Model $model, ?User $user = null, ?string $description = null): AuditLog
    {
        return $this->log(
            'deleted',
            $user ?? auth()->user(),
            $model,
            $description ?? class_basename($model) . ' deleted',
            ['old_values' => $model->toArray()]
        );
    }

    /**
     * Log an authentication event.
     */
    public function logAuth(string $action, User $user, array $metadata = []): AuditLog
    {
        return $this->log(
            $action,
            $user,
            $user,
            "User {$action}",
            $metadata
        );
    }

    /**
     * Log a login.
     */
    public function logLogin(User $user): AuditLog
    {
        return $this->logAuth('login', $user);
    }

    /**
     * Log a logout.
     */
    public function logLogout(User $user): AuditLog
    {
        return $this->logAuth('logout', $user);
    }

    /**
     * Log a failed login attempt.
     */
    public function logFailedLogin(string $email): AuditLog
    {
        return $this->log(
            'login_failed',
            null,
            null,
            "Failed login attempt for: {$email}",
            ['email' => $email]
        );
    }

    /**
     * Log a password reset.
     */
    public function logPasswordReset(User $user): AuditLog
    {
        return $this->logAuth('password_reset', $user);
    }

    /**
     * Log an activity approval/rejection.
     */
    public function logActivityReview(
        Model $activity,
        User $admin,
        string $decision,
        ?string $feedback = null
    ): AuditLog {
        return $this->log(
            "activity_{$decision}",
            $admin,
            $activity,
            "Activity {$decision}: " . ($feedback ?? 'No feedback provided'),
            [
                'decision' => $decision,
                'feedback' => $feedback,
                'fellow_id' => $activity->fellow_id,
            ]
        );
    }

    /**
     * Log a tier change.
     */
    public function logTierChange(
        User $fellow,
        string $previousTier,
        string $newTier,
        float $score
    ): AuditLog {
        return $this->log(
            'tier_change',
            $fellow,
            $fellow,
            "Tier changed from {$previousTier} to {$newTier}",
            [
                'previous_tier' => $previousTier,
                'new_tier' => $newTier,
                'score' => $score,
            ]
        );
    }

    /**
     * Log a recruiter action.
     */
    public function logRecruiterAction(
        User $recruiter,
        User $fellow,
        string $action,
        array $metadata = []
    ): AuditLog {
        return $this->log(
            "recruiter_{$action}",
            $recruiter,
            $fellow,
            "Recruiter {$action}: {$fellow->name}",
            array_merge($metadata, [
                'fellow_id' => $fellow->id,
                'recruiter_id' => $recruiter->id,
            ])
        );
    }

    /**
     * Log a subscription event.
     */
    public function logSubscription(
        Model $subscription,
        User $recruiter,
        string $action,
        array $metadata = []
    ): AuditLog {
        return $this->log(
            "subscription_{$action}",
            $recruiter,
            $subscription,
            "Subscription {$action}",
            array_merge($metadata, [
                'tier' => $subscription->tier->value ?? null,
            ])
        );
    }

    /**
     * Log an admin setting change.
     */
    public function logSettingChange(
        string $key,
        mixed $oldValue,
        mixed $newValue,
        User $admin
    ): AuditLog {
        return $this->log(
            'setting_changed',
            $admin,
            null,
            "Setting '{$key}' changed",
            [
                'key' => $key,
                'old_values' => ['value' => $oldValue],
                'new_values' => ['value' => $newValue],
            ]
        );
    }

    /**
     * Get recent audit logs.
     */
    public function getRecent(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('admin')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific model.
     */
    public function getForModel(Model $model, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->getKey())
            ->with('admin')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     */
    public function getForUser(User $user, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('fellow_id', $user->id)
            ->orWhere('admin_id', $user->id)
            ->with('auditable')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs by action type.
     */
    public function getByAction(string $action, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::where('action', $action)
            ->with(['admin', 'auditable'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Search audit logs.
     */
    public function search(array $filters): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = AuditLog::with(['admin', 'auditable']);

        if (!empty($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', 'like', "%{$filters['action']}%");
        }

        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('justification', 'like', "%{$filters['search']}%");
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Clean old audit logs (older than X days).
     */
    public function cleanOld(int $days = 365): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Get statistics for admin dashboard.
     */
    public function getStatistics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();

        return [
            'total_logs' => AuditLog::count(),
            'logs_today' => AuditLog::where('created_at', '>=', $today)->count(),
            'logs_this_week' => AuditLog::where('created_at', '>=', $thisWeek)->count(),
            'logins_today' => AuditLog::where('action', 'login')
                ->where('created_at', '>=', $today)
                ->count(),
            'failed_logins_today' => AuditLog::where('action', 'login_failed')
                ->where('created_at', '>=', $today)
                ->count(),
            'activity_reviews_today' => AuditLog::where('action', 'like', 'activity_%')
                ->where('created_at', '>=', $today)
                ->count(),
            'top_actions' => AuditLog::where('created_at', '>=', $thisWeek)
                ->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'action')
                ->toArray(),
        ];
    }
}
