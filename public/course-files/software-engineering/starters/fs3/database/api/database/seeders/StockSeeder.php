<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reads database/data/nkwen.json and fills, for every product:
 * its reorder level, and one stock row per open branch (20 products x 4 branches = 80 rows).
 * Limbe (planned) has no stock. Run it after ProductSeeder and BranchSeeder.
 */
class StockSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/nkwen.json')), true);

        DB::transaction(function () use ($data) {
            foreach ($data['products'] as $p) {
                Product::whereKey($p['id'])->update(['reorder_level' => $p['reorder_level']]);

                foreach ($p['stock'] as $branchCode => $quantity) {
                    StockLevel::create(['product_id' => $p['id'], 'branch_code' => $branchCode, 'quantity' => $quantity]);
                }
            }
        });
    }
}
