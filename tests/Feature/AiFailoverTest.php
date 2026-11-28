<?php

namespace Tests\Feature;

use App\Enums\InterviewType;
use App\Models\InterviewSession;
use App\Models\User;
use App\Services\AIInterviewService;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiProvider;
use App\Services\Ai\AiUnavailableException;
use App\Services\AiReview\AiGrader;
use App\Services\Integrations\IntegrationTester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Backup AI provider (Groq main, Gemini backup) and "not scored" instead of
 * invented interview scores.
 */
class AiFailoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Sleep::fake();
        config([
            'services.ai.provider' => 'groq',
            'services.ai.backup' => 'gemini',
            'services.groq.key' => 'gsk_main_key_000000000000',
            'services.gemini.key' => 'AQ.backup_key_00000000000',
            'services.openai.key' => null,
        ]);
    }

    protected function chatAnswer(array $content): array
    {
        return ['choices' => [['finish_reason' => 'stop', 'message' => ['content' => json_encode($content)]]], 'usage' => ['prompt_tokens' => 100, 'completion_tokens' => 50]];
    }

    protected function rateLimited(): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['error' => ['message' => 'Rate limit reached for model openai/gpt-oss-20b', 'code' => 'rate_limit_exceeded']], 429, ['retry-after' => '40']);
    }

    public function test_the_chain_is_main_then_backup_and_skips_providers_without_a_key(): void
    {
        $this->assertSame(['groq', 'gemini'], AiProvider::chain());
        $this->assertSame('gemini-3.5-flash', AiProvider::model('gemini'));
        $this->assertSame('https://generativelanguage.googleapis.com/v1beta/openai/chat/completions', AiProvider::chatUrl('gemini'));

        config(['services.groq.key' => null]);
        $this->assertSame(['gemini'], AiProvider::chain(), 'No main key: the backup still works.');

        config(['services.ai.backup' => 'groq', 'services.groq.key' => 'gsk_main_key_000000000000']);
        $this->assertSame(['groq'], AiProvider::chain(), 'A backup equal to the main provider is ignored.');
    }

    public function test_a_rate_limited_interview_moves_straight_to_the_backup(): void
    {
        Http::fake([
            'api.groq.com/*' => $this->rateLimited(),
            'generativelanguage.googleapis.com/*' => Http::response($this->chatAnswer(['score' => 82, 'feedback' => 'Clear and specific.', 'strengths' => ['STAR'], 'improvements' => []])),
        ]);

        $evaluation = app(AIInterviewService::class)->evaluateResponse('Tell me about a bug you fixed.', 'I traced a null user in a queued job and fixed it.', InterviewType::BEHAVIORAL);

        $this->assertSame(82, $evaluation['score']);
        $this->assertFalse($evaluation['not_scored']);
        // A fellow is waiting: no waiting for Groq's 40-second retry-after.
        Sleep::assertNeverSlept();
        Http::assertSentCount(2);
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'generativelanguage') && $r['model'] === 'gemini-3.5-flash'
            && $r->hasHeader('Authorization', 'Bearer AQ.backup_key_00000000000')
            && $r['reasoning_effort'] === 'low'); // fast enough for a waiting fellow
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'groq') && !isset($r['reasoning_effort']));
    }

    public function test_when_every_provider_fails_the_answer_is_not_scored_never_invented(): void
    {
        Http::fake([
            'api.groq.com/*' => $this->rateLimited(),
            'generativelanguage.googleapis.com/*' => Http::response([['error' => ['code' => 503, 'message' => 'The model is overloaded.', 'status' => 'UNAVAILABLE']]], 503),
        ]);

        $evaluation = app(AIInterviewService::class)->evaluateResponse('Q', str_repeat('a long answer ', 40), InterviewType::BEHAVIORAL);

        $this->assertNull($evaluation['score']);
        $this->assertTrue($evaluation['not_scored']);
        $this->assertStringContainsString("won't count toward your Career Capital", $evaluation['feedback']);
    }

    public function test_an_ai_answer_without_a_score_is_not_scored(): void
    {
        Http::fake(['api.groq.com/*' => Http::response($this->chatAnswer(['feedback' => 'Nice.']))]);

        $evaluation = app(AIInterviewService::class)->evaluateResponse('Q', 'Answer', InterviewType::BEHAVIORAL);

        $this->assertNull($evaluation['score']);
        $this->assertTrue($evaluation['not_scored']);
    }

    public function test_the_client_reports_what_each_provider_said(): void
    {
        Http::fake([
            'api.groq.com/*' => $this->rateLimited(),
            'generativelanguage.googleapis.com/*' => Http::response([['error' => ['code' => 503, 'message' => 'The model is overloaded.', 'status' => 'UNAVAILABLE']]], 503),
        ]);

        try {
            app(AiClient::class)->chat('interview', ['messages' => [['role' => 'user', 'content' => 'hi']]], 100);
            $this->fail('Expected AiUnavailableException');
        } catch (AiUnavailableException $e) {
            $this->assertStringContainsString('Groq (HTTP 429): Rate limit reached', $e->getMessage());
            $this->assertStringContainsString('Google Gemini (HTTP 503): The model is overloaded.', $e->getMessage());
        }
    }

    public function test_a_review_falls_back_and_records_the_model_that_answered(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => ['message' => 'Internal error']], 500),
            'generativelanguage.googleapis.com/*' => Http::response($this->chatAnswer([
                'criteria' => [['criterion' => 'Requirements', 'score' => 70, 'met' => true, 'reasoning' => 'ok', 'evidence' => 'text']],
                'requirements_met' => true, 'missing_requirements' => [], 'summary' => 's', 'confidence' => 'medium',
                'authenticity_concern' => 'none', 'authenticity_reasons' => [], 'flags' => [],
                'feedback_for_fellow' => 'Good.', 'notes_for_reviewer' => 'n',
            ])),
        ]);
        $package = [
            'activity' => ['title' => 'T', 'track' => 'SE', 'milestone' => 'M', 'type' => 'project', 'difficulty' => 'beginner', 'points' => 10,
                'evidence_requirements' => [], 'description' => 'Do it.', 'instructions' => '',
                'rubric' => [['criterion' => 'Requirements', 'weight' => 100, 'description' => 'Does it']]],
            'submission' => ['attempt' => 1, 'started_at' => null, 'submitted_at' => null, 'minutes_from_start_to_submit' => null,
                'url' => null, 'text' => 'Done, tested at 375px.', 'reflection' => '', 'file_names' => [], 'ai_assistance' => null],
            'github' => [], 'links' => [], 'files' => [], 'unread' => [],
        ];

        $graded = app(AiGrader::class)->grade($package, []);

        $this->assertSame(70, $graded['result']['criteria'][0]['score']);
        $this->assertSame('gemini-3.5-flash', $graded['model']);
        // Queued work retries a server error on the main provider before moving on.
        $this->assertSame(3, Http::recorded()->filter(fn ($pair) => str_contains($pair[0]->url(), 'groq'))->count());
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'generativelanguage') && $r['reasoning_effort'] === 'medium'
            && $r['max_tokens'] === 10500);
    }

    public function test_test_button_checks_the_main_and_the_backup(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response($this->chatAnswer(['ok' => true])),
            // First test run: works. Second run: the backup key is rejected.
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push($this->chatAnswer(['ok' => true]))
                ->push([['error' => ['code' => 400, 'message' => 'API key not valid.', 'status' => 'INVALID_ARGUMENT']]], 400),
        ]);
        $tester = app(IntegrationTester::class);
        $values = ['ai_provider' => 'groq', 'ai_backup_provider' => 'gemini', 'groq_api_key' => 'gsk_x', 'gemini_api_key' => 'AQ.x',
            'groq_model' => null, 'groq_review_model' => null, 'gemini_model' => null, 'gemini_review_model' => null];

        $result = $tester->openai($values);
        $this->assertSame('ok', $result['status']);
        $this->assertSame('Both work: Groq as the main provider, Google Gemini as the backup.', $result['message']);
        $this->assertContains('Backup (Google Gemini): gemini-3.5-flash answered for interviews and submission reviews (0 ms)',
            array_map(fn ($d) => preg_replace('/\(\d+ ms\)/', '(0 ms)', $d), $result['details']));

        $result = $tester->openai($values);
        $this->assertSame('warning', $result['status']);
        $this->assertStringContainsString('the backup failed', $result['message']);
    }
}
