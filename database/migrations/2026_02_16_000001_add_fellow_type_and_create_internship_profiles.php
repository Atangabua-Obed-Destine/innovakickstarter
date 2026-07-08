<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add fellow_type to users and create internship_profiles table.
 * 
 * Supports three fellow categories: academic (school/university intern),
 * corporate (company-sponsored intern), and independent (self-enrolled).
 * 
 * Academic and corporate fellows have a related internship_profiles record
 * storing institution details, supervisor info, and internship duration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── Add fellow_type to users ────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'fellow_type')) {
                $table->string('fellow_type', 20)->nullable()->after('role')
                    ->comment('academic, corporate, or independent');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('profile_completed_at')
                    ->comment('When fellow completed the full onboarding wizard');
            }
        });

        // ─── Create internship_profiles table ────────────────────────
        if (!Schema::hasTable('internship_profiles')) {
            Schema::create('internship_profiles', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                // Type: academic or corporate
                $table->string('type', 20)->comment('academic or corporate');

                // Institution info (school for academic, company for corporate)
                $table->string('institution_name', 255)->comment('School or company name');
                $table->string('department', 255)->nullable()->comment('Faculty/Department');

                // Academic-specific fields
                $table->string('academic_level', 50)->nullable()
                    ->comment('bachelor, master, hnd, phd, diploma, btec, etc.');
                $table->string('student_id', 100)->nullable()
                    ->comment('Matriculation / student ID number');

                // Supervisor info
                $table->string('supervisor_name', 255);
                $table->string('supervisor_email', 255);
                $table->string('supervisor_phone', 50)->nullable();

                // Internship letter/convention upload
                $table->string('internship_letter_path', 500)->nullable()
                    ->comment('Path to uploaded internship letter/convention PDF');

                // Duration
                $table->string('duration_type', 20)->default('predefined')
                    ->comment('predefined or custom');
                $table->unsignedSmallInteger('predefined_duration_months')->nullable()
                    ->comment('1, 2, 3, 6, or 12 months');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();

                // Status tracking
                $table->string('status', 20)->default('pending')
                    ->comment('pending, active, completed, withdrawn');
                $table->text('notes')->nullable()
                    ->comment('Additional notes from the fellow');

                $table->timestamps();

                // Indexes
                $table->index('user_id');
                $table->index('type');
                $table->index('status');
                $table->index('institution_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_profiles');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'fellow_type')) {
                $table->dropColumn('fellow_type');
            }
            if (Schema::hasColumn('users', 'onboarding_completed_at')) {
                $table->dropColumn('onboarding_completed_at');
            }
        });
    }
};
