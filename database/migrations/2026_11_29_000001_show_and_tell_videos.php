<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Show & Tell: some activities (every chapter project) require a public YouTube video
 * in which the fellow explains the work. The video lives on the fellow's own channel;
 * only the link (and what YouTube says about it) is stored, inside the submission evidence.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->boolean('requires_video')->default(false)->after('is_collaborative');
        });
    }

    public function down(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->dropColumn('requires_video');
        });
    }
};
