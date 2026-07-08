<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create programs and program_fellows tables
 * 
 * Programs represent administrative groupings of fellows across all tracks.
 * Examples: "IKS Fellowship 2025", "Mastercard Scholars Program Batch 3"
 * 
 * Key Concepts:
 * - Program = WHO you joined with (administrative, funding, certificates)
 * - Track = WHAT you're learning (career path)
 * - Cohort = WHEN you're learning it in that track (time-bound group)
 * 
 * A fellow can:
 * - Belong to ONE program (their intake/fellowship class)
 * - Enroll in MULTIPLE tracks (career paths)
 * - Be in MULTIPLE cohorts (one per track)
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
        // Programs table - Administrative groupings
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic info
            $table->string('name', 100)
                ->comment('Program name, e.g., "IKS Fellowship 2025"');
            $table->string('slug', 120)->unique()
                ->comment('URL-friendly identifier');
            $table->text('description')->nullable()
                ->comment('Detailed program description');
            $table->string('short_description', 255)->nullable()
                ->comment('Brief tagline for display');
            
            // Branding
            $table->string('logo_url')->nullable()
                ->comment('Program logo image URL');
            $table->string('banner_url')->nullable()
                ->comment('Program banner image URL');
            $table->string('color', 7)->default('#6366f1')
                ->comment('Primary brand color (hex)');
            
            // Dates
            $table->date('start_date')
                ->comment('Program official start date');
            $table->date('end_date')
                ->comment('Program official end date');
            $table->date('enrollment_opens_at')->nullable()
                ->comment('When applications/enrollment opens');
            $table->date('enrollment_closes_at')->nullable()
                ->comment('Enrollment deadline');
            $table->date('graduation_date')->nullable()
                ->comment('Official graduation/completion ceremony date');
            
            // Capacity
            $table->unsignedInteger('max_fellows')->default(100)
                ->comment('Maximum capacity for the program');
            $table->unsignedInteger('min_fellows')->default(10)
                ->comment('Minimum fellows to run the program');
            
            // Status: draft, upcoming, enrolling, active, graduated, archived
            $table->string('status', 20)->default('draft')
                ->comment('Program lifecycle status');
            
            // Sponsorship/Funding
            $table->string('sponsor_name')->nullable()
                ->comment('Funding organization name, e.g., "Mastercard Foundation"');
            $table->string('sponsor_logo_url')->nullable()
                ->comment('Sponsor logo image URL');
            $table->string('funding_type', 50)->nullable()
                ->comment('scholarship, grant, self-funded, hybrid');
            
            // Milestones (JSON array of milestone objects)
            // Each milestone: {key, title, description, due_date, required}
            $table->json('milestones')->nullable()
                ->comment('Program-level milestones all fellows must complete');
            
            // Settings (JSON for flexible configuration)
            $table->json('settings')->nullable()
                ->comment('Custom settings like notification preferences, requirements');
            
            // Certificate settings
            $table->boolean('has_certificate')->default(true)
                ->comment('Whether program issues certificates');
            $table->string('certificate_template')->nullable()
                ->comment('Template identifier for certificate generation');
            
            // Alumni tracking
            $table->boolean('track_alumni_outcomes')->default(true)
                ->comment('Whether to track post-program outcomes');
            
            // Statistics (denormalized for performance)
            $table->unsignedInteger('fellows_count')->default(0)
                ->comment('Current enrolled fellows');
            $table->unsignedInteger('graduated_count')->default(0)
                ->comment('Fellows who completed the program');
            $table->unsignedInteger('dropped_count')->default(0)
                ->comment('Fellows who left the program');
            $table->decimal('avg_completion_rate', 5, 2)->default(0.00)
                ->comment('Average milestone completion across fellows');
            $table->decimal('employment_rate', 5, 2)->nullable()
                ->comment('Percentage of graduates employed (alumni tracking)');
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('Admin who created the program');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['status', 'start_date']);
            $table->index('created_by');
        });

        // Program Fellows pivot table - Who's in which program
        Schema::create('program_fellows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relationships
            $table->uuid('program_id');
            $table->unsignedBigInteger('fellow_id');
            
            // Enrollment status: enrolled, active, completed, dropped, removed
            $table->string('status', 20)->default('enrolled')
                ->comment('Fellow status in the program');
            
            // Dates
            $table->timestamp('enrolled_at')->useCurrent()
                ->comment('When fellow joined the program');
            $table->timestamp('activated_at')->nullable()
                ->comment('When fellow became active (e.g., orientation complete)');
            $table->timestamp('completed_at')->nullable()
                ->comment('When fellow completed/graduated');
            $table->timestamp('dropped_at')->nullable()
                ->comment('When fellow left the program');
            
            // Completion tracking
            $table->string('drop_reason')->nullable()
                ->comment('Reason if fellow left');
            $table->boolean('certificate_issued')->default(false)
                ->comment('Whether certificate was issued');
            $table->timestamp('certificate_issued_at')->nullable()
                ->comment('When certificate was issued');
            $table->string('certificate_number')->nullable()
                ->comment('Unique certificate identifier');
            
            // Milestone progress (JSON - tracks completion of program milestones)
            // Format: {milestone_key: {completed: bool, completed_at: datetime, notes: string}}
            $table->json('milestones_completed')->nullable()
                ->comment('Which program milestones the fellow completed');
            
            // Alumni tracking
            $table->string('employment_status')->nullable()
                ->comment('employed, freelancing, further_education, seeking, other');
            $table->string('employer_name')->nullable()
                ->comment('Company name if employed');
            $table->string('job_title')->nullable()
                ->comment('Current position');
            $table->decimal('starting_salary', 12, 2)->nullable()
                ->comment('First job salary after program (optional)');
            $table->string('salary_currency', 3)->nullable()
                ->comment('Currency code');
            $table->timestamp('job_started_at')->nullable()
                ->comment('When they started the job');
            
            // Admin tracking
            $table->unsignedBigInteger('enrolled_by')->nullable()
                ->comment('Admin who enrolled the fellow');
            $table->text('notes')->nullable()
                ->comment('Admin notes about this enrollment');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('program_id')
                ->references('id')
                ->on('programs')
                ->onDelete('cascade');
            $table->foreign('fellow_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('enrolled_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Constraints
            $table->unique(['program_id', 'fellow_id'], 'program_fellow_unique');
            
            // Indexes
            $table->index('status');
            $table->index(['program_id', 'status']);
            $table->index('enrolled_at');
            $table->index('completed_at');
            $table->index('employment_status');
        });

        // Add program_id to users table for quick lookup
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('current_program_id')->nullable()->after('role')
                ->comment('Current/most recent program enrollment');
            
            $table->foreign('current_program_id')
                ->references('id')
                ->on('programs')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_program_id']);
            $table->dropColumn('current_program_id');
        });
        
        Schema::dropIfExists('program_fellows');
        Schema::dropIfExists('programs');
    }
};
