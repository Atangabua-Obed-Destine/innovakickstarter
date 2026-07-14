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
        Schema::create('fellow_activity_peer_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('progress_id');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->string('status', 30)->default('pending'); // pending, completed, bypassed
            $table->integer('score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('progress_id')
                ->references('id')
                ->on('fellow_curriculum_progress')
                ->onDelete('cascade');
                
            $table->unique(['progress_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fellow_activity_peer_reviews');
    }
};
