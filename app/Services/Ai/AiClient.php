<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Chat completions with automatic failover: the main provider first, then
 * the backup (see AiProvider::chain) when the main one fails, is down or
 * is rate-limited.
 */
class AiClient
{
    /**
     * @param string         $purpose      'interview' or 'review' (picks the model)
     * @param array|callable $payload      Chat body without model / max_tokens, or
     *                                     fn (string $provider): array to adapt it per provider
     * @param int            $answerTokens max_tokens sized for GPT-4o; thinking models get more
     * @param array          $options      timeout (seconds, default 30);
     *                                     try_efforts (bool): on json_validate_failed, retry with
     *                                     more reasoning (reviews);
     *                                     wait_on_rate_limit (bool, queued work only): retry server
     *                                     and connection errors, and on the last provider wait for
     *                                     retry-after instead of failing. Without it, a failure
     *                                     moves straight to the backup.
     * @return array{response: Response, provider: string, model: string}
     *
     * @throws AiUnavailableException when no provider answered
     */
    public function chat(string $purpose, array|callable $payload, int $answerTokens, array $options = []): array
    {
        $chain = AiProvider::chain();
        if ($chain === []) {
            throw new AiUnavailableException('No AI provider key is set (Admin → Integrations).');
        }

        $errors = [];
        foreach ($chain as $i => $provider) {
            $last = $i === count($chain) - 1;
            $model = $purpose === 'review' ? AiProvider::reviewModel($provider) : AiProvider::model($provider);
            $body = is_callable($payload) ? $payload($provider) : $payload;

            try {
                $response = $this->send($provider, $purpose, $model, $body, $answerTokens, $options, $last);

                if ($i > 0) {
                    Log::info("AI {$purpose} answered by backup provider {$provider} after: " . implode(' · ', $errors));
                }

                return ['response' => $response, 'provider' => $provider, 'model' => $model];
            } catch (RequestException|ConnectionException $e) {
                $errors[] = $this->describe($provider, $e);
            }
        }

        throw new AiUnavailableException(implode(' · ', $errors));
    }

    protected function send(string $provider, string $purpose, string $model, array $body, int $answerTokens, array $options, bool $last): Response
    {
        $efforts = match (true) {
            !empty($options['try_efforts']) => AiProvider::reasoningEfforts($provider),
            $purpose === 'interview' => [AiProvider::interviewEffort($provider)],
            default => [null],
        };

        foreach ($efforts as $i => $effort) {
            try {
                return $this->request($provider, $options, $last)
                    ->post(AiProvider::chatUrl($provider), array_filter($body + [
                        'model' => $model,
                        'max_tokens' => AiProvider::maxTokens($answerTokens, $provider),
                        'reasoning_effort' => $effort,
                    ], fn ($value) => $value !== null));
            } catch (RequestException $e) {
                // Groq checks the JSON after generation; a model that stops early
                // (missing fields) gets json_validate_failed. Thinking harder fixes it.
                if ($e->response->json('error.code') === 'json_validate_failed' && isset($efforts[$i + 1])) {
                    continue;
                }
                throw $e;
            }
        }

        throw new \LogicException('unreachable');
    }

    protected function request(string $provider, array $options, bool $last): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::withToken(AiProvider::key($provider))
            ->withHeaders(AiProvider::headers($provider))
            ->acceptJson()
            ->timeout($options['timeout'] ?? 30);

        // Queued work (reviews) can wait and retry. A fellow waiting on an answer
        // cannot: go straight to the backup, or fail.
        $patient = !empty($options['wait_on_rate_limit']);
        if (!$patient) {
            return $request->throw();
        }
        $waitOnRateLimit = $last;

        return $request->retry(3, function (int $attempt, ?\Throwable $e = null) {
            // A per-minute limit says how long to wait (Groq free tier: 8,000 tokens a minute).
            $after = $e instanceof RequestException ? (float) $e->response->header('retry-after') : 0;

            return $after > 0 ? (int) min(65000, ceil($after * 1000) + 500) : $attempt * 1000;
        }, function (\Throwable $e) use ($waitOnRateLimit) {
            if (!$e instanceof RequestException) {
                return true; // connection problem
            }
            $status = $e->response->status();
            if ($status === 429) {
                // Out of credits is not temporary. Otherwise wait only when there is
                // no backup to try and nobody is waiting on the answer.
                return $waitOnRateLimit && !$this->noCredits($e->response);
            }

            return $status >= 500;
        }, throw: true);
    }

    public function noCredits(Response $response): bool
    {
        return in_array('insufficient_quota', [$response->json('error.code'), $response->json('error.type')], true)
            || $response->json('error.code') === 'credit_balance_exhausted';
    }

    protected function describe(string $provider, \Throwable $e): string
    {
        $label = AiProvider::label($provider);
        if ($e instanceof RequestException) {
            $message = $e->response->json('error.message') ?? $e->response->json('0.error.message') ?? $e->getMessage();

            return "{$label} (HTTP {$e->response->status()}): " . mb_substr((string) $message, 0, 300);
        }

        return "{$label}: " . mb_substr($e->getMessage(), 0, 300);
    }
}
