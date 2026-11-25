<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\AdminSetting;
use App\Models\Fee;
use App\Models\FellowActivityDay;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FoundationArea;
use App\Models\FoundationResult;
use App\Models\HelpRequest;
use App\Models\Notification;
use App\Models\SupportBuddy;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\TypingRace;
use App\Models\TypingSession;
use App\Models\User;
use App\Models\WeeklyStanding;
use App\Services\FellowActivityService;
use App\Services\FoundationService;
use App\Services\StandingService;
use App\Services\SupportService;
use App\Services\TypingArenaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportStandingTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected Track $track;
    protected TrackCurriculumActivity $lesson;
    protected TrackCurriculumActivity $project;

    protected function setUp(): void
    {
        parent::setUp();
        // EnsureInternshipApproved's overdue-fee SQL is MySQL-only; the penalty exclusion is checked on MySQL in the browser run.
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        // Wednesday 16 Dec 2026: the bar has counted since Monday 30 Nov.
        $this->travelTo(Carbon::parse('2026-12-16 10:00:00'));
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Team Admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace', 'show_in_track_feed' => true]);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subWeeks(5)]);
        $m = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Welcome', 'description' => 'W.', 'sequence_order' => 1]);
        $this->lesson = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => 'Lesson', 'description' => 'L.', 'type' => 'research',
            'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => 1]);
        $this->project = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => 'Project', 'description' => 'P.', 'type' => 'project',
            'career_capital_category' => 'technical', 'points' => 20, 'is_active' => true, 'is_required' => true, 'sequence_order' => 2]);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::AVAILABLE]);
        TrackOrientation::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'completed_at' => now()->subWeeks(5)]);
    }

    protected function activeOn(string $date, array $kinds): void
    {
        FellowActivityDay::create(['fellow_id' => $this->fellow->id, 'day' => $date, 'kinds' => $kinds]);
    }

    public function test_actions_record_activity_days(): void
    {
        $svc = app(FellowActivityService::class);
        $svc->record($this->fellow, 'step');
        $svc->record($this->fellow, 'step');
        $svc->record($this->fellow, 'review');
        $svc->record($this->fellow, 'nonsense');
        $day = FellowActivityDay::sole();
        $this->assertSame(['step' => 2, 'review' => 1], $day->kinds);

        $sum = $svc->summary($this->fellow, now()->startOfWeek(), now()->endOfWeek());
        $this->assertSame(['active_days' => 1, 'practice_days' => 1, 'progress_actions' => 2], array_slice($sum, 0, 3));

        // Real endpoints record too: starting an activity counts as a curriculum action.
        $this->actingAs($this->fellow)->post(route('curriculum.activity.start', $this->lesson))->assertRedirect();
        $this->assertSame(1, FellowActivityDay::sole()->kinds['start']);
    }

    public function test_weekly_bar_escalates_from_warning_to_penalty_hold_and_final_warning(): void
    {
        $standing = app(StandingService::class);

        // Week of 30 Nov: met (3 active days, 3 practice days, 1 curriculum action).
        foreach (['2026-11-30', '2026-12-01', '2026-12-03'] as $d) {
            $this->activeOn($d, ['review' => 1, 'step' => 1]);
        }
        $standing->evaluateWeek(Carbon::parse('2026-11-30'));
        $this->assertTrue(WeeklyStanding::sole()->met);

        // Week of 7 Dec: only 1 active day -> warning, no fee.
        $this->activeOn('2026-12-08', ['review' => 1]);
        $standing->evaluateWeek(Carbon::parse('2026-12-07'));
        $w2 = WeeklyStanding::whereDate('week_start', '2026-12-07')->sole();
        $this->assertFalse($w2->met);
        $this->assertSame('warning', $w2->action);
        $this->assertNull($w2->fee_id);
        $this->assertFalse($standing->onHold($this->fellow));
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '⚠️ You missed the weekly bar')->count());

        // Re-running the same week changes nothing.
        $standing->evaluateWeek(Carbon::parse('2026-12-07'));
        $this->assertSame(2, WeeklyStanding::count());

        // Week of 14 Dec (travel to next Monday): second miss -> 500 FCFA penalty + hold.
        $this->travelTo(Carbon::parse('2026-12-21 02:00:00'));
        $this->artisan('standing:evaluate')->assertSuccessful();
        $w3 = WeeklyStanding::whereDate('week_start', '2026-12-14')->sole();
        $this->assertSame('fee', $w3->action);
        $this->assertSame(2, $w3->consecutive_misses);
        $this->assertSame(500.0, (float) $w3->fee->amount_total);
        $this->assertSame(WeeklyStanding::class, $w3->fee->billable_type);
        $this->assertTrue($standing->onHold($this->fellow));
        $this->assertSame('hold', $standing->state($this->fellow));

        // The hold blocks starting work (and JSON calls), but not learning pages or the whole platform.
        $this->actingAs($this->fellow);
        $this->post(route('curriculum.activity.start', $this->lesson))->assertRedirect(route('standing.show'));
        $this->postJson(route('typing.arena.join'))->assertStatus(423)->assertJson(['hold' => true]);
        $this->get(route('curriculum.index'))->assertOk()->assertSee('Progress on hold');
        $this->get(route('standing.show'))->assertOk()->assertSee('Penalty to settle')->assertSee('500 FCFA');
        $w3->fee->update(['final_due_date' => now()->subDays(5)]); // even overdue, a penalty never locks the whole platform
        $this->get(route('dashboard'))->assertOk();

        // Third miss: another fee and an honest final warning; the team is asked to review.
        $this->travelTo(Carbon::parse('2026-12-28 02:00:00'));
        $this->artisan('standing:evaluate')->assertSuccessful();
        $w4 = WeeklyStanding::whereDate('week_start', '2026-12-21')->sole();
        $this->assertSame('final', $w4->action);
        $this->assertSame(2, Fee::where('billable_type', WeeklyStanding::class)->count());
        $this->assertSame('at_risk', $standing->state($this->fellow));
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '⚠️ Final warning: your place in the program is at risk')->count());
        $this->assertSame(1, Notification::where('user_id', $this->admin->id)->where('title', 'Team review needed: Ada Lovelace')->count());

        // Admin bypass: waiving the fees (justified reason) lifts the hold.
        foreach (Fee::where('billable_type', WeeklyStanding::class)->get() as $fee) {
            $this->actingAs($this->admin)->post(route('admin.fees.waive', $fee), ['reason' => 'Hospitalised for two weeks, with a doctor note.'])->assertRedirect();
        }
        $this->assertFalse($standing->onHold($this->fellow));
        $this->actingAs($this->fellow)->post(route('curriculum.activity.start', $this->lesson))->assertRedirect(route('curriculum.activity.show', $this->lesson));
    }

    public function test_fairness_rules_and_admin_excuse(): void
    {
        $standing = app(StandingService::class);

        // Nothing before the start week.
        $this->assertSame(0, $standing->evaluateWeek(Carbon::parse('2026-11-23'))['evaluated']);

        // Nothing open to work on -> the curriculum part is waived.
        FellowCurriculumProgress::where('fellow_id', $this->fellow->id)->update(['status' => CurriculumStatus::SUBMITTED]);
        foreach (['2026-11-30', '2026-12-02', '2026-12-04'] as $d) {
            $this->activeOn($d, ['review' => 1]);
        }
        $standing->evaluateWeek(Carbon::parse('2026-11-30'));
        $w = WeeklyStanding::where('fellow_id', $this->fellow->id)->sole();
        $this->assertTrue($w->progress_waived);
        $this->assertTrue($w->met);

        // A fellow who joined during a week is not evaluated for it.
        $newbie = User::factory()->create(['role' => 'fellow']);
        $newbie->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $newbie->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => Carbon::parse('2026-12-08')]);
        TrackOrientation::create(['fellow_id' => $newbie->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        $standing->evaluateWeek(Carbon::parse('2026-12-07'));
        $this->assertSame(0, WeeklyStanding::where('fellow_id', $newbie->id)->count());
        $this->assertSame('warning', WeeklyStanding::where('fellow_id', $this->fellow->id)->whereDate('week_start', '2026-12-07')->sole()->action, 'Inactive week: warning (default fee from the 2nd miss).');

        // Configurable fee: an admin sets 750 FCFA and the fee from the 1st miss.
        $this->actingAs($this->admin)->patch(route('admin.settings.update', 'standing'), [
            'standing_enabled' => 1, 'standing_start_date' => '2026-11-30', 'weekly_bar_active_days' => 3, 'weekly_bar_practice_days' => 3,
            'weekly_bar_progress_actions' => 1, 'standing_penalty_fee' => 750, 'standing_penalty_from_miss' => 1,
            'foundations_typing_wpm' => 25, 'foundations_typing_accuracy' => 90, 'foundations_pass_percent' => 70,
        ])->assertRedirect();
        AdminSetting::clearAllCache();
        $this->assertSame(750, $standing->settings()['fee']);
        FellowCurriculumProgress::where('fellow_id', $this->fellow->id)->update(['status' => CurriculumStatus::AVAILABLE]);
        $this->travelTo(Carbon::parse('2026-12-21 02:00:00'));
        $standing->evaluateWeek(Carbon::parse('2026-12-14'));
        $miss = WeeklyStanding::where('fellow_id', $this->fellow->id)->whereDate('week_start', '2026-12-14')->sole();
        $this->assertSame(750.0, (float) $miss->fee->amount_total);
        $this->assertTrue($standing->onHold($this->fellow));

        // Admin excuses the week: counted as met and its fee waived.
        $this->actingAs($this->admin)->get(route('admin.support.index', ['tab' => 'standing']))->assertOk();
        $this->post(route('admin.support.excuse', $miss), ['reason' => 'No electricity in the whole area all week.'])->assertRedirect();
        $miss->refresh();
        $this->assertTrue($miss->exempt);
        $this->assertSame(Fee::STATUS_WAIVED, $miss->fee->status);
        $this->assertFalse($standing->onHold($this->fellow));
    }

    public function test_base_camp_check_lessons_and_test_keep_answers_on_the_server(): void
    {
        $q = fn ($k, $a) => ['key' => $k, 'question' => "Question {$k}?", 'options' => ['w', 'x', 'y', 'z'], 'answer' => $a, 'explain' => "Because {$k}."];
        FoundationArea::create(['key' => 'maths', 'title' => 'Everyday maths', 'icon' => '➗', 'summary' => 'S.', 'why' => 'W.', 'sort' => 1, 'content' => [
            'check' => ['passage' => null, 'questions' => [$q('m-c1', 2), $q('m-c2', 0)]],
            'lessons' => [['key' => 'percent', 'title' => 'Percentages', 'minutes' => 8, 'body' => "### Percent\n10% of 500 FCFA is 50 FCFA.", 'practice' => [$q('m-p1', 1)]]],
            'test' => ['passage' => null, 'questions' => [$q('m-t1', 3), $q('m-t2', 1)]],
        ]]);
        $foundations = app(FoundationService::class);
        $this->assertSame(['skills check'], $foundations->gaps($this->fellow));
        $this->actingAs($this->fellow);

        $page = $this->get(route('base-camp.check'))->assertOk()->assertSee('Skills check')->getContent();
        $this->assertStringNotContainsString('Because m-c1', $page);
        $this->assertStringNotContainsString('"answer"', $page);

        // Check: score only, no answers revealed.
        $this->postJson(route('base-camp.submit', ['maths', 'check']), ['answers' => ['m-c1' => 2, 'm-c2' => 1]])->assertOk()
            ->assertJson(['score' => 1, 'total' => 2, 'percent' => 50, 'passed' => false, 'results' => []]);
        // Typing test counts for the typing area.
        $this->postJson(route('typing.save'), ['mode' => 'test', 'wpm' => 31, 'accuracy' => 94, 'chars' => 155, 'seconds' => 60])->assertOk();
        $this->assertSame(['everyday maths'], $foundations->gaps($this->fellow));

        // Lesson practice explains; the module test says what was wrong, not the right answer.
        $this->get(route('base-camp.lesson', ['maths', 'percent']))->assertOk()->assertSee('10% of 500 FCFA');
        $this->postJson(route('base-camp.submit', ['maths', 'lesson', 'percent']), ['answers' => ['m-p1' => 0]])->assertJsonPath('results.m-p1.answer', 1)->assertJsonPath('results.m-p1.explain', 'Because m-p1.');
        $test = $this->postJson(route('base-camp.submit', ['maths', 'test']), ['answers' => ['m-t1' => 3, 'm-t2' => 0]])->assertJson(['passed' => false]);
        $this->assertSame(['correct' => false], $test->json('results.m-t2'));
        $this->postJson(route('base-camp.submit', ['maths', 'test']), ['answers' => ['m-t1' => 3, 'm-t2' => 1]])->assertJson(['passed' => true, 'percent' => 100]);
        $this->assertSame([], $foundations->gaps($this->fellow));
        $this->get(route('base-camp.index'))->assertOk()->assertSee('Badge earned');
        $this->assertSame(3, FoundationResult::where('kind', '!=', 'check')->count());
        $this->assertTrue(FellowActivityDay::sole()->kinds['base_camp'] >= 1);
    }

    public function test_typing_trainer_pages_and_impossible_results(): void
    {
        $this->actingAs($this->fellow);
        $this->get(route('typing.index'))->assertOk()->assertSee('Typing trainer')->assertSee('Home row');
        $this->get(route('typing.practice', 'test'))->assertOk()->assertSee('1-minute speed test');
        $this->get(route('typing.practice', ['lesson', 'home-row']))->assertOk()->assertSee('Lesson: Home row');
        $this->get(route('typing.practice', 'drill'))->assertOk()->assertSee('Daily drill');
        $this->get(route('typing.practice', ['lesson', 'nope']))->assertNotFound();
        $this->postJson(route('typing.save'), ['mode' => 'test', 'wpm' => 200, 'accuracy' => 99, 'chars' => 2000, 'seconds' => 60])->assertStatus(422);
        $this->postJson(route('typing.save'), ['mode' => 'drill', 'wpm' => 48, 'accuracy' => 96, 'chars' => 1200, 'seconds' => 300])->assertOk()->assertJson(['league' => 'gold']);
        $this->assertSame(1, TypingSession::count());
        $this->get(route('typing.arena'))->assertOk()->assertSee('Typing Arena');
    }

    public function test_typing_arena_races_live_with_places_ghosts_and_cheat_limits(): void
    {
        $other = User::factory()->create(['role' => 'fellow', 'name' => 'Grace Hopper']);
        $other->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $other->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subWeeks(5)]);
        $arena = app(TypingArenaService::class);

        $this->actingAs($this->fellow);
        $state = $this->postJson(route('typing.arena.join'))->assertOk()->json();
        $this->assertSame('waiting', $state['status']);
        $this->assertNull($state['starts_in']);
        $this->actingAs($other);
        $state2 = $this->postJson(route('typing.arena.join'))->assertOk()->json();
        $this->assertSame($state['id'], $state2['id'], 'Same league -> same race.');
        $this->assertGreaterThan(0, $state2['starts_in'], 'Countdown starts when a second racer joins.');

        $race = TypingRace::find($state['id']);
        $len = mb_strlen($race->text);
        $this->travel(9)->seconds();
        $this->assertSame('running', $arena->state($race, $other)['status']);

        // Cheat limit: claiming the whole text after 1 second is capped.
        $this->travel(1)->seconds();
        $capped = $this->postJson(route('typing.arena.progress', $race), ['typed' => $len, 'finished' => true, 'accuracy' => 100])->json();
        $this->assertLessThan($len, collect($capped['entries'])->firstWhere('me', true)['typed']);

        $this->travel(30)->seconds();
        $this->postJson(route('typing.arena.progress', $race), ['typed' => $len, 'finished' => true, 'accuracy' => 97])->assertOk();
        $this->actingAs($this->fellow);
        $this->travel(5)->seconds();
        $final = $this->postJson(route('typing.arena.progress', $race), ['typed' => $len, 'finished' => true, 'accuracy' => 92])->json();
        $this->assertSame('finished', $final['status']);
        $this->assertSame(2, collect($final['entries'])->firstWhere('me', true)['place']);
        $this->assertSame(1, collect($final['entries'])->firstWhere('name', 'Grace')['place']);
        $this->assertSame(2, TypingSession::where('mode', 'race')->count());

        // Alone for 15 seconds -> race your ghost.
        $solo = $arena->join($this->fellow);
        $this->travel(16)->seconds();
        $ghost = $arena->state($solo, $this->fellow);
        $this->assertNotNull($ghost['ghost_wpm']);
        $this->assertGreaterThan(0, $ghost['starts_in']);
    }

    public function test_ask_a_human_and_admin_answer(): void
    {
        $this->actingAs($this->fellow);
        $this->postJson(route('help.store'), ['question' => 'short'])->assertStatus(422);
        $this->postJson(route('help.store'), ['question' => 'About step 2: my groupby returns NaN for every branch and I do not know why.', 'activity_id' => $this->lesson->id, 'step_key' => 'steps:2'])
            ->assertOk()->assertJson(['ok' => true]);
        $help = HelpRequest::sole();
        $this->assertSame($this->lesson->id, $help->curriculum_activity_id);
        $this->assertSame(1, Notification::where('user_id', $this->admin->id)->where('title', '🙋 Ada Lovelace asked for help')->count());

        $this->actingAs($this->admin)->get(route('admin.support.index', ['tab' => 'help']))->assertOk()->assertSee('my groupby returns NaN');
        $this->post(route('admin.support.help.answer', $help), ['answer' => 'Check the column name: it is TotalSale_FCFA, with capitals.'])->assertRedirect();
        $this->assertSame('answered', $help->fresh()->status);
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '🙋 The team answered your question')->count());
        $this->actingAs($this->fellow)->get(route('help.index'))->assertOk()->assertSee('TotalSale_FCFA');
    }

    public function test_struggle_signals_nudges_and_support_buddy(): void
    {
        FellowCurriculumProgress::where('fellow_id', $this->fellow->id)->update(['status' => CurriculumStatus::IN_PROGRESS, 'started_at' => now()->subDays(6), 'deadline_at' => now()->subDays(2)]);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->project->id, 'status' => CurriculumStatus::REJECTED]);
        $support = app(SupportService::class);
        $keys = array_column($support->signals($this->fellow), 'key');
        foreach (['stuck', 'overdue'] as $k) {
            $this->assertContains($k, $keys);
        }
        $this->assertTrue($support->atRisk()->contains(fn ($r) => $r['fellow']->is($this->fellow)));

        $this->artisan('support:nudge')->assertSuccessful();
        $this->artisan('support:nudge')->assertSuccessful();
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '💬 A quick note from the I-NNOVA team')->count(), 'At most once a week per signal.');

        $buddy = User::factory()->create(['role' => 'fellow', 'name' => 'Grace Hopper']);
        $buddy->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $buddy->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subWeeks(5)]);
        $this->actingAs($this->admin)->get(route('admin.support.index'))->assertOk()->assertSee('Ada Lovelace')->assertSee('Stuck on an activity');
        $this->post(route('admin.support.buddy', $this->fellow), ['buddy_id' => $buddy->id, 'note' => 'Grace is strong in pandas.'])->assertRedirect();
        $this->assertSame($buddy->id, SupportBuddy::sole()->buddy_id);
        $this->assertSame(1, Notification::where('user_id', $buddy->id)->where('title', '🤝 You are a support buddy')->count());
        $this->post(route('admin.support.nudge', $this->fellow), ['message' => 'Hi Ada, shall we talk tomorrow at 4?'])->assertRedirect();

        $this->artisan('standing:announce')->assertSuccessful();
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '📏 New: the weekly bar')->count());
    }
}
