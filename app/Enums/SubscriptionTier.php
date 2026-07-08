<?php

namespace App\Enums;

/**
 * Subscription Tier Enum
 * 
 * Recruiter subscription tiers with pricing and limits.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum SubscriptionTier: string
{
    case FREE = 'free';
    case PARTNER = 'partner';
    case PREMIUM = 'premium';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::FREE => 'Free',
            self::PARTNER => 'Partner',
            self::PREMIUM => 'Premium',
        };
    }

    /**
     * Get tier description
     */
    public function description(): string
    {
        return match($this) {
            self::FREE => 'Basic access for exploring the talent pool',
            self::PARTNER => 'Full access for active hiring needs',
            self::PREMIUM => 'Enterprise features for high-volume recruiting',
        };
    }

    /**
     * Get default price in XAF
     */
    public function defaultPriceXAF(): int
    {
        return match($this) {
            self::FREE => 0,
            self::PARTNER => 300000, // XAF 300,000/year (~$500)
            self::PREMIUM => 1200000, // XAF 1,200,000/year (~$2,000)
        };
    }

    /**
     * Get default profile view limit per month
     */
    public function defaultProfileLimit(): int
    {
        return match($this) {
            self::FREE => 20,
            self::PARTNER => -1, // Unlimited
            self::PREMIUM => -1, // Unlimited
        };
    }

    /**
     * Get default intro request limit per month
     */
    public function defaultIntroLimit(): int
    {
        return match($this) {
            self::FREE => 0,
            self::PARTNER => 5,
            self::PREMIUM => -1, // Unlimited
        };
    }

    /**
     * Get default download limit per month
     */
    public function defaultDownloadLimit(): int
    {
        return match($this) {
            self::FREE => 5,
            self::PARTNER => -1, // Unlimited
            self::PREMIUM => -1, // Unlimited
        };
    }

    /**
     * Get tier features
     */
    public function features(): array
    {
        return match($this) {
            self::FREE => [
                'Browse 20 profiles per month',
                'View basic scores (tier only)',
                'Access public portfolios',
                'Download 5 resumes per month',
                'Monthly top graduates newsletter',
            ],
            self::PARTNER => [
                'Unlimited profile views',
                'Advanced search filters',
                'Request warm introductions',
                'Quarterly demo days',
                'Early access to Elite graduates',
                'Unlimited resume downloads',
                'Candidate pipeline management',
                'Analytics dashboard',
                '1 free job posting',
            ],
            self::PREMIUM => [
                'Everything in Partner, plus:',
                'Custom talent assessments',
                'Headhunter mode (curated candidates)',
                'White-label branded reports',
                'API access for ATS integration',
                'Exclusive talent pool access',
                'Co-branded hackathon sponsorship',
                'Priority support',
            ],
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::FREE => 'gray',
            self::PARTNER => 'blue',
            self::PREMIUM => 'amber',
        };
    }

    /**
     * Get badge gradient class
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::FREE => 'bg-gray-500/20 text-gray-400',
            self::PARTNER => 'bg-blue-500/20 text-blue-400',
            self::PREMIUM => 'bg-gradient-to-r from-amber-400 to-amber-600 text-white',
        };
    }

    /**
     * Check if tier has API access
     */
    public function hasApiAccess(): bool
    {
        return $this === self::PREMIUM;
    }

    /**
     * Check if tier can request introductions
     */
    public function canRequestIntros(): bool
    {
        return in_array($this, [self::PARTNER, self::PREMIUM]);
    }

    /**
     * Check if tier has unlimited views
     */
    public function hasUnlimitedViews(): bool
    {
        return in_array($this, [self::PARTNER, self::PREMIUM]);
    }

    /**
     * Get monthly credits for this tier
     * Credits are used for profile views, downloads, and intro requests
     */
    public function monthlyCredits(): int
    {
        return match($this) {
            self::FREE => 25, // 20 profile views + 5 downloads
            self::PARTNER => 500, // High limit for partner tier
            self::PREMIUM => 9999, // Essentially unlimited
        };
    }
}
