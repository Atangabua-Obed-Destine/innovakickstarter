<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Practice Lab: short self-checking challenges per track (SQL and Python run in
 * the fellow's browser, quizzes), with XP, a practice streak and a weekly
 * leaderboard. Expected answers are stored only as hashes (expected_hash) or,
 * for quizzes, as an index the page never receives.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('slug', 100);
            $table->string('title');
            $table->string('skill', 50)->nullable();
            $table->string('kind', 20); // sql | python | quiz
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedSmallInteger('xp')->default(10);
            $table->text('prompt');
            $table->text('starter_code')->nullable();
            $table->text('setup')->nullable();
            $table->text('hint')->nullable();
            $table->text('explanation')->nullable();
            $table->boolean('ordered')->default(false);
            $table->boolean('needs_pandas')->default(false);
            $table->json('options')->nullable();
            $table->unsignedTinyInteger('answer_index')->nullable();
            $table->string('expected_hash', 64)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['track_id', 'slug']);
        });

        Schema::create('practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('challenge_id')->constrained('practice_challenges')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('correct');
            $table->unsignedSmallInteger('xp_awarded')->default(0);
            $table->boolean('was_daily')->default(false);
            $table->date('practiced_on');
            $table->timestamps();

            $table->index(['fellow_id', 'practiced_on']);
            $table->index(['challenge_id', 'fellow_id', 'correct']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_attempts');
        Schema::dropIfExists('practice_challenges');
    }
};
