<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Job-market readiness: the role a fellow is aiming for, the mock offer they
 * unlock, and the job adverts they compared themselves with.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->string('career_role', 60)->nullable();
            $table->string('offer_role', 60)->nullable();
            $table->timestamp('offer_unlocked_at')->nullable();
        });

        Schema::create('job_advert_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('track_id')->nullable()->constrained('tracks')->nullOnDelete();
            $table->string('title', 200)->nullable();
            $table->string('company', 200)->nullable();
            $table->unsignedTinyInteger('match')->default(0);
            $table->json('result');
            $table->timestamps();
            $table->index(['fellow_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_advert_checks');
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->dropColumn(['career_role', 'offer_role', 'offer_unlocked_at']);
        });
    }
};
