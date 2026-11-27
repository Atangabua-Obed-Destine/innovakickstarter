<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reads database/data/nkwen.json and fills, for every product:
 * its reorder level, one stock row per open branch, and one "opening_stock" history row.
 * Run it after ProductSeeder and BranchSeeder.
 */
class StockSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/nkwen.json')), true);

        DB::transaction(function () use ($data) {
            foreach ($data['products'] as $p) {
                // TODO 1: copy $p['reorder_level'] into the product's reorder_level column.

                foreach ($p['stock'] as $branchCode => $quantity) {
                    // TODO 2: create a StockLevel row (product_id, branch_code, quantity).
                    // TODO 3: create a StockMovement: change = $quantity, reason 'opening_stock',
                    //         balance_after = $quantity, note 'Opening stock from nkwen.json'.
                }
            }
        });
    }
}
