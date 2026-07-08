<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create tracks table
 * 
 * Career tracks represent different professional paths a fellow can pursue.
 * Each track has its own scoring rubric (JSON) defining weight distribution
 * across the 5 Career Capital categories.
 * 
 * Examples: Full-Stack Engineering, Product Management, UI/UX Design, DevOps, AI/ML
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
        Schema::create('tracks', function (Blueprint $table) {
            // UUID primary key for security
            $table->uuid('id')->primary();
            
            // Track identification
            $table->string('name', 100)->unique()
                ->comment('Track name, e.g., Full-Stack Engineering');
            $table->string('slug', 100)->unique()
                ->comment('URL-friendly slug, e.g., full-stack-engineering');
            $table->string('category', 30)
                ->comment('technical, non-technical, hybrid');
            
            // Track details
            $table->text('description')
                ->comment('Detailed description of what this track covers');
            $table->text('short_description')->nullable()
                ->comment('One-liner for cards/listings');
            $table->string('icon', 50)->nullable()
                ->comment('Icon class or emoji for UI display');
            $table->string('color', 7)->default('#7C3AED')
                ->comment('Hex color code for track branding');
            
            // Scoring configuration (JSON)
            // Default: {"technical": 30, "interview": 25, "portfolio": 20, "collaboration": 15, "learning": 10}
            $table->json('scoring_rubric')
                ->comment('Weight distribution for Career Capital categories');
            
            // Requirements and benefits
            $table->json('requirements')->nullable()
                ->comment('Prerequisites for this track');
            $table->json('outcomes')->nullable()
                ->comment('Expected outcomes/job roles after completing track');
            
            // Track management
            $table->boolean('is_active')->default(true)
                ->comment('Whether track is available for enrollment');
            $table->boolean('is_featured')->default(false)
                ->comment('Featured tracks shown prominently on landing page');
            $table->integer('order')->default(0)
                ->comment('Display order on frontend (lower = first)');
            
            // Statistics (denormalized for performance)
            $table->integer('fellows_count')->default(0)
                ->comment('Number of fellows enrolled in this track');
            $table->decimal('avg_score', 5, 2)->default(0)
                ->comment('Average Career Capital score for this track');
            
            $table->timestamps();
            
            // Indexes
            $table->index('category');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('order');
            $table->index(['is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
