<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add enhanced interview features columns.
 * 
 * Adds support for:
 * - responses: Stores individual question responses with evaluations
 * - is_practice: Marks practice interviews that don't affect Career Capital
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interview_sessions', function (Blueprint $table) {
            // Store individual question responses with evaluations
            if (!Schema::hasColumn('interview_sessions', 'responses')) {
                $table->json('responses')->nullable()
                    ->comment('Array of question responses with evaluations')
                    ->after('questions');
            }
            
            // Mark practice interviews that don't affect Career Capital
            if (!Schema::hasColumn('interview_sessions', 'is_practice')) {
                $table->boolean('is_practice')->default(false)
                    ->comment('Practice mode interviews do not affect Career Capital')
                    ->after('points_earned');
            }
            
            // Difficulty level if not exists
            if (!Schema::hasColumn('interview_sessions', 'difficulty_level')) {
                $table->string('difficulty_level')->default('intermediate')
                    ->comment('beginner, intermediate, advanced')
                    ->after('difficulty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->dropColumn(['responses', 'is_practice', 'difficulty_level']);
        });
    }
};
