<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Project: Orders & Inventory API" (orders).
class OrdersApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;


    private function stockOf(int $productId, string $branch): int
    {
        return (int) StockLevel::where('product_id', $productId)->where('branch_code', $branch)->value('quantity');
    }

    private function goodOrder(array $overrides = []): array
    {
        return array_merge([
            'branch' => 'CAV',
            'customer_name' => 'Mbah Clarisse',
            'payment_method' => 'mtn_momo',
            'items' => [['product_id' => 6, 'quantity' => 2], ['product_id' => 15, 'quantity' => 1]],
        ], $overrides);
    }

    public function test_create_an_order(): void
    {
        $riceBefore = $this->stockOf(6, 'CAV');
        $rice = Product::find(6);
        $oil = Product::find(15);

        $response = $this->postJson('/api/orders', $this->goodOrder())->assertCreated();

        $response->assertJsonStructure(['data' => [
            'id', 'branch', 'customer_name', 'payment_method', 'status', 'total_fcfa', 'currency', 'created_at',
            'items' => [['product_id', 'name', 'quantity', 'unit_price_fcfa', 'line_total_fcfa']],
        ]]);
        $response->assertJsonPath('data.branch', 'CAV')
            ->assertJsonPath('data.status', 'awaiting_payment')
            ->assertJsonPath('data.items.0.unit_price_fcfa', $rice->price_fcfa)
            ->assertJsonPath('data.items.0.line_total_fcfa', 2 * $rice->price_fcfa)
            ->assertJsonPath('data.total_fcfa', 2 * $rice->price_fcfa + $oil->price_fcfa);

        $id = $response->json('data.id');
        $this->assertMatchesRegularExpression('/^ORD-[A-Z0-9]{8}$/', $id, 'The order id is a public code like ORD-7Q2K9X1B.');
        $response->assertHeader('Location', url("/api/orders/{$id}"));
        $this->assertSame($riceBefore - 2, $this->stockOf(6, 'CAV'), 'The order should take 2 bags of rice from CAV.');
        $this->assertSame(2, StockMovement::where('order_id', Order::where('code', $id)->value('id'))->count(), 'One sale movement per line.');
    }

    public function test_the_client_cannot_choose_the_price(): void
    {
        $rice = Product::find(6);
        $this->postJson('/api/orders', $this->goodOrder([
            'items' => [['product_id' => 6, 'quantity' => 1, 'unit_price_fcfa' => 1]],
        ]))->assertCreated()->assertJsonPath('data.items.0.unit_price_fcfa', $rice->price_fcfa);
    }

    public function test_old_orders_keep_their_price_when_the_price_changes(): void
    {
        $rice = Product::find(6);
        $id = $this->postJson('/api/orders', $this->goodOrder())->json('data.id');
        $this->patchJson('/api/products/6', ['price_fcfa' => $rice->price_fcfa + 1000])->assertOk();

        $this->getJson("/api/orders/{$id}")->assertJsonPath('data.items.0.unit_price_fcfa', $rice->price_fcfa);
    }

    public function test_a_cash_order_is_paid_at_the_counter(): void
    {
        $this->postJson('/api/orders', $this->goodOrder(['payment_method' => 'cash']))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pay_at_counter');
    }

    public function test_read_an_order_back(): void
    {
        $id = $this->postJson('/api/orders', $this->goodOrder())->json('data.id');

        $this->getJson("/api/orders/{$id}")
            ->assertOk()
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonCount(2, 'data.items');
        $this->getJson('/api/orders/ORD-NOTFOUND')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_list_orders_newest_first_with_filters(): void
    {
        $first = $this->postJson('/api/orders', $this->goodOrder())->json('data.id');
        $second = $this->postJson('/api/orders', $this->goodOrder(['branch' => 'NKW', 'payment_method' => 'cash', 'items' => [['product_id' => 17, 'quantity' => 1]]]))->json('data.id');

        $this->getJson('/api/orders')->assertOk()->assertJsonPath('data.0.id', $second)->assertJsonPath('meta.count', 2);
        $this->getJson('/api/orders?branch=CAV')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $first);
        $this->getJson('/api/orders?status=pay_at_counter')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $second);
        $this->getJson('/api/orders?status=lost')->assertStatus(422);
        $this->getJson('/api/orders?from=yesterday')->assertStatus(422);
    }

    public function test_short_stock_is_409_with_the_numbers_and_changes_nothing(): void
    {
        $riceBefore = $this->stockOf(6, 'CAV');
        $ordersBefore = Order::count();
        $available = $this->stockOf(19, 'CAV');

        $this->postJson('/api/orders', $this->goodOrder([
            'items' => [['product_id' => 6, 'quantity' => 1], ['product_id' => 19, 'quantity' => $available + 1]],
        ]))
            ->assertStatus(409)
            ->assertJson(['product_id' => 19, 'requested' => $available + 1, 'available' => $available])
            ->assertJsonStructure(['message']);

        $this->assertSame($riceBefore, $this->stockOf(6, 'CAV'), 'A refused order must not take any stock.');
        $this->assertSame($ordersBefore, Order::count(), 'A refused order must not be saved.');
    }

    public function test_invalid_orders_are_422(): void
    {
        $this->postJson('/api/orders', $this->goodOrder(['branch' => 'LMB']))
            ->assertStatus(422)->assertJsonValidationErrors('branch'); // planned, not open
        $this->postJson('/api/orders', $this->goodOrder(['items' => []]))
            ->assertStatus(422)->assertJsonValidationErrors('items');
        $this->postJson('/api/orders', $this->goodOrder(['items' => [['product_id' => 999, 'quantity' => 1]]]))
            ->assertStatus(422)->assertJsonValidationErrors('items.0.product_id');
        $this->postJson('/api/orders', $this->goodOrder(['items' => [['product_id' => 6, 'quantity' => 0]]]))
            ->assertStatus(422)->assertJsonValidationErrors('items.0.quantity');
        $this->postJson('/api/orders', $this->goodOrder(['payment_method' => 'credit']))
            ->assertStatus(422)->assertJsonValidationErrors('payment_method');
        $this->postJson('/api/orders', $this->goodOrder(['items' => [['product_id' => 6, 'quantity' => 1], ['product_id' => 6, 'quantity' => 1]]]))
            ->assertStatus(422);
    }

    public function test_the_same_idempotency_key_never_sells_twice(): void
    {
        $riceBefore = $this->stockOf(6, 'CAV');
        $headers = ['Idempotency-Key' => 'rep-tablet-7-0001'];

        $first = $this->postJson('/api/orders', $this->goodOrder(), $headers)->assertCreated();
        $again = $this->postJson('/api/orders', $this->goodOrder(), $headers)->assertCreated();

        $this->assertSame($first->json('data.id'), $again->json('data.id'), 'A repeated Idempotency-Key must return the first order.');
        $this->assertSame($riceBefore - 2, $this->stockOf(6, 'CAV'), 'The repeat must not take stock again.');
        $this->assertSame(1, Order::count());
    }

    public function test_status_changes_follow_the_rules(): void
    {
        $id = $this->postJson('/api/orders', $this->goodOrder(['payment_method' => 'cash']))->json('data.id');

        $this->patchJson("/api/orders/{$id}", ['status' => 'delivered'])->assertStatus(409); // not paid yet
        $this->patchJson("/api/orders/{$id}", ['status' => 'lost'])->assertStatus(422)->assertJsonValidationErrors('status');
        $this->patchJson("/api/orders/{$id}", ['status' => 'paid'])->assertOk()->assertJsonPath('data.status', 'paid');
        $this->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertStatus(409); // too late to cancel
        $this->patchJson("/api/orders/{$id}", ['status' => 'delivered'])->assertOk()->assertJsonPath('data.status', 'delivered');
        $this->patchJson('/api/orders/ORD-NOTFOUND', ['status' => 'paid'])->assertNotFound();
    }

    public function test_cancelling_gives_the_stock_back(): void
    {
        $riceBefore = $this->stockOf(6, 'CAV');
        $id = $this->postJson('/api/orders', $this->goodOrder())->json('data.id');

        $this->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertOk()->assertJsonPath('data.status', 'cancelled');

        $this->assertSame($riceBefore, $this->stockOf(6, 'CAV'), 'Cancelling should put the 2 bags back on the shelf.');
        $this->getJson('/api/stock-movements?branch=CAV&product_id=6')
            ->assertJsonPath('data.0.reason', 'cancel_return')
            ->assertJsonPath('data.0.change', 2)
            ->assertJsonPath('data.0.order_id', $id);
        $this->patchJson("/api/orders/{$id}", ['status' => 'paid'])->assertStatus(409);
    }

    public function test_a_product_with_history_cannot_be_deleted(): void
    {
        $this->deleteJson('/api/products/6')->assertStatus(409)->assertJsonStructure(['message']);
        $this->getJson('/api/products/6')->assertOk();
    }
}
