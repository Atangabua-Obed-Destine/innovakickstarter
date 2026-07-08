<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Roles and Permissions Seeder
 * 
 * Seeds the platform roles and their associated permissions.
 * 
 * Roles:
 * - admin: Full platform access
 * - fellow: Career development users
 * - mentor: Guidance providers
 * - recruiter: Talent seekers
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = $this->createPermissions();

        // Create roles and assign permissions
        $this->createRoles($permissions);
    }

    /**
     * Create all permissions.
     */
    protected function createPermissions(): array
    {
        $permissions = [
            // Dashboard
            'view-dashboard',
            'view-admin-dashboard',
            'view-recruiter-dashboard',
            'view-mentor-dashboard',

            // Profile
            'edit-own-profile',
            'view-public-profiles',
            'view-full-profiles', // Recruiters can see more details

            // Activities
            'submit-activities',
            'view-own-activities',
            'approve-activities',
            'reject-activities',
            'delete-activities',

            // Tracks
            'view-tracks',
            'create-tracks',
            'edit-tracks',
            'delete-tracks',
            'enroll-in-tracks',

            // Interviews
            'schedule-interviews',
            'view-own-interviews',
            'conduct-interviews', // Mentors/AI
            'manage-interviews',

            // Weekly Progress
            'submit-weekly-progress',
            'view-own-progress',
            'view-all-progress',

            // Career Capital
            'view-own-score',
            'view-fellow-scores', // Recruiters
            'recalculate-scores', // Admin

            // Marketplace
            'access-marketplace',
            'view-talent',
            'shortlist-talent',
            'contact-talent',
            'manage-subscriptions',

            // Mentorship
            'request-mentorship',
            'provide-mentorship',
            'manage-mentorship-sessions',

            // Admin
            'manage-users',
            'manage-fellows',
            'manage-admins',
            'manage-mentors',
            'manage-recruiters',
            'view-audit-logs',
            'manage-settings',
            'view-analytics',
            'manage-notifications',
            'export-data',

            // System
            'access-api',
            'impersonate-users',
        ];

        $created = [];
        foreach ($permissions as $permission) {
            $created[$permission] = Permission::firstOrCreate(['name' => $permission]);
        }

        return $created;
    }

    /**
     * Create roles and assign permissions.
     */
    protected function createRoles(array $permissions): void
    {
        // Admin Role - Full access
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(array_keys($permissions));

        // Fellow Role - Career development user
        $fellow = Role::firstOrCreate(['name' => 'fellow']);
        $fellow->syncPermissions([
            'view-dashboard',
            'edit-own-profile',
            'view-public-profiles',
            'submit-activities',
            'view-own-activities',
            'view-tracks',
            'enroll-in-tracks',
            'schedule-interviews',
            'view-own-interviews',
            'submit-weekly-progress',
            'view-own-progress',
            'view-own-score',
            'request-mentorship',
            'access-api',
        ]);

        // Mentor Role - Guidance provider
        $mentor = Role::firstOrCreate(['name' => 'mentor']);
        $mentor->syncPermissions([
            'view-dashboard',
            'view-mentor-dashboard',
            'edit-own-profile',
            'view-public-profiles',
            'view-tracks',
            'conduct-interviews',
            'view-own-interviews',
            'manage-interviews',
            'provide-mentorship',
            'manage-mentorship-sessions',
            'view-fellow-scores',
            'access-api',
        ]);

        // Recruiter Role - Talent seeker
        $recruiter = Role::firstOrCreate(['name' => 'recruiter']);
        $recruiter->syncPermissions([
            'view-dashboard',
            'view-recruiter-dashboard',
            'edit-own-profile',
            'view-public-profiles',
            'view-full-profiles',
            'view-tracks',
            'view-fellow-scores',
            'access-marketplace',
            'view-talent',
            'shortlist-talent',
            'contact-talent',
            'manage-subscriptions',
            'access-api',
        ]);
    }
}
