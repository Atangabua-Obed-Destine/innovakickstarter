<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Used by docker/start.sh on the live server.
 * Seeds the Nkwen data only when the database is still empty, so restarts never duplicate or reset anything.
 */
class FirstDeploySeeder extends Seeder
{
    public function run(): void
    {
        if (Product::count() > 0) {
            $this->command?->info('Data already there: nothing to seed.');

            return;
        }

        $this->call(DatabaseSeeder::class);
    }
}
