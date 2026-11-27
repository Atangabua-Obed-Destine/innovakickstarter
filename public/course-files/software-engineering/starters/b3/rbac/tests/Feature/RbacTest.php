<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\PlaceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Problem Solving 109: Role-Based Access Control".
// One test per row of the permission table in the README.
class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function orderAt(string $branch, string $payment = 'cash'): Order
    {
        return app(PlaceOrder::class)->handle($branch, 'Che Divine', $payment, [['product_id' => 17, 'quantity' => 1]]);
    }

    private function sale(string $branch): array
    {
        return ['branch' => $branch, 'customer_name' => 'Che Divine', 'payment_method' => 'cash', 'items' => [['product_id' => 17, 'quantity' => 1]]];
    }

    public function test_a_cashier_sells_only_at_their_own_branch(): void
    {
        $this->actingAsStaff('cashier', 'NKW');
        $this->postJson('/api/orders', $this->sale('NKW'))->assertCreated();
        $this->postJson('/api/orders', $this->sale('ML4'))->assertForbidden()->assertJsonStructure(['message']);
    }

    public function test_a_cashier_sees_only_their_own_branch_orders(): void
    {
        $mine = $this->orderAt('NKW');
        $theirs = $this->orderAt('CAV');
        $this->actingAsStaff('cashier', 'NKW');

        $this->getJson("/api/orders/{$mine->code}")->assertOk();
        $this->getJson("/api/orders/{$theirs->code}")->assertForbidden();
        $this->getJson('/api/orders?branch=CAV')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.branch', 'NKW');
    }

    public function test_head_office_sees_and_sells_everywhere(): void
    {
        $theirs = $this->orderAt('CAV');
        $this->actingAsHeadOffice();

        $this->getJson("/api/orders/{$theirs->code}")->assertOk();
        $this->postJson('/api/orders', $this->sale('BAF'))->assertCreated();
        $this->getJson('/api/orders')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_only_managers_and_head_office_cancel(): void
    {
        $order = $this->orderAt('NKW');

        $this->actingAsStaff('cashier', 'NKW');
        $this->patchJson("/api/orders/{$order->code}", ['status' => 'cancelled'])->assertForbidden();

        $this->actingAsStaff('branch_manager', 'CAV'); // a manager, but of another branch
        $this->patchJson("/api/orders/{$order->code}", ['status' => 'cancelled'])->assertForbidden();

        $this->actingAsStaff('branch_manager', 'NKW');
        $this->patchJson("/api/orders/{$order->code}", ['status' => 'cancelled'])->assertOk()->assertJsonPath('data.status', 'cancelled');
    }

    public function test_a_cashier_can_mark_a_cash_order_paid_but_not_a_mobile_money_one(): void
    {
        $cash = $this->orderAt('NKW');
        $momo = $this->orderAt('NKW', 'mtn_momo');
        $this->actingAsStaff('cashier', 'NKW');

        $this->patchJson("/api/orders/{$cash->code}", ['status' => 'paid'])->assertOk();
        $this->patchJson("/api/orders/{$momo->code}", ['status' => 'paid'])->assertStatus(409); // only a verified callback
    }

    public function test_stock_is_managed_by_the_branch_manager_or_head_office(): void
    {
        $delivery = ['branch' => 'NKW', 'product_id' => 6, 'quantity' => 5];

        $this->actingAsStaff('cashier', 'NKW');
        $this->postJson('/api/stock/restock', $delivery)->assertForbidden();
        $this->getJson('/api/stock-movements')->assertForbidden();

        $this->actingAsStaff('branch_manager', 'ML4');
        $this->postJson('/api/stock/restock', $delivery)->assertForbidden();
        $this->getJson('/api/stock-movements?branch=NKW')->assertForbidden();

        $this->actingAsStaff('branch_manager', 'NKW');
        $this->postJson('/api/stock/restock', $delivery)->assertCreated();
        $this->getJson('/api/stock-movements')->assertOk()->assertJsonPath('data.0.branch', 'NKW');

        $this->actingAsHeadOffice();
        $this->postJson('/api/stock/restock', $delivery)->assertCreated();
    }

    public function test_only_head_office_changes_products(): void
    {
        $this->actingAsStaff('branch_manager', 'NKW');
        $this->patchJson('/api/products/9', ['price_fcfa' => 800])->assertForbidden();

        $this->actingAsHeadOffice();
        $this->patchJson('/api/products/9', ['price_fcfa' => 800])->assertOk()->assertJsonPath('data.price_fcfa', 800);
    }

    public function test_staff_list(): void
    {
        $this->actingAsStaff('cashier', 'NKW');
        $this->getJson('/api/staff')->assertForbidden();

        $this->actingAsStaff('branch_manager', 'CAV');
        $rows = $this->getJson('/api/staff')->assertOk()->json('data');
        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('CAV', $row['branch'], 'A branch manager sees only their own branch staff.');
        }

        $this->actingAsHeadOffice();
        $this->getJson('/api/staff')->assertOk()->assertJsonPath('meta.count', \App\Models\User::count());
    }
}
