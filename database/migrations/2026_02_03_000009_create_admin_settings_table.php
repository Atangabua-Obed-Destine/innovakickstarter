<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create admin_settings table
 * 
 * CRITICAL: This table enables dynamic system configuration.
 * NO values should be hardcoded in the application.
 * 
 * All system parameters are stored as key-value pairs:
 * - Tier thresholds (rookie_max, intern_max, etc.)
 * - Scoring weights per track
 * - Interview points per session type
 * - Recruiter subscription limits and pricing
 * - Platform configuration
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Setting identification
            $table->string('key', 100)->unique()
                ->comment('Unique setting key, e.g., elite_tier_threshold');
            $table->string('group', 50)->default('general')
                ->comment('Setting group: career_capital, recruiter, billing, platform, interview');
            
            // Setting value
            $table->text('value')
                ->comment('Value stored as text, parsed based on type');
            $table->string('type', 20)->default('string')
                ->comment('string, integer, decimal, boolean, json, array');
            
            // Metadata
            $table->string('label', 100)
                ->comment('Human-readable label for admin UI');
            $table->text('description')->nullable()
                ->comment('Explanation of what this setting does');
            $table->string('input_type', 30)->default('text')
                ->comment('UI input type: text, number, textarea, select, toggle, json_editor');
            
            // Validation
            $table->json('validation_rules')->nullable()
                ->comment('Laravel validation rules as JSON');
            $table->json('options')->nullable()
                ->comment('Options for select inputs');
            $table->string('min')->nullable()
                ->comment('Minimum value for numeric inputs');
            $table->string('max')->nullable()
                ->comment('Maximum value for numeric inputs');
            
            // Defaults
            $table->text('default_value')->nullable()
                ->comment('Default value if not set');
            
            // Organization
            $table->integer('order')->default(0)
                ->comment('Display order within group');
            $table->boolean('is_public')->default(false)
                ->comment('Whether this setting is exposed to frontend');
            $table->boolean('is_readonly')->default(false)
                ->comment('Prevent editing in admin UI (system settings)');
            $table->boolean('requires_restart')->default(false)
                ->comment('Whether changes require cache clear or restart');
            
            // Audit
            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Last admin who updated this setting');
            
            $table->timestamps();
            
            // Indexes
            $table->index('group');
            $table->index('is_public');
            $table->index(['group', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
