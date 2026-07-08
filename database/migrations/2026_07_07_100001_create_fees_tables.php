<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── fees ────────────────────────────────────────────────
        Schema::create('fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference', 40)->unique()->comment('FEE-YYYYMM-NNNNNN');

            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic billable (InternshipProfile, Cohort, Program, Track, or null for one-off)
            $table->string('billable_type', 100)->nullable();
            $table->string('billable_id', 40)->nullable();
            $table->index(['billable_type', 'billable_id']);

            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->decimal('amount_total', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('currency', 3)->default('XAF');

            $table->string('plan_type', 20)->default('one_time')->comment('one_time|installments');
            $table->unsignedSmallInteger('installments_count')->nullable();
            $table->string('installment_cadence', 20)->nullable()->comment('weekly|biweekly|monthly|custom');

            $table->date('first_due_date');
            $table->date('final_due_date');
            $table->unsignedSmallInteger('grace_period_hours')->nullable();

            $table->string('status', 20)->default('active')
                ->comment('active|partially_paid|paid|overdue|waived');

            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waived_at')->nullable();
            $table->text('waived_reason')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('final_due_date');
            $table->index(['fellow_id', 'status']);
        });

        // ── fee_installments ────────────────────────────────────
        Schema::create('fee_installments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fee_id');
            $table->foreign('fee_id')->references('id')->on('fees')->cascadeOnDelete();

            $table->unsignedSmallInteger('sequence');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_date');
            $table->string('status', 20)->default('upcoming')
                ->comment('upcoming|due|partial|paid|overdue');

            $table->timestamps();

            $table->unique(['fee_id', 'sequence']);
            $table->index('status');
            $table->index('due_date');
        });

        // ── fee_payments ────────────────────────────────────────
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('verify_uuid')->unique()->comment('Public verification token');
            $table->string('receipt_number', 40)->nullable()->unique()
                ->comment('IKS-YYYYMM-NNNNNN, assigned on verification');
            $table->string('reference', 60)->nullable()->comment('Transaction ID entered by fellow or admin');

            $table->uuid('fee_id');
            $table->foreign('fee_id')->references('id')->on('fees')->cascadeOnDelete();

            $table->uuid('installment_id')->nullable();
            $table->foreign('installment_id')->references('id')->on('fee_installments')->nullOnDelete();

            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('method', 30)->comment('cash|mtn_momo|orange_money|bank_transfer|other');
            $table->date('payment_date');
            $table->string('source', 20)->default('fellow_upload')
                ->comment('admin_entry|fellow_upload');
            $table->string('receipt_path', 500)->nullable();
            $table->text('notes')->nullable();

            $table->string('status', 20)->default('submitted')
                ->comment('submitted|verified|rejected');

            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['fee_id', 'status']);
            $table->index(['fellow_id', 'status']);
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_installments');
        Schema::dropIfExists('fees');
    }
};
