<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mentorship_pods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('track_id');
            $table->unsignedBigInteger('lead_id');
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('emoji')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('max_members')->default(4);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('track_id')->references('id')->on('tracks')->onDelete('cascade');
            $table->foreign('lead_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['track_id', 'is_active']);
            $table->index('lead_id');
        });

        Schema::create('mentorship_pod_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pod_id');
            $table->unsignedBigInteger('fellow_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('pod_id')->references('id')->on('mentorship_pods')->onDelete('cascade');
            $table->foreign('fellow_id')->references('id')->on('users')->onDelete('cascade');

            // A fellow can only be in one active pod at a time
            $table->unique(['fellow_id', 'is_active'], 'unique_active_pod_member');
            $table->index(['pod_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentorship_pod_members');
        Schema::dropIfExists('mentorship_pods');
    }
};
