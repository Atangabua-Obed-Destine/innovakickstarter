<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Demo staff accounts for the automatic contract check and your Show & Tell video.
// TRAINING DATA ONLY: these passwords are public. Never seed known passwords into a real system.
class StaffSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Nkwen-Demo-2026';

    public function run(): void
    {
        $accounts = [
            ['email' => 'headoffice@nkwen.test', 'name' => 'Head Office Demo', 'role' => User::HEAD_OFFICE, 'branch_code' => null],
            ['email' => 'manager.nkw@nkwen.test', 'name' => 'Nkwen Manager Demo', 'role' => User::BRANCH_MANAGER, 'branch_code' => 'NKW'],
            ['email' => 'cashier.nkw@nkwen.test', 'name' => 'Nkwen Cashier Demo', 'role' => User::CASHIER, 'branch_code' => 'NKW'],
        ];

        foreach ($accounts as $a) {
            $user = User::firstOrNew(['email' => $a['email']]);
            $user->name = $a['name'];
            $user->password = self::DEMO_PASSWORD; // the 'hashed' cast hashes it before saving
            $user->branch_code = $a['branch_code'];
            $user->role = $a['role'];              // set directly: role is not mass assignable
            $user->is_active = true;
            $user->save();
        }
    }
}
