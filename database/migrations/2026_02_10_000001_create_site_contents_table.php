<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create site_contents table for dynamic landing page content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index(); // e.g., 'hero_title', 'hero_subtitle'
            $table->string('section')->index(); // e.g., 'hero', 'stats', 'pillars'
            $table->string('label'); // Human-readable label for admin
            $table->longText('value')->nullable(); // The content value
            $table->enum('type', ['text', 'html', 'json', 'image'])->default('text');
            $table->text('description')->nullable(); // Admin help text
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['section', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
