<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\RecruiterAction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\AdminSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Recruiter Service
 * 
 * Handles all recruiter-related business logic including:
 * - Subscription management (trials, renewals, upgrades)
 * - Talent discovery and shortlisting
 * - View/contact tracking with rate limits
 * - Recruiter analytics
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RecruiterService
{
    /**
     * Plan order, for upgrade checks.
     */
    protected const TIER_RANK = ['free' => 0, 'partner' => 1, 'premium' => 2];

    /**
     * Start a trial subscription (once per account).
     */
    public function startTrial(User $recruiter): Subscription
    {
        if ($recruiter->subscriptions()->whereNotNull('trial_ends_at')->exists()) {
            throw new \Exception('Trial already used for this account.');
        }

        if ($recruiter->activeSubscription()) {
            throw new \Exception('You already have an active plan.');
        }

        $trialDays = (int) AdminSetting::get('recruiter_trial_days', 14);

        $subscription = Subscription::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::FREE,
            'status' => SubscriptionStatus::TRIAL,
            'trial_ends_at' => now()->addDays($trialDays),
            'started_at' => now(),
            'expires_at' => now()->addDays($trialDays),
            'amount' => 0,
            'profile_view_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'intros'),
        ]);

        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_trial',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => 'Started trial subscription for ' . $trialDays . ' days',
            'new_values' => ['trial_days' => $trialDays],
        ]);

        Notification::create([
            'user_id' => $recruiter->id,
            'type' => 'subscription_started',
            'title' => 'Welcome to IKS Talent Marketplace!',
            'message' => "Your {$trialDays}-day trial has started. Explore our top talent!",
        ]);

        return $subscription;
    }

    /**
     * Choose a plan.
     *
     * The free plan starts at once. A paid plan becomes a pending request
     * carrying the recruiter's payment reference; it starts only when an
     * admin confirms the payment (approvePending).
     */
    public function subscribe(
        User $recruiter,
        SubscriptionTier $tier,
        int $months = 12,
        ?string $paymentReference = null,
        ?string $paymentMethod = null
    ): Subscription {
        if ($tier === SubscriptionTier::FREE) {
            return $this->activateFreePlan($recruiter);
        }

        if (blank($paymentReference)) {
            throw new \Exception('Enter the reference of your payment so we can confirm it.');
        }

        return $this->createPendingRequest($recruiter, $tier, [
            'amount' => (int) round($this->calculatePrice($tier, $months)),
            'billing_cycle' => $months >= 12 ? 'annual' : ($months >= 6 ? 'semi-annual' : 'monthly'),
            'requested_months' => $months,
            'payment_reference' => trim($paymentReference),
            'payment_method' => $paymentMethod,
        ], "Requested the {$tier->label()} plan for {$months} month(s)");
    }

    /**
     * Request an upgrade of the current paid plan for the rest of its period.
     * Like a new plan, it starts when an admin confirms the payment.
     */
    public function upgrade(
        User $recruiter,
        SubscriptionTier $newTier,
        ?string $paymentReference = null,
        ?string $paymentMethod = null
    ): Subscription {
        $currentSub = $recruiter->activeSubscription();

        if (!$currentSub || $currentSub->tier === SubscriptionTier::FREE || $currentSub->status === SubscriptionStatus::TRIAL) {
            throw new \Exception('Upgrades apply to a paid plan. Choose a plan below instead.');
        }

        if (self::TIER_RANK[$newTier->value] <= self::TIER_RANK[$currentSub->tier->value]) {
            throw new \Exception('Can only upgrade to a higher tier.');
        }

        if (blank($paymentReference)) {
            throw new \Exception('Enter the reference of your payment so we can confirm it.');
        }

        $daysRemaining = $currentSub->expires_at ? max(0, (int) now()->diffInDays($currentSub->expires_at, false)) : 0;
        $proratedPrice = $this->calculateProratedUpgrade($currentSub->tier, $newTier, $daysRemaining);

        return $this->createPendingRequest($recruiter, $newTier, [
            'amount' => (int) round($proratedPrice),
            'billing_cycle' => $currentSub->billing_cycle,
            'requested_months' => null,
            'expires_at' => $currentSub->expires_at,
            'payment_reference' => trim($paymentReference),
            'payment_method' => $paymentMethod,
            'notes' => "Upgrade from {$currentSub->tier->label()} for the remaining {$daysRemaining} day(s)",
        ], "Requested an upgrade from {$currentSub->tier->label()} to {$newTier->label()}");
    }

    /**
     * Confirm the payment of a pending request and start the plan.
     * The plan it replaces is closed.
     */
    public function approvePending(Subscription $subscription, User $admin): Subscription
    {
        return DB::transaction(function () use ($subscription, $admin) {
            $subscription = Subscription::whereKey($subscription->getKey())->lockForUpdate()->firstOrFail();

            if ($subscription->status !== SubscriptionStatus::PENDING) {
                throw new \Exception('This request has already been handled.');
            }

            $recruiter = $subscription->recruiter;

            $recruiter->subscriptions()
                ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
                ->update([
                    'status' => SubscriptionStatus::CANCELLED,
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Replaced by the ' . $subscription->tier->label() . ' plan',
                ]);

            $expiresAt = $subscription->requested_months
                ? now()->addMonths($subscription->requested_months)
                : ($subscription->expires_at && $subscription->expires_at->isFuture() ? $subscription->expires_at : now()->addMonth());

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'started_at' => now(),
                'current_period_start' => now(),
                'current_period_end' => $expiresAt,
                'expires_at' => $expiresAt,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            AuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'subscription_payment_confirmed',
                'auditable_type' => Subscription::class,
                'auditable_id' => $subscription->id,
                'justification' => "Confirmed payment {$subscription->payment_reference} for the {$subscription->tier->label()} plan of {$recruiter->name}",
                'new_values' => [
                    'tier' => $subscription->tier->value,
                    'amount' => $subscription->amount,
                    'expires_at' => $expiresAt->toDateString(),
                ],
            ]);

            Notification::create([
                'user_id' => $recruiter->id,
                'type' => 'subscription_activated',
                'title' => 'Subscription activated',
                'message' => "Payment confirmed. Your {$subscription->tier->label()} plan is active until {$expiresAt->format('M j, Y')}.",
                'action_url' => route('recruiter.marketplace.index'),
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Turn down a pending request (payment not found, wrong amount, ...).
     */
    public function rejectPending(Subscription $subscription, User $admin, string $reason): Subscription
    {
        if ($subscription->status !== SubscriptionStatus::PENDING) {
            throw new \Exception('This request has already been handled.');
        }

        $subscription->update([
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => now(),
            'rejection_reason' => $reason,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        AuditLog::create([
            'admin_id' => $admin->id,
            'action' => 'subscription_payment_rejected',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => "Rejected the {$subscription->tier->label()} plan request of {$subscription->recruiter?->name}: {$reason}",
        ]);

        Notification::create([
            'user_id' => $subscription->recruiter_id,
            'type' => 'subscription_rejected',
            'title' => 'Plan request not approved',
            'message' => "We could not confirm the payment for your {$subscription->tier->label()} plan: {$reason}",
            'action_url' => route('recruiter.subscription.index'),
        ]);

        return $subscription->fresh();
    }

    /**
     * Withdraw the recruiter's own pending request.
     */
    public function cancelPending(User $recruiter): void
    {
        $pending = $recruiter->pendingSubscription();

        if ($pending) {
            $pending->update([
                'status' => SubscriptionStatus::CANCELLED,
                'cancelled_at' => now(),
                'cancellation_reason' => 'Withdrawn by recruiter',
            ]);
        }
    }

    /**
     * Start the free plan, closing any current plan.
     */
    protected function activateFreePlan(User $recruiter): Subscription
    {
        $this->cancelActive($recruiter);

        $subscription = Subscription::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::FREE,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now(),
            'expires_at' => null,
            'amount' => 0,
            'profile_view_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'intros'),
        ]);

        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_create',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => 'Started the Free plan',
            'new_values' => ['tier' => SubscriptionTier::FREE->value],
        ]);

        return $subscription;
    }

    /**
     * Record a paid plan request awaiting payment confirmation. A newer
     * request replaces an older one. Admins are notified.
     */
    protected function createPendingRequest(User $recruiter, SubscriptionTier $tier, array $attributes, string $summary): Subscription
    {
        $this->cancelPending($recruiter);

        $subscription = Subscription::create(array_merge([
            'recruiter_id' => $recruiter->id,
            'tier' => $tier,
            'status' => SubscriptionStatus::PENDING,
            'started_at' => now(),
            'expires_at' => null,
            'profile_view_limit' => $this->getTierLimit($tier, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit($tier, 'intros'),
        ], $attributes));

        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_requested',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => "{$summary}. Payment reference: {$subscription->payment_reference}",
            'new_values' => [
                'tier' => $tier->value,
                'amount' => $subscription->amount,
                'months' => $subscription->requested_months,
            ],
        ]);

        Notification::create([
            'user_id' => $recruiter->id,
            'type' => 'subscription_requested',
            'title' => 'Plan request received',
            'message' => "We received your request for the {$tier->label()} plan. It starts as soon as we confirm your payment.",
            'action_url' => route('recruiter.subscription.index'),
        ]);

        $amount = number_format((int) $subscription->amount, 0, '.', ' ');
        User::role(UserRole::ADMIN->value)->get(['id'])->each(fn (User $admin) => Notification::create([
            'user_id' => $admin->id,
            'type' => 'subscription_payment_review',
            'title' => 'Recruiter payment to confirm',
            'message' => "{$recruiter->name} requested the {$tier->label()} plan ({$amount} XAF, ref. {$subscription->payment_reference}).",
            'action_url' => route('admin.subscriptions.index'),
        ]));

        return $subscription;
    }

    /**
     * Cancel active subscription.
     */
    public function cancelActive(User $recruiter): void
    {
        $active = $recruiter->activeSubscription();
        
        if ($active) {
            $active->update([
                'status' => SubscriptionStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            AuditLog::create([
                'admin_id' => $recruiter->id,
                'action' => 'subscription_cancel',
                'auditable_type' => Subscription::class,
                'auditable_id' => $active->id,
                'justification' => 'Subscription cancelled by recruiter',
            ]);
        }
    }

    /**
     * Record a profile view.
     */
    public function recordProfileView(User $recruiter, User $fellow): RecruiterAction
    {
        // Check if subscription allows more views
        $subscription = $recruiter->activeSubscription();
        
        if (!$subscription) {
            throw new \Exception('Active subscription required to view profiles.');
        }

        // Check rate limit
        $viewsThisMonth = RecruiterAction::where('recruiter_id', $recruiter->id)
            ->where('action', 'profile_view')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $viewLimit = $subscription->profile_view_limit ?? $this->getTierLimit($subscription->tier, 'profile_views');
        if ($viewLimit > 0 && $viewsThisMonth >= $viewLimit) {
            throw new \Exception('Monthly profile view limit reached. Consider upgrading your plan.');
        }

        // Don't count duplicate views within 24 hours
        $recentView = RecruiterAction::where('recruiter_id', $recruiter->id)
            ->where('fellow_id', $fellow->id)
            ->where('action', 'profile_view')
            ->where('created_at', '>=', now()->subDay())
            ->first();

        if ($recentView) {
            $recentView->touch();
            return $recentView;
        }

        // Record new view
        $action = RecruiterAction::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action' => 'profile_view',
            'metadata' => [
                'subscription_tier' => $subscription->tier->value,
            ],
        ]);

        // Update subscription view count
        $subscription->increment('profiles_viewed_this_month');

        return $action;
    }

    /**
     * Record a contact request.
     */
    public function requestContact(User $recruiter, User $fellow, ?string $message = null): RecruiterAction
    {
        $subscription = $recruiter->activeSubscription();
        
        if (!$subscription) {
            throw new \Exception('Active subscription required to contact talent.');
        }

        // Check tier allows contact
        if ($subscription->tier === SubscriptionTier::FREE && !$subscription->is_trial) {
            throw new \Exception('Upgrade to Partner or Premium to contact talent directly.');
        }

        // Check rate limit
        $contactsThisMonth = RecruiterAction::where('recruiter_id', $recruiter->id)
            ->where('action', 'contact_request')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $introLimit = $subscription->intro_request_limit ?? $this->getTierLimit($subscription->tier, 'intros');
        if ($introLimit > 0 && $contactsThisMonth >= $introLimit) {
            throw new \Exception('Monthly contact limit reached. Consider upgrading your plan.');
        }

        // Record contact
        $action = RecruiterAction::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action' => 'contact_request',
            'metadata' => [
                'message' => $message,
                'subscription_tier' => $subscription->tier->value,
            ],
        ]);

        // Update subscription contact count
        $subscription->increment('intros_requested_this_month');

        // Notify fellow
        Notification::create([
            'user_id' => $fellow->id,
            'type' => 'recruiter_contact',
            'title' => 'A Recruiter Wants to Connect!',
            'message' => "{$recruiter->company_name} is interested in your profile.",
        ]);

        // Log action
        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'recruiter_contact',
            'auditable_type' => User::class,
            'auditable_id' => $fellow->id,
            'justification' => "Contact request sent to {$fellow->name}",
            'new_values' => ['message' => $message],
        ]);

        return $action;
    }

    /**
     * Add fellow to shortlist.
     */
    public function addToShortlist(User $recruiter, User $fellow, ?string $notes = null): RecruiterAction
    {
        // Check if already shortlisted
        $existing = RecruiterAction::where('recruiter_id', $recruiter->id)
            ->where('fellow_id', $fellow->id)
            ->where('action', 'shortlist')
            ->first();

        if ($existing) {
            // Update notes if provided
            if ($notes !== null) {
                $existing->update(['metadata' => array_merge($existing->metadata ?? [], ['notes' => $notes])]);
            }
            return $existing;
        }

        return RecruiterAction::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action' => 'shortlist',
            'metadata' => [
                'notes' => $notes,
                'shortlisted_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Remove from shortlist.
     */
    public function removeFromShortlist(User $recruiter, User $fellow): bool
    {
        return RecruiterAction::where('recruiter_id', $recruiter->id)
            ->where('fellow_id', $fellow->id)
            ->where('action', 'shortlist')
            ->delete() > 0;
    }

    /**
     * Get recruiter's shortlist.
     */
    public function getShortlist(User $recruiter): Collection
    {
        return RecruiterAction::with('fellow.fellowTracks.track')
            ->where('recruiter_id', $recruiter->id)
            ->where('action', 'shortlist')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get tier-specific limits.
     */
    protected function getTierLimit(SubscriptionTier $tier, string $limitType): int
    {
        $limits = [
            SubscriptionTier::FREE->value => [
                'profile_views' => (int) AdminSetting::get('free_profile_views', 20),
                'intros' => 0,
                'downloads' => (int) AdminSetting::get('free_downloads', 5),
            ],
            SubscriptionTier::PARTNER->value => [
                'profile_views' => (int) AdminSetting::get('partner_profile_views', -1),
                'intros' => (int) AdminSetting::get('partner_intros', 5),
                'downloads' => (int) AdminSetting::get('partner_downloads', -1),
            ],
            SubscriptionTier::PREMIUM->value => [
                'profile_views' => (int) AdminSetting::get('premium_profile_views', -1),
                'intros' => (int) AdminSetting::get('premium_intros', -1),
                'downloads' => (int) AdminSetting::get('premium_downloads', -1),
            ],
        ];

        return $limits[$tier->value][$limitType] ?? 0;
    }

    /**
     * Calculate subscription price.
     */
    protected function calculatePrice(SubscriptionTier $tier, int $months): float
    {
        // Annual prices as set on the admin settings page; shorter periods are pro rata.
        $annual = match ($tier) {
            SubscriptionTier::FREE => 0.0,
            SubscriptionTier::PARTNER => (float) AdminSetting::get('partner_price', 300000),
            SubscriptionTier::PREMIUM => (float) AdminSetting::get('premium_price', 1200000),
        };

        return round($annual * $months / 12 / 100) * 100;
    }

    /**
     * Calculate prorated upgrade price.
     */
    protected function calculateProratedUpgrade(
        SubscriptionTier $currentTier,
        SubscriptionTier $newTier,
        int $daysRemaining
    ): float {
        $currentDaily = $this->calculatePrice($currentTier, 1) / 30;
        $newDaily = $this->calculatePrice($newTier, 1) / 30;
        $priceDifference = $newDaily - $currentDaily;

        return max(0, $priceDifference * $daysRemaining);
    }

    /**
     * Get recruiter dashboard statistics.
     */
    public function getDashboardStats(User $recruiter): array
    {
        $subscription = $recruiter->activeSubscription();
        $thisMonth = now()->startOfMonth();

        return [
            'subscription' => $subscription ? [
                'tier' => $subscription->tier->value,
                'status' => $subscription->status->value,
                'expires_at' => $subscription->expires_at,
                'is_trial' => $subscription->is_trial,
                'trial_ends_at' => $subscription->trial_ends_at,
                'days_remaining' => $subscription->is_trial 
                    ? now()->diffInDays($subscription->trial_ends_at)
                    : now()->diffInDays($subscription->expires_at),
            ] : null,
            'usage' => [
                'profile_views' => [
                    'used' => $subscription?->profiles_viewed_this_month ?? 0,
                    'limit' => $subscription?->profile_view_limit ?? 0,
                    'remaining' => $subscription 
                        ? $this->remaining($subscription->profile_view_limit, (int) $subscription->profiles_viewed_this_month)
                        : 0,
                ],
                'intros' => [
                    'used' => $subscription?->intros_requested_this_month ?? 0,
                    'limit' => $subscription?->intro_request_limit ?? 0,
                    'remaining' => $subscription 
                        ? $this->remaining($subscription->intro_request_limit, (int) $subscription->intros_requested_this_month)
                        : 0,
                ],
            ],
            'activity' => [
                'total_views' => RecruiterAction::where('recruiter_id', $recruiter->id)
                    ->where('action', 'profile_view')
                    ->count(),
                'views_this_month' => RecruiterAction::where('recruiter_id', $recruiter->id)
                    ->where('action', 'profile_view')
                    ->where('created_at', '>=', $thisMonth)
                    ->count(),
                'contacts_sent' => RecruiterAction::where('recruiter_id', $recruiter->id)
                    ->where('action', 'contact_request')
                    ->count(),
                'shortlist_count' => RecruiterAction::where('recruiter_id', $recruiter->id)
                    ->where('action', 'shortlist')
                    ->count(),
            ],
        ];
    }

    /**
     * What is left of a monthly limit; null means unlimited (limit -1 or not set).
     */
    protected function remaining(?int $limit, int $used): ?int
    {
        if ($limit === null || $limit < 0) {
            return null;
        }

        return max(0, $limit - $used);
    }

    /**
     * Check if recruiter can perform an action based on subscription.
     */
    public function canPerformAction(User $recruiter, string $action): array
    {
        $subscription = $recruiter->activeSubscription();

        if (!$subscription) {
            return [
                'allowed' => false,
                'reason' => 'No active subscription. Start a free trial to access the talent marketplace.',
            ];
        }

        if ($subscription->status === SubscriptionStatus::EXPIRED) {
            return [
                'allowed' => false,
                'reason' => 'Your subscription has expired. Please renew to continue.',
            ];
        }

        $stats = $this->getDashboardStats($recruiter);

        if ($action === 'view_profile') {
            if ($stats['usage']['profile_views']['remaining'] !== null && $stats['usage']['profile_views']['remaining'] <= 0) {
                return [
                    'allowed' => false,
                    'reason' => 'You have reached your monthly profile view limit. Upgrade your plan for more views.',
                ];
            }
            return ['allowed' => true, 'remaining' => $stats['usage']['profile_views']['remaining']];
        }

        if ($action === 'contact') {
            if ($subscription->tier === SubscriptionTier::FREE && !$subscription->is_trial) {
                return [
                    'allowed' => false,
                    'reason' => 'Direct contact is available on Partner and Premium plans.',
                ];
            }
            if ($stats['usage']['intros']['remaining'] !== null && $stats['usage']['intros']['remaining'] <= 0) {
                return [
                    'allowed' => false,
                    'reason' => 'You have reached your monthly contact limit. Upgrade for more contacts.',
                ];
            }
            return ['allowed' => true, 'remaining' => $stats['usage']['intros']['remaining']];
        }

        return ['allowed' => true];
    }
}
