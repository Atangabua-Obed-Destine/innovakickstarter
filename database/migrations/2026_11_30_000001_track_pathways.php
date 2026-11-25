<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pathways: a track can have a shared core and several pathways (e.g. Software
 * Engineering: Frontend, Backend, Mobile, Full-stack). Chapters (milestones) tagged
 * with pathways apply only to fellows whose major includes them, or who took them as
 * a minor. Activities may also be tagged (role-specific work in a shared chapter).
 *
 * catch_up: lessons added to a chapter a fellow had already finished are recommended
 * catch-up for that fellow; they never block the chapter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_milestones', function (Blueprint $table) {
            $table->json('pathways')->nullable()->after('unlock_after_milestone_id');
        });
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->json('pathways')->nullable()->after('requires_video');
        });
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->string('pathway', 30)->nullable()->after('career_role');
            $table->json('minors')->nullable()->after('pathway');
            $table->json('stacks')->nullable()->after('minors');
            $table->timestamp('pathway_chosen_at')->nullable()->after('stacks');
        });
        Schema::table('fellow_curriculum_progress', function (Blueprint $table) {
            $table->boolean('catch_up')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('fellow_curriculum_progress', fn (Blueprint $t) => $t->dropColumn('catch_up'));
        Schema::table('fellow_tracks', fn (Blueprint $t) => $t->dropColumn(['pathway', 'minors', 'stacks', 'pathway_chosen_at']));
        Schema::table('track_curriculum_activities', fn (Blueprint $t) => $t->dropColumn('pathways'));
        Schema::table('track_milestones', fn (Blueprint $t) => $t->dropColumn('pathways'));
    }
};
