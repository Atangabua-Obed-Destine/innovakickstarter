<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Database Seeder
 * 
 * Main seeder that orchestrates all other seeders.
 * Run with: php artisan db:seed
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting IKS Database Seeding...');
        $this->command->newLine();

        // 1. Seed Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);
        $this->command->newLine();

        // 2. Seed Career Tracks
        $this->call(CareerTrackSeeder::class);
        $this->command->newLine();

        // 3. Seed Cohorts
        $this->call(CohortSeeder::class);
        $this->command->newLine();

        // 4. Seed Programs
        $this->call(ProgramSeeder::class);
        $this->command->newLine();

        // 5. Seed Admin Settings (tier thresholds, scoring, platform config)
        $this->call(AdminSettingsSeeder::class);
        $this->command->newLine();

        // 6. Seed Activity Templates (informational only)
        $this->call(ActivityTemplateSeeder::class);
        $this->command->newLine();

        // 7. Seed Demo Users (admin, fellows, recruiters, mentors)
        $this->call(DemoUsersSeeder::class);
        $this->command->newLine();

        // 8. Seed Demo Data (activities, interviews, weekly progress)
        $this->call(DemoDataSeeder::class);
        $this->command->newLine();

        // 9. Seed Site Content
        $this->call(SiteContentSeeder::class);
        $this->command->newLine();

        // 10. Seed Testimonials
        $this->call(TestimonialSeeder::class);
        $this->command->newLine();

        // 11. Seed Footer Links
        $this->call(FooterLinkSeeder::class);
        $this->command->newLine();

        // 12. Seed FAQs
        $this->call(FAQSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Database seeding complete!');
        $this->command->newLine();
        $this->command->info('You can now log in with:');
        $this->command->info('  Email: admin@iks-innova.com');
        $this->command->info('  Password: password');
    }
}
