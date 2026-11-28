<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Enums\InterviewStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Services\CareerCapitalCalculator;
use App\Services\CurriculumService;
use App\Services\Interview\InterviewEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Interviews in the programme: retakes, the team alert, practice, mentor interviews,
 * the retired live room and older sessions.
 */
class InterviewFlowTest extends TestCase
{
    use RefreshDatabase;
    use InterviewFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInterviews();
        $this->importBlueprint();
    }

    public function test_a_failed_interview_blocks_a_retake_for_24_hours_and_the_third_fail_alerts_the_team(): void
    {
        [$activity, $progress, $first] = $this->startInterview();
        $curriculum = app(CurriculumService::class);
        $first->forceFill(['status' => 'completed', 'completed_at' => now(), 'score' => 40, 'overall_score' => 40, 'scoring_status' => 'done'])->save();
        $curriculum->handleInterviewCompletion($first->fresh());

        $this->assertSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);
        try {
            $curriculum->launchInterviewAttempt($this->fellow, $activity->id);
            $this->fail('A retake within 24 hours must be refused.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('24 hours', $e->getMessage());
        }
        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $activity))->assertOk()->assertSee('Retake from');

        // Second and third fails (each a day later).
        foreach ([2, 3] as $attempt) {
            $this->travel(25)->hours();
            $session = $curriculum->launchInterviewAttempt($this->fellow, $activity->id);
            $session->forceFill(['status' => 'completed', 'completed_at' => now(), 'score' => 50, 'overall_score' => 50, 'scoring_status' => 'done'])->save();
            $curriculum->handleInterviewCompletion($session->fresh());
        }

        $this->assertSame(1, Notification::where('user_id', $this->admin->id)->where('title', 'A fellow failed a mock interview 3 times')->count());
        $this->assertSame(1, Notification::where('user_id', $this->mentor->id)->where('title', 'A fellow failed a mock interview 3 times')->count());
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', 'Book a session with a mentor')->count());
        $this->assertTrue($curriculum->interviewStanding($this->fellow, $activity)['needs_mentor']);
        $this->get(route('curriculum.activity.show', $activity))->assertOk()->assertSee('Book a session with a mentor');

        // Not-scored and abandoned sessions never count as attempts.
        InterviewSession::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'technical_coding', 'mode' => 'ai',
            'status' => 'completed', 'score' => null, 'scoring_status' => 'failed', 'curriculum_activity_id' => $activity->id, 'curriculum_progress_id' => $progress->id]);
        $this->assertSame(3, $curriculum->interviewStanding($this->fellow, $activity)['counted']);
    }

    public function test_practice_is_post_only_limited_to_three_a_day_and_never_counts(): void
    {
        $this->actingAs($this->fellow);
        $this->get('/interviews/practice/start')->assertRedirect(route('interviews.index'));
        $this->assertSame(0, InterviewSession::count()); // a GET never creates a session

        $first = $this->post(route('interviews.practice'))->assertRedirect();
        $practice = InterviewSession::first();
        $this->assertTrue($practice->is_practice);
        $this->assertSame($this->track->id, $practice->track_id);
        $this->assertNull($practice->curriculum_activity_id);
        $first->assertRedirect(route('interviews.room', $practice));

        // An open practice is reused; finished ones count toward the daily limit.
        $this->post(route('interviews.practice'))->assertRedirect(route('interviews.room', $practice));
        $practice->update(['status' => 'cancelled']);
        $this->post(route('interviews.practice'));
        InterviewSession::where('status', 'in_progress')->update(['status' => 'cancelled']);
        $this->post(route('interviews.practice'));
        InterviewSession::where('status', 'in_progress')->update(['status' => 'cancelled']);
        $this->post(route('interviews.practice'))->assertRedirect(route('interviews.index'))->assertSessionHas('error');
        $this->assertSame(3, InterviewSession::where('is_practice', true)->count());

        // A scored practice interview never reaches Career Capital, weekly progress or the curriculum.
        $practice->forceFill(['status' => 'completed', 'completed_at' => now(), 'score' => 95, 'overall_score' => 95, 'scoring_status' => 'done'])->save();
        app(\App\Services\InterviewService::class)->recordScored($practice->fresh());
        $this->assertEquals(0.0, app(CareerCapitalCalculator::class)->mockInterviewScore($this->fellow, $this->track));
        $this->assertSame(0, \App\Models\WeeklyProgress::where('fellow_id', $this->fellow->id)->count());
        $this->get(route('interviews.index'))->assertOk()->assertSee('practice interviews left today');
    }

    public function test_practice_is_held_while_a_weekly_bar_penalty_is_unpaid(): void
    {
        $this->mock(\App\Services\StandingService::class, fn ($m) => $m->shouldReceive('onHold')->andReturn(true));
        $this->actingAs($this->fellow)->post(route('interviews.practice'))->assertRedirect(route('standing.show'));
        $this->assertSame(0, InterviewSession::count());
    }

    public function test_a_human_interview_goes_from_pending_to_scheduled_to_mentor_scores_to_the_review_queue(): void
    {
        [$activity, $progress, $interview] = $this->startInterview(['mode' => 'human', 'min_score' => 70]);
        $this->assertSame(InterviewStatus::PENDING, $interview->status);
        $this->assertNull($interview->engine_version);
        $this->assertTrue(Notification::where('user_id', $this->admin->id)->where('title', 'An interview needs a mentor and a time')->exists());

        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $activity))->assertOk()->assertSee('Waiting for the team');
        $this->get(route('interviews.index'))->assertOk()->assertSee('waiting for the team to schedule it');

        // Admin: mentor, time and meeting link.
        $at = now()->addDays(2)->setTime(10, 0);
        $this->actingAs($this->admin)->get(route('admin.interviews.show', $interview))->assertOk()->assertSee('Schedule: mentor, time, link');
        $this->get(route('admin.interviews.index'))->assertOk();
        $this->post(route('admin.interviews.assign-mentor', $interview), ['mentor_id' => $this->mentor->id, 'scheduled_at' => $at->format('Y-m-d H:i'), 'meeting_link' => 'https://meet.google.com/abc-defg-hij'])
            ->assertRedirect(route('admin.interviews.show', $interview));
        $interview->refresh();
        $this->assertSame(InterviewStatus::SCHEDULED, $interview->status);
        $this->assertSame($this->mentor->id, $interview->interviewer_id);
        $this->assertTrue(Notification::where('user_id', $this->fellow->id)->where('title', 'Interview scheduled')->exists());
        $this->assertTrue(Notification::where('user_id', $this->mentor->id)->where('action_url', route('mentor.interviews.review', $interview))->exists());
        $this->actingAs($this->fellow)->get(route('interviews.show', $interview))->assertOk()->assertSee('Mentor Mbah')->assertSee('https://meet.google.com/abc-defg-hij');

        // Mentor: four 1-10 scores -> 0-100.
        $this->actingAs($this->mentor)->get(route('mentor.interviews.review', $interview))->assertOk()->assertSee('Submit Feedback');
        $this->get(route('mentor.interviews'))->assertOk();
        $scores = ['technical_score' => 8, 'communication_score' => 7, 'problem_solving_score' => 9, 'overall_score' => 8,
            'feedback' => str_repeat('Clear walkthrough of the project and good trade-offs. ', 2), 'strengths' => "Explains decisions\nKnows the schema"];
        $this->post(route('mentor.interviews.complete', $interview), $scores)->assertRedirect(route('mentor.interviews'));
        $interview->refresh();
        $this->assertSame(InterviewStatus::COMPLETED, $interview->status);
        $this->assertEquals(80, (float) $interview->score);
        $this->assertSame(80, (int) $interview->overall_score);
        $this->assertSame('hire', $interview->hire_signal);
        $this->assertTrue(Notification::where('user_id', $this->fellow->id)->where('title', 'Interview scored')->where('action_url', route('interviews.show', $interview))->exists());

        // Passed (80 >= 70) -> review queue.
        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->fresh()->status);
        $this->assertSame($interview->id, $progress->fresh()->evidence['interview']['id']);

        // No double completion.
        $this->post(route('mentor.interviews.complete', $interview), ['technical_score' => 1] + $scores)->assertRedirect(route('mentor.interviews.review', $interview));
        $this->assertEquals(80, (float) $interview->fresh()->score);

        // The reviewer sees the interview on the review page.
        $this->actingAs($this->admin)->get(route('admin.curriculum.reviews.show', $progress))->assertOk()->assertSee('Mock interview')->assertSee('Scored by your mentor', false);
    }

    public function test_a_no_show_does_not_count_and_a_new_interview_can_be_requested(): void
    {
        [$activity, , $interview] = $this->startInterview(['mode' => 'human']);
        app(\App\Services\InterviewService::class)->scheduleHuman($interview, $this->mentor, now()->addDay(), null);

        $this->actingAs($this->mentor)->post(route('mentor.interviews.complete', $interview), ['no_show' => true, 'no_show_notes' => 'Did not join.'])
            ->assertRedirect(route('mentor.interviews'));
        $this->assertSame(InterviewStatus::NO_SHOW, $interview->fresh()->status);
        $this->post(route('mentor.interviews.complete', $interview), ['no_show' => true])->assertSessionHas('error');

        $again = app(CurriculumService::class)->launchInterviewAttempt($this->fellow, $activity->id);
        $this->assertSame(InterviewStatus::PENDING, $again->status);
    }

    public function test_the_live_voice_interview_is_retired(): void
    {
        $legacy = InterviewSession::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'behavioral', 'mode' => 'ai', 'status' => 'in_progress']);
        $this->actingAs($this->fellow);
        $this->get('/interviews/live')->assertRedirect(route('interviews.index'));
        $this->post('/interviews/live/start')->assertRedirect(route('interviews.index'));
        $this->get("/interviews/{$legacy->id}/live")->assertRedirect(route('interviews.index'));
        $this->post("/interviews/{$legacy->id}/live/message")->assertRedirect(route('interviews.index'));
        $this->get(route('interviews.create'))->assertRedirect(route('interviews.index'));
        $this->get(route('interviews.index'))->assertOk()->assertDontSee('Live AI Interview')->assertDontSee('Schedule Interview');

        // An old-room session cannot continue in the new room, but can be cancelled.
        $this->get(route('interviews.room', $legacy))->assertRedirect(route('interviews.show', $legacy));
        $this->post(route('interviews.cancel', $legacy))->assertRedirect(route('interviews.index'));
        $this->assertSame(InterviewStatus::CANCELLED, $legacy->fresh()->status);
    }

    public function test_old_sessions_still_display(): void
    {
        $old = InterviewSession::create([
            'fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'behavioral', 'mode' => 'ai',
            'status' => 'completed', 'completed_at' => now()->subMonth(), 'score' => 82.5, 'overall_score' => 83, 'title' => 'Old AI interview',
            'rubric_scores' => ['question_0' => 80], 'ai_feedback' => ['summary' => 'Good.'],
            'responses' => [['question_index' => 0, 'question' => 'Tell me about yourself.', 'response' => 'I am a data fellow.', 'evaluation' => ['score' => 80, 'feedback' => 'Nice start.']]],
        ]);

        $this->actingAs($this->fellow)->get(route('interviews.show', $old))->assertOk()->assertSee('82.5/100')->assertSee('Tell me about yourself.')->assertSee('Nice start.');
        $this->get(route('interviews.index'))->assertOk()->assertSee('Old AI interview');
        $this->get(route('interviews.ai-room', $old))->assertRedirect(route('interviews.room', $old));
        $this->actingAs($this->admin)->get(route('admin.interviews.show', $old))->assertOk()->assertSee('Performance Breakdown');
    }

    public function test_a_begun_ai_interview_cannot_be_thrown_away_to_retry_at_once(): void
    {
        [$activity, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $activity))->assertOk()->assertSee('Open the briefing');

        $this->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk();
        $this->post(route('interviews.cancel', $interview))->assertSessionHas('error');
        $this->assertSame(InterviewStatus::IN_PROGRESS, $interview->fresh()->status);
        $this->get(route('curriculum.activity.show', $activity))->assertOk()->assertSee('Continue interview');
    }

    public function test_after_a_reviewer_sends_a_passed_interview_back_only_new_interviews_count(): void
    {
        [$activity, $progress, $first] = $this->startInterview();
        $curriculum = app(CurriculumService::class);
        $first->forceFill(['status' => 'completed', 'completed_at' => now(), 'score' => 90, 'overall_score' => 90, 'scoring_status' => 'done'])->save();
        $curriculum->handleInterviewCompletion($first->fresh());
        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->fresh()->status);

        $this->travel(1)->minutes();
        $progress->fresh()->reject($this->admin, 'The integrity report needs a fresh attempt.');
        $this->assertFalse($curriculum->interviewStanding($this->fellow, $activity)['passed']);

        $retake = $curriculum->launchInterviewAttempt($this->fellow, $activity->id);
        $this->assertSame(InterviewStatus::IN_PROGRESS, $retake->status);
    }

    public function test_expired_sessions_do_not_block_a_new_attempt(): void
    {
        [$activity, , $interview] = $this->startInterview();
        $interview->forceFill(['expires_at' => now()->subMinute()])->save();

        $new = app(CurriculumService::class)->launchInterviewAttempt($this->fellow, $activity->id);
        $this->assertNotSame($interview->id, $new->id);
        $this->assertSame('abandoned', $interview->fresh()->cancellation_reason);
        $this->assertTrue(app(InterviewEngine::class)->isEngine($new));
    }
}
