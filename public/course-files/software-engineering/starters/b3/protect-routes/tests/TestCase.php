<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    // B3: log in as a member of staff for the next requests in this test (no password needed in tests).
    protected function actingAsStaff(string $role = 'head_office', ?string $branch = null): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'branch_code' => $role === 'head_office' ? null : ($branch ?? 'NKW'),
        ]);
        Sanctum::actingAs($user);

        return $user;
    }

    protected function actingAsHeadOffice(): User
    {
        return $this->actingAsStaff('head_office');
    }
}
