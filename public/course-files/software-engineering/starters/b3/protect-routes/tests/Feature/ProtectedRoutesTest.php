<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Self-checks for "Protecting Routes with Middleware".
class ProtectedRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_public_routes_need_no_token(): void
    {
        $this->getJson('/api/products')->assertOk();
        $this->getJson('/api/branches')->assertOk();
        $this->getJson('/api/branches/NKW/stock')->assertOk();
    }

    public function test_staff_routes_without_a_token_are_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401)->assertJsonStructure(['message']);
        $this->getJson('/api/orders')->assertStatus(401);
        $this->postJson('/api/orders', [])->assertStatus(401); // 401 comes before validation
        $this->postJson('/api/stock/restock', [])->assertStatus(401);
        $this->postJson('/api/products', [])->assertStatus(401);
    }

    public function test_a_made_up_token_is_401(): void
    {
        $this->withToken('1|this-is-not-a-real-token')->getJson('/api/orders')->assertStatus(401);
    }

    public function test_a_401_is_json_even_without_the_accept_header(): void
    {
        $response = $this->get('/api/orders'); // no "Accept: application/json"
        $response->assertStatus(401);
        $this->assertJson($response->getContent(), 'Expected JSON, not an HTML page or a redirect to a login page.');
    }

    public function test_a_valid_token_gets_in(): void
    {
        Sanctum::actingAs(User::factory()->cashier('NKW')->create());
        $this->getJson('/api/me')->assertOk()->assertJsonPath('data.role', 'cashier');
        $this->getJson('/api/orders')->assertOk();
    }

    public function test_a_deactivated_staff_member_is_403_even_with_a_valid_token(): void
    {
        $user = User::factory()->cashier('NKW')->create();
        $token = $user->createToken('till')->plainTextToken;
        $user->forceFill(['is_active' => false])->save(); // the cashier left the company

        $this->withToken($token)->getJson('/api/orders')
            ->assertStatus(403)
            ->assertJsonPath('message', 'This staff account is deactivated.');
    }
}
