<?php

namespace App\Enums;

/**
 * Difficulty Level Enum
 * 
 * Defines difficulty progression levels for curriculum activities.
 * Activities naturally escalate from beginner to expert through milestones.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum DifficultyLevel: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::BEGINNER => 'Beginner',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
            self::EXPERT => 'Expert',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::BEGINNER => 'Follow guided instructions and learn fundamentals',
            self::INTERMEDIATE => 'Apply concepts independently with some guidance',
            self::ADVANCED => 'Architect solutions and make design decisions',
            self::EXPERT => 'Lead, innovate, and contribute to the community',
        };
    }

    /**
     * Get color for UI badges
     */
    public function color(): string
    {
        return match($this) {
            self::BEGINNER => 'green',
            self::INTERMEDIATE => 'blue',
            self::ADVANCED => 'purple',
            self::EXPERT => 'red',
        };
    }

    /**
     * Get Tailwind badge classes
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::BEGINNER => 'bg-green-500/20 text-green-400 ring-green-500/30',
            self::INTERMEDIATE => 'bg-blue-500/20 text-blue-400 ring-blue-500/30',
            self::ADVANCED => 'bg-purple-500/20 text-purple-400 ring-purple-500/30',
            self::EXPERT => 'bg-red-500/20 text-red-400 ring-red-500/30',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::BEGINNER => '🌱',
            self::INTERMEDIATE => '🔧',
            self::ADVANCED => '🚀',
            self::EXPERT => '💎',
        };
    }

    /**
     * Get points multiplier for this difficulty
     */
    public function pointsMultiplier(): float
    {
        return match($this) {
            self::BEGINNER => 1.0,
            self::INTERMEDIATE => 1.25,
            self::ADVANCED => 1.5,
            self::EXPERT => 2.0,
        };
    }

    /**
     * Get numeric order for sorting
     */
    public function order(): int
    {
        return match($this) {
            self::BEGINNER => 1,
            self::INTERMEDIATE => 2,
            self::ADVANCED => 3,
            self::EXPERT => 4,
        };
    }
}
