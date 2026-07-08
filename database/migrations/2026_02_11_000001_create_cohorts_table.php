<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create cohorts and cohort_fellows tables
 * 
 * Cohorts represent time-bound groups of fellows going through a track together.
 * Example: "Software Engineering Cohort 8" running from Jan 1 - Mar 31, 2026
 * 
 * Business Rules:
 * - Each cohort belongs to exactly ONE track
 * - A fellow can be in ONE cohort per track (but multiple cohorts across different tracks)
 * - Cohorts have a defined start and end date
 * - Cohort status lifecycle: draft → upcoming → active → completed → archived
 * - When a cohort is active, all enrolled fellows' activities count toward cohort stats
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
        // Create cohorts table
        Schema::create('cohorts', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Cohort identification
            $table->string('name', 100)
                ->comment('Cohort name, e.g., "Cohort 8" or "January 2026 Batch"');
            $table->string('slug', 100)->unique()
                ->comment('URL-friendly identifier');
            $table->text('description')->nullable()
                ->comment('Description of this cohort, goals, special notes');
            
            // Track relationship - each cohort belongs to ONE track
            $table->uuid('track_id')
                ->comment('The track this cohort is for');
            
            // Cohort timeline
            $table->date('start_date')
                ->comment('When the cohort officially begins');
            $table->date('end_date')
                ->comment('When the cohort officially ends');
            $table->date('enrollment_opens_at')->nullable()
                ->comment('When fellows can start enrolling');
            $table->date('enrollment_closes_at')->nullable()
                ->comment('Deadline for enrollment');
            
            // Capacity management
            $table->integer('max_fellows')->default(50)
                ->comment('Maximum number of fellows in this cohort');
            $table->integer('min_fellows')->default(10)
                ->comment('Minimum fellows required to run cohort');
            
            // Status management
            $table->string('status', 20)->default('draft')
                ->comment('draft, upcoming, active, completed, archived, cancelled');
            
            // Cohort settings
            $table->json('settings')->nullable()
                ->comment('Custom settings: weekly requirements, interview quotas, etc.');
            $table->json('milestones')->nullable()
                ->comment('Key dates/milestones within the cohort');
            
            // Statistics (denormalized for dashboard performance)
            $table->integer('fellows_count')->default(0)
                ->comment('Current number of enrolled fellows');
            $table->decimal('avg_score', 5, 2)->default(0.00)
                ->comment('Average Career Capital score of cohort');
            $table->integer('completion_rate')->default(0)
                ->comment('Percentage of fellows who completed (0-100)');
            $table->integer('activities_count')->default(0)
                ->comment('Total activities submitted in this cohort');
            
            // Management
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin who created this cohort');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            // Indexes
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['track_id', 'status']);
            $table->index(['status', 'start_date']);
        });

        // Create cohort_fellows pivot table
        Schema::create('cohort_fellows', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->uuid('cohort_id')
                ->comment('The cohort');
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow enrolled in this cohort');
            
            // Enrollment details
            $table->string('status', 20)->default('enrolled')
                ->comment('enrolled, active, completed, dropped, removed');
            $table->timestamp('enrolled_at')->useCurrent()
                ->comment('When fellow was added to cohort');
            $table->timestamp('completed_at')->nullable()
                ->comment('When fellow completed the cohort');
            $table->timestamp('dropped_at')->nullable()
                ->comment('When fellow dropped/was removed');
            $table->string('drop_reason')->nullable()
                ->comment('Reason if dropped or removed');
            
            // Progress within cohort
            $table->decimal('cohort_score', 5, 2)->default(0.00)
                ->comment('Score earned during this cohort period');
            $table->integer('activities_completed')->default(0)
                ->comment('Activities completed during cohort');
            $table->integer('interviews_completed')->default(0)
                ->comment('Interviews completed during cohort');
            $table->integer('weeks_active')->default(0)
                ->comment('Number of weeks with activity');
            
            // Ranking within cohort
            $table->integer('rank')->nullable()
                ->comment('Fellow rank within cohort based on score');
            
            // Management
            $table->foreignId('enrolled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin who enrolled this fellow');
            $table->text('notes')->nullable()
                ->comment('Admin notes about this fellow in cohort');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->onDelete('cascade');
            
            // Unique: Fellow can only be in ONE cohort per track (enforced via cohort's track)
            $table->unique(['cohort_id', 'fellow_id']);
            
            // Indexes
            $table->index('status');
            $table->index('cohort_score');
            $table->index('rank');
            $table->index(['cohort_id', 'status']);
            $table->index(['fellow_id', 'status']);
        });
        
        // Add cohort_id to fellow_tracks table for direct lookup
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->uuid('cohort_id')->nullable()->after('track_id')
                ->comment('Current cohort the fellow is in for this track');
            
            $table->foreign('cohort_id')
                ->references('id')
                ->on('cohorts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fellow_tracks', function (Blueprint $table) {
            $table->dropForeign(['cohort_id']);
            $table->dropColumn('cohort_id');
        });
        
        Schema::dropIfExists('cohort_fellows');
        Schema::dropIfExists('cohorts');
    }
};
