<?php

namespace App\Enums;

/**
 * Interview Mode Enum
 * 
 * Differentiates between AI-powered and human interviews.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum InterviewMode: string
{
    case AI = 'ai';
    case HUMAN = 'human';
    case PEER = 'peer';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::AI => 'AI Interview',
            self::HUMAN => 'Human Interview',
            self::PEER => 'Peer Interview',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::AI => 'Practice anytime with AI-powered mock interviews',
            self::HUMAN => 'Scheduled session with an industry mentor',
            self::PEER => 'Practice with a fellow peer in your track',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::AI => '🤖',
            self::HUMAN => '👤',
            self::PEER => '👥',
        };
    }

    /**
     * Get color
     */
    public function color(): string
    {
        return match($this) {
            self::AI => 'cyan',
            self::HUMAN => 'green',
            self::PEER => 'purple',
        };
    }

    /**
     * Check if scheduling is required
     */
    public function requiresScheduling(): bool
    {
        return $this === self::HUMAN || $this === self::PEER;
    }
}
