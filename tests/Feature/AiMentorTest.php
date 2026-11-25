<?php

namespace Tests\Feature;

use App\Models\FellowTrack;
use App\Models\MentorMessage;
use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\MentorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiMentorTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected Track $track;
    protected TrackCurriculumActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => 'gemini', 'services.groq.key' => 'gsk_test', 'services.gemini.key' => 'AQ.test', 'services.openai.key' => null]);
        Role::findOrCreate('fellow');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Fellow']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['company' => 'Nkwen Traders Ltd.', 'team' => 'Analytics Team',
                'mentor' => ['name' => 'Mr. Tabe Kingsley', 'short' => 'Kingsley', 'title' => 'Senior Data Analyst', 'initials' => 'TK']]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        $m = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'pandas', 'description' => 'P.', 'sequence_order' => 1]);
        $this->activity = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => 'GroupBy: Aggregating Data',
            'description' => 'Revenue by branch.', 'instructions' => 'Use groupby.', 'type' => 'research', 'career_capital_category' => 'technical', 'points' => 20,
            'is_active' => true, 'sequence_order' => 1, 'reviewer_guide' => 'SECRET MODEL ANSWER 15,495,883']);
    }

    protected function reply(string $text): array
    {
        return ['choices' => [['message' => ['content' => $text]]]];
    }

    public function test_the_mentor_coaches_with_page_context_and_its_own_model(): void
    {
        Http::fake(['api.groq.com/*' => Http::response($this->reply("<think>plan</think>Try putting **Branch** in quotes. What does `groupby` need?"))]);

        $this->actingAs($this->fellow)->postJson(route('ai-mentor.ask'), ['message' => 'Just give me the code', 'context_type' => 'activity', 'context_id' => $this->activity->id])
            ->assertOk()
            ->assertJson(['ok' => true, 'remaining' => MentorService::DAILY_LIMIT - 1])
            ->assertJsonPath('html', "<p>Try putting <strong>Branch</strong> in quotes. What does <code>groupby</code> need?</p>\n");

        Http::assertSent(function (HttpRequest $r) {
            $system = $r['messages'][0]['content'];

            return $r['model'] === 'qwen/qwen3.8-27b'
                && $r['reasoning_format'] === 'hidden'
                && $r['max_tokens'] === 700
                && str_contains($system, 'Mr. Tabe Kingsley')
                && str_contains($system, 'GroupBy: Aggregating Data')
                && str_contains($system, "Coach, don't solve")
                && !str_contains($system, 'SECRET MODEL ANSWER');
        });

        $this->getJson(route('ai-mentor.history', ['context_type' => 'activity', 'context_id' => $this->activity->id]))
            ->assertOk()->assertJsonCount(2, 'messages')->assertJsonPath('messages.0.text', 'Just give me the code');
    }

    public function test_it_falls_back_to_the_backup_provider(): void
    {
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => ['message' => 'Rate limit reached: output tokens per minute']], 429),
            'generativelanguage.googleapis.com/*' => Http::response($this->reply('Look at the error message: what name is undefined?')),
        ]);

        $this->actingAs($this->fellow)->postJson(route('ai-mentor.ask'), ['message' => 'Why NameError?'])->assertOk()->assertJson(['ok' => true]);

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'generativelanguage') && $r['model'] === 'gemini-3.5-flash' && $r['reasoning_effort'] === 'low');
        $this->assertSame('gemini-3.5-flash', MentorMessage::where('role', 'assistant')->value('model'));
    }

    public function test_unsolved_challenges_get_hints_only_and_the_daily_limit_applies(): void
    {
        $challenge = PracticeChallenge::create(['track_id' => $this->track->id, 'slug' => 'x', 'title' => 'Revenue per branch', 'kind' => 'sql', 'prompt' => 'Sum per branch.',
            'expected_hash' => hash('sha256', 'x'), 'explanation' => 'SOLUTION SELECT Branch...']);
        Http::fake(['api.groq.com/*' => Http::response($this->reply('Which clause makes one row per branch?'))]);

        $this->actingAs($this->fellow)->postJson(route('ai-mentor.ask'), ['message' => 'help', 'context_type' => 'challenge', 'context_id' => $challenge->id])->assertOk();
        Http::assertSent(fn (HttpRequest $r) => str_contains($r['messages'][0]['content'], 'not solved yet: give hints only')
            && !str_contains($r['messages'][0]['content'], 'SOLUTION'));

        MentorMessage::query()->delete();
        foreach (range(1, MentorService::DAILY_LIMIT) as $i) {
            MentorMessage::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'role' => 'user', 'content' => "q{$i}"]);
        }
        $this->postJson(route('ai-mentor.ask'), ['message' => 'one more'])->assertStatus(422)->assertJson(['ok' => false, 'remaining' => 0]);
    }

    public function test_fellows_cannot_use_another_tracks_context_and_the_page_renders(): void
    {
        $other = User::factory()->create(['role' => 'fellow']);
        $other->assignRole('fellow');
        $this->actingAs($other)->postJson(route('ai-mentor.ask'), ['message' => 'hi', 'context_type' => 'activity', 'context_id' => $this->activity->id])->assertForbidden();

        $this->actingAs($this->fellow)->get(route('ai-mentor.index'))->assertOk()->assertSee('Ask Mr. Tabe Kingsley');
        $this->get(route('curriculum.activity.show', $this->activity))->assertOk()->assertSee('Ask Kingsley');
    }
}
