<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Used by docker/start.sh on the server. Seeds the Nkwen data the first time only,
 * so restarting the server never wipes real orders and stock.
 */
class SeedIfEmptySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('products')->exists()) {
            $this->command?->info('Products already exist: nothing to seed.');

            return;
        }

        $this->call(DatabaseSeeder::class);
    }
}
