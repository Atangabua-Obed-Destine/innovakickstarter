<?php

namespace Tests\Feature;

use Tests\TestCase;

class NkwenCourseApiTest extends TestCase
{
    private string $base = '/api/course/nkwen';

    public function test_catalogue_filters_and_cors(): void
    {
        $r = $this->getJson("{$this->base}/products?category=grains&sort=-price", ['Origin' => 'https://fellow.github.io'])->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')->assertJsonPath('currency', 'FCFA');
        $this->assertSame('Rice 50kg', $r->json('products.0.name'));
        $this->assertSame(4, $r->json('count'));
        $this->assertSame(20, $this->getJson("{$this->base}/products")->json('count'));
        $this->getJson("{$this->base}/products?search=palm")->assertJsonPath('count', 2);
        $this->getJson("{$this->base}/products/6")->assertOk()->assertJsonPath('name', 'Rice 50kg');
        $this->getJson("{$this->base}/products/999")->assertNotFound();
        $this->getJson("{$this->base}/products?branch=XXX")->assertNotFound();
        $this->getJson("{$this->base}/branches")->assertOk()->assertJsonCount(5, 'branches')->assertJsonPath('branches.4.status', 'planned');
        $this->getJson("{$this->base}/legacy/price-list")->assertOk()->assertJsonPath('rows.0.price', '36,005 FCFA');
    }

    public function test_practice_staff_login_and_protected_stock(): void
    {
        $this->postJson("{$this->base}/login", ['email' => 'manager.nkw@nkwen.test', 'password' => 'wrong'])->assertStatus(401);
        $login = $this->postJson("{$this->base}/login", ['email' => 'manager.nkw@nkwen.test', 'password' => 'Nkwen-Demo-2026'])->assertOk()
            ->assertJsonPath('data.role', 'branch_manager')->assertJsonPath('data.branch', 'NKW');
        $auth = ['Authorization' => 'Bearer ' . $login->json('token')];
        $this->getJson("{$this->base}/me", $auth)->assertOk()->assertJsonPath('data.email', 'manager.nkw@nkwen.test');
        $this->getJson("{$this->base}/me")->assertStatus(401);
        $this->getJson("{$this->base}/me", ['Authorization' => 'Bearer ' . $login->json('token') . 'x'])->assertStatus(401);

        $stock = $this->getJson("{$this->base}/branches/NKW/stock", $auth)->assertOk()->assertJsonPath('meta.count', 20);
        $rice25 = collect($stock->json('data'))->firstWhere('name', 'Rice 25kg');
        $this->assertSame('RESTOCK', $rice25['status'], 'Rice 25kg at NKW: 5 bags at a reorder level of 5.');
        $this->assertSame('OK', collect($stock->json('data'))->firstWhere('name', 'Rice 50kg')['status']);
        $this->getJson("{$this->base}/branches/CAV/stock", $auth)->assertForbidden();
        $this->getJson("{$this->base}/branches/CAV/stock")->assertStatus(401);
        $ho = $this->postJson("{$this->base}/login", ['email' => 'headoffice@nkwen.test', 'password' => 'Nkwen-Demo-2026'])->json('token');
        $this->getJson("{$this->base}/branches/CAV/stock", ['Authorization' => "Bearer {$ho}"])->assertOk();
    }

    public function test_teaching_switches(): void
    {
        $this->getJson("{$this->base}/products?fail=503")->assertStatus(503);
        $this->getJson("{$this->base}/categories?fail=404")->assertNotFound();
        $this->getJson("{$this->base}/categories?fail=500")->assertStatus(500);
    }

    public function test_practice_orders_are_checked_and_never_saved(): void
    {
        $ok = $this->postJson("{$this->base}/orders", ['branch' => 'NKW', 'customer_name' => 'Ama', 'payment_method' => 'mtn_momo', 'items' => [['product_id' => 6, 'quantity' => 2], ['product_id' => 13, 'quantity' => 1]]])
            ->assertCreated()->assertJsonPath('order.saved', false)->assertJsonPath('order.status', 'awaiting_payment');
        $this->assertSame(36005 * 2 + 410, $ok->json('order.total_fcfa'));
        $this->postJson("{$this->base}/orders", ['branch' => 'LMB', 'items' => []])->assertStatus(422)->assertJsonValidationErrors(['branch', 'customer_name', 'payment_method', 'items']);
        $this->postJson("{$this->base}/orders", ['branch' => 'BAF', 'customer_name' => 'Ama', 'payment_method' => 'cash', 'items' => [['product_id' => 18, 'quantity' => 50]]])
            ->assertStatus(409)->assertJsonPath('available', 3);
        // Two lines of the same product are added up before checking stock (2 + 2 > 3 Onions at BAF).
        $this->postJson("{$this->base}/orders", ['branch' => 'BAF', 'customer_name' => 'Ama', 'payment_method' => 'cash', 'items' => [['product_id' => 18, 'quantity' => 2], ['product_id' => 18, 'quantity' => 2]]])
            ->assertStatus(409)->assertJsonPath('requested', 4)->assertJsonPath('available', 3);

        // Idempotency-Key: a retried order returns the first one, never a second order.
        $body = ['branch' => 'NKW', 'customer_name' => 'Ama', 'payment_method' => 'mtn_momo', 'items' => [['product_id' => 6, 'quantity' => 1]]];
        $first = $this->postJson("{$this->base}/orders", $body, ['Idempotency-Key' => 'rep-abc-1'])->assertCreated();
        $again = $this->postJson("{$this->base}/orders", $body, ['Idempotency-Key' => 'rep-abc-1'])->assertCreated()->assertHeader('Idempotent-Replayed', 'true');
        $this->assertSame($first->json('order.id'), $again->json('order.id'));
        $this->postJson("{$this->base}/orders", array_merge($body, ['customer_name' => 'Someone else']), ['Idempotency-Key' => 'rep-abc-1'])->assertStatus(422);
        $this->assertNotSame($first->json('order.id'), $this->postJson("{$this->base}/orders", $body)->json('order.id'), 'Without a key, every request is a new order.');
        $this->postJson("{$this->base}/orders", ['branch' => 'NKW', 'customer_name' => 'Ama', 'payment_method' => 'cash', 'items' => [['product_id' => 99, 'quantity' => 1]]])
            ->assertStatus(422)->assertJsonValidationErrors(['items.0.product_id']);
    }
}
