<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add curriculum integration fields to interview_sessions table.
 * 
 * These columns bridge the interview system with the curriculum system,
 * allowing mock_interview curriculum activities to auto-create and track
 * interview sessions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->uuid('curriculum_activity_id')->nullable()->after('difficulty_level')
                ->comment('FK to track_curriculum_activities — links this interview to a curriculum activity');

            $table->uuid('curriculum_progress_id')->nullable()->after('curriculum_activity_id')
                ->comment('FK to fellow_curriculum_progress — links to the fellows progress record');

            // Foreign keys
            $table->foreign('curriculum_activity_id')
                ->references('id')
                ->on('track_curriculum_activities')
                ->onDelete('set null');

            $table->foreign('curriculum_progress_id')
                ->references('id')
                ->on('fellow_curriculum_progress')
                ->onDelete('set null');

            // Index for efficient lookups
            $table->index(['curriculum_activity_id', 'fellow_id'], 'idx_interview_curriculum_activity');
        });
    }

    public function down(): void
    {
        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->dropForeign(['curriculum_activity_id']);
            $table->dropForeign(['curriculum_progress_id']);
            $table->dropIndex('idx_interview_curriculum_activity');
            $table->dropColumn(['curriculum_activity_id', 'curriculum_progress_id']);
        });
    }
};
