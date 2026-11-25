<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily flash review (spaced repetition, Leitner boxes): 3 quick questions a
 * day from chapters the fellow has reached. Right answers come back later and
 * later (1, 3, 7, 14, 30 days); wrong ones come back tomorrow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('key', 80);
            $table->unsignedTinyInteger('chapter');
            $table->string('skill', 50)->nullable();
            $table->text('question');
            $table->json('options');
            $table->unsignedTinyInteger('answer_index');
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['track_id', 'key']);
        });

        Schema::create('flash_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('card_id')->constrained('flash_cards')->cascadeOnDelete();
            $table->unsignedTinyInteger('box')->default(1);
            $table->date('due_on');
            $table->date('last_reviewed_on')->nullable();
            $table->unsignedSmallInteger('times_right')->default(0);
            $table->unsignedSmallInteger('times_wrong')->default(0);
            $table->timestamps();
            $table->unique(['fellow_id', 'card_id']);
            $table->index(['fellow_id', 'due_on']);
        });

        Schema::create('flash_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('card_id')->constrained('flash_cards')->cascadeOnDelete();
            $table->boolean('correct');
            $table->unsignedSmallInteger('xp_awarded')->default(0);
            $table->date('answered_on');
            $table->timestamps();
            $table->index(['fellow_id', 'answered_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_answers');
        Schema::dropIfExists('flash_reviews');
        Schema::dropIfExists('flash_cards');
    }
};
