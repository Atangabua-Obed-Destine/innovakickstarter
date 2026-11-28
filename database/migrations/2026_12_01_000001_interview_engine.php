<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mock interview engine: track blueprints (rounds, rubric, question bank) and the
 * server-side state of each interview (plan, context, scorecard, integrity, turns).
 * Old sessions keep their columns and still display.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_blueprints', function (Blueprint $table) {
            $table->id();
            $table->uuid('track_id')->unique();
            $table->unsignedInteger('version')->default(1);
            $table->json('data');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->foreign('track_id')->references('id')->on('tracks')->cascadeOnDelete();
        });

        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->string('engine_version', 10)->nullable()->after('is_practice');
            $table->json('plan')->nullable()->after('engine_version')->comment('Server-only: planned questions and turn state. Never sent to the browser.');
            $table->json('context')->nullable()->after('plan')->comment('Snapshot of what the interview was based on');
            $table->json('scorecard')->nullable()->after('context');
            $table->json('integrity')->nullable()->after('scorecard');
            $table->string('integrity_level', 10)->nullable()->after('integrity')->comment('clean, review, concern');
            $table->string('hire_signal', 15)->nullable()->after('integrity_level');
            $table->string('scoring_status', 15)->nullable()->after('hire_signal')->comment('queued, done, failed, manual');
            $table->unsignedInteger('current_turn')->default(0)->after('scoring_status');
            $table->timestamp('turn_started_at')->nullable()->after('current_turn');
            $table->timestamp('last_activity_at')->nullable()->after('turn_started_at');
            $table->timestamp('expires_at')->nullable()->after('last_activity_at');

            $table->index(['status', 'expires_at'], 'idx_interview_status_expires');
        });

        // Mentor interviews stored overall_score on a 1-10 scale next to a 0-100 score.
        // Everything is 0-100 now.
        DB::table('interview_sessions')
            ->whereIn('mode', ['human', 'peer'])
            ->whereNotNull('score')
            ->where('status', 'completed')
            ->update(['overall_score' => DB::raw('ROUND(score)')]);
    }

    public function down(): void
    {
        Schema::table('interview_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_interview_status_expires');
            $table->dropColumn(['engine_version', 'plan', 'context', 'scorecard', 'integrity', 'integrity_level', 'hire_signal',
                'scoring_status', 'current_turn', 'turn_started_at', 'last_activity_at', 'expires_at']);
        });
        Schema::dropIfExists('interview_blueprints');
    }
};
