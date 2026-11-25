<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Interactive lessons and track orientation:
 * - quick checks are flash cards linked to a lesson (and a step), so answering
 *   them in the lesson feeds the daily spaced review later;
 * - fellows tick lesson steps and self-checks as they go;
 * - each fellow does a track orientation (rules quiz + commitment) before Chapter 1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flash_cards', function (Blueprint $table) {
            $table->foreignUuid('curriculum_activity_id')->nullable()->after('track_id')->constrained('track_curriculum_activities')->nullOnDelete();
            $table->unsignedTinyInteger('after_step')->nullable()->after('curriculum_activity_id');
        });

        Schema::table('flash_answers', function (Blueprint $table) {
            $table->string('source', 10)->default('daily')->after('xp_awarded');
        });

        Schema::table('fellow_curriculum_progress', function (Blueprint $table) {
            $table->json('checked_steps')->nullable();
        });

        Schema::create('track_orientations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->json('sections_done')->nullable();
            $table->timestamp('quiz_passed_at')->nullable();
            $table->unsignedTinyInteger('quiz_attempts')->default(0);
            $table->json('commitment')->nullable();
            $table->timestamp('letter_opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['fellow_id', 'track_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_orientations');
        Schema::table('fellow_curriculum_progress', fn (Blueprint $table) => $table->dropColumn('checked_steps'));
        Schema::table('flash_answers', fn (Blueprint $table) => $table->dropColumn('source'));
        Schema::table('flash_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curriculum_activity_id');
            $table->dropColumn('after_step');
        });
    }
};
