<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** "Explain it back": a fellow explains a lesson's key idea in their own words; the AI mentor gives feedback. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('curriculum_activity_id')->constrained('track_curriculum_activities')->cascadeOnDelete();
            $table->text('text');
            $table->unsignedTinyInteger('score')->nullable(); // 1-5
            $table->json('feedback')->nullable();
            $table->boolean('correct')->default(false); // score >= 3
            $table->unsignedSmallInteger('xp_awarded')->default(0);
            $table->date('answered_on');
            $table->string('model', 80)->nullable();
            $table->timestamps();
            $table->index(['fellow_id', 'curriculum_activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('explanations');
    }
};
