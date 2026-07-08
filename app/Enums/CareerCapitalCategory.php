<?php

namespace App\Enums;

/**
 * Career Capital Category Enum
 * 
 * The 5 dimensions of Career Capital measurement.
 * Each has a configurable weight that sums to 100%.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum CareerCapitalCategory: string
{
    case TECHNICAL = 'technical';
    case INTERVIEW = 'interview';
    case PORTFOLIO = 'portfolio';
    case COLLABORATION = 'collaboration';
    case LEARNING = 'learning';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::TECHNICAL => 'Technical Execution',
            self::INTERVIEW => 'Interview Readiness',
            self::PORTFOLIO => 'Portfolio Quality',
            self::COLLABORATION => 'Collaboration',
            self::LEARNING => 'Continuous Learning',
        };
    }

    /**
     * Get short label for charts
     */
    public function shortLabel(): string
    {
        return match($this) {
            self::TECHNICAL => 'Technical',
            self::INTERVIEW => 'Interview',
            self::PORTFOLIO => 'Portfolio',
            self::COLLABORATION => 'Collab',
            self::LEARNING => 'Learning',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::TECHNICAL => 'Projects shipped, code quality, hackathons, GitHub contributions',
            self::INTERVIEW => 'AI and human mock interviews, coding challenges, system design',
            self::PORTFOLIO => 'Deployed apps, case studies, personal website, documentation',
            self::COLLABORATION => 'Code reviews, mentoring, pair programming, open source',
            self::LEARNING => 'Certifications, courses, workshops, technical writing',
        };
    }

    /**
     * Get default weight (percentage)
     */
    public function defaultWeight(): int
    {
        return match($this) {
            self::TECHNICAL => 30,
            self::INTERVIEW => 25,
            self::PORTFOLIO => 20,
            self::COLLABORATION => 15,
            self::LEARNING => 10,
        };
    }

    /**
     * Get color for charts
     */
    public function color(): string
    {
        return match($this) {
            self::TECHNICAL => 'purple',
            self::INTERVIEW => 'blue',
            self::PORTFOLIO => 'teal',
            self::COLLABORATION => 'green',
            self::LEARNING => 'amber',
        };
    }

    /**
     * Get hex color
     */
    public function hexColor(): string
    {
        return match($this) {
            self::TECHNICAL => '#8B5CF6',
            self::INTERVIEW => '#3B82F6',
            self::PORTFOLIO => '#14B8A6',
            self::COLLABORATION => '#22C55E',
            self::LEARNING => '#F59E0B',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::TECHNICAL => '💻',
            self::INTERVIEW => '🎯',
            self::PORTFOLIO => '📁',
            self::COLLABORATION => '🤝',
            self::LEARNING => '📖',
        };
    }

    /**
     * Get all categories with default weights
     */
    public static function defaultRubric(): array
    {
        $rubric = [];
        foreach (self::cases() as $category) {
            $rubric[$category->value] = $category->defaultWeight();
        }
        return $rubric;
    }

    /**
     * Validate rubric weights sum to 100
     */
    public static function validateRubric(array $rubric): bool
    {
        $sum = 0;
        foreach (self::cases() as $category) {
            $sum += $rubric[$category->value] ?? 0;
        }
        return $sum === 100;
    }
}
