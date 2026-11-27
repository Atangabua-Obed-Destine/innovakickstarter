<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Staff accounts: every user is a member of staff with one role.
// Laravel's users table already has name, email and password (for the HASH, never the password itself).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // TODO: role: text up to 20, default 'cashier' (cashier, branch_manager or head_office)
            // TODO: branch_code: 3 characters, nullable (head office has no branch), foreign key to branches.code
            // TODO: is_active: boolean, default true (false = the staff member has left)
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_code']);
            $table->dropColumn(['role', 'branch_code', 'is_active']);
        });
    }
};
