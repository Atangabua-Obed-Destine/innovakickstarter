<?php

namespace App\Services\Integrations;

use App\Models\AuditLog;
use App\Models\IntegrationCredential;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keys, tokens and settings for outside services, managed from the admin
 * Integrations page instead of .env.
 *
 * A value saved on the page overrides .env; removing it falls back to .env.
 * Values are encrypted at rest, cached as ciphertext, and secrets are never
 * sent back to the browser (only the last four characters).
 *
 * Bound as a singleton: apply() runs at boot and before every queued job, so
 * long-running queue workers pick up changes without a restart.
 */
class IntegrationSettings
{
    public const CACHE_KEY = 'integration_credentials';

    /**
     * Groups and fields. 'config' is the Laravel config key the value feeds;
     * 'env' is the .env variable it overrides (shown to admins).
     */
    public const GROUPS = [
        'openai' => [
            'title' => 'OpenAI',
            'used_for' => 'AI mock interviews and AI review of curriculum submissions.',
            'fields' => [
                'openai_api_key' => [
                    'label' => 'API key',
                    'config' => 'services.openai.key',
                    'env' => 'OPENAI_API_KEY',
                    'secret' => true,
                    'rules' => 'string|max:300',
                    'placeholder' => 'sk-...',
                    'help' => 'Create one at platform.openai.com → API keys. The account needs credits (Settings → Billing).',
                ],
                'openai_organization' => [
                    'label' => 'Organization ID',
                    'config' => 'services.openai.organization',
                    'env' => 'OPENAI_ORGANIZATION',
                    'rules' => 'string|max:100',
                    'placeholder' => 'org-... (optional)',
                    'help' => 'Only needed if your key belongs to several organizations.',
                ],
                'openai_model' => [
                    'label' => 'Interview model',
                    'config' => 'services.openai.model',
                    'env' => 'OPENAI_MODEL',
                    'rules' => 'string|max:100|regex:/^[A-Za-z0-9._:-]+$/',
                    'placeholder' => 'gpt-4o',
                    'help' => 'Used for AI mock interviews.',
                ],
                'openai_review_model' => [
                    'label' => 'Submission review model',
                    'config' => 'services.openai.review_model',
                    'env' => 'OPENAI_REVIEW_MODEL',
                    'follows' => 'openai_model',
                    'rules' => 'string|max:100|regex:/^[A-Za-z0-9._:-]+$/',
                    'placeholder' => 'Same as the interview model',
                    'help' => 'Must support structured outputs (gpt-4o, gpt-4o-mini or newer). Leave blank to use the interview model.',
                ],
            ],
        ],
        'github' => [
            'title' => 'GitHub',
            'used_for' => 'AI review reads submitted repositories and checks their history.',
            'fields' => [
                'github_token' => [
                    'label' => 'Access token',
                    'config' => 'services.github.token',
                    'env' => 'GITHUB_TOKEN',
                    'secret' => true,
                    'rules' => 'string|max:255',
                    'placeholder' => 'github_pat_... or ghp_...',
                    'help' => 'GitHub → Settings → Developer settings → Personal access tokens. A fine-grained token with "Public repositories (read-only)" and no other permissions is enough. Without one, GitHub allows 60 requests an hour (about 10 reviews).',
                ],
            ],
        ],
        'mail' => [
            'title' => 'Email',
            'used_for' => 'Password resets, email verification and all notifications.',
            'fields' => [
                'mail_mailer' => [
                    'label' => 'Send with',
                    'config' => 'mail.default',
                    'env' => 'MAIL_MAILER',
                    'options' => ['smtp' => 'SMTP server', 'log' => 'Log file only (nothing is sent)'],
                    'rules' => 'in:smtp,log',
                ],
                'mail_host' => [
                    'label' => 'SMTP host',
                    'config' => 'mail.mailers.smtp.host',
                    'env' => 'MAIL_HOST',
                    'rules' => 'string|max:255|regex:/^[A-Za-z0-9.\-]+$/',
                    'placeholder' => 'mail.example.com',
                ],
                'mail_port' => [
                    'label' => 'Port',
                    'config' => 'mail.mailers.smtp.port',
                    'env' => 'MAIL_PORT',
                    'cast' => 'int',
                    'rules' => 'integer|min:1|max:65535',
                    'placeholder' => '465 or 587',
                ],
                'mail_scheme' => [
                    'label' => 'Security',
                    'config' => 'mail.mailers.smtp.scheme',
                    'env' => 'MAIL_SCHEME',
                    'options' => [
                        'auto' => 'Automatic (SSL on port 465, STARTTLS otherwise)',
                        'smtps' => 'SSL/TLS',
                        'smtp' => 'STARTTLS / plain',
                    ],
                    'null_option' => 'auto',
                    'rules' => 'in:auto,smtp,smtps',
                ],
                'mail_username' => [
                    'label' => 'Username',
                    'config' => 'mail.mailers.smtp.username',
                    'env' => 'MAIL_USERNAME',
                    'rules' => 'string|max:255',
                    'placeholder' => 'Usually the full email address',
                ],
                'mail_password' => [
                    'label' => 'Password',
                    'config' => 'mail.mailers.smtp.password',
                    'env' => 'MAIL_PASSWORD',
                    'secret' => true,
                    'hide_tail' => true,
                    'rules' => 'string|max:255',
                ],
                'mail_from_address' => [
                    'label' => 'From address',
                    'config' => 'mail.from.address',
                    'env' => 'MAIL_FROM_ADDRESS',
                    'rules' => 'email|max:255',
                    'placeholder' => 'noreply@example.com',
                    'help' => 'Most providers only deliver mail sent from an address on the account.',
                ],
                'mail_from_name' => [
                    'label' => 'From name',
                    'config' => 'mail.from.name',
                    'env' => 'MAIL_FROM_NAME',
                    'rules' => 'string|max:100',
                ],
            ],
        ],
    ];

    /** Config values as loaded from .env / config files, before any override. */
    protected array $originals = [];

    /** Fingerprint of the mail settings last applied, to reset cached mailers. */
    protected ?string $appliedMail = null;

    public static function groupExists(string $group): bool
    {
        return isset(self::GROUPS[$group]);
    }

    public static function fields(string $group): array
    {
        return self::GROUPS[$group]['fields'] ?? [];
    }

    public static function allFields(): array
    {
        return array_merge(...array_values(array_map(fn ($g) => $g['fields'], self::GROUPS)));
    }

    /**
     * Push saved values into the running config. Never throws: a missing
     * table or unreachable database leaves .env values in place.
     */
    public function apply(): void
    {
        try {
            $this->rememberOriginals();
            $saved = $this->saved();
        } catch (\Throwable) {
            return;
        }

        foreach (self::allFields() as $key => $field) {
            config([$field['config'] => $this->toConfig($field, $this->resolve($key, $saved))]);
        }

        $mail = md5(serialize([config('mail.default'), config('mail.mailers.smtp'), config('mail.from')]));
        if ($this->appliedMail !== null && $this->appliedMail !== $mail && app()->resolved('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
        $this->appliedMail = $mail;
    }

    /**
     * What the page shows for one group: where each value comes from, the
     * form value for plain fields and a masked hint for secrets.
     */
    public function status(string $group): array
    {
        $this->rememberOriginals();
        $saved = $this->saved();
        $meta = IntegrationCredential::whereIn('key', array_keys(self::fields($group)))
            ->with('updatedBy:id,name')
            ->get()
            ->keyBy('key');

        $rows = [];
        foreach (self::fields($group) as $key => $field) {
            $base = $this->base($key);
            $effective = $this->resolve($key, $saved);
            $isSaved = array_key_exists($key, $saved);
            $secret = !empty($field['secret']);

            $rows[$key] = $field + [
                'key' => $key,
                'source' => $isSaved ? 'dashboard' : (filled($base) ? 'env' : 'none'),
                'is_set' => filled($effective),
                'value' => $secret ? '' : (string) ($isSaved ? $saved[$key] : ($base ?? '')),
                'masked' => $secret && filled($effective) ? $this->mask((string) $effective, !empty($field['hide_tail'])) : null,
                'updated_at' => $meta[$key]->updated_at ?? null,
                'updated_by' => $meta[$key]->updatedBy->name ?? null,
            ];
        }

        return $rows;
    }

    /**
     * Save a group from the form. Secrets: blank keeps the current value,
     * the remove box deletes it. Plain fields: blank or equal to .env means
     * "use .env". Returns what changed (secret values never included).
     */
    public function save(string $group, array $values, array $remove, User $by): array
    {
        $this->rememberOriginals();
        $saved = $this->saved();
        $changes = [];

        foreach (self::fields($group) as $key => $field) {
            $value = trim((string) ($values[$key] ?? ''));

            if (!empty($field['secret'])) {
                if (!empty($remove[$key])) {
                    if (array_key_exists($key, $saved)) {
                        IntegrationCredential::where('key', $key)->delete();
                        $changes[$key] = 'removed (back to .env)';
                    }
                    continue;
                }
                if ($value !== '' && $value !== ($saved[$key] ?? null)) {
                    $this->store($key, $value, $by);
                    $changes[$key] = 'updated';
                }
                continue;
            }

            $base = (string) ($this->base($key) ?? '');
            if ($value === '' || $value === $base) {
                if (array_key_exists($key, $saved)) {
                    IntegrationCredential::where('key', $key)->delete();
                    $changes[$key] = $base === '' ? 'cleared' : "back to .env ({$base})";
                }
                continue;
            }
            if ($value !== ($saved[$key] ?? null)) {
                $this->store($key, $value, $by);
                $changes[$key] = $value;
            }
        }

        if ($changes) {
            Cache::forget(self::CACHE_KEY);
            $this->apply();

            AuditLog::create([
                'admin_id' => $by->id,
                'action' => 'integration_settings_updated',
                'auditable_type' => IntegrationCredential::class,
                'auditable_id' => 'integration_' . $group,
                'justification' => self::GROUPS[$group]['title'] . ' integration settings changed',
                'new_values' => $changes,
            ]);
        }

        return $changes;
    }

    /**
     * The values a test should use: what is typed in the form, falling back
     * to the current settings for blank secrets. Nothing is saved.
     */
    public function valuesForTest(string $group, array $values, array $remove): array
    {
        $this->rememberOriginals();
        $saved = $this->saved();
        $result = [];

        foreach (self::fields($group) as $key => $field) {
            $typed = trim((string) ($values[$key] ?? ''));
            if (!empty($field['secret'])) {
                $value = match (true) {
                    !empty($remove[$key]) => $this->base($key),
                    $typed !== '' => $typed,
                    default => $this->resolve($key, $saved),
                };
            } else {
                $value = $typed !== '' ? $typed : $this->base($key);
            }
            $result[$key] = $value;
        }

        foreach (self::fields($group) as $key => $field) {
            if (isset($field['follows']) && blank($result[$key])) {
                $result[$key] = $result[$field['follows']] ?? null;
            }
            $result[$key] = $this->toConfig($field, $result[$key]);
        }

        return $result;
    }

    /**
     * Decrypted saved values, keyed by field. The cache holds ciphertext only.
     */
    public function saved(): array
    {
        $rows = Cache::rememberForever(self::CACHE_KEY, fn () => DB::table('integration_credentials')->pluck('value', 'key')->all());

        $values = [];
        foreach ($rows as $key => $cipher) {
            try {
                $values[$key] = Crypt::decryptString($cipher);
            } catch (DecryptException) {
                // APP_KEY changed since it was saved: treat as not set.
                Log::warning("Integration setting {$key} could not be decrypted (APP_KEY changed?); using .env instead.");
            }
        }

        return $values;
    }

    protected function store(string $key, string $value, User $by): void
    {
        IntegrationCredential::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $by->id]);
    }

    protected function rememberOriginals(): void
    {
        foreach (self::allFields() as $field) {
            if (!array_key_exists($field['config'], $this->originals)) {
                $this->originals[$field['config']] = config($field['config']);
            }
        }
    }

    /**
     * The .env value of a field, as a form value. A review model that only
     * mirrors the interview model counts as unset, so it keeps following it.
     */
    protected function base(string $key): mixed
    {
        $field = self::allFields()[$key];
        $value = $this->originals[$field['config']] ?? null;

        if (isset($field['follows'])) {
            $leader = self::allFields()[$field['follows']];
            if ($value === ($this->originals[$leader['config']] ?? null)) {
                return null;
            }
        }
        if ($value === null && isset($field['null_option'])) {
            return $field['null_option'];
        }

        return $value === null ? null : (string) $value;
    }

    /** Effective form-level value: saved, else .env, else the field it follows. */
    protected function resolve(string $key, array $saved): mixed
    {
        $value = array_key_exists($key, $saved) ? $saved[$key] : $this->base($key);
        $field = self::allFields()[$key];

        if (blank($value) && isset($field['follows'])) {
            return $this->resolve($field['follows'], $saved);
        }

        return $value;
    }

    protected function toConfig(array $field, mixed $value): mixed
    {
        if (isset($field['null_option']) && $value === $field['null_option']) {
            return null;
        }
        if (($field['cast'] ?? null) === 'int' && filled($value)) {
            return (int) $value;
        }

        return $value === '' ? null : $value;
    }

    protected function mask(string $value, bool $hideTail): string
    {
        if ($hideTail || mb_strlen($value) < 12) {
            return '••••••••';
        }

        return '••••••••' . mb_substr($value, -4);
    }
}
