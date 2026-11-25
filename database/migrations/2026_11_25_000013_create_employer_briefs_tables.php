<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employer mini-briefs: short real-world challenges from employers, posted by
 * the I-NNOVA team for a track (or all tracks). Fellows submit; the team
 * shortlists and picks winners; a linked recruiter sees the shortlist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_briefs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->string('company_name');
            $table->foreignId('recruiter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('summary');
            $table->text('deliverable');
            $table->string('resources_url', 500)->nullable();
            $table->date('deadline_on');
            $table->string('reward')->nullable();
            $table->string('status', 20)->default('draft'); // draft | open | closed
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'deadline_on']);
        });

        Schema::create('brief_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('brief_id')->constrained('employer_briefs')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('url', 500);
            $table->text('notes');
            $table->string('status', 20)->default('submitted'); // submitted | shortlisted | winner | not_selected
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['brief_id', 'fellow_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brief_submissions');
        Schema::dropIfExists('employer_briefs');
    }
};
