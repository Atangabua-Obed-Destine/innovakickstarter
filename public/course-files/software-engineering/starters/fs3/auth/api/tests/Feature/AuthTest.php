<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The staff token API, the same as Backend Chapter 9.
class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function signUp(array $changes = [])
    {
        return $this->postJson('/api/register', array_merge([
            'name' => 'Ngwa Brenda',
            'email' => 'brenda@nkwen.test',
            'password' => 'Till-Pass-2026',
            'password_confirmation' => 'Till-Pass-2026',
            'branch' => 'CAV',
        ], $changes));
    }

    private function logIn(string $email = 'brenda@nkwen.test', string $password = 'Till-Pass-2026')
    {
        return $this->postJson('/api/login', ['email' => $email, 'password' => $password]);
    }

    public function test_a_new_cashier_signs_up_at_a_branch(): void
    {
        $response = $this->signUp();

        $response->assertStatus(201);
        $response->assertJsonPath('data.email', 'brenda@nkwen.test');
        $response->assertJsonPath('data.role', 'cashier');
        $response->assertJsonPath('data.branch', 'CAV');
        $response->assertJsonMissingPath('data.password');
        // The password is stored hashed, never as the plain text.
        $this->assertNotSame('Till-Pass-2026', User::where('email', 'brenda@nkwen.test')->first()->password);
    }

    public function test_sign_up_cannot_choose_its_own_role(): void
    {
        $this->signUp(['role' => 'head_office'])->assertStatus(201)->assertJsonPath('data.role', 'cashier');
    }

    public function test_sign_up_checks_the_email_password_and_branch(): void
    {
        $this->signUp();

        $response = $this->signUp(['password' => 'short', 'password_confirmation' => 'different', 'branch' => 'LMB']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password', 'branch']);
    }

    public function test_login_with_the_right_password_gives_a_token(): void
    {
        $this->signUp();

        $response = $this->logIn();

        $response->assertStatus(200);
        $response->assertJsonPath('data.name', 'Ngwa Brenda');
        $response->assertJsonPath('data.role', 'cashier');
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_the_demo_head_office_account_can_log_in(): void
    {
        $this->logIn('headoffice@nkwen.test', 'Nkwen-Demo-2026')->assertStatus(200)->assertJsonPath('data.role', 'head_office');
    }

    public function test_login_with_a_wrong_password_is_refused(): void
    {
        $this->signUp();

        $response = $this->logIn('brenda@nkwen.test', 'guess-1234');

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Wrong email or password.');
        $this->assertNull($response->json('token'));
    }

    public function test_the_token_says_who_is_logged_in(): void
    {
        $this->signUp();
        $token = $this->logIn()->json('token');

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonPath('data.email', 'brenda@nkwen.test');
    }

    public function test_without_a_token_the_api_answers_401_in_json(): void
    {
        $this->getJson('/api/me')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
        $this->postJson('/api/orders', [])->assertStatus(401);
    }

    public function test_logout_deletes_the_token(): void
    {
        $this->signUp();
        $token = $this->logIn()->json('token');

        $this->withToken($token)->postJson('/api/logout')->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards(); // forget the user remembered from the request above
        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }

    public function test_a_cashier_cannot_sell_at_another_branch(): void
    {
        $this->signUp(); // a cashier at Commercial Avenue
        $token = $this->logIn()->json('token');

        $response = $this->withToken($token)->postJson('/api/orders', [
            'branch' => 'ML4',
            'customer_name' => 'Mbah Clarisse',
            'payment_method' => 'cash',
            'items' => [['product_id' => 12, 'quantity' => 1]],
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You can only sell at your own branch.');
    }
}
