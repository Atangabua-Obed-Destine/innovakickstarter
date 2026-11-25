<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\IntegrationCredential;
use App\Models\User;
use App\Services\Integrations\IntegrationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('fellow');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        Http::preventStrayRequests();
    }

    /**
     * Pretend .env holds these values, and start from a fresh settings
     * service so it records them as the .env baseline.
     */
    protected function env(array $config): void
    {
        config($config);
        $this->app->forgetInstance(IntegrationSettings::class);
    }

    protected function saveGroup(string $group, array $values, array $remove = [])
    {
        return $this->actingAs($this->admin)->post(route('admin.integrations.update', $group), ['values' => $values, 'remove' => $remove]);
    }

    public function test_a_saved_key_is_encrypted_overrides_env_and_is_never_shown_again(): void
    {
        $this->env(['services.openai.key' => 'sk-from-env-0000000000', 'services.openai.model' => 'gpt-4o', 'services.openai.review_model' => 'gpt-4o']);

        $this->saveGroup('openai', ['openai_api_key' => 'sk-dashboard-secret-ABCD', 'openai_model' => 'gpt-4o'])
            ->assertRedirect()
            ->assertSessionHas('success', 'OpenAI settings saved.');

        $raw = DB::table('integration_credentials')->where('key', 'openai_api_key')->value('value');
        $this->assertStringNotContainsString('sk-dashboard-secret', $raw);
        $this->assertSame('sk-dashboard-secret-ABCD', Crypt::decryptString($raw));
        $this->assertSame('sk-dashboard-secret-ABCD', config('services.openai.key'));
        // Equal to .env, so not stored.
        $this->assertDatabaseMissing('integration_credentials', ['key' => 'openai_model']);

        $page = $this->actingAs($this->admin)->get(route('admin.integrations.index'))->assertOk();
        $page->assertSee('••••••••ABCD')->assertSee('Saved here');
        $this->assertStringNotContainsString('sk-dashboard-secret', $page->getContent());
        $this->assertStringNotContainsString('sk-from-env', $page->getContent());

        $log = AuditLog::where('action', 'integration_settings_updated')->first();
        $this->assertSame(['openai_api_key' => 'updated'], $log->new_values);
    }

    public function test_blank_keeps_the_key_and_remove_goes_back_to_env(): void
    {
        $this->env(['services.openai.key' => 'sk-from-env-0000000000']);
        $this->saveGroup('openai', ['openai_api_key' => 'sk-dashboard-secret-ABCD']);

        $this->saveGroup('openai', ['openai_api_key' => ''])->assertSessionHas('success', 'No changes to OpenAI settings.');
        $this->assertSame('sk-dashboard-secret-ABCD', config('services.openai.key'));

        $this->saveGroup('openai', ['openai_api_key' => ''], ['openai_api_key' => '1']);
        $this->assertDatabaseMissing('integration_credentials', ['key' => 'openai_api_key']);
        $this->assertSame('sk-from-env-0000000000', config('services.openai.key'));
        $this->actingAs($this->admin)->get(route('admin.integrations.index'))->assertSee('From .env');
    }

    public function test_review_model_follows_the_interview_model_unless_set(): void
    {
        $this->env(['services.openai.model' => 'gpt-4o', 'services.openai.review_model' => 'gpt-4o']);

        $this->saveGroup('openai', ['openai_model' => 'gpt-4.1', 'openai_review_model' => '']);
        $this->assertSame('gpt-4.1', config('services.openai.model'));
        $this->assertSame('gpt-4.1', config('services.openai.review_model'));

        $this->saveGroup('openai', ['openai_model' => 'gpt-4.1', 'openai_review_model' => 'gpt-4o-mini']);
        $this->assertSame('gpt-4o-mini', config('services.openai.review_model'));

        // Back to .env for the interview model; the review model stays.
        $this->saveGroup('openai', ['openai_model' => '', 'openai_review_model' => 'gpt-4o-mini']);
        $this->assertSame('gpt-4o', config('services.openai.model'));
        $this->assertSame('gpt-4o-mini', config('services.openai.review_model'));
    }

    public function test_mail_settings_feed_the_mailer_config(): void
    {
        $this->env(['mail.default' => 'log', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 2525, 'mail.mailers.smtp.scheme' => null]);

        $this->saveGroup('mail', [
            'mail_mailer' => 'smtp',
            'mail_host' => 'mail.example.org',
            'mail_port' => '465',
            'mail_scheme' => 'smtps',
            'mail_username' => 'noreply@example.org',
            'mail_password' => 'p4ssword',
            'mail_from_address' => 'noreply@example.org',
            'mail_from_name' => 'Platform',
        ])->assertSessionHasNoErrors();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('mail.example.org', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('p4ssword', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@example.org', config('mail.from.address'));

        $page = $this->actingAs($this->admin)->get(route('admin.integrations.index'))->assertOk();
        $this->assertStringNotContainsString('p4ssword', $page->getContent());

        // "Automatic" security is stored as null for Laravel.
        $this->saveGroup('mail', ['mail_mailer' => 'smtp', 'mail_host' => 'mail.example.org', 'mail_port' => '587', 'mail_scheme' => 'auto', 'mail_from_address' => 'noreply@example.org']);
        $this->assertNull(config('mail.mailers.smtp.scheme'));
        $this->assertDatabaseMissing('integration_credentials', ['key' => 'mail_scheme']);
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->saveGroup('mail', ['mail_from_address' => 'not-an-email', 'mail_port' => '99999', 'mail_mailer' => 'sendmail'])
            ->assertSessionHasErrors(['values.mail_from_address', 'values.mail_port', 'values.mail_mailer']);
        $this->saveGroup('openai', ['openai_model' => 'gpt-4o; rm -rf'])->assertSessionHasErrors('values.openai_model');
        $this->assertSame(0, IntegrationCredential::count());
    }

    public function test_queue_workers_pick_up_a_new_key_before_each_job(): void
    {
        $this->saveGroup('github', ['github_token' => 'ghp_dashboardtoken1234']);
        config(['services.github.token' => 'stale-value-in-a-long-running-worker']);

        dispatch(fn () => cache()->put('seen_token', config('services.github.token')));

        $this->assertSame('ghp_dashboardtoken1234', cache('seen_token'));
    }

    public function test_a_value_that_cannot_be_decrypted_falls_back_to_env(): void
    {
        $this->env(['services.github.token' => 'ghp_from_env_token_9999']);
        DB::table('integration_credentials')->insert(['key' => 'github_token', 'value' => 'not-ciphertext', 'created_at' => now(), 'updated_at' => now()]);

        app(IntegrationSettings::class)->apply();

        $this->assertSame('ghp_from_env_token_9999', config('services.github.token'));
        $this->actingAs($this->admin)->get(route('admin.integrations.index'))->assertOk();
    }

    public function test_only_admins_can_open_the_page(): void
    {
        $fellow = User::factory()->create(['role' => 'fellow']);
        $fellow->assignRole('fellow');

        $this->actingAs($fellow)->get(route('admin.integrations.index'))->assertForbidden();
        $this->actingAs($fellow)->postJson(route('admin.integrations.test', 'openai'))->assertForbidden();
    }

    // ---------------------------------------------------------------- tests of the Test buttons

    public function test_openai_test_uses_the_typed_key_without_saving_and_explains_no_credits(): void
    {
        $this->env(['services.openai.key' => 'sk-from-env-0000000000', 'services.openai.model' => 'gpt-4o', 'services.openai.review_model' => 'gpt-4o']);
        Http::fake([
            // The body OpenAI really returns for an account without credits.
            'api.openai.com/*' => Http::response(['error' => ['message' => 'You have no credits remaining.', 'type' => 'insufficient_quota', 'param' => null, 'code' => 'credit_balance_exhausted']], 429),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'openai'), [
            'values' => ['openai_api_key' => 'sk-typed-not-saved-1234', 'openai_model' => 'gpt-4o'],
        ])->assertOk();

        $response->assertJson(['status' => 'error']);
        $this->assertStringContainsString('no credits', $response->json('message'));
        $this->assertStringNotContainsString('sk-', $response->getContent());
        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer sk-typed-not-saved-1234'));
        $this->assertSame(0, IntegrationCredential::count());
    }

    public function test_openai_test_passes_and_checks_both_models_the_way_the_platform_calls_them(): void
    {
        $this->env(['services.openai.key' => 'sk-from-env-0000000000', 'services.openai.model' => 'gpt-4o', 'services.openai.review_model' => 'gpt-4o']);
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => '{"ok":true}']]]])]);

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'openai'), [
            'values' => ['openai_api_key' => '', 'openai_model' => 'gpt-4o', 'openai_review_model' => 'gpt-4o-mini'],
        ])->assertOk()->assertJson(['status' => 'ok']);

        Http::assertSentCount(2);
        Http::assertSent(fn (HttpRequest $r) => $r['model'] === 'gpt-4o' && $r['response_format']['type'] === 'json_object'
            && $r->hasHeader('Authorization', 'Bearer sk-from-env-0000000000'));
        Http::assertSent(fn (HttpRequest $r) => $r['model'] === 'gpt-4o-mini' && $r['response_format']['type'] === 'json_schema' && $r['temperature'] === 0);
    }

    public function test_openai_test_explains_a_wrong_key_and_a_missing_key(): void
    {
        $this->env(['services.openai.key' => null]);
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Incorrect API key provided', 'code' => 'invalid_api_key']], 401)]);

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'openai'), ['values' => []])
            ->assertJson(['status' => 'error', 'message' => 'No API key. Paste one above, then test again.']);
        Http::assertNothingSent();

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'openai'), ['values' => ['openai_api_key' => 'sk-wrong-key-000000']])
            ->assertJson(['status' => 'error'])
            ->assertJsonFragment(['message' => 'OpenAI rejected the key (401). Check it was copied in full and has not been revoked.']);
    }

    public function test_github_test_reports_the_limit_with_and_without_a_token(): void
    {
        $this->env(['services.github.token' => null]);
        Http::fake([
            'api.github.com/rate_limit' => Http::sequence()
                ->push(['resources' => ['core' => ['limit' => 60, 'remaining' => 58, 'reset' => time() + 600]]])
                ->push(['message' => 'Bad credentials'], 401)
                ->push(['resources' => ['core' => ['limit' => 5000, 'remaining' => 4999, 'reset' => time() + 600]]]),
            'api.github.com/user' => Http::response(['login' => 'iks-bot']),
        ]);

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'github'), ['values' => []])
            ->assertJson(['status' => 'warning'])->assertJsonFragment(['details' => ['58 of 60 requests left this hour, resets at ' . now()->addSeconds(600)->format('H:i')]]);

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'github'), ['values' => ['github_token' => 'ghp_bad']])
            ->assertJson(['status' => 'error']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'github'), ['values' => ['github_token' => 'ghp_good']])
            ->assertJson(['status' => 'ok', 'message' => 'The token works.']);
        $this->assertContains('Token belongs to @iks-bot', $response->json('details'));
    }

    public function test_mail_test_sends_with_the_form_values_and_explains_failures(): void
    {
        $base = ['mail_host' => '127.0.0.1', 'mail_port' => '1', 'mail_scheme' => 'smtp', 'mail_from_address' => 'noreply@example.org', 'mail_from_name' => 'Platform'];

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'mail'), ['values' => $base + ['mail_mailer' => 'log'], 'test_to' => 'admin@example.org'])
            ->assertJson(['status' => 'warning']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'mail'), ['values' => $base + ['mail_mailer' => 'smtp'], 'test_to' => 'admin@example.org'])
            ->assertJson(['status' => 'error']);
        $this->assertStringContainsString('Could not connect to 127.0.0.1:1', $response->json('message'));

        $this->actingAs($this->admin)->postJson(route('admin.integrations.test', 'mail'), ['values' => $base, 'test_to' => 'nope'])
            ->assertStatus(422);
        $this->assertSame(0, IntegrationCredential::count());
    }
}
