<?php

namespace App\Services\Integrations;

use App\Services\Ai\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * "Test" buttons on the Integrations page. Each test makes the same kind of
 * call the platform makes in real use, so a pass means the feature works.
 *
 * Results: ['status' => ok|warning|error, 'message' => string, 'details' => string[]].
 */
class IntegrationTester
{
    public const GITHUB_URL = 'https://api.github.com';

    public function run(string $group, array $values, array $options = []): array
    {
        return match ($group) {
            'openai' => $this->openai($values),
            'github' => $this->github($values),
            'mail' => $this->mail($values, (string) ($options['to'] ?? '')),
        };
    }

    /**
     * Tests the main provider chosen in the form and, when one is chosen, the
     * backup. Two tiny requests each, shaped like the real ones: the
     * interview call (JSON mode, temperature 0.7) and the review call (strict
     * JSON schema, temperature 0). On OpenAI they cost a fraction of a cent;
     * on the free tiers, nothing.
     */
    public function openai(array $v): array
    {
        $main = isset(AiProvider::PROVIDERS[$v['ai_provider'] ?? '']) ? $v['ai_provider'] : 'openai';
        $backup = $v['ai_backup_provider'] ?? null;
        $backup = isset(AiProvider::PROVIDERS[$backup ?? '']) && $backup !== $main ? $backup : null;

        $result = $this->testProvider($main, $v);
        if ($backup === null) {
            if ($result['status'] === 'ok' && AiProvider::name() !== $main) {
                $result['message'] .= ' Save to switch the platform to ' . AiProvider::label($main) . '.';
            }

            return $result;
        }

        $second = $this->testProvider($backup, $v);
        $mainLabel = AiProvider::label($main);
        $backupLabel = AiProvider::label($backup);
        $details = array_merge(
            array_map(fn ($d) => "Main ({$mainLabel}): {$d}", $result['details']),
            array_map(fn ($d) => "Backup ({$backupLabel}): {$d}", $second['details']),
        );

        if ($result['status'] === 'ok' && $second['status'] === 'ok') {
            return $this->result('ok', "Both work: {$mainLabel} as the main provider, {$backupLabel} as the backup.", $details);
        }
        if ($result['status'] !== 'ok' && $second['status'] !== 'ok') {
            return $this->result('error', "Main ({$mainLabel}): {$result['message']} Backup ({$backupLabel}): {$second['message']}", $details);
        }

        return $result['status'] !== 'ok'
            ? $this->result('warning', "The main provider failed, so everything would run on the backup. {$mainLabel}: {$result['message']}", $details)
            : $this->result('warning', "The main provider works, but the backup failed. {$backupLabel}: {$second['message']}", $details);
    }

    protected function testProvider(string $provider, array $v): array
    {
        $label = AiProvider::label($provider);
        $key = $v["{$provider}_api_key"] ?? null;
        if (blank($key)) {
            return $this->result('error', "No {$label} API key. Paste one above, then test again.");
        }

        if ($provider === 'openai') {
            $interviewModel = $v['openai_model'] ?: 'gpt-4o';
            $reviewModel = $v['openai_review_model'] ?: $interviewModel;
        } else {
            $interviewModel = ($v["{$provider}_model"] ?? null) ?: AiProvider::model($provider);
            $reviewModel = ($v["{$provider}_review_model"] ?? null) ?: AiProvider::reviewModel($provider);
        }
        // Tiny answers, plus thinking room for reasoning models.
        $maxTokens = AiProvider::maxTokens(10, $provider);

        $calls = [];
        if ($interviewModel !== $reviewModel) {
            $calls[] = ['interviews', $interviewModel, [
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'Answer with the JSON {"ok": true}.'],
                    ['role' => 'user', 'content' => 'ping'],
                ],
            ]];
        }
        $calls[] = [$interviewModel === $reviewModel ? 'interviews and submission reviews' : 'submission reviews', $reviewModel, [
            'temperature' => 0,
            'max_tokens' => $maxTokens,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'ping',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => ['ok' => ['type' => 'boolean']],
                        'required' => ['ok'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'messages' => [['role' => 'user', 'content' => 'Reply ok=true.']],
        ]];

        $details = [];
        $response = null;
        foreach ($calls as [$purpose, $model, $payload]) {
            $started = microtime(true);
            try {
                $response = Http::withToken($key)
                    ->withHeaders($provider === 'openai' ? array_filter(['OpenAI-Organization' => $v['openai_organization'] ?? null]) : [])
                    ->acceptJson()
                    ->timeout(30)
                    ->post(AiProvider::chatUrl($provider), ['model' => $model] + $payload);
            } catch (ConnectionException $e) {
                return $this->result('error', "Could not reach {$label} from this server: " . $e->getMessage(), $details);
            }

            if (!$response->successful()) {
                return $this->result('error', $this->explainOpenAi($response, $model, $provider), $details);
            }

            $details[] = sprintf('%s answered for %s (%d ms)', $model, $purpose, (microtime(true) - $started) * 1000);
        }

        if ($provider === 'groq' && $response) {
            $limits = $response->header('x-ratelimit-limit-tokens');
            if ($limits) {
                $details[] = "Limits on this key: {$limits} tokens a minute, " . $response->header('x-ratelimit-limit-requests') . ' requests a day per model';
            }
        }

        $message = $provider === 'openai'
            ? 'The key works, the account has credits and the models answer.'
            : 'The key works and the models answer.';

        return $this->result('ok', $message, $details);
    }

    public function github(array $v): array
    {
        $token = $v['github_token'] ?? null;
        $request = Http::withHeaders(['Accept' => 'application/vnd.github+json', 'User-Agent' => 'IKS-Reviewer', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(15);
        if (filled($token)) {
            $request = $request->withToken($token);
        }

        try {
            $response = $request->get(self::GITHUB_URL . '/rate_limit');
        } catch (ConnectionException $e) {
            return $this->result('error', 'Could not reach GitHub from this server: ' . $e->getMessage());
        }

        if ($response->status() === 401) {
            return $this->result('error', 'GitHub rejected the token (401). It may be mistyped, expired or revoked. Create a new one.');
        }
        if (!$response->successful()) {
            return $this->result('error', "GitHub answered HTTP {$response->status()}: " . ($response->json('message') ?? 'unknown error'));
        }

        $limit = (int) $response->json('resources.core.limit');
        $remaining = (int) $response->json('resources.core.remaining');
        $reset = $response->json('resources.core.reset');
        $details = ["{$remaining} of {$limit} requests left this hour" . ($reset ? ', resets at ' . now()->setTimestamp((int) $reset)->format('H:i') : '')];

        if (blank($token)) {
            return $this->result('warning', "No token set. GitHub allows this server {$limit} requests an hour, which is about 10 repository reviews. Add a token to raise it to 5,000.", $details);
        }

        try {
            $login = $request->get(self::GITHUB_URL . '/user')->json('login');
            if ($login) {
                $details[] = "Token belongs to @{$login}";
            }
        } catch (ConnectionException) {
            // The rate-limit check already proved the token; the name is a nicety.
        }

        if ($remaining === 0) {
            return $this->result('warning', 'The token works, but its hourly limit is used up. Reviews will read repositories again after the reset.', $details);
        }

        return $this->result('ok', 'The token works.', $details);
    }

    /**
     * Sends a real email with the given settings (unsaved form values
     * included), without touching the saved configuration.
     */
    public function mail(array $v, string $to): array
    {
        $mailer = $v['mail_mailer'] ?: 'smtp';

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $v['mail_host'],
            'mail.mailers.smtp.port' => $v['mail_port'],
            'mail.mailers.smtp.scheme' => $v['mail_scheme'],
            'mail.mailers.smtp.username' => $v['mail_username'],
            'mail.mailers.smtp.password' => $v['mail_password'],
            'mail.mailers.smtp.timeout' => 20,
            'mail.from.address' => $v['mail_from_address'],
            'mail.from.name' => $v['mail_from_name'],
        ]);
        app('mail.manager')->forgetMailers();

        if (blank($v['mail_from_address'])) {
            return $this->result('error', 'Set a From address first.');
        }
        if ($mailer === 'smtp' && blank($v['mail_host'])) {
            return $this->result('error', 'Set the SMTP host first.');
        }

        try {
            Mail::mailer($mailer)->raw(
                "This is a test email from the Integrations page of " . config('app.name') . ".\n\nIf you can read this, email sending works.",
                fn ($message) => $message->to($to)->subject('Test email from ' . config('app.name'))
            );
        } catch (\Throwable $e) {
            return $this->result('error', $this->explainMail($e, $v));
        }

        if ($mailer === 'log') {
            return $this->result('warning', 'Email is set to "Log file only": the test message was written to storage/logs, not sent. Choose SMTP server to send real email.');
        }

        return $this->result('ok', "Test email sent to {$to}. Check that inbox, and the spam folder.", [
            "Via {$v['mail_host']}:{$v['mail_port']} as " . ($v['mail_username'] ?: 'no login'),
        ]);
    }

    protected function explainOpenAi(Response $response, string $model, string $provider = 'openai'): string
    {
        $label = AiProvider::label($provider);
        $status = $response->status();
        // Gemini sometimes answers errors as a list: [{"error": {...}}]
        $code = $response->json('error.code') ?? $response->json('0.error.status');
        $type = $response->json('error.type');
        $message = (string) ($response->json('error.message') ?? $response->json('0.error.message') ?? '');
        $noCredits = $type === 'insufficient_quota' || in_array($code, ['insufficient_quota', 'credit_balance_exhausted'], true);
        $suggest = match ($provider) {
            'groq' => 'openai/gpt-oss-120b or openai/gpt-oss-20b',
            'gemini' => 'gemini-3.5-flash',
            default => 'gpt-4o or gpt-4o-mini',
        };

        return match (true) {
            $status === 401 => "{$label} rejected the key (401). Check it was copied in full and has not been revoked.",
            $noCredits => 'The key is valid, but the OpenAI account has no credits left. Add credits at platform.openai.com → Settings → Billing, or switch the provider to Groq (free).',
            $status === 413 => "The request is too large for this {$label} key's limits: {$message}",
            $status === 429 => "{$label} is rate-limiting this key right now" . ($provider === 'groq' ? ' (free tier: per-minute and daily limits)' : '') . '. Wait a minute and test again.',
            $status === 404 || in_array($code, ['model_not_found', 'model_decommissioned'], true) => "This key cannot use the model \"{$model}\". Check the name, or use {$suggest}.",
            $status === 400 && ($code === 'json_validate_failed' || str_contains($message, 'response_format') || str_contains($message, 'json_schema'))
                => "The model \"{$model}\" does not give the structured answers the platform needs. Use {$suggest}.",
            $status === 400 && (str_contains($message, 'max_tokens') || str_contains($message, 'temperature')) => "The model \"{$model}\" does not accept the settings the platform sends (temperature, max_tokens). Use {$suggest}.",
            $status === 403 => "{$label} refused access (403): {$message}",
            default => "{$label} answered HTTP {$status}" . ($message !== '' ? ": {$message}" : '.'),
        };
    }

    protected function explainMail(\Throwable $e, array $v): string
    {
        $message = $e->getMessage();
        $where = "{$v['mail_host']}:{$v['mail_port']}";

        return match (true) {
            (bool) preg_match('/\b(535|534|530)\b|authenticat|credentials|username and password/i', $message)
                => "The mail server at {$where} rejected the username or password. Details: {$message}",
            (bool) preg_match('/certificate|ssl|tls|crypto/i', $message)
                => "Secure connection to {$where} failed. Try the other Security option (port 465 usually needs SSL/TLS, 587 needs STARTTLS). Details: {$message}",
            (bool) preg_match('/could not be established|timed out|refused|getaddrinfo|resolve|unreachable/i', $message)
                => "Could not connect to {$where}. Check the host and port, and that the hosting firewall allows outgoing mail. Details: {$message}",
            (bool) preg_match('/\b55[0-4]\b|sender|relay|not (allowed|permitted)/i', $message)
                => "The server refused the message. The From address usually has to belong to the mail account. Details: {$message}",
            default => 'Sending failed: ' . $message,
        };
    }

    protected function result(string $status, string $message, array $details = []): array
    {
        return compact('status', 'message', 'details');
    }
}
