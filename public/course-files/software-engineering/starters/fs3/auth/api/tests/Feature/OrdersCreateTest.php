<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * POST /api/orders, as in ORDERS_API_REFERENCE.md.
 * Run these after the auth lesson's migration exists (users have a role and a branch).
 * actingAs($cashier, 'sanctum') logs a cashier in for the test, without a real token.
 */
class OrdersCreateTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function order(array $changes = []): array
    {
        return array_merge([
            'branch' => 'CAV',
            'customer_name' => 'Mbah Clarisse',
            'payment_method' => 'mtn_momo',
            'items' => [
                ['product_id' => 6, 'quantity' => 2],
                ['product_id' => 15, 'quantity' => 1],
            ],
        ], $changes);
    }

    // A cashier at Commercial Avenue (or at another branch).
    private function cashier(string $branch = 'CAV')
    {
        return $this->actingAs(User::factory()->create(['branch_code' => $branch]), 'sanctum');
    }

    public function test_an_order_is_priced_by_the_server_and_saved(): void
    {
        $response = $this->cashier()->postJson('/api/orders', $this->order());

        $response->assertStatus(201);
        $response->assertJsonPath('data.branch', 'CAV');
        $response->assertJsonPath('data.status', 'awaiting_payment');
        $response->assertJsonPath('data.items.0.name', 'Rice 50kg');
        $response->assertJsonPath('data.items.0.unit_price_fcfa', 36005);
        $response->assertJsonPath('data.items.0.line_total_fcfa', 72010);
        $response->assertJsonPath('data.total_fcfa', 78610);
        $response->assertJsonPath('data.currency', 'FCFA');
        $this->assertMatchesRegularExpression('/^ORD-[A-Z0-9]{8}$/', $response->json('data.id'));
        $this->assertStringEndsWith('/api/orders/'.$response->json('data.id'), $response->headers->get('Location'));
        $this->assertDatabaseHas('orders', ['code' => $response->json('data.id'), 'total_fcfa' => 78610]);
        $this->assertDatabaseCount('order_lines', 2);
    }

    public function test_the_order_takes_the_stock_off_the_shelf(): void
    {
        $this->cashier()->postJson('/api/orders', $this->order())->assertStatus(201);

        $this->assertDatabaseHas('stock_levels', ['product_id' => 6, 'branch_code' => 'CAV', 'quantity' => 56]);
        $this->assertDatabaseHas('stock_levels', ['product_id' => 15, 'branch_code' => 'CAV', 'quantity' => 30]);
    }

    public function test_cash_orders_are_paid_at_the_counter(): void
    {
        $response = $this->cashier()->postJson('/api/orders', $this->order(['payment_method' => 'cash']));

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'pay_at_counter');
    }

    public function test_a_price_sent_by_the_browser_is_ignored(): void
    {
        $order = $this->order(['items' => [['product_id' => 6, 'quantity' => 1, 'unit_price_fcfa' => 1]]]);

        $this->cashier()->postJson('/api/orders', $order)->assertJsonPath('data.total_fcfa', 36005);
    }

    public function test_a_bad_order_is_422_with_every_problem(): void
    {
        $bad = $this->order(['branch' => 'LMB', 'customer_name' => '', 'payment_method' => 'bitcoin', 'items' => [['product_id' => 6, 'quantity' => 0]]]);

        $response = $this->actingAs(User::factory()->create(['branch_code' => 'CAV'])->forceFill(['role' => 'head_office']), 'sanctum')
            ->postJson('/api/orders', $bad);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['branch', 'customer_name', 'payment_method', 'items.0.quantity']);
    }

    public function test_not_enough_stock_is_409_and_changes_nothing(): void
    {
        // Mile 4 has 3 bags of Rice 50kg. Ask for 2 sugar (fine) and 5 rice (too many).
        $order = $this->order(['branch' => 'ML4', 'items' => [['product_id' => 9, 'quantity' => 2], ['product_id' => 6, 'quantity' => 5]]]);

        $response = $this->cashier('ML4')->postJson('/api/orders', $order);

        $response->assertStatus(409);
        $response->assertJson(['product_id' => 6, 'requested' => 5, 'available' => 3]);
        // All or nothing: the sugar must still be on the shelf, and no order saved.
        $this->assertDatabaseHas('stock_levels', ['product_id' => 9, 'branch_code' => 'ML4', 'quantity' => 26]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_the_same_idempotency_key_never_sells_twice(): void
    {
        $first = $this->cashier()->withHeader('Idempotency-Key', 'tap-1234')->postJson('/api/orders', $this->order());
        $again = $this->withHeader('Idempotency-Key', 'tap-1234')->postJson('/api/orders', $this->order());

        $this->assertSame($first->json('data.id'), $again->json('data.id'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('stock_levels', ['product_id' => 6, 'branch_code' => 'CAV', 'quantity' => 56]);
    }
}
