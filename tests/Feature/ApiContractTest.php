<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\ApiContractService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://93.184.215.14';

    private array $contract = ['checks' => [
        ['name' => 'List products', 'method' => 'GET', 'path' => '/api/products', 'expect' => ['status' => 200, 'has' => ['data.0.id'], 'equals' => ['currency' => 'FCFA'], 'min_count' => ['data' => 2]]],
        ['name' => 'Sign up', 'method' => 'POST', 'path' => '/api/register', 'body' => ['email' => 'cashier+{{rand}}@nkwen.test', 'password' => 'secret-123'], 'expect' => ['status' => [200, 201]], 'save' => ['token' => 'token']],
        ['name' => 'Create order with token', 'method' => 'POST', 'path' => '/api/orders', 'headers' => ['Authorization' => 'Bearer {{token}}'], 'body' => ['items' => [['product_id' => 6, 'quantity' => 2]]], 'expect' => ['status' => 201, 'has' => ['data.id']], 'save' => ['orderId' => 'data.id']],
        ['name' => 'Read the order back', 'method' => 'GET', 'path' => '/api/orders/{{orderId}}', 'headers' => ['Authorization' => 'Bearer {{token}}'], 'expect' => ['status' => 200, 'equals' => ['data.total_fcfa' => 72010]]],
        ['name' => 'Orders need a login', 'method' => 'GET', 'path' => '/api/orders/{{orderId}}', 'expect' => ['status' => 401]],
    ]];

    private function fakeApi(bool $brokenTotal = false): void
    {
        Http::fake(function (HttpRequest $r) use ($brokenTotal) {
            $path = parse_url($r->url(), PHP_URL_PATH);
            $auth = $r->header('Authorization')[0] ?? '';

            return match (true) {
                $path === '/api/products' => Http::response(['currency' => 'FCFA', 'data' => [['id' => 1], ['id' => 2]]]),
                $path === '/api/register' && str_starts_with($r['email'], 'cashier+') && strlen($r['email']) > 20 => Http::response(['token' => 'tok-123'], 201),
                $path === '/api/orders' && $auth === 'Bearer tok-123' => Http::response(['data' => ['id' => 77]], 201),
                $path === '/api/orders/77' && $auth === 'Bearer tok-123' => Http::response(['data' => ['id' => 77, 'total_fcfa' => $brokenTotal ? 7201 : 72010]]),
                $path === '/api/orders/77' => Http::response(['message' => 'Unauthenticated.'], 401),
                default => Http::response(['message' => 'Not found'], 404),
            };
        });
    }

    public function test_only_public_https_addresses_are_called(): void
    {
        $c = app(ApiContractService::class);
        foreach (['http://93.184.215.14', 'https://localhost:8000', 'https://127.0.0.1', 'https://10.0.0.5', 'https://192.168.1.20', 'https://169.254.169.254', 'https://[::1]', 'https://93.184.215.14/?x=1', 'https://user:pw@93.184.215.14', 'nonsense'] as $url) {
            $this->assertFalse($c->validateBaseUrl($url)['ok'], $url);
        }
        $this->assertTrue($c->validateBaseUrl(self::BASE . '/')['ok']);
        Http::fake();
        $this->assertFalse($c->run($this->contract, 'https://127.0.0.1')['ok']);
        Http::assertNothingSent();
    }

    public function test_a_working_api_passes_every_check_using_saved_values(): void
    {
        $this->fakeApi();
        $r = app(ApiContractService::class)->run($this->contract, self::BASE);
        $this->assertTrue($r['ok']);
        $this->assertSame(5, $r['passed'], json_encode($r['results']));
        $this->assertSame('GET /api/orders/77 ', substr($r['results'][3]['request'], 0, 18) . ' ');
    }

    public function test_failures_explain_what_is_wrong(): void
    {
        $this->fakeApi(brokenTotal: true);
        $r = app(ApiContractService::class)->run($this->contract, self::BASE);
        $this->assertSame(4, $r['passed']);
        $this->assertStringContainsString('"data.total_fcfa" should be 72010', $r['results'][3]['message']);
    }

    public function test_a_broken_server_fails_with_clear_messages(): void
    {
        Http::fake(fn () => Http::response('<html>oops</html>', 500));
        $r = app(ApiContractService::class)->run($this->contract, self::BASE);
        $this->assertSame(0, $r['passed']);
        $this->assertStringContainsString('Expected status 200, got 500', $r['results'][0]['message']);
        $this->assertStringContainsString('not JSON', $r['results'][0]['message']);
    }

    public function test_submission_runs_the_contract_and_reviewers_see_it(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $fellow = User::factory()->create(['role' => 'fellow']);
        $fellow->assignRole('fellow');
        $track = Track::create(['name' => 'SE', 'slug' => 'se-test', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $fellow->id, 'track_id' => $track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        TrackOrientation::create(['fellow_id' => $fellow->id, 'track_id' => $track->id, 'completed_at' => now()]);
        $m = TrackMilestone::create(['track_id' => $track->id, 'title' => 'Backend', 'description' => 'D.', 'sequence_order' => 1, 'is_active' => true]);
        $a = TrackCurriculumActivity::create(['track_id' => $track->id, 'milestone_id' => $m->id, 'title' => 'Project: Products API', 'description' => 'Build it.', 'type' => 'project',
            'career_capital_category' => 'technical', 'points' => 25, 'is_active' => true, 'is_required' => true, 'sequence_order' => 1, 'api_contract' => $this->contract]);
        $p = FellowCurriculumProgress::create(['fellow_id' => $fellow->id, 'curriculum_activity_id' => $a->id, 'status' => CurriculumStatus::IN_PROGRESS, 'started_at' => now()]);
        $this->fakeApi();

        $this->actingAs($fellow)->get(route('curriculum.submit.form', $p))->assertOk()->assertSee('Your deployed API')->assertSee('5 checks');
        // "Check my API" runs in the background and the page polls the result (the queue is synchronous in tests).
        $start = $this->actingAs($fellow)->postJson(route('curriculum.contract-check', $p), ['url' => self::BASE])->assertOk()->assertJsonPath('pending', true);
        $this->actingAs($fellow)->getJson($start->json('poll'))->assertOk()->assertJsonPath('passed', 5);
        $this->actingAs($fellow)->postJson(route('curriculum.contract-check', $p), ['url' => 'https://127.0.0.1'])->assertStatus(422);
        $other = User::factory()->create(['role' => 'fellow']);
        $this->actingAs($other)->getJson($start->json('poll'))->assertForbidden();
        $this->actingAs($fellow)->post(route('curriculum.submit', $p), ['evidence_text' => 'Done.', 'ai_used' => 'no'])->assertSessionHasErrors('api_base_url');
        $this->actingAs($fellow)->post(route('curriculum.submit', $p), ['evidence_text' => 'Done.', 'ai_used' => 'no', 'api_base_url' => self::BASE])->assertSessionHasNoErrors();
        $p->refresh();
        $this->assertSame(5, $p->evidence['contract']['passed']);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('admin.curriculum.reviews.show', $p))->assertOk()->assertSee('API contract check')->assertSee('5 of 5 passed');
    }
}
