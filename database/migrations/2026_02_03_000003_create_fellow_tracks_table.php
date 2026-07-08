<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create fellow_tracks pivot table
 * 
 * This is the core table for Career Capital scoring. Each record represents
 * a fellow's enrollment in a specific track, with their current score and tier.
 * 
 * Key business rules (enforced in service layer):
 * - Only ONE primary track per fellow (is_primary = true)
 * - Sum of effort_allocation across all active tracks MUST = 100%
 * - Tier auto-calculated based on score + admin settings
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
        Schema::create('fellow_tracks', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow enrolled in this track');
            $table->uuid('track_id')
                ->comment('The track the fellow is enrolled in');
            
            // Career Capital Score
            $table->decimal('score', 5, 2)->default(0.00)
                ->comment('Career Capital score 0.00 to 100.00');
            $table->string('tier', 20)->default('rookie')
                ->comment('rookie, intern, professional, elite');
            
            // Score breakdown by category (denormalized for fast dashboard loading)
            $table->decimal('technical_score', 5, 2)->default(0.00);
            $table->decimal('interview_score', 5, 2)->default(0.00);
            $table->decimal('portfolio_score', 5, 2)->default(0.00);
            $table->decimal('collaboration_score', 5, 2)->default(0.00);
            $table->decimal('learning_score', 5, 2)->default(0.00);
            
            // Track configuration
            $table->boolean('is_primary')->default(false)
                ->comment('Only ONE primary track per fellow');
            $table->integer('effort_allocation')->default(100)
                ->comment('Percentage 0-100, sum across all tracks must = 100');
            
            // Points earned in this track
            $table->integer('total_points_earned')->default(0)
                ->comment('Cumulative points earned in this track');
            
            // Timestamps
            $table->timestamp('started_at')->useCurrent()
                ->comment('When fellow enrolled in this track');
            $table->timestamp('last_active_at')->nullable()
                ->comment('Last activity in this track');
            $table->timestamp('tier_promoted_at')->nullable()
                ->comment('When fellow was last promoted to current tier');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            // Unique constraint: Fellow can only enroll in each track once
            $table->unique(['fellow_id', 'track_id']);
            
            // Indexes for performance
            $table->index('score');
            $table->index('tier');
            $table->index('is_primary');
            $table->index(['fellow_id', 'is_primary']);
            $table->index(['track_id', 'tier']);
            $table->index(['track_id', 'score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_tracks');
    }
};
