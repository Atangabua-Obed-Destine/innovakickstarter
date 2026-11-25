<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the products table with the 20 Nkwen Traders products
 * from database/data/nkwen.json (the same data as the course API).
 * Run it with: php artisan db:seed --class=ProductSeeder
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(database_path('data/nkwen.json'));
        $data = json_decode($json, true);

        $rows = [];
        foreach ($data['products'] as $product) {
            $rows[] = [
                'id' => $product['id'],
                'sku' => $product['sku'],
                'name' => $product['name'],
                'category' => $product['category'],
                'unit' => $product['unit'],
                'price_fcfa' => $product['price_fcfa'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('products')->insert($rows);
    }
}
