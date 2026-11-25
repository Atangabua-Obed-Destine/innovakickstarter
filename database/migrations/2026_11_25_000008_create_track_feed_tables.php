<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track community feed: achievements of fellows in the same track (chapters
 * completed, projects and Brand posts approved, presentations, level-ups,
 * practice streaks), with kudos. Fellows can hide themselves from the feed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_feed_events', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('subject_key', 120)->nullable(); // de-duplication
            $table->timestamps();

            $table->unique(['fellow_id', 'type', 'subject_key']);
            $table->index(['track_id', 'created_at']);
        });

        Schema::create('track_feed_kudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('track_feed_events')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'fellow_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_in_track_feed')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('show_in_track_feed'));
        Schema::dropIfExists('track_feed_kudos');
        Schema::dropIfExists('track_feed_events');
    }
};
