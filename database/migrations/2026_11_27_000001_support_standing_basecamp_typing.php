<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Support for every fellow, and something at stake:
 * - activity days (what a fellow actually did each day) feed the weekly bar and early-warning signals;
 * - weekly standings (met / missed, consecutive misses, penalty fee);
 * - Base Camp: foundation areas (check, lessons, tests) and results;
 * - typing trainer sessions and live typing races;
 * - "Ask a human" help requests, support buddies and nudge log.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fellow_activity_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->date('day');
            $table->json('kinds');
            $table->timestamps();
            $table->unique(['fellow_id', 'day']);
        });

        Schema::create('weekly_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->date('week_start');
            $table->unsignedTinyInteger('active_days')->default(0);
            $table->unsignedTinyInteger('practice_days')->default(0);
            $table->unsignedSmallInteger('progress_actions')->default(0);
            $table->boolean('progress_waived')->default(false);
            $table->boolean('met')->default(false);
            $table->boolean('exempt')->default(false);
            $table->string('exempt_reason', 500)->nullable();
            $table->foreignId('exempted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('consecutive_misses')->default(0);
            $table->string('action', 20)->default('none'); // none | warning | fee | final
            $table->foreignUuid('fee_id')->nullable()->constrained('fees')->nullOnDelete();
            $table->timestamps();
            $table->unique(['fellow_id', 'week_start']);
        });

        Schema::create('foundation_areas', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('title');
            $table->string('icon', 16)->nullable();
            $table->text('summary')->nullable();
            $table->text('why')->nullable();
            $table->json('content'); // check, lessons, test (with answers: never sent to pages as-is)
            $table->unsignedTinyInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('foundation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('area', 40);
            $table->string('kind', 12); // check | lesson | test
            $table->string('item_key', 80)->nullable();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total')->default(0);
            $table->decimal('percent', 5, 1)->default(0);
            $table->boolean('passed')->default(false);
            $table->json('details')->nullable();
            $table->timestamps();
            $table->index(['fellow_id', 'area', 'kind']);
        });

        Schema::create('typing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('mode', 12); // test | lesson | drill | race
            $table->string('lesson_key', 40)->nullable();
            $table->decimal('wpm', 5, 1);
            $table->decimal('accuracy', 5, 1);
            $table->unsignedInteger('chars')->default(0);
            $table->unsignedSmallInteger('seconds')->default(0);
            $table->timestamps();
            $table->index(['fellow_id', 'mode']);
        });

        Schema::create('typing_races', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('league', 10);
            $table->text('text');
            $table->string('status', 10)->default('waiting'); // waiting | running | finished
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('ghost_wpm', 5, 1)->nullable();
            $table->timestamps();
            $table->index(['league', 'status']);
        });

        Schema::create('typing_race_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('race_id')->constrained('typing_races')->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('typed')->default(0);
            $table->unsignedSmallInteger('errors')->default(0);
            $table->decimal('wpm', 5, 1)->nullable();
            $table->decimal('accuracy', 5, 1)->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedTinyInteger('place')->nullable();
            $table->timestamps();
            $table->unique(['race_id', 'fellow_id']);
        });

        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->foreignUuid('curriculum_activity_id')->nullable()->constrained('track_curriculum_activities')->nullOnDelete();
            $table->string('step_key', 120)->nullable();
            $table->text('question');
            $table->string('status', 12)->default('open'); // open | answered | closed
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('support_buddies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buddy_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('support_nudges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('signal', 40);
            $table->timestamps();
            $table->index(['fellow_id', 'signal', 'created_at']);
        });
    }

    public function down(): void
    {
        foreach (['support_nudges', 'support_buddies', 'help_requests', 'typing_race_entries', 'typing_races', 'typing_sessions',
            'foundation_results', 'foundation_areas', 'weekly_standings', 'fellow_activity_days'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
