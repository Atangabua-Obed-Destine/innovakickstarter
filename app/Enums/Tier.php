<?php

namespace App\Enums;

/**
 * Career Capital Tier Enum
 * 
 * Defines the progression tiers based on Career Capital score.
 * Thresholds are admin-configurable but defaults are:
 * - Rookie: 0-20%
 * - Intern: 21-40%
 * - Professional: 41-60%
 * - Elite: 61-100%
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum Tier: string
{
    case ROOKIE = 'rookie';
    case INTERN = 'intern';
    case PROFESSIONAL = 'professional';
    case ELITE = 'elite';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ROOKIE => 'Rookie',
            self::INTERN => 'Intern',
            self::PROFESSIONAL => 'Professional',
            self::ELITE => 'Elite',
        };
    }

    /**
     * Get tier description
     */
    public function description(): string
    {
        return match($this) {
            self::ROOKIE => 'Just starting the career building journey',
            self::INTERN => 'Building foundational skills and portfolio',
            self::PROFESSIONAL => 'Job-ready with proven skills',
            self::ELITE => 'Top performer, highly sought by recruiters',
        };
    }

    /**
     * Get tier color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::ROOKIE => 'gray',
            self::INTERN => 'blue',
            self::PROFESSIONAL => 'purple',
            self::ELITE => 'amber',
        };
    }

    /**
     * Get tier hex color
     */
    public function hexColor(): string
    {
        return match($this) {
            self::ROOKIE => '#6B7280',
            self::INTERN => '#3B82F6',
            self::PROFESSIONAL => '#8B5CF6',
            self::ELITE => '#F59E0B',
        };
    }

    /**
     * Get tier badge gradient classes
     */
    public function gradientClass(): string
    {
        return match($this) {
            self::ROOKIE => 'from-gray-500 to-gray-600',
            self::INTERN => 'from-blue-500 to-blue-600',
            self::PROFESSIONAL => 'from-purple-500 to-purple-600',
            self::ELITE => 'from-amber-400 to-amber-600',
        };
    }

    /**
     * Get tier icon/emoji
     */
    public function icon(): string
    {
        return match($this) {
            self::ROOKIE => '🌱',
            self::INTERN => '📚',
            self::PROFESSIONAL => '💼',
            self::ELITE => '⭐',
        };
    }

    /**
     * Get default score range (can be overridden by admin settings)
     */
    public function defaultRange(): array
    {
        try {
            $t = \App\Models\AdminSetting::getTierThresholds();
            $elite = (float) ($t['elite'] ?? 75);
            $professional = (float) ($t['professional'] ?? 50);
            $intern = (float) ($t['intern'] ?? 25);
        } catch (\Throwable $e) {
            $elite = 75;
            $professional = 50;
            $intern = 25;
        }

        return match($this) {
            self::ROOKIE => ['min' => 0, 'max' => max(0, $intern - 0.1)],
            self::INTERN => ['min' => $intern, 'max' => max(0, $professional - 0.1)],
            self::PROFESSIONAL => ['min' => $professional, 'max' => max(0, $elite - 0.1)],
            self::ELITE => ['min' => $elite, 'max' => 100],
        };
    }

    /**
     * Get tier benefits
     */
    public function benefits(): array
    {
        return match($this) {
            self::ROOKIE => [
                'Access to basic resources',
                'Submit activities for review',
                'Practice AI mock interviews',
                'Join community forums',
            ],
            self::INTERN => [
                'All Rookie benefits',
                'Monthly 1-on-1 mentor office hours',
                'Resume review sessions',
                'Featured on "Rising Stars" board',
            ],
            self::PROFESSIONAL => [
                'All Intern benefits',
                'Priority job opportunities',
                'Resume roast with hiring managers',
                'Exclusive networking events',
                'Can add secondary track',
            ],
            self::ELITE => [
                'All Professional benefits',
                'Direct recruiter introductions',
                'Featured profiles on marketplace',
                'Lifetime alumni status',
                'Annual Elite Summit invitation',
                'Paid mentoring opportunities',
            ],
        };
    }

    /**
     * Get tier from score using configurable thresholds.
     * 
     * @param float $score The career capital score (0-100)
     * @param array|null $thresholds Optional ['elite' => 75, 'professional' => 50, 'intern' => 25]
     *                               If null, uses AdminSetting defaults or hardcoded fallbacks.
     */
    public static function fromScore(float $score, ?array $thresholds = null): self
    {
        if ($thresholds === null) {
            // Try to load from admin settings if available
            try {
                $t = \App\Models\AdminSetting::getTierThresholds();
                $thresholds = [
                    'elite' => (float) ($t['elite'] ?? 75),
                    'professional' => (float) ($t['professional'] ?? 50),
                    'intern' => (float) ($t['intern'] ?? 25),
                ];
            } catch (\Throwable $e) {
                // Fallback if DB not available (e.g., during migrations)
                $thresholds = [
                    'elite' => 75,
                    'professional' => 50,
                    'intern' => 25,
                ];
            }
        }

        return match(true) {
            $score >= $thresholds['elite'] => self::ELITE,
            $score >= $thresholds['professional'] => self::PROFESSIONAL,
            $score >= $thresholds['intern'] => self::INTERN,
            default => self::ROOKIE,
        };
    }

    /**
     * Get ordered tiers (for progression display)
     */
    public static function ordered(): array
    {
        return [
            self::ROOKIE,
            self::INTERN,
            self::PROFESSIONAL,
            self::ELITE,
        ];
    }

    /**
     * Get tier order (1-4)
     */
    public function order(): int
    {
        return match($this) {
            self::ROOKIE => 1,
            self::INTERN => 2,
            self::PROFESSIONAL => 3,
            self::ELITE => 4,
        };
    }

    /**
     * Check if this tier is higher than another
     */
    public function isHigherThan(self $other): bool
    {
        return $this->order() > $other->order();
    }
}
