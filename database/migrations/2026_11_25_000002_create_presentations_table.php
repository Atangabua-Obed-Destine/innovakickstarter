<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Presentations a fellow gave (project demos, workshops, talks...), submitted
 * any time and reviewed by the I-NNOVA team. An approved presentation adds the
 * amounts the reviewer chose to one or more Career Capital categories of the
 * track it was submitted for (`category_boosts`, e.g. {"technical": 12}).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();

            $table->string('title');
            $table->string('kind', 30);
            $table->date('presented_at');
            $table->string('event_name');
            $table->string('audience')->nullable();
            $table->text('summary');
            $table->text('learnings')->nullable();
            $table->string('co_presenters', 500)->nullable();
            $table->json('links')->nullable();
            $table->json('files')->nullable();

            $table->string('status', 30)->default('submitted')->index();
            $table->unsignedInteger('submission_count')->default(1);
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_feedback')->nullable();
            $table->json('category_boosts')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['fellow_id', 'track_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};
