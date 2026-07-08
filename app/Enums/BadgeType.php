<?php

namespace App\Enums;

/**
 * Badge Type Enum
 * 
 * Defines the types of digital badges fellows can earn
 * through the curriculum and engagement systems.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum BadgeType: string
{
    case MILESTONE = 'milestone';
    case STREAK = 'streak';
    case ACHIEVEMENT = 'achievement';
    case TRACK_COMPLETION = 'track_completion';
    case POWER_WEEK = 'power_week';
    case PEER_CHAMPION = 'peer_champion';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::MILESTONE => 'Milestone Badge',
            self::STREAK => 'Streak Badge',
            self::ACHIEVEMENT => 'Achievement Badge',
            self::TRACK_COMPLETION => 'Track Completion',
            self::POWER_WEEK => 'Power Week',
            self::PEER_CHAMPION => 'Peer Champion',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::MILESTONE => 'Earned by completing a curriculum milestone',
            self::STREAK => 'Earned by maintaining a consistent weekly streak',
            self::ACHIEVEMENT => 'Earned by reaching a special accomplishment',
            self::TRACK_COMPLETION => 'Earned by completing an entire track curriculum',
            self::POWER_WEEK => 'Earned during a Power Week for exceptional performance',
            self::PEER_CHAMPION => 'Earned for outstanding peer reviews and collaboration',
        };
    }

    /**
     * Get default icon
     */
    public function icon(): string
    {
        return match($this) {
            self::MILESTONE => '🏅',
            self::STREAK => '🔥',
            self::ACHIEVEMENT => '⭐',
            self::TRACK_COMPLETION => '🎓',
            self::POWER_WEEK => '⚡',
            self::PEER_CHAMPION => '🤝',
        };
    }

    /**
     * Get color
     */
    public function color(): string
    {
        return match($this) {
            self::MILESTONE => 'purple',
            self::STREAK => 'orange',
            self::ACHIEVEMENT => 'yellow',
            self::TRACK_COMPLETION => 'green',
            self::POWER_WEEK => 'blue',
            self::PEER_CHAMPION => 'teal',
        };
    }

    /**
     * Get hex color
     */
    public function hexColor(): string
    {
        return match($this) {
            self::MILESTONE => '#8B5CF6',
            self::STREAK => '#F97316',
            self::ACHIEVEMENT => '#EAB308',
            self::TRACK_COMPLETION => '#22C55E',
            self::POWER_WEEK => '#3B82F6',
            self::PEER_CHAMPION => '#14B8A6',
        };
    }

    /**
     * Whether this badge type is shareable on LinkedIn
     */
    public function isShareable(): bool
    {
        return in_array($this, [
            self::MILESTONE,
            self::TRACK_COMPLETION,
            self::ACHIEVEMENT,
        ]);
    }
}
