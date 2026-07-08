<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create subscriptions table
 * 
 * Manages recruiter subscription tiers:
 * - Free: XAF 0 (20 profiles/month)
 * - Partner: XAF 300,000/year (~$500)
 * - Premium: XAF 1,200,000/year (~$2,000)
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationship
            $table->foreignId('recruiter_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The recruiter who owns this subscription');
            
            // Subscription tier
            $table->string('tier', 20)->default('free')
                ->comment('free, partner, premium');
            
            // Billing details
            $table->integer('amount')->default(0)
                ->comment('Amount in XAF');
            $table->string('currency', 3)->default('XAF');
            $table->string('billing_cycle', 20)->nullable()
                ->comment('monthly, yearly');
            
            // Status
            $table->string('status', 20)->default('active')
                ->comment('active, cancelled, expired, trial, past_due, paused');
            
            // Payment provider references
            $table->string('stripe_subscription_id')->nullable()
                ->comment('Stripe subscription ID for international payments');
            $table->string('stripe_customer_id')->nullable();
            $table->string('paystack_subscription_id')->nullable()
                ->comment('Paystack for African payments');
            $table->string('payment_method', 30)->nullable()
                ->comment('stripe, paystack, bank_transfer, mobile_money');
            
            // Dates
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Usage tracking
            $table->integer('profiles_viewed_this_month')->default(0);
            $table->integer('intros_requested_this_month')->default(0);
            $table->integer('downloads_this_month')->default(0);
            $table->date('usage_reset_date')->nullable()
                ->comment('Date when monthly usage counters reset');
            
            // Limits (can override defaults from admin_settings)
            $table->integer('profile_view_limit')->nullable()
                ->comment('Override default limit for this subscription');
            $table->integer('intro_request_limit')->nullable();
            $table->integer('download_limit')->nullable();
            
            // Additional features
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('has_custom_branding')->default(false);
            
            // Notes
            $table->text('notes')->nullable()
                ->comment('Admin notes about this subscription');
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('tier');
            $table->index('status');
            $table->index('expires_at');
            $table->index(['tier', 'status']);
            $table->index('stripe_subscription_id');
            $table->index('paystack_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
