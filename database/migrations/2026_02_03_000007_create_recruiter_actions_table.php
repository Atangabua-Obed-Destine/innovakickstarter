<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create recruiter_actions table
 * 
 * Tracks all recruiter interactions with fellows for analytics.
 * Used for:
 * - Tracking recruiter engagement metrics
 * - Preventing duplicate intro requests
 * - Showing fellows who viewed their profile
 * - Building hiring funnel analytics
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
        Schema::create('recruiter_actions', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('recruiter_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The recruiter performing the action');
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow being acted upon');
            
            // Action type (hiring funnel stages)
            $table->string('action', 30)
                ->comment('viewed, saved, intro_requested, intro_approved, contacted, interviewed, offered, hired, rejected');
            
            // Context
            $table->uuid('track_id')->nullable()
                ->comment('Which track the recruiter was interested in');
            $table->string('source', 50)->nullable()
                ->comment('How they found the fellow: search, featured, recommendation');
            $table->json('search_filters')->nullable()
                ->comment('Filters used when they found this fellow');
            
            // Notes
            $table->text('notes')->nullable()
                ->comment('Recruiter notes about this candidate');
            
            // For intro requests
            $table->string('intro_status', 20)->nullable()
                ->comment('pending, approved, rejected (for intro_requested action)');
            $table->text('intro_message')->nullable()
                ->comment('Message sent with intro request');
            $table->timestamp('intro_responded_at')->nullable();
            $table->foreignId('intro_responded_by')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin who responded to intro request');
            
            // For hiring outcomes
            $table->date('interview_date')->nullable();
            $table->date('offer_date')->nullable();
            $table->date('hire_date')->nullable();
            $table->integer('offered_salary')->nullable()
                ->comment('Offered salary in XAF');
            $table->string('job_title', 100)->nullable();
            $table->string('company_name', 100)->nullable();
            
            // Pipeline management
            $table->string('pipeline_stage', 50)->nullable()
                ->comment('Custom stage name in recruiter pipeline');
            $table->string('pipeline_folder', 100)->nullable()
                ->comment('Custom folder, e.g., "Fintech Hires Q1"');
            
            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('set null');
            
            // Indexes
            $table->index('action');
            $table->index('intro_status');
            $table->index(['recruiter_id', 'action']);
            $table->index(['recruiter_id', 'fellow_id']);
            $table->index(['fellow_id', 'action']);
            $table->index(['recruiter_id', 'created_at']);
            $table->index('pipeline_folder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_actions');
    }
};
