<?php

namespace App\Enums;

/**
 * User Role Enum
 * 
 * Defines the four primary user roles in the IKS platform.
 * Used for role-based access control throughout the application.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum UserRole: string
{
    case FELLOW = 'fellow';
    case ADMIN = 'admin';
    case MENTOR = 'mentor';
    case RECRUITER = 'recruiter';

    /**
     * Get human-readable label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::FELLOW => 'Fellow',
            self::ADMIN => 'Administrator',
            self::MENTOR => 'Mentor',
            self::RECRUITER => 'Recruiter',
        };
    }

    /**
     * Get role description
     */
    public function description(): string
    {
        return match($this) {
            self::FELLOW => 'Career builder pursuing tracks on the IKS platform',
            self::ADMIN => 'Platform administrator with full system access',
            self::MENTOR => 'Industry professional conducting mock interviews and providing guidance',
            self::RECRUITER => 'Talent acquisition professional hiring fellows',
        };
    }

    /**
     * Get role color for UI badges
     */
    public function color(): string
    {
        return match($this) {
            self::FELLOW => 'purple',
            self::ADMIN => 'red',
            self::MENTOR => 'blue',
            self::RECRUITER => 'teal',
        };
    }

    /**
     * Get dashboard route for the role
     */
    public function dashboardRoute(): string
    {
        return match($this) {
            self::FELLOW => 'fellow.dashboard',
            self::ADMIN => 'admin.dashboard',
            self::MENTOR => 'mentor.dashboard',
            self::RECRUITER => 'recruiter.dashboard',
        };
    }

    /**
     * Check if role can access admin panel
     */
    public function canAccessAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role can conduct interviews
     */
    public function canConductInterviews(): bool
    {
        return in_array($this, [self::ADMIN, self::MENTOR]);
    }

    /**
     * Get all roles as array for select dropdowns
     */
    public static function toSelectArray(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }
}
