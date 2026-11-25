<?php

namespace App\Services\Ai;

/**
 * The AI provider the platform talks to (AI interviews and AI review).
 *
 * Both providers speak the OpenAI chat-completions API, so services only
 * need the URL, key and model from here. The active provider is chosen on
 * the admin Integrations page (or AI_PROVIDER in .env).
 */
class AiProvider
{
    public const PROVIDERS = [
        'openai' => [
            'label' => 'OpenAI',
            'base_url' => 'https://api.openai.com/v1',
            // Models answer directly: the token budget is for the answer only.
            'reasoning' => false,
            // No practical request-size limit at our prompt sizes.
            'review_prompt_chars' => null,
        ],
        'groq' => [
            'label' => 'Groq',
            'base_url' => 'https://api.groq.com/openai/v1',
            // gpt-oss models think before answering, and the thinking counts
            // against max_tokens: give them room.
            'reasoning' => true,
            // Free tier: 8,000 tokens per minute per model, prompt + answer.
            // ~16k characters keeps a review prompt near 4,500 tokens.
            'review_prompt_chars' => 16000,
        ],
    ];

    public static function name(): string
    {
        $name = (string) config('services.ai.provider', 'openai');

        return isset(self::PROVIDERS[$name]) ? $name : 'openai';
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

    public static function configured(): bool
    {
        return self::key() !== null;
    }

    /** Model for AI interviews. */
    public static function model(?string $provider = null): string
    {
        $provider ??= self::name();

        return (string) (config("services.{$provider}.model") ?: ($provider === 'groq' ? 'openai/gpt-oss-20b' : 'gpt-4o'));
    }

    /** Model for reviewing submissions (needs strict JSON-schema output). */
    public static function reviewModel(?string $provider = null): string
    {
        $provider ??= self::name();

        return (string) (config("services.{$provider}.review_model") ?: ($provider === 'groq' ? 'openai/gpt-oss-120b' : self::model($provider)));
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
     * max_tokens for a call sized for OpenAI. Reasoning models get extra
     * room for their thinking, which counts against the same budget.
     */
    public static function maxTokens(int $answerTokens, ?string $provider = null): int
    {
        return self::PROVIDERS[$provider ?? self::name()]['reasoning'] ? $answerTokens + 2000 : $answerTokens;
    }

    /**
     * reasoning_effort values to try for a review, in order. Groq's gpt-oss
     * sometimes stops before finishing the JSON at its default effort; an
     * explicit "medium", then "high" on retries, completes it.
     * [null] = do not send the parameter (OpenAI GPT-4o).
     */
    public static function reasoningEfforts(?string $provider = null): array
    {
        return self::PROVIDERS[$provider ?? self::name()]['reasoning'] ? ['medium', 'high', 'high'] : [null];
    }

    /** Character budget for a review prompt, or null for no limit. */
    public static function reviewPromptChars(?string $provider = null): ?int
    {
        return self::PROVIDERS[$provider ?? self::name()]['review_prompt_chars'];
    }
}
