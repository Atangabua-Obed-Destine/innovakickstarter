<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fellow_tracks', function (Blueprint $table) {
            if (!Schema::hasColumn('fellow_tracks', 'status')) {
                // Default 'approved' so existing enrollments keep working
                $table->string('status', 20)->default('approved')->after('is_primary')
                    ->comment('pending, approved, needs_revision, rejected');
                $table->index('status');
            }
            if (!Schema::hasColumn('fellow_tracks', 'motivation')) {
                $table->text('motivation')->nullable()->after('status')
                    ->comment('Fellow-supplied reason for wanting this track');
            }
            if (!Schema::hasColumn('fellow_tracks', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('motivation');
            }
            if (!Schema::hasColumn('fellow_tracks', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('requested_at');
            }
            if (!Schema::hasColumn('fellow_tracks', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('fellow_tracks', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_by');
            }
        });

        // Backfill existing rows: they were already usable, mark them approved and set requested_at.
        DB::table('fellow_tracks')
            ->whereNull('requested_at')
            ->update([
                'status' => 'approved',
                'requested_at' => DB::raw('COALESCE(started_at, created_at)'),
                'reviewed_at' => DB::raw('COALESCE(started_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('fellow_tracks', function (Blueprint $table) {
            foreach (['reviewed_by'] as $col) {
                if (Schema::hasColumn('fellow_tracks', $col)) {
                    $table->dropForeign(['reviewed_by']);
                    $table->dropColumn($col);
                }
            }
            foreach (['status', 'motivation', 'requested_at', 'reviewed_at', 'review_notes'] as $col) {
                if (Schema::hasColumn('fellow_tracks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
