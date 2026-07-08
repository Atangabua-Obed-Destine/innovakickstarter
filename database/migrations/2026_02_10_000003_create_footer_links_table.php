<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create footer_links table for dynamic footer navigation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('column'); // product, company, resources, legal, social
            $table->string('label'); // Display text
            $table->string('url')->nullable(); // External URL or path
            $table->string('route_name')->nullable(); // Laravel route name
            $table->string('icon')->nullable(); // Icon class or name
            $table->boolean('is_external')->default(false);
            $table->boolean('open_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['column', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_links');
    }
};
