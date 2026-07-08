<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create testimonials table for social proof on landing page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Person's name
            $table->string('role'); // Their role/title
            $table->string('company')->nullable(); // Company/organization
            $table->text('quote'); // The testimonial text
            $table->string('image_url')->nullable(); // Avatar/photo
            $table->uuid('track_id')->nullable();
            $table->foreign('track_id')->references('id')->on('tracks')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5); // 1-5 stars
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
