<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Enums\InterviewMode;
use App\Enums\InterviewStatus;
use App\Enums\InterviewType;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Curriculum fixes found in the end-to-end Data Science run: AI mock
 * interviews that never completed, deadlines counted from the track start,
 * and reviewers awarding any number of points.
 */
class CurriculumFlowFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected Track $track;
    protected TrackMilestone $milestone;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        // The fee check in the internship middleware uses MySQL date functions (tests run on SQLite).
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => null, 'services.groq.key' => 'gsk_test_key_0000000000', 'services.openai.key' => null]);

        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'profile_completed_at' => now(), 'onboarding_completed_at' => now()]);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'Data.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true,
            'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subDays(60)]);
        $this->milestone = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'NumPy & Pandas Fundamentals',
            'description' => 'Arrays, DataFrames, groupby and merges.', 'sequence_order' => 4]);
    }

    protected function activity(array $attributes = []): TrackCurriculumActivity
    {
        return TrackCurriculumActivity::create($attributes + [
            'track_id' => $this->track->id, 'milestone_id' => $this->milestone->id, 'title' => 'GroupBy: Aggregating Data',
            'description' => 'Aggregate sales by region.', 'type' => 'research', 'career_capital_category' => 'technical',
            'points' => 20, 'is_active' => true, 'sequence_order' => 1, 'deadline_days' => 3, 'grace_period_days' => 1, 'late_penalty_percent' => 15,
        ]);
    }

    protected function mockInterviewActivity(): TrackCurriculumActivity
    {
        $this->activity(['title' => 'Selecting & Filtering Data', 'sequence_order' => 1]);

        return $this->activity([
            'title' => 'Mock Interview: Pandas Fundamentals Check', 'type' => 'mock_interview', 'career_capital_category' => 'interview',
            'points' => 10, 'sequence_order' => 9, 'interview_config' => ['mode' => 'ai', 'type' => 'mock_interview', 'count' => 1, 'min_score' => 70],
        ]);
    }

    /** Starts the curriculum mock interview the way the activity page does. */
    protected function startMockInterview(): array
    {
        $activity = $this->mockInterviewActivity();
        $progress = FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $activity->id, 'status' => CurriculumStatus::AVAILABLE]);
        $result = app(CurriculumService::class)->startActivity($this->fellow, $activity->id);

        return [$activity, $progress->fresh(), $result['interview']];
    }

    protected function answered(InterviewSession $interview, array $scores): void
    {
        $interview->update(['responses' => collect($scores)->map(fn ($score, $i) => [
            'question_index' => $i, 'question' => "Q{$i}", 'response' => 'An answer.', 'evaluation' => ['score' => $score, 'not_scored' => $score === null],
        ])->values()->all()]);
    }

    // ---------------------------------------------------------------- interviews

    public function test_a_mock_interview_with_an_unknown_type_is_a_technical_check(): void
    {
        [, , $interview] = $this->startMockInterview();

        $this->assertSame(InterviewType::TECHNICAL_CODING, $interview->type);
        $this->assertSame(InterviewStatus::IN_PROGRESS, $interview->status);
    }

    public function test_finishing_the_ai_room_completes_the_interview_from_server_scores_and_passes_the_activity(): void
    {
        [, $progress, $interview] = $this->startMockInterview();
        $this->answered($interview, [80, 90, null]); // the third answer could not be scored

        // Exactly what the room sends; a made-up score from the browser is ignored.
        $this->actingAs($this->fellow)
            ->postJson(route('interviews.complete', $interview), ['score' => 100, 'scores' => [100], 'evaluations' => [['score' => 80], ['score' => 90], ['score' => null], ['score' => 0, 'skipped' => true]]])
            ->assertOk()
            ->assertJson(['status' => 'completed', 'not_scored' => false]);

        $interview->refresh();
        $this->assertSame(InterviewStatus::COMPLETED, $interview->status);
        // (80 + 90 + 0 for the skipped question) / 3; the unscored answer is left out.
        $this->assertEqualsWithDelta(56.7, (float) $interview->overall_score, 0.05);

        // Below the 70 minimum: the activity stays open for another attempt.
        $this->assertSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);

        // A second attempt that passes submits the activity for review.
        $retry = app(CurriculumService::class)->launchInterviewAttempt($this->fellow, $progress->curriculum_activity_id);
        $this->answered($retry, [85, 75]);
        $this->postJson(route('interviews.complete', $retry), ['evaluations' => []])->assertOk();

        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->fresh()->status);
        $this->assertSame(80, (int) $progress->fresh()->score_awarded);
    }

    public function test_an_interview_nothing_could_score_is_completed_as_not_scored(): void
    {
        [, $progress, $interview] = $this->startMockInterview();
        $this->answered($interview, [null, null]);

        $this->actingAs($this->fellow)->postJson(route('interviews.complete', $interview), ['evaluations' => []])
            ->assertOk()->assertJson(['not_scored' => true, 'score' => null]);

        $this->assertNull($interview->fresh()->overall_score);
        $this->assertSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);
        // Completing it twice is refused.
        $this->postJson(route('interviews.complete', $interview), [])->assertStatus(409);
    }

    public function test_a_fellow_cannot_post_rubric_scores_for_a_human_interview(): void
    {
        $interview = InterviewSession::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => InterviewType::BEHAVIORAL,
            'mode' => InterviewMode::HUMAN, 'status' => InterviewStatus::IN_PROGRESS, 'scheduled_at' => now()]);

        $this->actingAs($this->fellow)->post(route('interviews.complete', $interview), ['scores' => ['communication' => 100]])->assertForbidden();
        $this->assertSame(InterviewStatus::IN_PROGRESS, $interview->fresh()->status);
    }

    public function test_a_stuck_ai_interview_can_be_cancelled_and_a_new_attempt_launched(): void
    {
        [$activity, , $interview] = $this->startMockInterview();

        $this->actingAs($this->fellow)->post(route('interviews.cancel', $interview))
            ->assertRedirect(route('curriculum.activity.show', $activity));
        $this->assertSame(InterviewStatus::CANCELLED, $interview->fresh()->status);

        $this->post(route('curriculum.activity.interview', $activity))->assertRedirect();
        $this->assertSame(1, InterviewSession::where('curriculum_activity_id', $activity->id)->where('status', InterviewStatus::IN_PROGRESS->value)->count());
    }

    public function test_the_interview_page_shows_real_results(): void
    {
        [, , $interview] = $this->startMockInterview();
        $this->answered($interview, [null]);

        $this->actingAs($this->fellow)->get(route('interviews.show', $interview))
            ->assertOk()->assertSee('Continue Interview')->assertSee('Cancel Interview')->assertDontSee('John Doe');

        $interview->update(['status' => InterviewStatus::COMPLETED, 'score' => null, 'overall_score' => null]);
        $this->get(route('interviews.show', $interview))->assertOk()->assertSee('Not scored')->assertDontSee('85%');
    }

    public function test_curriculum_interview_questions_are_about_the_milestone(): void
    {
        [, , $interview] = $this->startMockInterview();
        $questions = ['questions' => [
            ['question' => 'What does groupby return before you aggregate?', 'type' => 'conceptual'],
            ['question' => 'How would you find total sales per region?', 'type' => 'coding'],
            ['question' => 'When would you use a left merge?', 'type' => 'conceptual'],
        ]];
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode($questions)]]]])]);

        $this->actingAs($this->fellow)->getJson(route('interviews.questions', $interview))
            ->assertOk()->assertJsonPath('questions.0.question', 'What does groupby return before you aggregate?');

        Http::assertSent(fn (HttpRequest $r) => str_contains($r['messages'][1]['content'], 'NumPy & Pandas Fundamentals')
            && str_contains($r['messages'][1]['content'], 'Selecting & Filtering Data'));
    }

    public function test_question_answers_in_another_shape_are_read_and_a_useless_one_is_retried(): void
    {
        [, , $interview] = $this->startMockInterview();
        $answer = fn (array $content) => ['choices' => [['message' => ['content' => json_encode($content)]]]];
        Http::fake(['api.groq.com/*' => Http::sequence()
            ->push($answer(['note' => 'Here are some questions']))
            ->push($answer(['interview_questions' => [['text' => 'What is a DataFrame index?'], ['prompt' => 'How do you filter rows?'], ['question' => 'What does merge(how="left") keep?']]]))]);

        $this->actingAs($this->fellow)->getJson(route('interviews.questions', $interview))
            ->assertOk()->assertJsonCount(3, 'questions')->assertJsonPath('questions.1.question', 'How do you filter rows?');
        Http::assertSentCount(2);
    }

    // ---------------------------------------------------------------- deadlines

    public function test_a_deadline_counts_from_when_the_activity_opens(): void
    {
        $first = $this->activity(['title' => 'Selecting & Filtering Data']);
        $second = $this->activity(['title' => 'GroupBy', 'sequence_order' => 2, 'prerequisites' => [$first->id]]);

        app(CurriculumService::class)->initializeFellowCurriculum($this->fellow, $this->track);
        $locked = FellowCurriculumProgress::where('curriculum_activity_id', $second->id)->first();
        $this->assertSame(CurriculumStatus::LOCKED, $locked->status);

        // The fellow joined 60 days ago; the first activity opens today.
        $opened = FellowCurriculumProgress::where('curriculum_activity_id', $first->id)->first();
        $this->assertTrue($opened->deadline_at->isSameDay(now()->addDays(3)));

        $this->travel(10)->days();
        $opened->update(['status' => CurriculumStatus::COMPLETED, 'completed_at' => now()]);
        app(CurriculumService::class)->unlockDependentActivities($this->fellow, $first);

        $this->assertTrue($locked->fresh()->deadline_at->isSameDay(now()->addDays(3)), 'Deadline is 3 days after it opened, not after the track start.');
    }

    public function test_repair_command_refunds_penalties_caused_by_the_old_rule_only(): void
    {
        $first = $this->activity(['title' => 'Selecting & Filtering Data']);
        $second = $this->activity(['title' => 'GroupBy', 'sequence_order' => 2, 'prerequisites' => [$first->id]]);
        $base = ['fellow_id' => $this->fellow->id, 'late_penalty_applied' => true, 'status' => CurriculumStatus::COMPLETED];

        // First opened at track start (60 days ago), submitted 20 days later: really late.
        $a = FellowCurriculumProgress::create($base + ['curriculum_activity_id' => $first->id, 'submitted_at' => now()->subDays(40),
            'completed_at' => now()->subDays(39), 'points_awarded' => 17, 'deadline_at' => now()->subDays(57)]);
        // Second opened when the first was completed, submitted 2 days later: on time.
        $b = FellowCurriculumProgress::create($base + ['curriculum_activity_id' => $second->id, 'submitted_at' => now()->subDays(37),
            'completed_at' => now()->subDays(36), 'points_awarded' => 17, 'deadline_at' => now()->subDays(57)]);

        $this->artisan('curriculum:repair-deadlines', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(17, (int) $b->fresh()->points_awarded);

        $this->artisan('curriculum:repair-deadlines')->assertSuccessful();

        $this->assertTrue((bool) $a->fresh()->late_penalty_applied);
        $this->assertSame(17, (int) $a->fresh()->points_awarded);
        $this->assertFalse((bool) $b->fresh()->late_penalty_applied);
        $this->assertSame(20, (int) $b->fresh()->points_awarded, '17 after a 15% penalty was 20 points.');
        $this->assertTrue($b->fresh()->deadline_at->isSameDay(now()->subDays(36)), 'Opened when the prerequisite was completed (39 days ago) + 3 days.');
    }

    // ---------------------------------------------------------------- review points

    public function test_a_reviewer_cannot_award_more_than_the_activity_is_worth(): void
    {
        $activity = $this->activity();
        $progress = FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $activity->id,
            'status' => CurriculumStatus::SUBMITTED, 'submitted_at' => now(), 'attempt_number' => 1]);

        $this->actingAs($this->admin)->get(route('admin.curriculum.reviews.show', $progress))
            ->assertOk()->assertSee('value="20"', false)->assertDontSee('award bonus points');

        $this->post(route('admin.curriculum.reviews.process', $progress), ['decision' => 'approve', 'points' => 1000])
            ->assertSessionHasErrors(['points' => "This activity is worth 20 points; you can't award more."]);
        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->fresh()->status);

        $this->post(route('admin.curriculum.reviews.process', $progress), ['decision' => 'approve', 'points' => 20])->assertRedirect();
        $this->assertSame(20, (int) $progress->fresh()->points_awarded);
    }
}
