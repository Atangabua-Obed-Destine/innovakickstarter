<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing columns referenced by models but not present in original migrations.
 * 
 * Tables affected: users, activities, interview_sessions, notifications, recruiter_actions
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── Users Table ─────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            // Recruiter profile fields
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name', 255)->nullable()->after('bio')
                    ->comment('Recruiter company name');
            }
            if (!Schema::hasColumn('users', 'company_website')) {
                $table->string('company_website', 255)->nullable()->after('company_name')
                    ->comment('Recruiter company website URL');
            }

            // Fellow profile fields
            if (!Schema::hasColumn('users', 'headline')) {
                $table->string('headline', 255)->nullable()->after('bio')
                    ->comment('Short professional headline');
            }
            if (!Schema::hasColumn('users', 'skills')) {
                $table->json('skills')->nullable()->after('headline')
                    ->comment('Array of skills e.g. ["PHP", "Laravel", "React"]');
            }

            // Mentor profile fields
            if (!Schema::hasColumn('users', 'mentor_availability')) {
                $table->json('mentor_availability')->nullable()->after('availability')
                    ->comment('Mentor availability schedule');
            }
            if (!Schema::hasColumn('users', 'mentor_specializations')) {
                $table->json('mentor_specializations')->nullable()->after('mentor_availability')
                    ->comment('Mentor areas of expertise');
            }

            // Suspension
            if (!Schema::hasColumn('users', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('suspended_at')
                    ->comment('Reason for account suspension');
            }
        });

        // ─── Activities Table ────────────────────────────────────────
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'proof_url')) {
                $table->string('proof_url', 500)->nullable()->after('video_url')
                    ->comment('URL proving activity completion');
            }
            if (!Schema::hasColumn('activities', 'proof_files')) {
                $table->json('proof_files')->nullable()->after('proof_url')
                    ->comment('Array of uploaded proof file paths');
            }
            if (!Schema::hasColumn('activities', 'metadata')) {
                $table->json('metadata')->nullable()->after('proof_files')
                    ->comment('Additional structured data for the activity');
            }
            if (!Schema::hasColumn('activities', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('admin_feedback')
                    ->comment('Internal admin notes (not visible to fellow)');
            }
        });

        // ─── Interview Sessions Table ────────────────────────────────
        Schema::table('interview_sessions', function (Blueprint $table) {
            // Mentor rubric scoring (1-10 scale)
            if (!Schema::hasColumn('interview_sessions', 'technical_score')) {
                $table->unsignedTinyInteger('technical_score')->nullable()->after('score')
                    ->comment('Technical competency score 1-10');
            }
            if (!Schema::hasColumn('interview_sessions', 'communication_score')) {
                $table->unsignedTinyInteger('communication_score')->nullable()->after('technical_score')
                    ->comment('Communication score 1-10');
            }
            if (!Schema::hasColumn('interview_sessions', 'problem_solving_score')) {
                $table->unsignedTinyInteger('problem_solving_score')->nullable()->after('communication_score')
                    ->comment('Problem-solving score 1-10');
            }
            if (!Schema::hasColumn('interview_sessions', 'overall_score')) {
                $table->unsignedTinyInteger('overall_score')->nullable()->after('problem_solving_score')
                    ->comment('Overall impression score 1-10');
            }

            // Mentor feedback text fields
            if (!Schema::hasColumn('interview_sessions', 'feedback')) {
                $table->text('feedback')->nullable()->after('ai_feedback')
                    ->comment('Mentor written feedback');
            }
            if (!Schema::hasColumn('interview_sessions', 'strengths')) {
                $table->text('strengths')->nullable()->after('feedback')
                    ->comment('Key strengths identified');
            }
            if (!Schema::hasColumn('interview_sessions', 'areas_for_improvement')) {
                $table->text('areas_for_improvement')->nullable()->after('strengths')
                    ->comment('Areas needing improvement');
            }
            if (!Schema::hasColumn('interview_sessions', 'recommendations')) {
                $table->text('recommendations')->nullable()->after('areas_for_improvement')
                    ->comment('Mentor recommendations');
            }
            if (!Schema::hasColumn('interview_sessions', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('recommendations')
                    ->comment('Private notes (not visible to fellow)');
            }

            // AI metrics
            if (!Schema::hasColumn('interview_sessions', 'filler_word_count')) {
                $table->unsignedInteger('filler_word_count')->nullable()->after('communication_metrics')
                    ->comment('Number of filler words detected by AI');
            }
            if (!Schema::hasColumn('interview_sessions', 'speaking_pace_wpm')) {
                $table->unsignedInteger('speaking_pace_wpm')->nullable()->after('filler_word_count')
                    ->comment('Speaking pace in words per minute');
            }
            if (!Schema::hasColumn('interview_sessions', 'confidence_score')) {
                $table->decimal('confidence_score', 5, 2)->nullable()->after('speaking_pace_wpm')
                    ->comment('AI-assessed confidence score 0-100');
            }
        });

        // ─── Notifications Table ─────────────────────────────────────
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'icon')) {
                $table->string('icon', 50)->nullable()->after('priority')
                    ->comment('Icon name or emoji for the notification');
            }
            if (!Schema::hasColumn('notifications', 'color')) {
                $table->string('color', 20)->nullable()->after('icon')
                    ->comment('Color theme for the notification');
            }
        });

        // ─── Recruiter Actions Table ─────────────────────────────────
        Schema::table('recruiter_actions', function (Blueprint $table) {
            if (!Schema::hasColumn('recruiter_actions', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes')
                    ->comment('Additional structured data (subscription tier, messages, etc.)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['company_name', 'company_website', 'headline', 'skills', 
                     'mentor_availability', 'mentor_specializations', 'suspension_reason'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('activities', function (Blueprint $table) {
            $cols = ['proof_url', 'proof_files', 'metadata', 'admin_notes'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('activities', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('interview_sessions', function (Blueprint $table) {
            $cols = ['technical_score', 'communication_score', 'problem_solving_score', 
                     'overall_score', 'feedback', 'strengths', 'areas_for_improvement',
                     'recommendations', 'internal_notes', 'filler_word_count', 
                     'speaking_pace_wpm', 'confidence_score'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('interview_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            $cols = ['icon', 'color'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('notifications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('recruiter_actions', function (Blueprint $table) {
            if (Schema::hasColumn('recruiter_actions', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
