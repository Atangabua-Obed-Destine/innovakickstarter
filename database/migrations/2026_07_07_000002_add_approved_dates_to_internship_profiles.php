<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internship_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('internship_profiles', 'approved_start_date')) {
                $table->date('approved_start_date')->nullable()->after('review_notes');
            }
            if (!Schema::hasColumn('internship_profiles', 'approved_end_date')) {
                $table->date('approved_end_date')->nullable()->after('approved_start_date');
            }
            if (!Schema::hasColumn('internship_profiles', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('approved_end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internship_profiles', function (Blueprint $table) {
            foreach (['approved_start_date', 'approved_end_date', 'completed_at'] as $col) {
                if (Schema::hasColumn('internship_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
