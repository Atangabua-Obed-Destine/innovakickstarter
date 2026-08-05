<?php

namespace App\Enums;

/**
 * Fellow Type Enum
 * 
 * Distinguishes between the three categories of fellows
 * on the IKS platform, each with different onboarding flows.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum FellowType: string
{
    case ACADEMIC = 'academic';
    case CORPORATE = 'corporate';
    case INDEPENDENT = 'independent';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match($this) {
            self::ACADEMIC => 'Academic Intern',
            self::CORPORATE => 'Corporate Intern',
            self::INDEPENDENT => 'Independent Fellow',
        };
    }

    /**
     * Get description for onboarding UI.
     */
    public function description(): string
    {
        return match($this) {
            self::ACADEMIC => 'I\'m a student completing an internship required by my school or university program.',
            self::CORPORATE => 'I\'m sponsored by my company or organization for a professional development internship.',
            self::INDEPENDENT => 'I\'m joining independently to build my career capital and professional skills.',
        };
    }

    /**
     * Get icon emoji for UI.
     */
    public function icon(): string
    {
        return match($this) {
            self::ACADEMIC => '🎓',
            self::CORPORATE => '🏢',
            self::INDEPENDENT => '🚀',
        };
    }

    /**
     * Whether this type requires internship details during onboarding.
     */
    public function requiresInternshipDetails(): bool
    {
        return true;
    }

    /**
     * Whether this type requires academic-specific fields.
     */
    public function requiresAcademicFields(): bool
    {
        return $this === self::ACADEMIC;
    }
}
