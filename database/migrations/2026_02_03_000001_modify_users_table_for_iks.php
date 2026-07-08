<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Modify users table for IKS Career Capital Platform
 * 
 * This migration transforms the default Laravel users table to support:
 * - UUID primary keys (security - prevents enumeration attacks)
 * - Multiple user roles (fellow, admin, mentor, recruiter)
 * - Extended profile fields (location, availability, bio, etc.)
 * - Soft deletes for data compliance
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add UUID column (we'll make it primary after data migration)
            $table->uuid('uuid')->after('id')->unique();
            
            // Role-based access control
            $table->string('role', 20)->default('fellow')->after('password')
                ->comment('User role: fellow, admin, mentor, recruiter');
            
            // Extended profile fields
            $table->string('username', 50)->unique()->nullable()->after('email')
                ->comment('URL-friendly username for public profiles');
            $table->string('phone', 20)->nullable()->after('username');
            $table->string('avatar_url')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('avatar_url')
                ->comment('Short biography for profile display');
            $table->string('location', 100)->nullable()->after('bio')
                ->comment('City, Country format');
            $table->string('availability', 20)->nullable()->after('location')
                ->comment('immediate, 2_weeks, 1_month, 3_months');
            
            // Professional information
            $table->string('linkedin_url')->nullable()->after('availability');
            $table->string('github_url')->nullable()->after('linkedin_url');
            $table->string('portfolio_url')->nullable()->after('github_url');
            $table->string('resume_url')->nullable()->after('portfolio_url')
                ->comment('Path to uploaded resume PDF');
            
            // Salary expectations (for recruiters)
            $table->integer('salary_min')->nullable()->after('resume_url')
                ->comment('Minimum expected salary in XAF');
            $table->integer('salary_max')->nullable()->after('salary_min')
                ->comment('Maximum expected salary in XAF');
            $table->string('salary_currency', 3)->default('XAF')->after('salary_max');
            
            // Profile visibility
            $table->boolean('is_public')->default(true)->after('salary_currency')
                ->comment('Whether profile is visible to recruiters');
            $table->boolean('is_active')->default(true)->after('is_public')
                ->comment('Account active status');
            $table->boolean('open_to_opportunities')->default(true)->after('is_active')
                ->comment('Whether actively seeking opportunities');
            
            // Tracking
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->timestamp('profile_completed_at')->nullable()->after('last_login_ip')
                ->comment('When user completed their profile setup');
            
            // Soft deletes for compliance
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('role');
            $table->index('location');
            $table->index('availability');
            $table->index('is_public');
            $table->index('is_active');
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['location']);
            $table->dropIndex(['availability']);
            $table->dropIndex(['is_public']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role', 'is_active']);
            
            $table->dropSoftDeletes();
            
            $table->dropColumn([
                'uuid',
                'role',
                'username',
                'phone',
                'avatar_url',
                'bio',
                'location',
                'availability',
                'linkedin_url',
                'github_url',
                'portfolio_url',
                'resume_url',
                'salary_min',
                'salary_max',
                'salary_currency',
                'is_public',
                'is_active',
                'open_to_opportunities',
                'last_login_at',
                'last_login_ip',
                'profile_completed_at',
            ]);
        });
    }
};
