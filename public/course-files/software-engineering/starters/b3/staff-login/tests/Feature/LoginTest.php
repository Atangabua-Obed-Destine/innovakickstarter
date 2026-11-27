<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Self-checks for "Building Login & Sessions".
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function cashier(): User
    {
        return User::factory()->cashier('NKW')->create(['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Till-Pass-2026']);
    }

    public function test_login_gives_a_token_that_works(): void
    {
        $this->cashier();

        $token = $this->postJson('/api/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Till-Pass-2026'])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('data.role', 'cashier')
            ->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)->getJson('/api/me')->assertOk()->assertJsonPath('data.email', 'ngwa.brenda@nkwen.test');
    }

    public function test_wrong_password_and_unknown_email_get_the_same_401(): void
    {
        $this->cashier();

        $wrong = $this->postJson('/api/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Wrong-Pass-2026'])->assertStatus(401);
        $unknown = $this->postJson('/api/login', ['email' => 'nobody@nkwen.test', 'password' => 'Till-Pass-2026'])->assertStatus(401);
        $this->assertSame($wrong->json('message'), $unknown->json('message'),
            'Use the same message, so an attacker cannot find out which emails exist.');
        $this->assertArrayNotHasKey('token', $wrong->json());
    }

    public function test_too_many_wrong_passwords_are_slowed_down(): void
    {
        $this->cashier();
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Guess-'.$i])->assertStatus(401);
        }
        $this->postJson('/api/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Till-Pass-2026'])->assertStatus(429);
    }

    public function test_logout_kills_the_token(): void
    {
        $this->cashier();
        $token = $this->postJson('/api/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Till-Pass-2026'])->json('token');

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->app['auth']->forgetGuards(); // tests reuse the app: forget who was logged in, like a new request would

        $this->withToken($token)->getJson('/api/me')->assertStatus(401);
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        User::factory()->cashier('NKW')->deactivated()->create(['email' => 'left@nkwen.test', 'password' => 'Till-Pass-2026']);

        $this->postJson('/api/login', ['email' => 'left@nkwen.test', 'password' => 'Till-Pass-2026'])->assertStatus(403);
    }

    public function test_session_login_remembers_you_with_a_cookie(): void
    {
        $this->cashier();

        $this->getJson('/staff/me')->assertStatus(401);
        $this->postJson('/staff/login', ['email' => 'ngwa.brenda@nkwen.test', 'password' => 'Till-Pass-2026'])->assertOk();
        $this->getJson('/staff/me')->assertOk()->assertJsonPath('data.email', 'ngwa.brenda@nkwen.test'); // same session
        $this->postJson('/staff/logout')->assertOk();
        $this->getJson('/staff/me')->assertStatus(401);
    }
}
