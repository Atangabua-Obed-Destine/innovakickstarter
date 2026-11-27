<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    // B3: staff roles for tests, for example User::factory()->branchManager('CAV')->create()
    public function cashier(string $branch = 'NKW'): static
    {
        return $this->state(fn () => ['role' => 'cashier', 'branch_code' => $branch]);
    }

    public function branchManager(string $branch = 'NKW'): static
    {
        return $this->state(fn () => ['role' => 'branch_manager', 'branch_code' => $branch]);
    }

    public function headOffice(): static
    {
        return $this->state(fn () => ['role' => 'head_office', 'branch_code' => null]);
    }

    public function deactivated(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
