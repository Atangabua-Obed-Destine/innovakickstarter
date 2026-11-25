<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API integration used for AI-powered interviews
    | and dynamic question generation.
    |
    */

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        // Model used to review curriculum submissions; needs structured outputs (json_schema).
        'review_model' => env('OPENAI_REVIEW_MODEL') ?: env('OPENAI_MODEL', 'gpt-4o'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    // Which AI provider the platform uses: openai, groq or gemini, and an optional
    // backup used automatically when the main one fails (see App\Services\Ai\AiProvider).
    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'backup' => env('AI_BACKUP_PROVIDER'),
    ],

    // Google Gemini (OpenAI-compatible endpoint).
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),
        'review_model' => env('GEMINI_REVIEW_MODEL', 'gemini-3.5-flash'),
    ],

    // Groq (OpenAI-compatible API, free tier). Separate models for interviews
    // and reviews, because each model has its own per-minute token limit.
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'review_model' => env('GROQ_REVIEW_MODEL', 'openai/gpt-oss-120b'),
    ],

    // Optional token: raises the GitHub API limit from 60 to 5,000 requests per hour.
    'github' => [
        'token' => env('GITHUB_TOKEN'),
    ],

];
