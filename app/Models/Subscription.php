<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription Model
 * 
 * Manages recruiter subscription tiers and billing.
 * 
 * Tiers:
 * - FREE: Limited profile views, basic search
 * - PARTNER: More views/intros, advanced filters, priority support
 * - PREMIUM: Unlimited access, analytics, API, dedicated account manager
 * 
 * @property string $id UUID
 * @property string $recruiter_id
 * @property SubscriptionTier $tier
 * @property SubscriptionStatus $status
 * @property float $amount
 * @property string $currency
 * @property Carbon $started_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $trial_ends_at
 * @property int $profiles_viewed_this_month
 * @property int $intros_requested_this_month
 * @property int $downloads_this_month
 * @property int $profile_view_limit
 * @property int $intro_request_limit
 * @property int $download_limit
 * 
 * @author IKS Engineering Team
 * @version 2.0
 */
class Subscription extends Model
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
        'recruiter_id',
        'tier',
        'status',
        // Dates matching migration columns
        'started_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'expires_at',
        // Billing matching migration columns
        'amount',
        'currency',
        'billing_cycle',
        'payment_method',
        'stripe_subscription_id',
        'stripe_customer_id',
        'paystack_subscription_id',
        // Usage tracking matching migration columns
        'profiles_viewed_this_month',
        'intros_requested_this_month',
        'downloads_this_month',
        'usage_reset_date',
        // Limit overrides
        'profile_view_limit',
        'intro_request_limit',
        'download_limit',
        // Features
        'has_api_access',
        'has_priority_support',
        'has_custom_branding',
        // Notes
        'notes',
        'cancellation_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'tier' => SubscriptionTier::class,
            'status' => SubscriptionStatus::class,
            'started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
            'expires_at' => 'datetime',
            'usage_reset_date' => 'date',
            'amount' => 'integer',
            'profiles_viewed_this_month' => 'integer',
            'intros_requested_this_month' => 'integer',
            'downloads_this_month' => 'integer',
            'profile_view_limit' => 'integer',
            'intro_request_limit' => 'integer',
            'download_limit' => 'integer',
            'has_api_access' => 'boolean',
            'has_priority_support' => 'boolean',
            'has_custom_branding' => 'boolean',
        ];
    }

    /**
     * The attributes that should have default values.
     */
    protected $attributes = [
        'profiles_viewed_this_month' => 0,
        'intros_requested_this_month' => 0,
        'downloads_this_month' => 0,
        'currency' => 'XAF',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Subscription $subscription) {
            // Set default started_at
            $subscription->started_at = $subscription->started_at ?? now();

            // Set amount based on tier
            if ($subscription->tier) {
                $subscription->amount = $subscription->amount ?? $subscription->tier->defaultPriceXAF();
                
                // Set default limits based on tier
                $subscription->profile_view_limit = $subscription->profile_view_limit 
                    ?? $subscription->tier->defaultProfileLimit();
                $subscription->intro_request_limit = $subscription->intro_request_limit 
                    ?? $subscription->tier->defaultIntroLimit();
                $subscription->download_limit = $subscription->download_limit 
                    ?? $subscription->tier->defaultDownloadLimit();
                $subscription->has_api_access = $subscription->has_api_access 
                    ?? $subscription->tier->hasApiAccess();
            }

            // Set default status
            $subscription->status = $subscription->status ?? SubscriptionStatus::ACTIVE;
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the recruiter who owns this subscription.
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Check if subscription is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE ||
               $this->status === SubscriptionStatus::TRIAL;
    }

    /**
     * Check if subscription is on trial.
     */
    public function getIsTrialAttribute(): bool
    {
        return $this->status === SubscriptionStatus::TRIAL &&
               $this->trial_ends_at?->isFuture();
    }

    /**
     * Check if subscription is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if subscription is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at && 
               $this->expires_at->isFuture() && 
               $this->expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Get days until expiration.
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        return $this->expires_at?->diffInDays(now());
    }

    /**
     * Check if has unlimited views/access (Premium or Partner tier).
     */
    public function getHasUnlimitedViewsAttribute(): bool
    {
        return $this->tier?->hasUnlimitedViews() ?? false;
    }

    /**
     * Get profile view usage percentage.
     */
    public function getViewUsagePercentageAttribute(): float
    {
        if ($this->has_unlimited_views || !$this->profile_view_limit || $this->profile_view_limit < 0) {
            return 0;
        }

        return min(100, ($this->profiles_viewed_this_month / $this->profile_view_limit) * 100);
    }

    /**
     * Check if profile views are running low (< 20% remaining).
     */
    public function getViewsLowAttribute(): bool
    {
        if ($this->has_unlimited_views || !$this->profile_view_limit || $this->profile_view_limit < 0) {
            return false;
        }

        $remaining = $this->profile_view_limit - $this->profiles_viewed_this_month;
        return $remaining <= ($this->profile_view_limit * 0.2);
    }

    /**
     * Get remaining profile views.
     */
    public function getRemainingProfileViewsAttribute(): int
    {
        if ($this->has_unlimited_views || !$this->profile_view_limit || $this->profile_view_limit < 0) {
            return 9999;
        }
        return max(0, $this->profile_view_limit - $this->profiles_viewed_this_month);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->amount ?? 0, 0, ',', ' ') . ' ' . ($this->currency ?? 'XAF');
    }

    /**
     * Get tier features.
     */
    public function getFeaturesAttribute(): array
    {
        return $this->tier->features();
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return $this->status->badgeClass();
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Use credits.
     * 
     * @return bool Whether credits were successfully used
     */
    /**
     * Record a profile view.
     *
     * @return bool Whether the view was allowed
     */
    public function recordProfileView(): bool
    {
        if ($this->has_unlimited_views) {
            $this->increment('profiles_viewed_this_month');
            return true;
        }

        if ($this->profile_view_limit && $this->profiles_viewed_this_month >= $this->profile_view_limit) {
            return false;
        }

        $this->increment('profiles_viewed_this_month');
        return true;
    }

    /**
     * Record an intro request.
     *
     * @return bool Whether the intro was allowed
     */
    public function recordIntroRequest(): bool
    {
        if ($this->intro_request_limit < 0) {
            $this->increment('intros_requested_this_month');
            return true; // Unlimited
        }

        if ($this->intro_request_limit && $this->intros_requested_this_month >= $this->intro_request_limit) {
            return false;
        }

        $this->increment('intros_requested_this_month');
        return true;
    }

    /**
     * Record a download.
     *
     * @return bool Whether the download was allowed
     */
    public function recordDownload(): bool
    {
        if ($this->download_limit < 0) {
            $this->increment('downloads_this_month');
            return true; // Unlimited
        }

        if ($this->download_limit && $this->downloads_this_month >= $this->download_limit) {
            return false;
        }

        $this->increment('downloads_this_month');
        return true;
    }

    /**
     * Reset monthly usage counters.
     */
    public function resetMonthlyUsage(): void
    {
        $this->update([
            'profiles_viewed_this_month' => 0,
            'intros_requested_this_month' => 0,
            'downloads_this_month' => 0,
            'usage_reset_date' => now()->addMonth()->startOfMonth(),
        ]);
    }

    /**
     * Upgrade tier.
     */
    public function upgradeTo(SubscriptionTier $newTier): bool
    {
        $this->update([
            'tier' => $newTier,
            'amount' => $newTier->defaultPriceXAF(),
            'profile_view_limit' => $newTier->defaultProfileLimit(),
            'intro_request_limit' => $newTier->defaultIntroLimit(),
            'download_limit' => $newTier->defaultDownloadLimit(),
            'has_api_access' => $newTier->hasApiAccess(),
        ]);

        return true;
    }

    /**
     * Downgrade tier.
     */
    public function downgradeTo(SubscriptionTier $newTier): bool
    {
        $this->update([
            'tier' => $newTier,
            'amount' => $newTier->defaultPriceXAF(),
            'profile_view_limit' => $newTier->defaultProfileLimit(),
            'intro_request_limit' => $newTier->defaultIntroLimit(),
            'download_limit' => $newTier->defaultDownloadLimit(),
            'has_api_access' => $newTier->hasApiAccess(),
        ]);

        return true;
    }

    /**
     * Cancel subscription.
     */
    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Reactivate subscription.
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Extend subscription.
     */
    public function extendBy(int $months = 1): void
    {
        $currentEnd = $this->expires_at ?? now();
        
        $this->update([
            'expires_at' => $currentEnd->addMonths($months),
        ]);
    }

    /**
     * Start trial.
     */
    public function startTrial(int $days = 14): void
    {
        $this->update([
            'status' => SubscriptionStatus::TRIAL,
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    /**
     * Check if can access a feature.
     */
    public function canAccess(string $feature): bool
    {
        $features = $this->tier->features();
        return in_array($feature, $features);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SubscriptionStatus::ACTIVE,
            SubscriptionStatus::TRIAL,
        ]);
    }

    /**
     * Scope to cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', SubscriptionStatus::CANCELLED);
    }

    /**
     * Scope to expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    /**
     * Scope to trial subscriptions.
     */
    public function scopeOnTrial($query)
    {
        return $query->where('status', SubscriptionStatus::TRIAL)
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope by tier.
     */
    public function scopeOfTier($query, SubscriptionTier $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope to subscriptions expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    /**
     * Scope to subscriptions needing usage reset.
     */
    public function scopeNeedingUsageReset($query)
    {
        return $query->whereNotNull('usage_reset_date')
            ->where('usage_reset_date', '<=', now());
    }

    /**
     * Scope to subscriptions with low profile views.
     */
    public function scopeLowViews($query)
    {
        return $query->where('tier', SubscriptionTier::FREE)
            ->whereRaw('profiles_viewed_this_month >= (profile_view_limit * 0.8)');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Create a free subscription for a recruiter.
     */
    public static function createFree(User $recruiter): self
    {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::FREE,
            'status' => SubscriptionStatus::ACTIVE,
        ]);
    }

    /**
     * Create a partner subscription.
     */
    public static function createPartner(User $recruiter, int $months = 12): self
    {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::PARTNER,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonths($months),
        ]);
    }

    /**
     * Create a premium subscription.
     */
    public static function createPremium(User $recruiter, int $months = 12): self
    {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'tier' => SubscriptionTier::PREMIUM,
            'status' => SubscriptionStatus::ACTIVE,
            'expires_at' => now()->addMonths($months),
        ]);
    }
}
