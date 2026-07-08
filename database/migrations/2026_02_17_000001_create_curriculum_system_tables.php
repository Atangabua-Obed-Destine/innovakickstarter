<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create Track Curriculum System Tables
 * 
 * Builds the structured curriculum engine for the IKS platform:
 * 
 * 1. track_milestones — Phases/stages within a track (e.g., "Week 1-2: Foundation")
 * 2. track_curriculum_activities — Admin-defined activities assigned to milestones
 * 3. fellow_curriculum_progress — Individual fellow's progress on each curriculum activity
 * 4. fellow_streaks — Consecutive weekly completion tracking with multipliers
 * 5. accountability_pairs — Auto-paired fellows for peer review within cohorts
 * 6. fellow_badges — Digital badges earned through milestones, streaks, and achievements
 * 
 * Key design decisions:
 * - All tables use UUID primary keys for security (consistent with existing system)
 * - Deadlines are relative (days from start), calculated into absolute dates per fellow
 * - Activities link to existing ActivityType enum and Career Capital categories
 * - Review workflow supports peer review → mentor/admin review pipeline
 * - Streak system integrates with existing weekly_progress table
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
        // =============================================
        // TABLE 1: track_milestones
        // =============================================
        Schema::create('track_milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->uuid('track_id')
                ->comment('The track this milestone belongs to');
            
            // Milestone details
            $table->string('title', 150)
                ->comment('e.g., "Week 1-2: Foundation", "Phase 3: Building"');
            $table->text('description')
                ->comment('What fellows will learn/accomplish in this milestone');
            $table->string('short_description', 255)->nullable()
                ->comment('One-liner for progress cards');
            
            // Sequencing
            $table->integer('sequence_order')->default(0)
                ->comment('Display/unlock order within the track (lower = first)');
            $table->uuid('unlock_after_milestone_id')->nullable()
                ->comment('FK to self — must complete this milestone to unlock current');
            
            // Duration
            $table->integer('estimated_duration_days')->default(14)
                ->comment('Expected calendar days to complete this milestone');
            
            // Badge reward
            $table->string('badge_name', 100)->nullable()
                ->comment('Name of badge awarded on milestone completion');
            $table->string('badge_icon', 50)->nullable()
                ->comment('Icon/emoji for the milestone badge');
            $table->string('badge_color', 7)->default('#8B5CF6')
                ->comment('Hex color for the badge');
            
            // Configuration
            $table->boolean('is_required')->default(true)
                ->comment('Whether this milestone must be completed for track completion');
            $table->boolean('is_active')->default(true)
                ->comment('Whether this milestone is currently active');
            $table->integer('bonus_points')->default(0)
                ->comment('Extra points awarded on milestone completion');
            
            // Management
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin who created this milestone');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            $table->foreign('unlock_after_milestone_id')
                ->references('id')
                ->on('track_milestones')
                ->nullOnDelete();
            
            // Indexes
            $table->index(['track_id', 'sequence_order']);
            $table->index(['track_id', 'is_active']);
            $table->unique(['track_id', 'sequence_order']);
        });

        // =============================================
        // TABLE 2: track_curriculum_activities
        // =============================================
        Schema::create('track_curriculum_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->uuid('track_id')
                ->comment('The track this activity belongs to');
            $table->uuid('milestone_id')
                ->comment('The milestone this activity is part of');
            
            // Activity details
            $table->string('title', 200)
                ->comment('e.g., "Build a REST API", "Write your first LinkedIn post"');
            $table->text('description')
                ->comment('Rich instructions, context, and requirements');
            $table->text('instructions')->nullable()
                ->comment('Step-by-step guide for completing the activity');
            
            // Type & classification
            $table->string('type', 30)
                ->comment('ActivityType enum value (project, blog_post, mock_interview, etc.)');
            $table->string('difficulty_level', 20)->default('beginner')
                ->comment('DifficultyLevel enum: beginner, intermediate, advanced, expert');
            $table->string('career_capital_category', 20)
                ->comment('CareerCapitalCategory: technical, interview, portfolio, collaboration, learning');
            $table->string('pillar', 15)->nullable()
                ->comment('Weekly pillar: build, brand, interview, collaborate');
            
            // Points & scoring
            $table->integer('points')->default(10)
                ->comment('Career Capital points awarded on completion');
            $table->json('evaluation_rubric')->nullable()
                ->comment('JSON: {"criterion": {"weight": 30, "description": "..."}, ...}');
            
            // Sequencing within milestone
            $table->integer('sequence_order')->default(0)
                ->comment('Order within the milestone (lower = first)');
            $table->boolean('is_sequential')->default(false)
                ->comment('If true, must complete previous activity first. If false, can do in parallel');
            
            // Deadlines (relative to fellow start)
            $table->integer('deadline_days')->nullable()
                ->comment('Days from track enrollment to complete this activity');
            $table->integer('grace_period_days')->default(3)
                ->comment('Extra days allowed after deadline (late penalty applies)');
            $table->integer('late_penalty_percent')->default(20)
                ->comment('Percentage of points deducted for late submission');
            
            // Evidence requirements
            $table->json('evidence_requirements')->nullable()
                ->comment('JSON array of required EvidenceType values');
            
            // Resources & links
            $table->json('resources')->nullable()
                ->comment('JSON: [{"title": "...", "url": "...", "type": "article|video|doc"}]');
            
            // Prerequisite activities (within same milestone or cross-milestone)
            $table->json('prerequisites')->nullable()
                ->comment('JSON array of curriculum_activity UUIDs that must be completed first');
            
            // Activity chains (multi-part activities)
            $table->uuid('chain_parent_id')->nullable()
                ->comment('FK to self — parent activity in a chain (e.g., Build API → Test API → Deploy API)');
            
            // Collaboration requirements
            $table->boolean('is_required')->default(true)
                ->comment('Whether activity must be completed for milestone completion');
            $table->boolean('is_collaborative')->default(false)
                ->comment('Whether this requires working with an accountability partner');
            $table->boolean('requires_cross_track')->default(false)
                ->comment('Whether partner must be from a different track');
            $table->boolean('requires_peer_review')->default(false)
                ->comment('Whether submission goes through peer review before mentor review');
            
            // Interview module integration
            $table->json('interview_config')->nullable()
                ->comment('For mock_interview type: {"type": "behavioral", "min_score": 70, "count": 2}');
            
            // Management
            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin who created this curriculum activity');
            $table->boolean('is_active')->default(true)
                ->comment('Whether this activity is currently included in the curriculum');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            $table->foreign('milestone_id')
                ->references('id')
                ->on('track_milestones')
                ->onDelete('cascade');
            
            $table->foreign('chain_parent_id')
                ->references('id')
                ->on('track_curriculum_activities')
                ->nullOnDelete();
            
            // Indexes
            $table->index(['track_id', 'milestone_id', 'sequence_order'], 'tca_track_milestone_order');
            $table->index(['milestone_id', 'is_active']);
            $table->index(['type']);
            $table->index(['difficulty_level']);
            $table->index(['chain_parent_id']);
        });

        // =============================================
        // TABLE 3: fellow_curriculum_progress
        // =============================================
        Schema::create('fellow_curriculum_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow working on this activity');
            $table->uuid('curriculum_activity_id')
                ->comment('The curriculum activity being tracked');
            
            // Status lifecycle
            $table->string('status', 20)->default('locked')
                ->comment('CurriculumStatus: locked, available, in_progress, submitted, peer_review, under_review, completed, rejected, overdue');
            
            // Calculated deadlines (absolute dates, computed from fellow start + relative days)
            $table->timestamp('deadline_at')->nullable()
                ->comment('Calculated: fellow started_at + activity deadline_days');
            $table->timestamp('grace_deadline_at')->nullable()
                ->comment('Calculated: deadline_at + grace_period_days');
            
            // Timeline
            $table->timestamp('started_at')->nullable()
                ->comment('When fellow began working on this activity');
            $table->timestamp('submitted_at')->nullable()
                ->comment('When fellow submitted evidence');
            $table->timestamp('completed_at')->nullable()
                ->comment('When activity was approved as complete');
            
            // Evidence submission
            $table->json('evidence')->nullable()
                ->comment('JSON: [{"type": "url", "value": "https://...", "label": "GitHub repo"}]');
            $table->text('submission_notes')->nullable()
                ->comment('Fellow\'s notes about their submission');
            
            // Review workflow (mentor/admin)
            $table->foreignId('reviewer_id')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Mentor or admin who reviewed the submission');
            $table->text('review_notes')->nullable()
                ->comment('Reviewer feedback');
            $table->timestamp('reviewed_at')->nullable()
                ->comment('When the review was completed');
            $table->json('rubric_scores')->nullable()
                ->comment('JSON: Per-criterion scores from the evaluation rubric');
            
            // Scoring
            $table->integer('score_awarded')->default(0)
                ->comment('Final score out of 100 based on rubric evaluation');
            $table->integer('points_awarded')->default(0)
                ->comment('Career Capital points awarded (may differ from template due to late penalty)');
            $table->boolean('late_penalty_applied')->default(false)
                ->comment('Whether a late submission penalty was applied');
            
            // Resubmission tracking
            $table->integer('attempt_number')->default(1)
                ->comment('Current attempt number (increments on resubmission)');
            
            // Peer review workflow
            $table->foreignId('peer_reviewer_id')->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Accountability partner who pre-reviewed');
            $table->text('peer_review_notes')->nullable()
                ->comment('Peer reviewer feedback');
            $table->timestamp('peer_reviewed_at')->nullable()
                ->comment('When peer review was completed');
            $table->integer('peer_review_score')->nullable()
                ->comment('Peer reviewer\'s assessment score (0-100)');
            
            // Link to freestyle activity (if a freestyle submission was mapped)
            $table->uuid('linked_activity_id')->nullable()
                ->comment('FK to activities table — if a freestyle activity satisfied this curriculum item');
            
            // Link to interview session (for mock_interview type)
            $table->uuid('linked_interview_id')->nullable()
                ->comment('FK to interview_sessions — auto-linked for mock_interview activities');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('curriculum_activity_id')
                ->references('id')
                ->on('track_curriculum_activities')
                ->onDelete('cascade');
            
            $table->foreign('linked_activity_id')
                ->references('id')
                ->on('activities')
                ->nullOnDelete();
            
            $table->foreign('linked_interview_id')
                ->references('id')
                ->on('interview_sessions')
                ->nullOnDelete();
            
            // Unique: One progress record per fellow per curriculum activity
            $table->unique(['fellow_id', 'curriculum_activity_id'], 'fcp_fellow_activity_unique');
            
            // Indexes
            $table->index(['fellow_id', 'status']);
            $table->index(['curriculum_activity_id', 'status']);
            $table->index(['status', 'deadline_at']);
            $table->index(['reviewer_id', 'status']);
            $table->index(['peer_reviewer_id']);
            $table->index(['fellow_id', 'completed_at']);
        });

        // =============================================
        // TABLE 4: fellow_streaks
        // =============================================
        Schema::create('fellow_streaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow whose streak is tracked');
            $table->uuid('track_id')
                ->comment('The track the streak is for');
            
            // Streak data
            $table->integer('current_streak')->default(0)
                ->comment('Consecutive weeks with all 4 pillars completed');
            $table->integer('longest_streak')->default(0)
                ->comment('Best consecutive streak ever achieved');
            $table->decimal('multiplier', 3, 2)->default(1.00)
                ->comment('Points multiplier: 1.0, 1.1, 1.25, 1.5 based on streak');
            $table->date('last_completed_week')->nullable()
                ->comment('Monday of the last week where all pillars were completed');
            $table->timestamp('streak_broken_at')->nullable()
                ->comment('When the current streak was last broken');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            // Unique: One streak record per fellow per track
            $table->unique(['fellow_id', 'track_id']);
            
            // Indexes
            $table->index('current_streak');
            $table->index('longest_streak');
        });

        // =============================================
        // TABLE 5: accountability_pairs
        // =============================================
        Schema::create('accountability_pairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // The two fellows
            $table->foreignId('fellow_a_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('First fellow in the pair');
            $table->foreignId('fellow_b_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Second fellow in the pair');
            
            // Context
            $table->uuid('track_id')
                ->comment('The track they share');
            $table->uuid('cohort_id')->nullable()
                ->comment('The cohort they belong to (nullable for non-cohort pairing)');
            $table->uuid('milestone_id')->nullable()
                ->comment('Current milestone — pairs rotate per milestone');
            
            // Status
            $table->boolean('is_active')->default(true)
                ->comment('Whether this pairing is currently active');
            $table->timestamp('paired_at')->useCurrent()
                ->comment('When the pair was created');
            $table->timestamp('unpaired_at')->nullable()
                ->comment('When the pair was dissolved');
            
            // Stats
            $table->integer('reviews_exchanged')->default(0)
                ->comment('Number of peer reviews between this pair');
            $table->integer('bonus_points_earned')->default(0)
                ->comment('Collaboration bonus points earned together');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->nullOnDelete();
            
            $table->foreign('milestone_id')
                ->references('id')
                ->on('track_milestones')
                ->nullOnDelete();
            
            // Indexes
            $table->index(['fellow_a_id', 'is_active']);
            $table->index(['fellow_b_id', 'is_active']);
            $table->index(['track_id', 'is_active']);
            $table->index(['cohort_id', 'is_active']);
        });

        // =============================================
        // TABLE 6: fellow_badges
        // =============================================
        Schema::create('fellow_badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow who earned this badge');
            
            // Badge details
            $table->string('badge_type', 30)
                ->comment('BadgeType enum: milestone, streak, achievement, track_completion, power_week, peer_champion');
            $table->string('badge_name', 150)
                ->comment('Display name of the badge');
            $table->string('badge_icon', 50)->default('⭐')
                ->comment('Icon/emoji for the badge');
            $table->string('badge_color', 7)->default('#8B5CF6')
                ->comment('Hex color for badge display');
            $table->text('badge_description')->nullable()
                ->comment('What the fellow did to earn this badge');
            
            // Earned context
            $table->timestamp('earned_at')
                ->comment('When the badge was earned');
            $table->uuid('milestone_id')->nullable()
                ->comment('FK to track_milestones — if earned from milestone completion');
            $table->uuid('track_id')->nullable()
                ->comment('FK to tracks — associated track');
            
            // Sharing
            $table->string('shareable_url', 500)->nullable()
                ->comment('Public URL to verify/share this badge');
            $table->boolean('is_shared')->default(false)
                ->comment('Whether fellow has shared this badge publicly');
            
            // Metadata
            $table->json('metadata')->nullable()
                ->comment('Additional context: streak count, score, etc.');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('milestone_id')
                ->references('id')
                ->on('track_milestones')
                ->nullOnDelete();
            
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->nullOnDelete();
            
            // Indexes
            $table->index(['fellow_id', 'badge_type']);
            $table->index(['fellow_id', 'earned_at']);
            $table->index(['badge_type']);
            $table->index(['track_id']);
        });

        // =============================================
        // Add power_week support to cohorts
        // =============================================
        Schema::table('cohorts', function (Blueprint $table) {
            $table->boolean('has_power_week')->default(false)->after('status')
                ->comment('Whether current week is a Power Week (2x points)');
            $table->timestamp('power_week_start')->nullable()->after('has_power_week')
                ->comment('When the current Power Week started');
            $table->timestamp('power_week_end')->nullable()->after('power_week_start')
                ->comment('When the current Power Week ends');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove power week columns from cohorts
        Schema::table('cohorts', function (Blueprint $table) {
            $table->dropColumn(['has_power_week', 'power_week_start', 'power_week_end']);
        });

        // Drop tables in reverse order (respect foreign keys)
        Schema::dropIfExists('fellow_badges');
        Schema::dropIfExists('accountability_pairs');
        Schema::dropIfExists('fellow_streaks');
        Schema::dropIfExists('fellow_curriculum_progress');
        Schema::dropIfExists('track_curriculum_activities');
        Schema::dropIfExists('track_milestones');
    }
};
