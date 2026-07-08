<?php

namespace App\Enums;

/**
 * Track Category Enum
 * 
 * Categorizes tracks as technical, non-technical, or hybrid.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum TrackCategory: string
{
    case TECHNICAL = 'technical';
    case NON_TECHNICAL = 'non-technical';
    case HYBRID = 'hybrid';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::TECHNICAL => 'Technical',
            self::NON_TECHNICAL => 'Non-Technical',
            self::HYBRID => 'Hybrid',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::TECHNICAL => 'Engineering and development focused tracks',
            self::NON_TECHNICAL => 'Business, design, and strategy focused tracks',
            self::HYBRID => 'Combination of technical and business skills',
        };
    }

    /**
     * Get color
     */
    public function color(): string
    {
        return match($this) {
            self::TECHNICAL => 'purple',
            self::NON_TECHNICAL => 'teal',
            self::HYBRID => 'blue',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::TECHNICAL => '⚙️',
            self::NON_TECHNICAL => '📈',
            self::HYBRID => '🔄',
        };
    }
}
