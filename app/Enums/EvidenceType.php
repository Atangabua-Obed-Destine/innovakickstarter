<?php

namespace App\Enums;

/**
 * Evidence Type Enum
 * 
 * Defines the types of evidence a fellow can submit to prove
 * completion of a curriculum activity. Each curriculum activity
 * specifies which evidence types are required/accepted.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum EvidenceType: string
{
    case URL = 'url';
    case GITHUB_REPO = 'github_repo';
    case GITHUB_COMMIT = 'github_commit';
    case FILE_UPLOAD = 'file_upload';
    case TEXT = 'text';
    case SCREENSHOT = 'screenshot';
    case VIDEO = 'video';
    case INTERVIEW_SESSION = 'interview_session';
    case SOCIAL_POST = 'social_post';
    case PRESENTATION = 'presentation';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::URL => 'URL / Link',
            self::GITHUB_REPO => 'GitHub Repository',
            self::GITHUB_COMMIT => 'GitHub Commit / PR',
            self::FILE_UPLOAD => 'File Upload',
            self::TEXT => 'Written Submission',
            self::SCREENSHOT => 'Screenshot',
            self::VIDEO => 'Video Recording',
            self::INTERVIEW_SESSION => 'Interview Session',
            self::SOCIAL_POST => 'Social Media Post',
            self::PRESENTATION => 'Presentation / Slide Deck',
        };
    }

    /**
     * Get description with instructions
     */
    public function description(): string
    {
        return match($this) {
            self::URL => 'Provide a link to a deployed app, blog post, or external resource',
            self::GITHUB_REPO => 'Link to a GitHub repository with your code',
            self::GITHUB_COMMIT => 'Link to a specific commit, pull request, or merge request',
            self::FILE_UPLOAD => 'Upload a PDF, document, or other file as evidence',
            self::TEXT => 'Write a reflection, summary, or analysis',
            self::SCREENSHOT => 'Upload a screenshot showing your completed work',
            self::VIDEO => 'Provide a link to a video recording (YouTube, Loom, etc.)',
            self::INTERVIEW_SESSION => 'Complete an interview session (auto-verified from interview module)',
            self::SOCIAL_POST => 'Link to your LinkedIn post, Twitter/X thread, or other social media',
            self::PRESENTATION => 'Upload or link to your presentation slides or recording',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::URL => '🔗',
            self::GITHUB_REPO => '📦',
            self::GITHUB_COMMIT => '🔀',
            self::FILE_UPLOAD => '📎',
            self::TEXT => '📝',
            self::SCREENSHOT => '📸',
            self::VIDEO => '🎬',
            self::INTERVIEW_SESSION => '🎯',
            self::SOCIAL_POST => '📱',
            self::PRESENTATION => '📊',
        };
    }

    /**
     * Whether this evidence type requires a URL input
     */
    public function requiresUrl(): bool
    {
        return in_array($this, [
            self::URL,
            self::GITHUB_REPO,
            self::GITHUB_COMMIT,
            self::VIDEO,
            self::SOCIAL_POST,
        ]);
    }

    /**
     * Whether this evidence type requires file upload
     */
    public function requiresFileUpload(): bool
    {
        return in_array($this, [
            self::FILE_UPLOAD,
            self::SCREENSHOT,
            self::PRESENTATION,
        ]);
    }

    /**
     * Whether this evidence type requires text input
     */
    public function requiresText(): bool
    {
        return $this === self::TEXT;
    }

    /**
     * Whether this evidence type is auto-verified from another module
     */
    public function isAutoVerified(): bool
    {
        return $this === self::INTERVIEW_SESSION;
    }

    /**
     * Get allowed file extensions (for upload types)
     */
    public function allowedExtensions(): array
    {
        return match($this) {
            self::FILE_UPLOAD => ['pdf', 'doc', 'docx', 'txt', 'md'],
            self::SCREENSHOT => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            self::PRESENTATION => ['pdf', 'pptx', 'ppt', 'key'],
            default => [],
        };
    }

    /**
     * Get max file size in KB
     */
    public function maxFileSizeKb(): int
    {
        return match($this) {
            self::FILE_UPLOAD => 10240, // 10MB
            self::SCREENSHOT => 5120,   // 5MB
            self::PRESENTATION => 20480, // 20MB
            default => 0,
        };
    }
}
