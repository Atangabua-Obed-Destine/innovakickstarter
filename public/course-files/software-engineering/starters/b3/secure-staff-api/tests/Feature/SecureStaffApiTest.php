<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Project: Secure Multi-Role Staff API": the whole story, end to end, with real logins.
class SecureStaffApiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true; // includes StaffSeeder: the demo accounts

    private function login(string $email, string $password): string
    {
        $token = $this->postJson('/api/login', ['email' => $email, 'password' => $password])->assertOk()->json('token');
        $this->app['auth']->forgetGuards(); // the next request must prove itself with its own token

        return $token;
    }

    public function test_the_demo_head_office_account_can_log_in(): void
    {
        $token = $this->login('headoffice@nkwen.test', 'Nkwen-Demo-2026');
        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('data.role', 'head_office');
    }

    public function test_a_day_at_commercial_avenue(): void
    {
        // A new cashier signs up and logs in.
        $this->postJson('/api/register', [
            'name' => 'Ngwa Brenda', 'email' => 'ngwa.brenda@nkwen.test', 'branch' => 'CAV',
            'password' => 'Till-Pass-2026', 'password_confirmation' => 'Till-Pass-2026',
        ])->assertCreated();
        $cashier = $this->login('ngwa.brenda@nkwen.test', 'Till-Pass-2026');

        // She sells at her branch, not at another one.
        $sale = ['customer_name' => 'Mbah Clarisse', 'payment_method' => 'mtn_momo', 'items' => [['product_id' => 6, 'quantity' => 2], ['product_id' => 15, 'quantity' => 1]]];
        $order = $this->withToken($cashier)->postJson('/api/orders', ['branch' => 'CAV'] + $sale)->assertCreated()->json('data');
        $this->withToken($cashier)->postJson('/api/orders', ['branch' => 'ML4'] + $sale)->assertForbidden();

        // The customer pays by MTN MoMo; the provider calls back with a signed message.
        $reference = $this->withToken($cashier)->postJson("/api/orders/{$order['id']}/payments", ['phone' => '237670112233'])
            ->assertStatus(202)->json('data.reference');
        $raw = json_encode(['referenceId' => $reference, 'amount' => (string) $order['total_fcfa'], 'currency' => 'XAF', 'status' => 'SUCCESSFUL', 'financialTransactionId' => '555']);
        $this->call('POST', '/api/payments/momo/callback', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_NKWEN_SIGNATURE' => 'sha256='.hash_hmac('sha256', $raw, config('services.momo.webhook_secret')),
        ], $raw)->assertOk();

        // Head office sees the paid order; the cashier cannot see the staff list.
        $headOffice = $this->login('headoffice@nkwen.test', 'Nkwen-Demo-2026');
        $this->withToken($headOffice)->getJson("/api/orders/{$order['id']}")->assertOk()->assertJsonPath('data.status', 'paid');
        $this->app['auth']->forgetGuards(); // in tests, forget the last user before switching tokens
        $this->withToken($cashier)->getJson('/api/staff')->assertForbidden();
    }
}
