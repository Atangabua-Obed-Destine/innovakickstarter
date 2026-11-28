<?php

namespace App\Services\Interview;

use App\Services\Ai\AiClient;
use App\Services\Ai\AiUnavailableException;

/**
 * One JSON-object chat call for the interview engine. Returns the decoded object or
 * throws (AiUnavailableException when no provider answered, RuntimeException when the
 * answer is not usable JSON).
 */
class AiJson
{
    public function __construct(protected AiClient $ai) {}

    public function ask(string $system, string $user, int $tokens, array $options = [], string $purpose = 'interview'): array
    {
        $call = $this->ai->chat($purpose, [
            'temperature' => $options['temperature'] ?? 0.4,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
        ], $tokens, array_diff_key($options, ['temperature' => true]));

        $choice = $call['response']->json('choices.0');
        if (($choice['finish_reason'] ?? null) === 'length') {
            throw new \RuntimeException('The AI answer was cut off.');
        }
        $decoded = self::decode((string) ($choice['message']['content'] ?? ''));
        if ($decoded === null) {
            throw new \RuntimeException('The AI answer was not JSON.');
        }

        return $decoded;
    }

    public static function decode(string $content): ?array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $content);
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            $start = strpos($content, '{');
            $end = strrpos($content, '}');
            if ($start !== false && $end > $start) {
                $decoded = json_decode(substr($content, $start, $end - $start + 1), true);
            }
        }

        return is_array($decoded) ? $decoded : null;
    }

    public static function isUnavailable(\Throwable $e): bool
    {
        return $e instanceof AiUnavailableException;
    }
}
