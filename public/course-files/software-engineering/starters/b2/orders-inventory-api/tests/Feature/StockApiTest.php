<?php

namespace Tests\Feature;

use App\Models\StockLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Project: Orders & Inventory API" (stock and its history).
class StockApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;


    public function test_restock_adds_stock_and_writes_a_movement(): void
    {
        $before = (int) StockLevel::where('product_id', 6)->where('branch_code', 'ML4')->value('quantity');

        $this->postJson('/api/stock/restock', ['branch' => 'ML4', 'product_id' => 6, 'quantity' => 10, 'note' => 'Delivery from Douala'])
            ->assertCreated()
            ->assertJsonPath('data.change', 10)
            ->assertJsonPath('data.reason', 'restock')
            ->assertJsonPath('data.balance_after', $before + 10);

        $this->getJson('/api/branches/ML4/stock')->assertOk()
            ->assertJsonFragment(['product_id' => 6, 'quantity' => $before + 10]);
    }

    public function test_restock_validation(): void
    {
        $this->postJson('/api/stock/restock', ['branch' => 'LMB', 'product_id' => 6, 'quantity' => 10])
            ->assertStatus(422)->assertJsonValidationErrors('branch');
        $this->postJson('/api/stock/restock', ['branch' => 'NKW', 'product_id' => 6, 'quantity' => -5])
            ->assertStatus(422)->assertJsonValidationErrors('quantity');
    }

    public function test_movements_newest_first_with_filters(): void
    {
        $this->postJson('/api/stock/restock', ['branch' => 'NKW', 'product_id' => 6, 'quantity' => 4])->assertCreated();
        $this->postJson('/api/orders', [
            'branch' => 'NKW', 'customer_name' => 'Che Divine', 'payment_method' => 'cash',
            'items' => [['product_id' => 6, 'quantity' => 3]],
        ])->assertCreated();

        $response = $this->getJson('/api/stock-movements?branch=NKW&product_id=6')->assertOk();
        $response->assertJsonPath('data.0.reason', 'sale')
            ->assertJsonPath('data.0.change', -3)
            ->assertJsonPath('data.1.reason', 'restock')
            ->assertJsonPath('data.1.change', 4)
            ->assertJsonPath('data.2.reason', 'opening_stock');

        foreach ($response->json('data') as $movement) {
            $this->assertSame('NKW', $movement['branch']);
            $this->assertSame(6, $movement['product_id']);
        }
        // balance_after of the newest movement equals the stock level now
        $this->assertSame(
            (int) StockLevel::where('product_id', 6)->where('branch_code', 'NKW')->value('quantity'),
            $response->json('data.0.balance_after'),
        );
    }

    public function test_the_history_adds_up_to_the_stock(): void
    {
        $this->postJson('/api/stock/restock', ['branch' => 'CAV', 'product_id' => 3, 'quantity' => 7])->assertCreated();
        $this->postJson('/api/orders', [
            'branch' => 'CAV', 'customer_name' => 'Ambe Hotel', 'payment_method' => 'bank_transfer',
            'items' => [['product_id' => 3, 'quantity' => 5]],
        ])->assertCreated();

        $sum = collect($this->getJson('/api/stock-movements?branch=CAV&product_id=3')->json('data'))->sum('change');
        $level = (int) StockLevel::where('product_id', 3)->where('branch_code', 'CAV')->value('quantity');
        $this->assertSame($level, $sum, 'Adding up every movement for a product at a branch must give its stock level.');
    }
}
