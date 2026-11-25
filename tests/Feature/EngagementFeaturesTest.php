<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\BriefSubmission;
use App\Models\DetectiveCase;
use App\Models\EmployerBrief;
use App\Models\Explanation;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FlashCard;
use App\Models\FlashReview;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackFeedEvent;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\DetectiveService;
use App\Services\FlashReviewService;
use App\Services\PracticeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EngagementFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected Track $track;
    protected TrackCurriculumActivity $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => null, 'services.groq.key' => 'gsk_test', 'services.openai.key' => null]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        Role::findOrCreate('recruiter');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace', 'is_public' => true]);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['company' => 'Nkwen Traders Ltd.']]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        $m1 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Welcome', 'description' => 'W.', 'sequence_order' => 1]);
        $m2 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Python', 'description' => 'P.', 'sequence_order' => 2]);
        $this->lesson = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m1->id, 'title' => 'Mean vs Median', 'description' => 'Averages.',
            'type' => 'research', 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => 1]);
        TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m2->id, 'title' => 'Loops', 'description' => 'L.',
            'type' => 'research', 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => 2]);
    }

    protected function card(string $key, int $chapter, int $answer = 1): FlashCard
    {
        return FlashCard::create(['track_id' => $this->track->id, 'key' => $key, 'chapter' => $chapter, 'question' => "Q {$key}?", 'options' => ['a', 'b', 'c', 'd'],
            'answer_index' => $answer, 'explanation' => "Because {$key}."]);
    }

    public function test_flash_review_serves_three_cards_from_reached_chapters_and_spaces_them(): void
    {
        foreach (['a', 'b', 'c', 'd'] as $k) {
            $this->card("c1-{$k}", 1);
        }
        $this->card('c2-x', 2); // chapter 2 not reached yet (chapter 1 not completed)
        $service = app(FlashReviewService::class);

        $cards = $service->todaysCards($this->fellow, $this->track);
        $this->assertCount(3, $cards);
        $this->assertTrue($cards->every(fn ($c) => $c->chapter === 1));

        $page = $this->actingAs($this->fellow)->get(route('flash-review.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Because c1', $page, 'Explanations only after answering.');

        $first = $cards[0];
        $this->postJson(route('flash-review.answer', $first), ['option' => 1])->assertOk()->assertJson(['correct' => true, 'box' => 1, 'xp' => 5, 'answer_index' => 1, 'remaining' => 2]);
        $this->assertSame(now()->addDay()->toDateString(), FlashReview::where('card_id', $first->id)->first()->due_on->toDateString());
        $this->postJson(route('flash-review.answer', $cards[1]), ['option' => 0])->assertJson(['correct' => false, 'xp' => 0, 'box' => 1]);
        $this->postJson(route('flash-review.answer', $cards[2]), ['option' => 1])->assertJson(['remaining' => 0]);
        $this->assertCount(0, $service->todaysCards($this->fellow, $this->track), 'Three a day.');

        // Practice XP and the streak include flash reviews.
        $stats = app(PracticeService::class)->stats($this->fellow, $this->track);
        $this->assertSame(10, $stats['xp']);
        $this->assertSame(1, $stats['streak']);

        // Tomorrow, the wrong card is due again; a right card in box 2 waits 3 days.
        $this->travel(1)->days();
        $this->assertTrue($service->todaysCards($this->fellow, $this->track)->pluck('id')->contains($cards[1]->id));
    }

    public function test_explain_it_back_gives_ai_feedback_and_xp_once(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        Http::fake(['api.groq.com/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => json_encode(['score' => 4, 'verdict' => 'Clear and correct.', 'strengths' => ['Good example'], 'missing' => ['Why it matters'], 'tip' => 'Add a decision it helps.'])]]]])
            ->push(['choices' => [['message' => ['content' => json_encode(['score' => 5, 'verdict' => 'Great.', 'strengths' => [], 'missing' => [], 'tip' => ''])]]]])]);

        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->lesson))->assertOk()->assertSee('Explain it back')->assertSee('branch manager who doesn');

        $text = 'The median is the middle value when sales are sorted, so one huge bulk order does not pull it up like it pulls the mean.';
        $this->postJson(route('curriculum.activity.explain', $this->lesson), ['text' => $text])->assertOk()->assertJson(['ok' => true, 'score' => 4, 'xp' => 10, 'tip' => 'Add a decision it helps.']);
        $this->postJson(route('curriculum.activity.explain', $this->lesson), ['text' => $text . ' Example: Nkwen.'])->assertOk()->assertJson(['score' => 5, 'xp' => 0]);
        $this->assertSame(2, Explanation::count());
        $this->postJson(route('curriculum.activity.explain', $this->lesson), ['text' => 'too short'])->assertStatus(422);
    }

    public function test_detective_cases_check_answers_without_revealing_them(): void
    {
        $case = DetectiveCase::create(['track_id' => $this->track->id, 'slug' => 'saturday', 'title' => 'The Saturday Stampede', 'difficulty' => 1, 'xp' => 30,
            'story' => 'Which day is busiest?', 'questions' => [
                ['key' => 'q1', 'prompt' => 'Saturday revenue?', 'kind' => 'number', 'unit' => 'FCFA'],
                ['key' => 'q2', 'prompt' => 'Best day?', 'kind' => 'choice', 'options' => ['Monday', 'Saturday']],
            ], 'answer_key' => ['q1' => hash('sha256', DetectiveService::canonicalNumber(9486566)), 'q2' => 1], 'solution' => 'GROUP BY weekday.']);
        $this->actingAs($this->fellow);

        $this->get(route('detective.index'))->assertRedirect(route('detective.show', $case));
        $html = $this->get(route('detective.show', $case))->assertOk()->assertSee('The Saturday Stampede')->getContent();
        $this->assertStringNotContainsString('GROUP BY weekday', $html);
        $this->assertStringNotContainsString(hash('sha256', '9486566'), $html);

        $this->postJson(route('detective.answer', $case), ['key' => 'q1', 'value' => '9 486 000'])->assertJson(['correct' => false]);
        $this->postJson(route('detective.answer', $case), ['key' => 'q1', 'value' => '9,486,566 FCFA'])->assertJson(['correct' => true, 'solved' => false]);
        $this->postJson(route('detective.answer', $case), ['key' => 'q2', 'value' => 1])->assertJson(['correct' => true, 'solved' => true, 'xp' => 30])
            ->assertJsonPath('solution_html', "<p>GROUP BY weekday.</p>\n");
        $this->assertSame(1, TrackFeedEvent::where('type', 'detective')->count());
        $this->assertSame(30, app(PracticeService::class)->stats($this->fellow, $this->track)['xp']);
        $this->postJson(route('detective.reveal', $case))->assertForbidden(); // this week's case is never revealed
    }

    public function test_employer_briefs_flow_from_posting_to_portfolio_and_recruiter(): void
    {
        $recruiter = User::factory()->create(['role' => 'recruiter', 'company_name' => 'Bamenda Foods']);
        $recruiter->assignRole('recruiter');

        $this->actingAs($this->admin)->post(route('admin.briefs.store'), [
            'title' => 'Rainy season stock plan', 'company_name' => 'Bamenda Foods', 'track_id' => $this->track->id, 'recruiter_id' => $recruiter->id,
            'summary' => 'Which products sell more when it rains?', 'deliverable' => 'A notebook and 3 recommendations.', 'deadline_on' => now()->addWeek()->toDateString(), 'status' => 'open',
        ])->assertRedirect();
        $brief = EmployerBrief::sole();
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '💼 New employer brief: Bamenda Foods')->count());

        $this->actingAs($this->fellow)->get(route('briefs.index'))->assertOk()->assertSee('Rainy season stock plan');
        $this->post(route('briefs.submit', $brief), ['url' => 'https://github.com/ada/rainy', 'notes' => 'I compared sales by month and category; produce and rice rise in the rainy months, so stock up early on rice and tomatoes.'])
            ->assertRedirect(route('briefs.show', $brief));
        $submission = BriefSubmission::sole();

        $this->actingAs($this->admin)->post(route('admin.briefs.review', [$brief, $submission]), ['status' => 'winner', 'feedback' => 'Clear and actionable.'])->assertRedirect();
        $this->assertSame('winner', $submission->fresh()->status);
        $this->assertSame(1, TrackFeedEvent::where('type', 'brief')->count());

        $this->get(route('public.profile.show', $this->fellow))->assertOk()->assertSee('Employer challenges')->assertSee('Rainy season stock plan');

        // Fellows can't change a reviewed submission.
        $this->actingAs($this->fellow)->post(route('briefs.submit', $brief), ['url' => 'https://github.com/ada/v2', 'notes' => str_repeat('x', 90)])->assertSessionHas('error');
        $this->assertSame('https://github.com/ada/rainy', $submission->fresh()->url);
    }
}
