<?php

namespace App\Enums;

/**
 * Interview Status Enum
 * 
 * States for interview sessions.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum InterviewStatus: string
{
    case PENDING = 'pending';
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::SCHEDULED => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'gray',
            self::SCHEDULED => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
            self::NO_SHOW => 'orange',
        };
    }

    /**
     * Get badge class
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-gray-500/20 text-gray-400',
            self::SCHEDULED => 'bg-blue-500/20 text-blue-400',
            self::IN_PROGRESS => 'bg-yellow-500/20 text-yellow-400',
            self::COMPLETED => 'bg-green-500/20 text-green-400',
            self::CANCELLED => 'bg-red-500/20 text-red-400',
            self::NO_SHOW => 'bg-orange-500/20 text-orange-400',
        };
    }

    /**
     * Check if points should be awarded
     */
    public function awardsPoints(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if interview can be started
     */
    public function canStart(): bool
    {
        return in_array($this, [self::PENDING, self::SCHEDULED]);
    }

    /**
     * Check if interview can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::SCHEDULED]);
    }
}
