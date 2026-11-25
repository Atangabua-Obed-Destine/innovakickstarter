<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Weekly "Data Detective" cases: a story-driven mystery on the track's data,
 * investigated in an in-browser sandbox and answered on a form. Answers are
 * stored only as hashes (numbers) or hidden indexes (choices).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detective_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('slug', 100);
            $table->string('title');
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->unsignedSmallInteger('xp')->default(50);
            $table->text('story');
            $table->text('data_note')->nullable();
            $table->json('hints')->nullable();
            $table->json('questions');   // public: key, prompt, kind, unit, options
            $table->json('answer_key');  // private: key => sha256 of canonical number, or choice index
            $table->text('solution')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['track_id', 'slug']);
        });

        Schema::create('detective_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('case_id')->constrained('detective_cases')->cascadeOnDelete();
            $table->string('question_key', 40); // or "__reveal"
            $table->boolean('correct');
            $table->unsignedSmallInteger('xp_awarded')->default(0);
            $table->date('answered_on');
            $table->timestamps();
            $table->index(['fellow_id', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detective_answers');
        Schema::dropIfExists('detective_cases');
    }
};
