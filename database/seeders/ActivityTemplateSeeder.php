<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Activity Template Seeder
 * 
 * Placeholder seeder - Activity templates to be implemented in a future update.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ActivityTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delegate to the new CurriculumTemplateSeeder for structured curriculum
        $this->call(CurriculumTemplateSeeder::class);
    }
}
