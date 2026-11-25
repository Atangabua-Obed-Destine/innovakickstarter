<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Skill certificates: issued when a fellow completes every required activity for one track skill. Publicly verifiable by id. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->constrained('tracks')->cascadeOnDelete();
            $table->string('skill_key', 50);
            $table->string('skill_label', 100);
            $table->unsignedSmallInteger('activities_completed')->default(0);
            $table->unsignedInteger('points_earned')->default(0);
            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['fellow_id', 'track_id', 'skill_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_certificates');
    }
};
