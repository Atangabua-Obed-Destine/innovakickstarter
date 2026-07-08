<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create interview_sessions table
 * 
 * Stores both AI-powered and human mock interviews.
 * This is a flagship feature of IKS - interview readiness is 25% of Career Capital.
 * 
 * Interview Types:
 * - Behavioral (STAR method)
 * - Technical Coding (LeetCode-style)
 * - System Design (Architecture)
 * - Product Case (for Product track)
 * - Design Challenge (for Design track)
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
        Schema::create('interview_sessions', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Relationships
            $table->foreignId('fellow_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('The fellow being interviewed');
            $table->uuid('track_id')
                ->comment('The track this interview is for');
            $table->foreignId('interviewer_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Human interviewer (null if AI)');
            
            // Interview classification
            $table->string('type', 30)
                ->comment('behavioral, technical_coding, system_design, product_case, design_challenge');
            $table->string('mode', 10)
                ->comment('ai or human');
            $table->string('difficulty', 15)->default('medium')
                ->comment('easy, medium, hard, expert');
            
            // Session details
            $table->string('title', 255)->nullable()
                ->comment('Interview session title, e.g., "System Design: Design Twitter"');
            $table->text('description')->nullable();
            
            // Timing
            $table->integer('duration_minutes')->nullable()
                ->comment('Actual duration of the interview');
            $table->integer('target_duration')->default(30)
                ->comment('Expected duration in minutes');
            $table->timestamp('scheduled_at')->nullable()
                ->comment('For scheduled human interviews');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Status
            $table->string('status', 20)->default('pending')
                ->comment('pending, in_progress, completed, cancelled, no_show');
            
            // Scoring
            $table->decimal('score', 5, 2)->nullable()
                ->comment('Overall score 0-100');
            $table->json('rubric_scores')->nullable()
                ->comment('{"clarity": 92, "structure": 88, "confidence": 95, "technical_accuracy": 85}');
            $table->integer('percentile')->nullable()
                ->comment('Percentile ranking compared to other fellows');
            
            // AI Interview Data
            $table->json('questions')->nullable()
                ->comment('Array of interview questions asked');
            $table->json('transcript')->nullable()
                ->comment('Full Q&A transcript');
            $table->json('ai_feedback')->nullable()
                ->comment('AI-generated detailed feedback');
            
            // Human Interview Data
            $table->text('interviewer_notes')->nullable()
                ->comment('Notes from human interviewer');
            $table->text('fellow_feedback')->nullable()
                ->comment('Fellow feedback on the interview experience');
            $table->integer('interviewer_rating')->nullable()
                ->comment('Rating 1-5 from fellow on interviewer');
            
            // Communication Analysis (Yoodli-style)
            $table->json('communication_metrics')->nullable()
                ->comment('{"filler_words_per_min": 2, "speaking_pace": 145, "confidence_score": 88}');
            
            // Recording
            $table->string('video_url')->nullable()
                ->comment('Recorded video URL (S3/Cloudinary)');
            $table->string('audio_url')->nullable()
                ->comment('Audio recording URL');
            
            // Points awarded
            $table->integer('points_earned')->default(0);
            
            // For scheduling system
            $table->string('meeting_link')->nullable()
                ->comment('Video call link for human interviews');
            $table->text('cancellation_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('track_id')
                ->references('id')
                ->on('tracks')
                ->onDelete('cascade');
            
            // Indexes
            $table->index('type');
            $table->index('mode');
            $table->index('status');
            $table->index('score');
            $table->index(['fellow_id', 'track_id']);
            $table->index(['fellow_id', 'type']);
            $table->index(['fellow_id', 'mode']);
            $table->index(['interviewer_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_sessions');
    }
};
