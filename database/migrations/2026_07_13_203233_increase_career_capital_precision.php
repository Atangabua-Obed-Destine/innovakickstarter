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
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->decimal('score', 8, 3)->default(0.000)->change();
            $table->decimal('technical_score', 8, 3)->default(0.000)->change();
            $table->decimal('interview_score', 8, 3)->default(0.000)->change();
            $table->decimal('portfolio_score', 8, 3)->default(0.000)->change();
            $table->decimal('collaboration_score', 8, 3)->default(0.000)->change();
            $table->decimal('learning_score', 8, 3)->default(0.000)->change();
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->decimal('avg_score', 8, 3)->default(0.000)->change();
        });

        Schema::table('cohorts', function (Blueprint $table) {
            $table->decimal('avg_score', 8, 3)->default(0.000)->change();
        });

        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->decimal('score', 8, 3)->nullable()->change();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->decimal('previous_score', 8, 3)->nullable()->change();
            $table->decimal('new_score', 8, 3)->nullable()->change();
            $table->decimal('score_delta', 8, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->default(0.00)->change();
            $table->decimal('technical_score', 5, 2)->default(0.00)->change();
            $table->decimal('interview_score', 5, 2)->default(0.00)->change();
            $table->decimal('portfolio_score', 5, 2)->default(0.00)->change();
            $table->decimal('collaboration_score', 5, 2)->default(0.00)->change();
            $table->decimal('learning_score', 5, 2)->default(0.00)->change();
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->decimal('avg_score', 5, 2)->default(0.00)->change();
        });

        Schema::table('cohorts', function (Blueprint $table) {
            $table->decimal('avg_score', 5, 2)->default(0.00)->change();
        });

        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->change();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->decimal('previous_score', 5, 2)->nullable()->change();
            $table->decimal('new_score', 5, 2)->nullable()->change();
            $table->decimal('score_delta', 5, 2)->nullable()->change();
        });
    }
};
