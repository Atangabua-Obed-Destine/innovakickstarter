<?php

namespace App\Services\Ai;

/**
 * The AI providers the platform talks to (AI interviews and AI review).
 *
 * All of them speak the OpenAI chat-completions API, so services only need
 * the URL, key and model from here. The main provider and an optional
 * backup (used automatically when the main one fails or is rate-limited)
 * are chosen on the admin Integrations page, or AI_PROVIDER /
 * AI_BACKUP_PROVIDER in .env. Calls go through AiClient.
 */
class AiProvider
{
    public const PROVIDERS = [
        'openai' => [
            'label' => 'OpenAI',
            'base_url' => 'https://api.openai.com/v1',
            // GPT-4o answers directly: the token budget is for the answer only.
            'thinking_tokens' => 0,
            // No practical request-size limit at our prompt sizes.
            'review_prompt_chars' => null,
            'default_model' => 'gpt-4o',
            'default_review_model' => null, // same as the interview model
            'interview_effort' => null,
            'mentor_model' => 'gpt-4o-mini',
            'mentor_max_tokens' => 600,
            'mentor_params' => [],
        ],
        'groq' => [
            'label' => 'Groq',
            'base_url' => 'https://api.groq.com/openai/v1',
            // gpt-oss models think before answering, and the thinking counts
            // against max_tokens: give them room.
            'thinking_tokens' => 2000,
            // Free tier: 8,000 tokens per minute per model, prompt + answer.
            // ~16k characters keeps a review prompt near 4,500 tokens.
            'review_prompt_chars' => 16000,
            'default_model' => 'openai/gpt-oss-20b',
            'default_review_model' => 'openai/gpt-oss-120b',
            'interview_effort' => null, // default effort answers in ~1 s
            // The mentor has its own model so it never uses up the interview/review quotas
            // (free tier: limits are per model). Reasoning stays hidden from the reply.
            // This model allows ~1,000 output tokens per minute: keep replies short.
            'mentor_model' => 'qwen/qwen3.8-27b',
            'mentor_max_tokens' => 700,
            'mentor_params' => ['reasoning_format' => 'hidden'],
        ],
        'gemini' => [
            'label' => 'Google Gemini',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            // Gemini thinking models: thinking counts against max_tokens.
            'thinking_tokens' => 8000,
            // Large per-minute limits on the free tier.
            'review_prompt_chars' => null,
            'default_model' => 'gemini-3.5-flash',
            'default_review_model' => 'gemini-3.5-flash',
            // Default thinking takes ~6 s per interview answer; "low" ~3.5 s, same scores.
            'interview_effort' => 'low',
            'mentor_model' => 'gemini-3.5-flash',
            'mentor_max_tokens' => 2500, // thinking counts against it
            'mentor_params' => ['reasoning_effort' => 'low'],
        ],
    ];

    /** The main provider. */
    public static function name(): string
    {
        $name = (string) config('services.ai.provider', 'openai');

        return isset(self::PROVIDERS[$name]) ? $name : 'openai';
    }

    /** The backup provider, when one is chosen, differs from the main one and has a key. */
    public static function backup(): ?string
    {
        $name = (string) config('services.ai.backup');

        return isset(self::PROVIDERS[$name]) && $name !== self::name() && self::key($name) !== null ? $name : null;
    }

    /**
     * Providers to try, in order: the main one, then the backup. Providers
     * without a key are skipped.
     *
     * @return string[]
     */
    public static function chain(): array
    {
        return array_values(array_filter(
            array_unique(array_filter([self::name(), self::backup()])),
            fn ($provider) => self::key($provider) !== null
        ));
    }

    public static function label(?string $provider = null): string
    {
        return self::PROVIDERS[$provider ?? self::name()]['label'];
    }

    public static function key(?string $provider = null): ?string
    {
        $key = config('services.' . ($provider ?? self::name()) . '.key');

        return filled($key) ? (string) $key : null;
    }

    /** At least one provider can be called. */
    public static function configured(): bool
    {
        return self::chain() !== [];
    }

    /** Model for AI interviews. */
    public static function model(?string $provider = null): string
    {
        $provider ??= self::name();

        return (string) (config("services.{$provider}.model") ?: self::PROVIDERS[$provider]['default_model']);
    }

    /** Model for the track mentor chat. */
    public static function mentorModel(?string $provider = null): string
    {
        $provider ??= self::name();

        return (string) (config("services.{$provider}.mentor_model") ?: self::PROVIDERS[$provider]['mentor_model'] ?? self::model($provider));
    }

    public static function mentorMaxTokens(?string $provider = null): int
    {
        return (int) (self::PROVIDERS[$provider ?? self::name()]['mentor_max_tokens'] ?? 700);
    }

    /** Extra request parameters for a purpose (e.g. hidden reasoning for the mentor). */
    public static function extraParams(string $purpose, ?string $provider = null): array
    {
        return $purpose === 'mentor' ? (self::PROVIDERS[$provider ?? self::name()]['mentor_params'] ?? []) : [];
    }

    /** Model for reviewing submissions (needs strict JSON-schema output). */
    public static function reviewModel(?string $provider = null): string
    {
        $provider ??= self::name();

        return (string) (config("services.{$provider}.review_model")
            ?: self::PROVIDERS[$provider]['default_review_model']
            ?: self::model($provider));
    }

    public static function chatUrl(?string $provider = null): string
    {
        return self::PROVIDERS[$provider ?? self::name()]['base_url'] . '/chat/completions';
    }

    /** Extra headers (OpenAI organization). */
    public static function headers(?string $provider = null): array
    {
        $provider ??= self::name();

        return $provider === 'openai'
            ? array_filter(['OpenAI-Organization' => config('services.openai.organization')])
            : [];
    }

    /**
     * max_tokens for a call sized for GPT-4o. Thinking models get extra
     * room, because their thinking counts against the same budget.
     */
    public static function maxTokens(int $answerTokens, ?string $provider = null): int
    {
        return $answerTokens + self::PROVIDERS[$provider ?? self::name()]['thinking_tokens'];
    }

    /**
     * reasoning_effort values to try for a review, in order. Groq's gpt-oss
     * sometimes stops before finishing the JSON at its default effort; an
     * explicit "medium", then "high" on retries, completes it.
     * [null] = do not send the parameter (OpenAI GPT-4o).
     */
    public static function reasoningEfforts(?string $provider = null): array
    {
        return self::PROVIDERS[$provider ?? self::name()]['thinking_tokens'] > 0 ? ['medium', 'high', 'high'] : [null];
    }

    /** reasoning_effort for interview calls, where a fellow waits for the answer. */
    public static function interviewEffort(?string $provider = null): ?string
    {
        return self::PROVIDERS[$provider ?? self::name()]['interview_effort'];
    }

    /** Character budget for a review prompt, or null for no limit. */
    public static function reviewPromptChars(?string $provider = null): ?int
    {
        return self::PROVIDERS[$provider ?? self::name()]['review_prompt_chars'];
    }
}
