<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create audit_logs table
 * 
 * CRITICAL: This table stores immutable audit trail for all Career Capital changes.
 * 
 * Every score change MUST include:
 * - Who: Admin user who made the change
 * - When: Exact timestamp
 * - What: Previous score → New score (with delta)
 * - Why: Justification note (minimum 10 characters)
 * 
 * This table is IMMUTABLE - records should never be updated or deleted.
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
        Schema::create('audit_logs', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // What was changed
            $table->string('auditable_type', 100)
                ->comment('Model class name, e.g., App\\Models\\FellowTrack');
            $table->uuid('auditable_id')
                ->comment('UUID of the record that was changed');
            
            // Who was affected
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow whose data was changed');
            $table->uuid('track_id')->nullable()
                ->comment('The track if Career Capital related');
            
            // Who made the change
            $table->foreignId('admin_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin/user who made the change');
            
            // Type of change
            $table->string('action', 30)
                ->comment('created, updated, deleted, approved, rejected, score_adjusted, tier_changed');
            $table->string('category', 30)->nullable()
                ->comment('career_capital, activity, interview, profile, track_switch');
            
            // Score changes (for Career Capital updates)
            $table->decimal('previous_score', 5, 2)->nullable();
            $table->decimal('new_score', 5, 2)->nullable();
            $table->decimal('score_delta', 5, 2)->nullable()
                ->comment('Difference: new_score - previous_score');
            $table->string('previous_tier', 20)->nullable();
            $table->string('new_tier', 20)->nullable();
            
            // What changed (detailed)
            $table->json('old_values')->nullable()
                ->comment('Previous values before change');
            $table->json('new_values')->nullable()
                ->comment('New values after change');
            $table->json('changed_fields')->nullable()
                ->comment('List of field names that were changed');
            
            // Justification (REQUIRED for score changes)
            $table->text('justification')
                ->comment('Reason for the change, minimum 10 characters');
            
            // Related activity (if score change due to activity approval)
            $table->uuid('related_activity_id')->nullable()
                ->comment('Activity that triggered this change');
            
            // Context
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            // Immutable timestamp (no updated_at)
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('set null');
            
            $table->foreign('related_activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('set null');
            
            // Indexes for querying audit history
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('fellow_id');
            $table->index('admin_id');
            $table->index('action');
            $table->index('category');
            $table->index('created_at');
            $table->index(['fellow_id', 'track_id']);
            $table->index(['fellow_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
