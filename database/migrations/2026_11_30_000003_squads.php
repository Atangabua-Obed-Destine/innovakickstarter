<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Squads: teams of 3-5 fellows from different pathways who work together through a
 * "squad chapter" (e.g. Software Engineering's Squad Sprint). A milestone with a
 * squad_size is a squad chapter; its collaborative activities use the squad instead
 * of two-by-two review partners.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('track_milestones', function (Blueprint $table) {
            $table->unsignedTinyInteger('squad_size')->nullable()->after('pathways');
        });
        Schema::create('squads', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('track_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('milestone_id')->constrained('track_milestones')->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('repo_url', 300)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('squad_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('squad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fellow_id')->constrained('users')->cascadeOnDelete();
            $table->string('pathway', 30)->nullable();
            $table->timestamps();
            $table->unique(['squad_id', 'fellow_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_members');
        Schema::dropIfExists('squads');
        Schema::table('track_milestones', fn (Blueprint $t) => $t->dropColumn('squad_size'));
    }
};
