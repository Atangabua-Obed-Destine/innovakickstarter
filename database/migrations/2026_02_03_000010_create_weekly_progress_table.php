<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create weekly_progress table
 * 
 * Tracks fellow's weekly accountability across the 4 pillars:
 * - BUILD: Submit project/code
 * - BRAND: Publish content (blog, LinkedIn, Twitter)
 * - INTERVIEW: Complete mock interview
 * - COLLABORATE: Code reviews, mentoring
 * 
 * If any pillar is incomplete by Sunday 11:59 PM, score freezes.
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
        Schema::create('weekly_progress', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->uuid('track_id')
                ->comment('Primary track for this week');
            
            // Week identification
            $table->date('week_start')
                ->comment('Monday of the week (ISO week)');
            $table->date('week_end')
                ->comment('Sunday of the week');
            $table->integer('year');
            $table->integer('week_number')
                ->comment('ISO week number 1-52');
            
            // Pillar completion status
            $table->boolean('build_completed')->default(false);
            $table->boolean('brand_completed')->default(false);
            $table->boolean('interview_completed')->default(false);
            $table->boolean('collaborate_completed')->default(false);
            
            // Pillar activity references
            $table->uuid('build_activity_id')->nullable();
            $table->uuid('brand_activity_id')->nullable();
            $table->uuid('interview_activity_id')->nullable();
            $table->uuid('collaborate_activity_id')->nullable();
            
            // Timestamps for pillar completion
            $table->timestamp('build_completed_at')->nullable();
            $table->timestamp('brand_completed_at')->nullable();
            $table->timestamp('interview_completed_at')->nullable();
            $table->timestamp('collaborate_completed_at')->nullable();
            
            // Points earned this week
            $table->integer('build_points')->default(0);
            $table->integer('brand_points')->default(0);
            $table->integer('interview_points')->default(0);
            $table->integer('collaborate_points')->default(0);
            $table->integer('total_points')->default(0);
            
            // Week status
            $table->boolean('all_pillars_completed')->default(false)
                ->comment('True if all 4 pillars completed');
            $table->boolean('score_frozen')->default(false)
                ->comment('True if week ended without all pillars');
            $table->timestamp('score_frozen_at')->nullable();
            $table->timestamp('score_unfrozen_at')->nullable();
            
            // Notifications
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('freeze_warning_sent')->default(false);
            $table->timestamp('freeze_warning_sent_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            $table->foreign('build_activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('set null');
            
            $table->foreign('brand_activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('set null');
            
            $table->foreign('interview_activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('set null');
            
            $table->foreign('collaborate_activity_id')
                ->references('id')
                ->on('activities')
                ->onDelete('set null');
            
            // Unique constraint: One record per fellow per week
            $table->unique(['fellow_id', 'week_start']);
            
            // Indexes
            $table->index(['fellow_id', 'year', 'week_number']);
            $table->index('all_pillars_completed');
            $table->index('score_frozen');
            $table->index('week_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_progress');
    }
};
