<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Enums\UserRole;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions grouped by feature
        $permissions = [
            'users' => [
                'view_users',
                'create_users',
                'edit_users',
                'delete_users',
            ],
            'roles' => [
                'view_roles',
                'edit_roles',
            ],
            'curriculum' => [
                'view_curriculum',
                'edit_curriculum',
                'manage_tracks',
            ],
            'attendance' => [
                'view_attendance',
                'manage_attendance',
            ],
            'financials' => [
                'view_financials',
                'manage_financials',
            ],
            'content' => [
                'manage_content',
            ],
        ];

        // Create permissions
        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }
        }

        // Ensure roles exist
        foreach (UserRole::cases() as $roleEnum) {
            Role::firstOrCreate(['name' => $roleEnum->value]);
        }

        // Admin gets all permissions
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->givePermissionTo(Permission::all());
    }
}
