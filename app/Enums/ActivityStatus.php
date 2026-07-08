<?php

namespace App\Enums;

/**
 * Activity Status Enum
 * 
 * Workflow states for activity submissions.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum ActivityStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case NEEDS_REVISION = 'needs_revision';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::NEEDS_REVISION => 'Needs Revision',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Waiting for admin review',
            self::APPROVED => 'Activity approved and points awarded',
            self::REJECTED => 'Activity does not meet requirements',
            self::NEEDS_REVISION => 'Please update and resubmit',
        };
    }

    /**
     * Get color for UI badges
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::NEEDS_REVISION => 'orange',
        };
    }

    /**
     * Get Tailwind badge classes
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
            self::APPROVED => 'bg-green-500/20 text-green-400 border-green-500/30',
            self::REJECTED => 'bg-red-500/20 text-red-400 border-red-500/30',
            self::NEEDS_REVISION => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => '⏳',
            self::APPROVED => '✅',
            self::REJECTED => '❌',
            self::NEEDS_REVISION => '📝',
        };
    }

    /**
     * Check if points should be awarded
     */
    public function awardsPoints(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * Check if activity can be edited
     */
    public function canEdit(): bool
    {
        return in_array($this, [self::PENDING, self::NEEDS_REVISION]);
    }

    /**
     * Check if activity can be resubmitted
     */
    public function canResubmit(): bool
    {
        return in_array($this, [self::REJECTED, self::NEEDS_REVISION]);
    }

    /**
     * Get statuses visible to fellows
     */
    public static function fellowVisible(): array
    {
        return self::cases();
    }

    /**
     * Get pending statuses for admin queue
     */
    public static function pendingReview(): array
    {
        return [self::PENDING, self::NEEDS_REVISION];
    }
}
