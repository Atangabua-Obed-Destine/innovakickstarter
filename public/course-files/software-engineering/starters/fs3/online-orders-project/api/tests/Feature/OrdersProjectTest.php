<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The project's new endpoints: the branch's orders, one order, and changing the status.
 */
class OrdersProjectTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function staff(string $role, ?string $branch): User
    {
        $user = User::factory()->create(['branch_code' => $branch]);
        $user->forceFill(['role' => $role])->save(); // role is not fillable, so set it directly

        return $user;
    }

    // Places the reference order (2 x Rice 50kg + 1 x Palm Oil 5L) as this member of staff, and returns its id.
    private function placeOrder(User $by, string $branch = 'CAV', string $method = 'mtn_momo'): string
    {
        return $this->actingAs($by, 'sanctum')->postJson('/api/orders', [
            'branch' => $branch,
            'customer_name' => 'Mbah Clarisse',
            'payment_method' => $method,
            'items' => [['product_id' => 6, 'quantity' => 2], ['product_id' => 15, 'quantity' => 1]],
        ])->assertStatus(201)->json('data.id');
    }

    public function test_staff_see_only_their_branch_orders_newest_first(): void
    {
        $cav = $this->staff('cashier', 'CAV');
        $first = $this->placeOrder($cav);
        $second = $this->placeOrder($cav, 'CAV', 'cash');
        $this->placeOrder($this->staff('cashier', 'NKW'), 'NKW');

        $response = $this->actingAs($cav, 'sanctum')->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonPath('meta.count', 2);
        $response->assertJsonPath('data.0.id', $second);
        $response->assertJsonPath('data.1.id', $first);
    }

    public function test_head_office_sees_every_branch_and_can_filter(): void
    {
        $this->placeOrder($this->staff('cashier', 'CAV'));
        $this->placeOrder($this->staff('cashier', 'NKW'), 'NKW');
        $office = $this->staff('head_office', null);

        $this->actingAs($office, 'sanctum')->getJson('/api/orders')->assertJsonPath('meta.count', 2);
        $this->actingAs($office, 'sanctum')->getJson('/api/orders?branch=NKW')->assertJsonPath('meta.count', 1)->assertJsonPath('data.0.branch', 'NKW');
    }

    public function test_an_order_from_another_branch_is_403_and_an_unknown_one_404(): void
    {
        $id = $this->placeOrder($this->staff('cashier', 'CAV'));
        $nkw = $this->staff('cashier', 'NKW');

        $this->actingAs($nkw, 'sanctum')->getJson("/api/orders/{$id}")->assertStatus(403);
        $this->actingAs($nkw, 'sanctum')->getJson('/api/orders/ORD-NOTFOUND')->assertStatus(404);
    }

    public function test_a_cashier_cannot_cancel_but_the_branch_manager_can(): void
    {
        $id = $this->placeOrder($this->staff('cashier', 'CAV'));

        $this->actingAs($this->staff('cashier', 'CAV'), 'sanctum')->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertStatus(403);
        $this->actingAs($this->staff('branch_manager', 'NKW'), 'sanctum')->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertStatus(403);

        $response = $this->actingAs($this->staff('branch_manager', 'CAV'), 'sanctum')->patchJson("/api/orders/{$id}", ['status' => 'cancelled']);

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'cancelled');
    }

    public function test_cancelling_puts_the_stock_back_once(): void
    {
        $manager = $this->staff('branch_manager', 'CAV');
        $id = $this->placeOrder($manager);
        $this->assertDatabaseHas('stock_levels', ['product_id' => 6, 'branch_code' => 'CAV', 'quantity' => 56]);

        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertStatus(200);
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$id}", ['status' => 'cancelled'])->assertStatus(200); // asking twice is safe

        $this->assertDatabaseHas('stock_levels', ['product_id' => 6, 'branch_code' => 'CAV', 'quantity' => 58]);
        $this->assertDatabaseHas('stock_levels', ['product_id' => 15, 'branch_code' => 'CAV', 'quantity' => 31]);
    }

    public function test_status_changes_follow_the_rules(): void
    {
        $manager = $this->staff('branch_manager', 'CAV');
        $cash = $this->placeOrder($manager, 'CAV', 'cash');
        $momo = $this->placeOrder($manager);

        // Cash: paid at the counter, then delivered.
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$cash}", ['status' => 'paid'])->assertStatus(200);
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$cash}", ['status' => 'delivered'])->assertStatus(200);
        // A delivered order cannot be cancelled: 409.
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$cash}", ['status' => 'cancelled'])->assertStatus(409);
        // Mobile money becomes paid only through the payment callback: 409.
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$momo}", ['status' => 'paid'])->assertStatus(409);
        // An unknown status: 422.
        $this->actingAs($manager, 'sanctum')->patchJson("/api/orders/{$momo}", ['status' => 'lost'])->assertStatus(422)->assertJsonValidationErrors(['status']);

        $this->assertSame('awaiting_payment', Order::where('code', $momo)->first()->status);
    }
}
