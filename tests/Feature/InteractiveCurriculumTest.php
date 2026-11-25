<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FlashAnswer;
use App\Models\FlashCard;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackFeedEvent;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\FlashReviewService;
use App\Services\OrientationService;
use App\Support\LessonSteps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InteractiveCurriculumTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected Track $track;
    protected TrackMilestone $m1;
    protected TrackCurriculumActivity $lesson;
    protected TrackCurriculumActivity $second;

    protected const INSTRUCTIONS = "### Goal\nLearn medians.\n\n### Steps\n1. Open the notebook.\n2. Sort the sales.\n   Use `sorted()`.\n3. Find the middle value.\n\n**Part B**\n\n1. Compare with the mean.\n\n### What to submit\n1. A GitHub link.\n\n### Self-check\n- [ ] My median is correct.\n- [ ] I explained the difference.";

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => null, 'services.groq.key' => 'gsk_test', 'services.openai.key' => null]);
        Role::findOrCreate('fellow');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['company' => 'Nkwen Traders Ltd.', 'team' => 'Analytics Team', 'role' => 'Data Analyst', 'manager' => ['name' => 'Mrs. Ngum Esther', 'title' => 'Head'],
                'skills' => [['key' => 'statistics', 'label' => 'Statistics', 'description' => 'Numbers.'], ['key' => 'python', 'label' => 'Python', 'description' => 'Code.']],
                'practice' => ['sql_tables' => ['sales' => '/course-files/data-science/data/nkwen_traders_ch2_sales.csv'], 'python_files' => ['/data/s.csv' => '/course-files/data-science/data/nkwen_traders_ch2_sales.csv']],
                'orientation' => ['welcome' => 'Hello **team**.', 'day' => [['time' => '08:00', 'title' => 'Check data', 'story' => 'Rows.', 'skill' => 'statistics', 'chapter' => 1]],
                    'myths' => [['claim' => 'X', 'truth' => false, 'explain' => 'Y']], 'ladder' => [['title' => 'Rookie', 'does' => 'Learns', 'marker' => 'you']],
                    'senior' => ['statistics' => 'Knows noise from signal.']]]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        $this->m1 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Welcome', 'description' => 'W.', 'sequence_order' => 1, 'estimated_duration_days' => 14]);
        $this->lesson = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->m1->id, 'title' => 'Mean vs Median', 'description' => 'Averages.',
            'instructions' => self::INSTRUCTIONS, 'skills' => ['statistics'], 'type' => 'research', 'career_capital_category' => 'technical', 'points' => 10,
            'is_active' => true, 'is_required' => true, 'sequence_order' => 1, 'deadline_days' => 3, 'grace_period_days' => 1, 'late_penalty_percent' => 10,
            'resources' => [['type' => 'link', 'title' => 'Colab', 'content' => 'https://colab.research.google.com/github/x/y/blob/main/public/course-files/data-science/notebooks/m1.ipynb'],
                ['type' => 'file', 'title' => 'Download', 'content' => 'https://innovakickstarter.com/course-files/data-science/notebooks/m1.ipynb']]]);
        $this->second = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->m1->id, 'title' => 'Project', 'description' => 'Build.',
            'type' => 'project', 'career_capital_category' => 'technical', 'points' => 20, 'is_active' => true, 'is_required' => true, 'sequence_order' => 2]);
    }

    public function test_lesson_steps_parser_builds_steps_parts_and_checklist(): void
    {
        $sections = LessonSteps::parse(self::INSTRUCTIONS);
        $steps = collect($sections)->firstWhere('key', 'steps');
        $this->assertSame('steps', $steps['kind']);
        $this->assertCount(4, $steps['items'], 'Part B continues the numbered steps.');
        $this->assertStringContainsString('sorted()', (string) $steps['items'][1]['html'], 'Indented lines stay with their step.');
        $this->assertStringContainsString('Part B', (string) $steps['items'][3]['lead']);
        $this->assertSame('text', collect($sections)->firstWhere('key', 'what-to-submit')['kind'], 'Deliverables are not tick boxes.');
        $this->assertSame(['self-check:1', 'self-check:2'], array_column(collect($sections)->firstWhere('key', 'self-check')['items'], 'key'));
        $this->assertNull(LessonSteps::parse('Just a paragraph.'));
    }

    public function test_new_fellows_take_the_orientation_before_chapter_one(): void
    {
        $this->actingAs($this->fellow);
        $this->get(route('curriculum.index'))->assertOk()->assertSee('Start your orientation')->assertSee('Orientation: your path to senior level');
        $this->post(route('curriculum.activity.start', $this->lesson))->assertRedirect(route('curriculum.orientation'));
        $this->get(route('curriculum.activity.show', $this->lesson))->assertOk()->assertSee('Start orientation first');

        $page = $this->get(route('curriculum.orientation'))->assertOk()->assertSee('Welcome to the Analytics Team, Ada')->assertSee('ORIENTATION PENDING')->getContent();
        foreach (['clock' => 1, 'late' => 2, 'ai' => 1, 'score' => 2, 'stuck' => 0] as $key => $answer) {
            $this->assertStringNotContainsString('"answer":' . $answer, $page); // answers never reach the page
        }

        // Commitment needs the quiz first.
        $commit = ['goal_role' => 'Junior Data Analyst', 'goal_month' => now()->addMonths(6)->format('Y-m'), 'weekly_hours' => 12, 'study_days' => ['mon', 'wed'],
            'why' => 'I want to help my family business make decisions with data, and get my first analyst job.', 'signature' => 'Ada Lovelace'];
        $this->postJson(route('curriculum.orientation.complete'), $commit)->assertStatus(422);

        $wrong = $this->postJson(route('curriculum.orientation.quiz'), ['answers' => ['clock' => 0, 'late' => 2, 'ai' => 1, 'score' => 2, 'stuck' => 0]])->assertOk();
        $wrong->assertJson(['passed' => false])->assertJsonPath('results.clock.correct', false)->assertJsonPath('results.clock.explain', null);
        $this->postJson(route('curriculum.orientation.quiz'), ['answers' => ['clock' => 1, 'late' => 2, 'ai' => 1, 'score' => 2, 'stuck' => 0]])->assertJson(['passed' => true]);

        $this->postJson(route('curriculum.orientation.complete'), ['why' => 'short'] + $commit)->assertStatus(422);
        $this->postJson(route('curriculum.orientation.complete'), $commit)->assertJson(['ok' => true, 'first' => true]);
        $record = TrackOrientation::sole();
        $this->assertNotNull($record->completed_at);
        $this->assertSame(12, $record->commitment['weekly_hours']);
        $this->assertSame(1, TrackFeedEvent::where('type', 'orientation')->count());

        // Now Chapter 1 opens, and the dashboard-style card shows the commitment.
        $this->post(route('curriculum.activity.start', $this->lesson))->assertRedirect(route('curriculum.activity.show', $this->lesson));
        $this->postJson(route('curriculum.orientation.letter'))->assertForbidden(); // sealed until the track is done
    }

    public function test_fellows_already_under_way_are_not_blocked(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        $this->assertFalse(app(OrientationService::class)->isRequired($this->fellow, $this->track));
        $this->actingAs($this->fellow)->get(route('curriculum.index'))->assertOk()->assertSee('New · recommended');
    }

    public function test_orientation_content_comes_from_the_real_curriculum(): void
    {
        $service = app(OrientationService::class);
        $plan = $service->plan($this->track);
        $this->assertSame(2, $plan['weeks']);
        $this->assertSame(8, $plan['total_hours']); // research 2h + project 6h
        $skills = collect($service->skillPaths($this->track))->keyBy('key');
        $this->assertSame([1], $skills['statistics']['chapters']);
        $this->assertSame('Knows noise from signal.', $skills['statistics']['senior']);
        $toolkit = collect($service->toolkit($this->track))->pluck('what')->implode(' ');
        $this->assertStringContainsString('about 3 days, plus 1 grace day', $toolkit);
        $this->assertStringContainsString('loses 10-20% of its points', $toolkit);
    }

    public function test_steps_are_ticked_and_saved(): void
    {
        $progress = FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        $this->actingAs($this->fellow);
        $this->postJson(route('curriculum.activity.steps', $this->lesson), ['key' => 'steps:2', 'done' => true])->assertOk();
        $this->postJson(route('curriculum.activity.steps', $this->lesson), ['key' => 'self-check:1', 'done' => true])->assertOk();
        $this->postJson(route('curriculum.activity.steps', $this->lesson), ['key' => 'steps:2', 'done' => false])->assertOk();
        $this->assertSame(['self-check:1'], $progress->fresh()->checked_steps);
        $this->postJson(route('curriculum.activity.steps', $this->lesson), ['key' => 'made-up:1', 'done' => true])->assertStatus(422);

        $html = $this->get(route('curriculum.activity.show', $this->lesson))->assertOk()
            ->assertSee('Your lesson, step by step')->assertSee('Stuck?')->assertSee('Try it here')->assertSee('Open starter notebook in Colab')->getContent();
        $this->assertStringContainsString('/course-files/data-science/notebooks/m1.ipynb', $html);

        $progress->update(['status' => CurriculumStatus::LOCKED]);
        $this->postJson(route('curriculum.activity.steps', $this->lesson), ['key' => 'steps:1', 'done' => true])->assertForbidden();
    }

    public function test_quick_checks_give_xp_once_and_join_the_daily_review_later(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        TrackOrientation::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        $check = FlashCard::create(['track_id' => $this->track->id, 'curriculum_activity_id' => $this->lesson->id, 'after_step' => 3, 'key' => 'qc-1', 'chapter' => 1,
            'question' => 'Which is the median of 1, 2, 90?', 'options' => ['2', '31', '90', '1'], 'answer_index' => 0, 'explanation' => 'Middle value.']);
        $daily = FlashCard::create(['track_id' => $this->track->id, 'key' => 'c1-a', 'chapter' => 1, 'question' => 'Q?', 'options' => ['a', 'b'], 'answer_index' => 1]);
        $flash = app(FlashReviewService::class);

        $this->assertSame([$daily->id], $flash->todaysCards($this->fellow, $this->track)->pluck('id')->all(), 'Unseen quick checks stay out of the daily review.');

        $this->actingAs($this->fellow);
        $page = $this->get(route('curriculum.activity.show', $this->lesson))->assertOk()->assertSee('Which is the median of 1, 2, 90?')->getContent();
        $this->assertStringNotContainsString('Middle value.', $page, 'No explanation before answering.');

        $this->postJson(route('curriculum.quick-check', $check), ['option' => 0])->assertOk()->assertJson(['correct' => true, 'xp' => 5, 'answer_index' => 0]);
        $this->postJson(route('curriculum.quick-check', $check), ['option' => 0])->assertJson(['xp' => 0]);
        $this->assertSame('lesson', FlashAnswer::first()->source);
        $this->assertCount(1, $flash->todaysCards($this->fellow, $this->track), 'Lesson answers do not use up the 3 daily questions.');

        $this->travel(1)->days();
        $this->assertTrue($flash->todaysCards($this->fellow, $this->track)->pluck('id')->contains($check->id), 'It comes back in the daily review when due.');
    }

    public function test_pre_submit_check_points_out_gaps(): void
    {
        $progress = FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'ready' => true, 'summary' => 'Almost there.', 'items' => [['status' => 'ok', 'text' => 'GitHub link looks right.'], ['status' => 'missing', 'text' => 'No explanation of the difference.']],
            'tip' => 'Add two sentences comparing mean and median.'])]]]])]);
        $this->actingAs($this->fellow);

        $this->get(route('curriculum.submit.form', $progress))->assertOk()->assertSee('Check my work first');
        $this->postJson(route('curriculum.precheck', $progress), ['url' => '', 'text' => ''])->assertStatus(422);
        $this->postJson(route('curriculum.precheck', $progress), ['url' => 'https://github.com/ada/innova-data-science/blob/main/m1/median.ipynb', 'text' => 'My median is 2.'])
            ->assertOk()->assertJson(['ok' => true, 'ready' => false, 'tip' => 'Add two sentences comparing mean and median.'])
            ->assertJsonPath('items.1.status', 'missing');

        Http::assertSent(fn ($r) => str_contains($r['messages'][1]['content'] ?? '', 'What to submit') && str_contains($r['messages'][1]['content'] ?? '', 'My median is correct.'));
    }

    public function test_curriculum_page_shows_the_next_step_and_classmates(): void
    {
        TrackOrientation::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::IN_PROGRESS, 'deadline_at' => now()->addHours(30)]);
        $grace = User::factory()->create(['role' => 'fellow', 'name' => 'Grace Hopper', 'show_in_track_feed' => true]);
        FellowTrack::create(['fellow_id' => $grace->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        FellowCurriculumProgress::create(['fellow_id' => $grace->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::COMPLETED, 'completed_at' => now()->subDay()]);
        $hidden = User::factory()->create(['role' => 'fellow', 'name' => 'Hidden Person', 'show_in_track_feed' => false]);
        FellowTrack::create(['fellow_id' => $hidden->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);

        $this->actingAs($this->fellow)->get(route('curriculum.index'))->assertOk()
            ->assertSee('Your next step')->assertSee('Mean vs Median')->assertSee('Due in 1 day')
            ->assertSee('1 classmate is here')->assertSee('1 classmate done · Grace')
            ->assertDontSee('Hidden');
    }
}
