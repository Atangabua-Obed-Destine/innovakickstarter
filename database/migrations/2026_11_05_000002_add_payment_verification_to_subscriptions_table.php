<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paid recruiter plans start as `pending` requests carrying the recruiter's
 * payment reference, and become active only when an admin confirms payment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_reference', 120)->nullable()->after('payment_method');
            $table->unsignedSmallInteger('requested_months')->nullable()->after('payment_reference')
                ->comment('Plan length requested; the period starts when payment is confirmed');
            $table->foreignId('verified_by')->nullable()->after('requested_months')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['payment_reference', 'requested_months', 'verified_at', 'rejection_reason']);
        });
    }
};
