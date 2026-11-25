<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track identity and story: a track's look, job-style level titles, story
 * setting and skill list (tracks.experience); each milestone's story chapter
 * (track_milestones.story: brief when it opens, debrief when completed); and
 * the skills each activity builds (track_curriculum_activities.skills).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->json('experience')->nullable();
        });
        Schema::table('track_milestones', function (Blueprint $table) {
            $table->json('story')->nullable();
        });
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->json('skills')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tracks', fn (Blueprint $table) => $table->dropColumn('experience'));
        Schema::table('track_milestones', fn (Blueprint $table) => $table->dropColumn('story'));
        Schema::table('track_curriculum_activities', fn (Blueprint $table) => $table->dropColumn('skills'));
    }
};
