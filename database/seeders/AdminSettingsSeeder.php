<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use Illuminate\Database\Seeder;

/**
 * Admin Settings Seeder
 * 
 * Seeds default admin settings from the AdminSetting::DEFAULTS constant.
 * Uses firstOrCreate so it's safe to run multiple times.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class AdminSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminSetting::seedDefaults();

        $count = AdminSetting::count();
        $this->command->info("✓ Seeded {$count} admin settings across " . count(array_unique(array_column(AdminSetting::DEFAULTS, 'group'))) . " groups");
    }
}
