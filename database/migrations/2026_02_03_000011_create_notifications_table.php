<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create notifications table
 * 
 * Custom notifications table for in-app notifications.
 * Supports different notification types and channels.
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
        Schema::create('notifications', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();
            
            // Recipient
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Notification type
            $table->string('type', 50)
                ->comment('activity_approved, score_updated, interview_scheduled, intro_requested, etc.');
            $table->string('category', 30)->default('general')
                ->comment('career_capital, interview, recruiter, system');
            
            // Content
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable()
                ->comment('Additional data for the notification');
            
            // Related entity
            $table->string('notifiable_type', 100)->nullable();
            $table->uuid('notifiable_id')->nullable();
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Channels
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_sms')->default(false);
            $table->boolean('sent_push')->default(false);
            
            // Action
            $table->string('action_url')->nullable()
                ->comment('Link to related page');
            $table->string('action_text', 50)->nullable()
                ->comment('Button text, e.g., "View Activity"');
            
            // Priority
            $table->string('priority', 10)->default('normal')
                ->comment('low, normal, high, urgent');
            
            // Expiry
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
            $table->index('category');
            $table->index(['notifiable_type', 'notifiable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
