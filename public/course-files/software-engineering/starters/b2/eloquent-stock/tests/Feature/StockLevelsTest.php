<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Self-checks for "Eloquent: Models, Migrations & Relationships".
class StockLevelsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true; // runs DatabaseSeeder once: NkwenSeeder, then StockSeeder

    public function test_the_tests_run_on_mysql(): void
    {
        $this->assertContains(DB::connection()->getDriverName(), ['mysql', 'mariadb'],
            'Point phpunit.xml at your MySQL test database (DB_CONNECTION=mysql, DB_DATABASE=nkwen_api_test).');
    }

    public function test_the_four_new_tables_exist_with_their_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'reorder_level'), 'Add reorder_level to products with a new migration.');
        $expected = [
            'stock_levels' => ['id', 'product_id', 'branch_code', 'quantity'],
            'orders' => ['id', 'code', 'branch_code', 'customer_name', 'payment_method', 'status', 'total_fcfa', 'created_at'],
            'order_lines' => ['id', 'order_id', 'product_id', 'quantity', 'unit_price_fcfa', 'line_total_fcfa'],
            'stock_movements' => ['id', 'product_id', 'branch_code', 'change', 'reason', 'order_id', 'balance_after', 'note', 'created_at'],
        ];
        foreach ($expected as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table {$table} is missing. Write its migration, then run php artisan migrate.");
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Column {$table}.{$column} is missing.");
            }
        }
    }

    public function test_every_open_branch_has_a_stock_row_for_every_product(): void
    {
        $openBranches = Branch::where('status', 'open')->count();
        $this->assertSame($openBranches * Product::count(), StockLevel::count(),
            'StockSeeder should create one stock_levels row per product per open branch.');
        $this->assertSame(0, Branch::where('code', 'LMB')->first()->stockLevels()->count(),
            'Limbe (LMB) is planned, not open: it should have no stock rows.');
    }

    public function test_each_stock_row_has_an_opening_stock_movement(): void
    {
        $this->assertSame(StockLevel::count(), StockMovement::where('reason', 'opening_stock')->count(),
            'StockSeeder should write one opening_stock movement per stock row.');
        $this->assertSame((int) StockLevel::sum('quantity'), (int) StockMovement::sum('change'),
            'The movements should add up to the stock levels.');
    }

    public function test_the_relationships_work_in_both_directions(): void
    {
        $stock = StockLevel::where('branch_code', 'NKW')->first();
        $this->assertNotNull($stock->product, 'StockLevel needs a product() relationship (belongsTo).');
        $this->assertSame('NKW', $stock->branch?->code, 'StockLevel needs a branch() relationship that uses branch_code and code.');
        $this->assertSame(Branch::where('status', 'open')->count(), $stock->product->stockLevels()->count(),
            'Product needs a stockLevels() relationship (hasMany).');

        $order = Order::create(['branch_code' => 'NKW', 'customer_name' => 'Test', 'payment_method' => 'cash', 'status' => 'pay_at_counter', 'total_fcfa' => 1200]);
        $order->lines()->create(['product_id' => 1, 'quantity' => 2, 'unit_price_fcfa' => 600, 'line_total_fcfa' => 1200]);
        $this->assertSame(1, $order->fresh()->lines()->count(), 'Order needs a lines() relationship (hasMany OrderLine).');
        $this->assertSame('NKW', $order->branch?->code, 'Order needs a branch() relationship.');
        $this->assertSame(1, Branch::where('code', 'NKW')->first()->orders()->count(), 'Branch needs an orders() relationship.');
    }

    public function test_the_database_refuses_stock_for_a_product_that_does_not_exist(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        StockLevel::create(['product_id' => 9999, 'branch_code' => 'NKW', 'quantity' => 5]);
    }

    public function test_branch_stock_endpoint(): void
    {
        $this->getJson('/api/branches/NKW/stock')
            ->assertOk()
            ->assertJsonCount(Product::count(), 'data')
            ->assertJsonPath('meta.count', Product::count())
            ->assertJsonStructure(['data' => [['product_id', 'branch_code', 'sku', 'name', 'quantity', 'reorder_level', 'status']]]);
        $this->getJson('/api/branches/LMB/stock')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/branches/XYZ/stock')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_stock_status_uses_the_reorder_level(): void
    {
        $rows = collect($this->getJson('/api/branches/NKW/stock')->json('data'));
        foreach ($rows as $row) {
            $expected = $row['quantity'] <= $row['reorder_level'] ? 'RESTOCK' : ($row['quantity'] <= 2 * $row['reorder_level'] ? 'LOW' : 'OK');
            $this->assertSame($expected, $row['status'], "Wrong status for {$row['name']}: at or below the reorder level is RESTOCK, at or below twice the reorder level is LOW.");
        }
        $statuses = $rows->pluck('status')->unique()->sort()->values()->all();
        $this->assertSame(['LOW', 'OK', 'RESTOCK'], $statuses, 'Nkwen has OK, LOW and RESTOCK products in the seed data.');
        $this->assertGreaterThan(0, Product::where('reorder_level', '>', 0)->count(), 'StockSeeder should copy reorder_level from nkwen.json.');
    }

    public function test_branch_stock_endpoint_avoids_the_n_plus_one_problem(): void
    {
        DB::enableQueryLog();
        $this->getJson('/api/branches/NKW/stock')->assertOk();
        $queries = count(DB::getQueryLog());
        $this->assertLessThanOrEqual(4, $queries,
            "The stock list ran {$queries} queries. Load the products with with('product') instead of one query per row.");
    }
}
