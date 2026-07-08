<?php

namespace App\Enums;

/**
 * Subscription Status Enum
 * 
 * States for recruiter subscriptions.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIAL = 'trial';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case PAST_DUE = 'past_due';
    case PAUSED = 'paused';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::TRIAL => 'Trial',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::PAST_DUE => 'Past Due',
            self::PAUSED => 'Paused',
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::TRIAL => 'blue',
            self::CANCELLED => 'gray',
            self::EXPIRED => 'red',
            self::PAST_DUE => 'orange',
            self::PAUSED => 'yellow',
        };
    }

    /**
     * Get badge class
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::ACTIVE => 'bg-green-500/20 text-green-400',
            self::TRIAL => 'bg-blue-500/20 text-blue-400',
            self::CANCELLED => 'bg-gray-500/20 text-gray-400',
            self::EXPIRED => 'bg-red-500/20 text-red-400',
            self::PAST_DUE => 'bg-orange-500/20 text-orange-400',
            self::PAUSED => 'bg-yellow-500/20 text-yellow-400',
        };
    }

    /**
     * Check if subscription allows access
     */
    public function allowsAccess(): bool
    {
        return in_array($this, [self::ACTIVE, self::TRIAL]);
    }

    /**
     * Check if subscription can be renewed
     */
    public function canRenew(): bool
    {
        return in_array($this, [self::EXPIRED, self::CANCELLED, self::PAST_DUE]);
    }

    /**
     * Check if subscription is billable
     */
    public function isBillable(): bool
    {
        return in_array($this, [self::ACTIVE, self::PAST_DUE]);
    }
}
