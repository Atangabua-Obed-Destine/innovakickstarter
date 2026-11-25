<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI review of curriculum submissions (phase 1: shadow and assist modes).
 *
 * - ai_reviews: one AI review per submission attempt, with the evidence it
 *   read, the rubric scores, the integrity findings, the recommended decision
 *   and, once a human decides, the human decision for agreement statistics.
 * - submission_fingerprints: text fingerprints used to detect copying
 *   between fellows.
 * - track_curriculum_activities.ai_review_mode: per-activity override.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('progress_id');
            $table->uuid('curriculum_activity_id');
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);

            $table->string('status', 20)->default('queued')->comment('queued, running, completed, failed, skipped');
            $table->string('mode', 10)->comment('shadow or assist, at the time of review');
            $table->string('model', 60)->nullable();
            $table->string('prompt_version', 20)->nullable();

            $table->json('evidence')->nullable()->comment('What was read: GitHub, links, files, with excerpts');
            $table->json('checks')->nullable()->comment('Hard checks (required evidence, empty text, dead links)');
            $table->json('integrity')->nullable()->comment('Similarity matches, repo origin findings, disclosure');
            $table->json('criteria')->nullable()->comment('Per rubric criterion: score, met, reasoning, evidence');
            $table->decimal('weighted_score', 5, 2)->nullable();
            $table->string('confidence', 10)->nullable();
            $table->json('flags')->nullable();
            $table->boolean('requirements_met')->nullable();
            $table->json('missing_requirements')->nullable();
            $table->string('authenticity_concern', 10)->nullable();
            $table->text('summary')->nullable();
            $table->text('feedback_for_fellow')->nullable();
            $table->text('notes_for_reviewer')->nullable();

            $table->string('recommended_decision', 10)->nullable()->comment('approve, revise, escalate');
            $table->json('decision_reasons')->nullable();

            $table->boolean('revealed_before_decision')->default(false)
                ->comment('Reviewer looked at the AI verdict before deciding (excluded from agreement stats)');
            $table->string('human_decision', 10)->nullable()->comment('approve or reject');
            $table->foreignId('human_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('human_score')->nullable();
            $table->timestamp('human_decided_at')->nullable();

            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('progress_id')->references('id')->on('fellow_curriculum_progress')->cascadeOnDelete();
            $table->foreign('curriculum_activity_id')->references('id')->on('track_curriculum_activities')->cascadeOnDelete();
            $table->index(['progress_id', 'attempt_number']);
            $table->index(['status', 'recommended_decision']);
        });

        Schema::create('submission_fingerprints', function (Blueprint $table) {
            $table->id();
            $table->uuid('progress_id');
            $table->uuid('curriculum_activity_id');
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('kind', 10)->default('text');
            $table->json('hashes');
            $table->unsignedInteger('shingle_count');
            $table->string('repo', 200)->nullable()->comment('owner/repo when a GitHub repository was submitted');
            $table->timestamps();

            $table->foreign('progress_id')->references('id')->on('fellow_curriculum_progress')->cascadeOnDelete();
            $table->index(['curriculum_activity_id', 'kind']);
            $table->index('repo');
        });

        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->string('ai_review_mode', 10)->nullable()->after('requires_peer_review')
                ->comment('null = platform default; off, shadow, assist');
        });
    }

    public function down(): void
    {
        Schema::table('track_curriculum_activities', function (Blueprint $table) {
            $table->dropColumn('ai_review_mode');
        });
        Schema::dropIfExists('submission_fingerprints');
        Schema::dropIfExists('ai_reviews');
    }
};
