<?php

namespace App\Enums;

/**
 * Interview Type Enum
 * 
 * Types of interviews available on the platform.
 * Each type has different scoring criteria and point values.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum InterviewType: string
{
    case BEHAVIORAL = 'behavioral';
    case TECHNICAL_CODING = 'technical_coding';
    case SYSTEM_DESIGN = 'system_design';
    case PRODUCT_CASE = 'product_case';
    case DESIGN_CHALLENGE = 'design_challenge';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::BEHAVIORAL => 'Behavioral Interview',
            self::TECHNICAL_CODING => 'Technical Coding',
            self::SYSTEM_DESIGN => 'System Design',
            self::PRODUCT_CASE => 'Product Case Study',
            self::DESIGN_CHALLENGE => 'Design Challenge',
        };
    }

    /**
     * Get short label
     */
    public function shortLabel(): string
    {
        return match($this) {
            self::BEHAVIORAL => 'Behavioral',
            self::TECHNICAL_CODING => 'Coding',
            self::SYSTEM_DESIGN => 'System Design',
            self::PRODUCT_CASE => 'Product',
            self::DESIGN_CHALLENGE => 'Design',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::BEHAVIORAL => 'Practice STAR method responses for common behavioral questions',
            self::TECHNICAL_CODING => 'LeetCode-style algorithm and data structure problems',
            self::SYSTEM_DESIGN => 'Architect scalable systems like Twitter, Instagram, etc.',
            self::PRODUCT_CASE => 'Product management case studies and strategy questions',
            self::DESIGN_CHALLENGE => 'UI/UX redesign challenges and portfolio critiques',
        };
    }

    /**
     * Get default duration in minutes
     */
    public function defaultDuration(): int
    {
        return match($this) {
            self::BEHAVIORAL => 30,
            self::TECHNICAL_CODING => 45,
            self::SYSTEM_DESIGN => 60,
            self::PRODUCT_CASE => 45,
            self::DESIGN_CHALLENGE => 45,
        };
    }

    /**
     * Get default AI points per session
     */
    public function defaultAiPoints(): int
    {
        return match($this) {
            self::BEHAVIORAL => 5,
            self::TECHNICAL_CODING => 8,
            self::SYSTEM_DESIGN => 10,
            self::PRODUCT_CASE => 10,
            self::DESIGN_CHALLENGE => 10,
        };
    }

    /**
     * Get default human interview points
     */
    public function defaultHumanPoints(): int
    {
        return 15; // Same for all types
    }

    /**
     * Get weekly limit (max sessions per week)
     */
    public function weeklyLimit(): int
    {
        return match($this) {
            self::BEHAVIORAL => 4,
            self::TECHNICAL_CODING => 3,
            self::SYSTEM_DESIGN => 2,
            self::PRODUCT_CASE => 2,
            self::DESIGN_CHALLENGE => 2,
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::BEHAVIORAL => 'blue',
            self::TECHNICAL_CODING => 'purple',
            self::SYSTEM_DESIGN => 'teal',
            self::PRODUCT_CASE => 'amber',
            self::DESIGN_CHALLENGE => 'pink',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::BEHAVIORAL => '💬',
            self::TECHNICAL_CODING => '💻',
            self::SYSTEM_DESIGN => '🏗️',
            self::PRODUCT_CASE => '📊',
            self::DESIGN_CHALLENGE => '🎨',
        };
    }

    /**
     * Get scoring rubric criteria
     */
    public function rubricCriteria(): array
    {
        return match($this) {
            self::BEHAVIORAL => [
                'structure' => 'STAR method adherence',
                'clarity' => 'Clear and concise communication',
                'relevance' => 'Relevant examples provided',
                'confidence' => 'Confident delivery',
                'self_awareness' => 'Shows growth and learning',
            ],
            self::TECHNICAL_CODING => [
                'correctness' => 'Solution produces correct output',
                'efficiency' => 'Optimal time/space complexity',
                'code_quality' => 'Clean, readable code',
                'problem_solving' => 'Logical approach to problem',
                'edge_cases' => 'Handles edge cases',
            ],
            self::SYSTEM_DESIGN => [
                'requirements' => 'Clarifies requirements',
                'scalability' => 'Designs for scale',
                'trade_offs' => 'Discusses trade-offs',
                'components' => 'Appropriate component choices',
                'depth' => 'Deep dive on key areas',
            ],
            self::PRODUCT_CASE => [
                'framework' => 'Uses structured framework',
                'metrics' => 'Defines success metrics',
                'prioritization' => 'Prioritizes effectively',
                'creativity' => 'Creative solutions',
                'business_acumen' => 'Understands business impact',
            ],
            self::DESIGN_CHALLENGE => [
                'user_empathy' => 'Understands user needs',
                'process' => 'Follows design process',
                'creativity' => 'Creative solutions',
                'visual' => 'Strong visual skills',
                'rationale' => 'Explains design decisions',
            ],
        };
    }

    /**
     * Get applicable tracks for this interview type
     */
    public function applicableTracks(): array
    {
        return match($this) {
            self::BEHAVIORAL => ['all'], // Applies to all tracks
            self::TECHNICAL_CODING => ['full-stack-engineering', 'backend-engineering', 'frontend-engineering', 'devops', 'ai-ml'],
            self::SYSTEM_DESIGN => ['full-stack-engineering', 'backend-engineering', 'devops'],
            self::PRODUCT_CASE => ['product-management'],
            self::DESIGN_CHALLENGE => ['ui-ux-design', 'frontend-engineering'],
        };
    }
}
