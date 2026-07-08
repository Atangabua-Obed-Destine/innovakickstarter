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
     * Start a trial subscription.
     */
    public function startTrial(User $recruiter): Subscription
    {
        // Check if recruiter already had a trial
        if ($recruiter->subscriptions()->where('is_trial', true)->exists()) {
            throw new \Exception('Trial already used for this account.');
        }

        $trialDays = (int) AdminSetting::get('recruiter_trial_days', 14);

        $subscription = Subscription::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::FREE,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_ends_at' => now()->addDays($trialDays),
            'started_at' => now(),
            'expires_at' => now()->addDays($trialDays),
            'amount' => 0,
            'profile_view_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit(SubscriptionTier::FREE, 'intros'),
        ]);

        // Log action
        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_trial',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => 'Started trial subscription for ' . $trialDays . ' days',
            'new_values' => ['trial_days' => $trialDays],
        ]);

        // Notify fellow
        Notification::create([
            'user_id' => $recruiter->id,
            'type' => 'subscription_started',
            'title' => 'Welcome to IKS Talent Marketplace!',
            'message' => "Your {$trialDays}-day trial has started. Explore our top talent!",
        ]);

        return $subscription;
    }

    /**
     * Create or renew a subscription.
     */
    public function subscribe(
        User $recruiter,
        SubscriptionTier $tier,
        int $months = 1,
        ?string $paymentReference = null
    ): Subscription {
        // Cancel any active subscription
        $this->cancelActive($recruiter);

        $price = $this->calculatePrice($tier, $months);

        $subscription = Subscription::create([
            'recruiter_id' => $recruiter->id,
            'tier' => $tier,
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'started_at' => now(),
            'expires_at' => now()->addMonths($months),
            'amount' => $price,
            'billing_cycle' => $months >= 12 ? 'annual' : ($months >= 6 ? 'semi-annual' : 'monthly'),
            'profile_view_limit' => $this->getTierLimit($tier, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit($tier, 'intros'),
            'notes' => $paymentReference ? "Payment ref: {$paymentReference}" : null,
        ]);

        // Log action
        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_create',
            'auditable_type' => Subscription::class,
            'auditable_id' => $subscription->id,
            'justification' => "Subscribed to {$tier->value} plan for {$months} month(s)",
            'new_values' => [
                'tier' => $tier->value,
                'months' => $months,
                'price' => $price,
            ],
        ]);

        // Notify
        Notification::create([
            'user_id' => $recruiter->id,
            'type' => 'subscription_activated',
            'title' => 'Subscription Activated!',
            'message' => "Your {$tier->value} subscription is now active. Happy recruiting!",
        ]);

        return $subscription;
    }

    /**
     * Upgrade subscription tier.
     */
    public function upgrade(User $recruiter, SubscriptionTier $newTier): Subscription
    {
        $currentSub = $recruiter->activeSubscription();
        
        if (!$currentSub) {
            throw new \Exception('No active subscription to upgrade.');
        }

        if ($newTier->value <= $currentSub->tier->value) {
            throw new \Exception('Can only upgrade to a higher tier.');
        }

        $oldTier = $currentSub->tier;

        // Calculate prorated price
        $daysRemaining = now()->diffInDays($currentSub->expires_at);
        $proratedPrice = $this->calculateProratedUpgrade($oldTier, $newTier, $daysRemaining);

        // Update subscription
        $currentSub->update([
            'tier' => $newTier,
            'profile_view_limit' => $this->getTierLimit($newTier, 'profile_views'),
            'intro_request_limit' => $this->getTierLimit($newTier, 'intros'),
            'notes' => "Upgraded from {$oldTier->value} at " . now()->toISOString() . ". Prorated: {$proratedPrice} XAF",
        ]);

        // Log action
        AuditLog::create([
            'admin_id' => $recruiter->id,
            'action' => 'subscription_upgrade',
            'auditable_type' => Subscription::class,
            'auditable_id' => $currentSub->id,
            'justification' => "Upgraded from {$oldTier->value} to {$newTier->value}",
            'old_values' => ['tier' => $oldTier->value],
            'new_values' => ['tier' => $newTier->value],
        ]);

        // Notify
        Notification::create([
            'user_id' => $recruiter->id,
            'type' => 'subscription_upgraded',
            'title' => 'Subscription Upgraded!',
            'message' => "Welcome to {$newTier->value}! Enjoy enhanced features.",
        ]);

        return $currentSub->fresh();
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
        $monthlyPrices = [
            SubscriptionTier::FREE->value => 0.0,
            SubscriptionTier::PARTNER->value => (float) AdminSetting::get('partner_yearly_price', 300000) / 12, // XAF 300,000/year
            SubscriptionTier::PREMIUM->value => (float) AdminSetting::get('premium_yearly_price', 1200000) / 12, // XAF 1,200,000/year
        ];

        $basePrice = $monthlyPrices[$tier->value] * $months;

        // Apply discounts for longer subscriptions
        if ($months >= 12) {
            return $basePrice * 0.8; // 20% off annual
        } elseif ($months >= 6) {
            return $basePrice * 0.9; // 10% off semi-annual
        }

        return $basePrice;
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
                        ? max(0, ($subscription->profile_view_limit ?? 999999) - $subscription->profiles_viewed_this_month)
                        : 0,
                ],
                'intros' => [
                    'used' => $subscription?->intros_requested_this_month ?? 0,
                    'limit' => $subscription?->intro_request_limit ?? 0,
                    'remaining' => $subscription 
                        ? max(0, ($subscription->intro_request_limit ?? 999999) - $subscription->intros_requested_this_month)
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
            if ($stats['usage']['profile_views']['remaining'] <= 0) {
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
            if ($stats['usage']['intros']['remaining'] <= 0) {
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
