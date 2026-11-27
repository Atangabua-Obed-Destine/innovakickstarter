<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_the_seeder_fills_one_row_per_product_per_open_branch(): void
    {
        // 20 products x 4 open branches. Limbe is planned, so it has no stock rows.
        $this->assertDatabaseCount('stock_levels', 80);
        $this->assertDatabaseHas('stock_levels', ['product_id' => 6, 'branch_code' => 'CAV', 'quantity' => 58]);
        $this->assertDatabaseHas('products', ['id' => 6, 'reorder_level' => 5]);
    }

    public function test_branch_stock_lists_every_product_with_its_status(): void
    {
        $response = $this->getJson('/api/branches/ML4/stock');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
        $response->assertJsonPath('meta.count', 20);
        $response->assertJsonPath('data.0.product_id', 1);
        $response->assertJsonPath('data.0.branch_code', 'ML4');
        $response->assertJsonPath('data.0.sku', 'NKT-BAK-001');
        $response->assertJsonPath('data.0.name', 'Bread (loaf)');
        $response->assertJsonPath('data.0.quantity', 0);
        $response->assertJsonPath('data.0.status', 'RESTOCK');
        // The manager's rule: at or below the reorder level = RESTOCK, at or below twice it = LOW, else OK.
        // Milk Powder 400g: 37 tins, reorder level 10: OK.
        $response->assertJsonPath('data.1.status', 'OK');
        // Beans (White): 21 kg, reorder level 12 (21 is at or below 24): LOW.
        $response->assertJsonPath('data.3.quantity', 21);
        $response->assertJsonPath('data.3.status', 'LOW');
        // Rice 50kg: 3 bags, reorder level 5: RESTOCK.
        $response->assertJsonPath('data.5.quantity', 3);
        $response->assertJsonPath('data.5.reorder_level', 5);
        $response->assertJsonPath('data.5.status', 'RESTOCK');
    }

    public function test_an_unknown_branch_is_404(): void
    {
        $response = $this->getJson('/api/branches/XYZ/stock');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }

    public function test_a_product_knows_its_stock_rows(): void
    {
        // The Eloquent relation Product::stockLevels() (hasMany).
        $product = Product::find(6);

        $this->assertCount(4, $product->stockLevels);
        $this->assertSame(42, $product->stockLevels->firstWhere('branch_code', 'NKW')->quantity);
    }
}
