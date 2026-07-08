<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internship_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('internship_profiles', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('internship_profiles', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('internship_profiles', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internship_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('internship_profiles', 'reviewed_by')) {
                $table->dropForeign(['reviewed_by']);
                $table->dropColumn('reviewed_by');
            }
            if (Schema::hasColumn('internship_profiles', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('internship_profiles', 'review_notes')) {
                $table->dropColumn('review_notes');
            }
        });
    }
};
