<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fellow_curriculum_progress', function (Blueprint $table) {
            $table->text('peer_review_bypass_reason')->nullable()->after('peer_review_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellow_curriculum_progress', function (Blueprint $table) {
            $table->dropColumn('peer_review_bypass_reason');
        });
    }
};
