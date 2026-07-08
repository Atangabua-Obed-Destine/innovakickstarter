<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create activities table
 * 
 * Activities represent all fellow contributions that earn Career Capital points:
 * - Projects (shipped code, apps, websites)
 * - Blog posts and content creation
 * - Mentoring sessions
 * - Code reviews
 * - Hackathon participation
 * - Certifications
 * - Workshops attended
 * 
 * Activities go through admin approval workflow to prevent gaming.
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
        Schema::create('activities', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow who submitted this activity');
            $table->uuid('track_id')->nullable()
                ->comment('The track this activity contributes to');
            
            // Activity classification
            $table->string('type', 30)
                ->comment('project, blog_post, mentoring, code_review, hackathon, certification, workshop, open_source, other');
            $table->string('category', 30)
                ->comment('Which Career Capital category: technical, interview, portfolio, collaboration, learning');
            
            // Activity details
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('summary')->nullable()
                ->comment('Brief summary for activity feed display');
            
            // External links
            $table->string('url')->nullable()
                ->comment('Primary link: GitHub repo, blog URL, demo link');
            $table->string('demo_url')->nullable()
                ->comment('Live demo URL if different from main URL');
            $table->string('github_url')->nullable()
                ->comment('GitHub repository URL');
            $table->string('video_url')->nullable()
                ->comment('Demo video or presentation URL');
            
            // Media
            $table->string('thumbnail_url')->nullable()
                ->comment('Thumbnail image for activity cards');
            $table->json('images')->nullable()
                ->comment('Array of image URLs for gallery');
            
            // Impact metrics (flexible JSON for different activity types)
            $table->json('impact_metrics')->nullable()
                ->comment('{"users": 2400, "revenue": 15000000, "github_stars": 45, "retention_rate": 82}');
            
            // Tech stack (for projects)
            $table->json('tech_stack')->nullable()
                ->comment('["Laravel", "React", "MySQL", "Tailwind CSS"]');
            
            // Points and scoring
            $table->integer('points_earned')->default(0)
                ->comment('Points awarded for this activity');
            $table->integer('points_requested')->nullable()
                ->comment('Points the fellow requested (admin may adjust)');
            
            // Weekly pillar tracking
            $table->string('pillar', 20)->nullable()
                ->comment('Which weekly pillar: build, brand, interview, collaborate');
            $table->date('pillar_week')->nullable()
                ->comment('The week this activity counts towards');
            
            // Approval workflow
            $table->string('status', 20)->default('pending')
                ->comment('pending, approved, rejected, needs_revision');
            $table->foreignId('verified_by_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Admin who approved/rejected');
            $table->text('admin_feedback')->nullable()
                ->comment('Feedback from admin on approval/rejection');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Featured/spotlight
            $table->boolean('is_featured')->default(false)
                ->comment('Featured on homepage/project gallery');
            $table->boolean('is_public')->default(true)
                ->comment('Visible on public profile');
            
            // For case studies
            $table->text('problem')->nullable()
                ->comment('Problem statement for case study format');
            $table->text('solution')->nullable()
                ->comment('Solution description for case study format');
            $table->text('outcome')->nullable()
                ->comment('Outcome/impact for case study format');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('set null');
            
            // Indexes
            $table->index('type');
            $table->index('category');
            $table->index('status');
            $table->index('pillar');
            $table->index('is_featured');
            $table->index(['fellow_id', 'track_id']);
            $table->index(['fellow_id', 'status']);
            $table->index(['fellow_id', 'pillar', 'pillar_week']);
            $table->index(['status', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
