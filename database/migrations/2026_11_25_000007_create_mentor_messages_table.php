<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Conversations with the AI track mentor, kept per page context so it remembers the thread. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentor_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('context_type', 20)->default('general'); // general | activity | challenge
            $table->string('context_id', 64)->nullable();
            $table->string('role', 12); // user | assistant
            $table->text('content');
            $table->string('model', 80)->nullable();
            $table->timestamps();

            $table->index(['fellow_id', 'context_type', 'context_id']);
            $table->index(['fellow_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentor_messages');
    }
};
