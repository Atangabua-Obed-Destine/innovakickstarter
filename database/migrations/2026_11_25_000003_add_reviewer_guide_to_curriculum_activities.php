<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-only notes for reviewing an activity: model answers, what a good
 * submission contains, common mistakes. Shown to reviewers and given to the
 * AI review as a reference; never shown to fellows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->longText('reviewer_guide')->nullable()->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->dropColumn('reviewer_guide');
        });
    }
};
