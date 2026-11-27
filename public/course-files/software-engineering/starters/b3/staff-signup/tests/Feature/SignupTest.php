<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Self-checks for "Building Signup with Hashed Passwords".
class SignupTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function signup(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ngwa Brenda',
            'email' => 'ngwa.brenda@nkwen.test',
            'password' => 'Till-Pass-2026',
            'password_confirmation' => 'Till-Pass-2026',
            'branch' => 'NKW',
        ], $overrides);
    }

    public function test_a_cashier_can_sign_up(): void
    {
        $this->postJson('/api/register', $this->signup())
            ->assertCreated()
            ->assertJsonPath('data.email', 'ngwa.brenda@nkwen.test')
            ->assertJsonPath('data.role', 'cashier')
            ->assertJsonPath('data.branch', 'NKW')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role', 'branch']]);
    }

    public function test_the_password_is_stored_as_a_hash_never_as_plain_text(): void
    {
        $this->postJson('/api/register', $this->signup())->assertCreated();
        $stored = User::where('email', 'ngwa.brenda@nkwen.test')->value('password');

        $this->assertNotSame('Till-Pass-2026', $stored, 'The password column must never hold the plain password.');
        $this->assertStringStartsWith('$2y$', $stored, 'Use Hash::make (bcrypt): bcrypt hashes start with $2y$.');
        $this->assertTrue(Hash::check('Till-Pass-2026', $stored), 'Hash::check should accept the right password.');
        $this->assertFalse(Hash::check('till-pass-2026', $stored), 'Hash::check should refuse a wrong password.');
    }

    public function test_two_people_with_the_same_password_get_different_hashes(): void
    {
        $this->postJson('/api/register', $this->signup())->assertCreated();
        $this->postJson('/api/register', $this->signup(['email' => 'che.divine@nkwen.test', 'name' => 'Che Divine']))->assertCreated();

        $hashes = User::whereIn('email', ['ngwa.brenda@nkwen.test', 'che.divine@nkwen.test'])->pluck('password');
        $this->assertNotSame($hashes[0], $hashes[1], 'Each hash has its own random salt, so the same password gives different hashes.');
    }

    public function test_the_response_never_contains_the_password(): void
    {
        $body = $this->postJson('/api/register', $this->signup())->assertCreated()->getContent();
        $this->assertStringNotContainsString('Till-Pass-2026', $body);
        $this->assertStringNotContainsString('password', $body, 'Do not send the password or its hash back.');
    }

    public function test_bad_signups_are_422(): void
    {
        $this->postJson('/api/register', $this->signup())->assertCreated();

        $this->postJson('/api/register', $this->signup())->assertStatus(422)->assertJsonValidationErrors('email'); // taken
        $this->postJson('/api/register', $this->signup(['email' => 'not-an-email']))->assertStatus(422)->assertJsonValidationErrors('email');
        $this->postJson('/api/register', $this->signup(['email' => 'a@nkwen.test', 'password' => 'short1', 'password_confirmation' => 'short1']))
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->postJson('/api/register', $this->signup(['email' => 'b@nkwen.test', 'password_confirmation' => 'Something-Else-1']))
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->postJson('/api/register', $this->signup(['email' => 'c@nkwen.test', 'branch' => 'LMB']))
            ->assertStatus(422)->assertJsonValidationErrors('branch');
    }

    public function test_nobody_can_choose_their_own_role(): void
    {
        $this->postJson('/api/register', $this->signup(['role' => 'head_office', 'is_active' => false]))
            ->assertCreated()
            ->assertJsonPath('data.role', 'cashier');
        $this->assertSame('cashier', User::where('email', 'ngwa.brenda@nkwen.test')->value('role'));
    }
}
