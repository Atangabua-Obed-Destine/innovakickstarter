<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Career Capital reads the 0-100 `score` column of interview sessions.
 * One completion path only wrote `overall_score`; copy it across where
 * `score` is missing so those interviews count.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('interview_sessions')
            ->whereNull('score')
            ->whereNotNull('overall_score')
            ->where('status', 'completed')
            ->update(['score' => DB::raw('overall_score')]);
    }

    public function down(): void
    {
        // Data backfill; nothing to undo.
    }
};
