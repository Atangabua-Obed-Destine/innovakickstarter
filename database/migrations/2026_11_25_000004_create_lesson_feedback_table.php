<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** "Was this lesson clear?" answers from fellows, one per fellow and activity. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('curriculum_activity_id')->constrained('track_curriculum_activities')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_clear');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['curriculum_activity_id', 'fellow_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_feedback');
    }
};
