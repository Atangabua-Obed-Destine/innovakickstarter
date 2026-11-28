<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Enums\InterviewStatus;
use App\Models\InterviewBlueprint;
use App\Models\InterviewSession;
use App\Services\Interview\IntegrityAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The mock interview engine: blueprints, plans, the turn loop, transcription,
 * integrity, final scoring and expiry.
 */
class InterviewEngineTest extends TestCase
{
    use RefreshDatabase;
    use InterviewFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInterviews();
    }

    // ---------------------------------------------------------------- blueprints

    public function test_blueprint_import_validates_and_stores(): void
    {
        $dir = sys_get_temp_dir() . '/iks-bp-' . uniqid();
        mkdir($dir);
        file_put_contents("{$dir}/good.json", json_encode($this->blueprint()));
        $bad = $this->blueprint();
        $bad['rubric'][0]['weight'] = 0.9;          // weights no longer add up to 1
        $bad['bank'][0]['round'] = 'whiteboard';     // unknown round
        $bad['hire_signal']['hire'] = 90;            // thresholds out of order
        file_put_contents("{$dir}/bad.json", json_encode($bad));

        $this->artisan('interviews:import', ['file' => "{$dir}/bad.json"])
            ->expectsOutputToContain('rubric weights must add up to 1.0')
            ->expectsOutputToContain('unknown round whiteboard')
            ->assertFailed();
        $this->artisan('interviews:import', ['file' => "{$dir}/good.json", '--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, InterviewBlueprint::count());

        $this->artisan('interviews:import', ['file' => "{$dir}/good.json"])->expectsOutputToContain('2 rounds, 1 chapters, 4 bank questions')->assertSuccessful();
        $stored = InterviewBlueprint::where('track_id', $this->track->id)->first();
        $this->assertSame(3, $stored->version);
        $this->assertCount(4, $stored->data['bank']);
    }

    public function test_a_track_without_a_blueprint_gets_a_generic_interview(): void
    {
        [, , $interview] = $this->startInterview(); // the AI is down: generic questions about the topics

        $this->assertSame('generic', $interview->plan['source']);
        $this->assertCount(4, $interview->plan['items']);
        $this->assertStringContainsString('GROUP BY', collect($interview->plan['items'])->pluck('question')->implode(' '));
        $this->assertTrue($interview->context['blueprint']['generic']);
    }

    // ---------------------------------------------------------------- planning

    public function test_starting_a_chapter_interview_builds_a_plan_from_the_bank_when_the_ai_fails(): void
    {
        $this->importBlueprint();
        [, $progress, $interview] = $this->startInterview();

        $this->assertSame(InterviewStatus::IN_PROGRESS, $interview->status);
        $this->assertSame('bank', $interview->plan['source']);
        $this->assertSame(['sql', 'communication'], array_column($interview->plan['items'], 'round'));
        $this->assertContains($interview->plan['items'][0]['bank_id'], ['ds-sql-1', 'ds-sql-2']);
        $this->assertSame('sql', $interview->plan['items'][0]['answer_mode']);
        $this->assertSame(300, $interview->plan['items'][0]['time_limit_seconds']);
        $this->assertSame($interview->id, $progress->linked_interview_id);
        $this->assertSame(60, $interview->context['pass_mark']); // the activity's min_score wins

        // The briefing never shows a question, and the plan is never sent to the browser.
        $first = $interview->plan['items'][0]['question'];
        $this->actingAs($this->fellow)->get(route('interviews.room', $interview))->assertOk()
            ->assertSee('Ngozi Tabe')->assertSee('Enter full screen and start')->assertDontSee($first);
        $this->getJson(route('interviews.state', $interview))->assertOk()->assertJsonPath('prompt', null)->assertJsonMissing(['plan']);
        $this->assertArrayNotHasKey('plan', $interview->toArray());
    }

    public function test_a_retake_avoids_questions_the_fellow_has_already_seen(): void
    {
        $this->importBlueprint();
        [$activity, $progress, $first] = $this->startInterview();
        $seen = array_column($first->plan['items'], 'bank_id');
        $first->update(['status' => 'cancelled']);

        $second = app(\App\Services\CurriculumService::class)->launchInterviewAttempt($this->fellow, $activity->id);
        $this->assertEmpty(array_intersect($seen, array_column($second->plan['items'], 'bank_id')));
    }

    public function test_pathway_rounds_and_questions_follow_the_fellows_pathway(): void
    {
        $bp = $this->blueprint();
        $depth = $bp['rounds']['communication'];
        $bp['rounds']['backend_depth'] = $depth + ['pathways' => ['backend', 'fullstack']];
        $bp['rounds']['frontend_depth'] = $depth + ['pathways' => ['frontend']];
        $bp['chapters'][0]['rounds'] = ['sql', 'backend_depth', 'frontend_depth'];
        $bp['chapters'][0]['main_questions'] = 4;
        $q = $bp['bank'][2];
        $bp['bank'][] = ['id' => 'ds-be-1', 'round' => 'backend_depth', 'pathways' => ['backend']] + $q;
        $bp['bank'][] = ['id' => 'ds-fe-1', 'round' => 'frontend_depth', 'pathways' => ['frontend']] + $q;
        $bp['bank'][] = ['id' => 'ds-sql-be', 'round' => 'sql', 'pathways' => ['backend']] + $bp['bank'][0];
        $this->assertSame([], app(\App\Services\Interview\BlueprintService::class)->validate($bp));

        $planner = app(\App\Services\Interview\InterviewPlanner::class);
        $context = ['chapter' => $bp['chapters'][0] + ['matched' => true], 'pathway' => 'frontend', 'project' => null, 'level' => [], 'track' => ['name' => 'X']];
        $items = $planner->plan($bp, $context, [])['items'];

        $this->assertNotContains('backend_depth', array_column($items, 'round'));
        $this->assertContains('frontend_depth', array_column($items, 'round'));
        $this->assertEmpty(array_intersect(['ds-be-1', 'ds-sql-be'], array_column($items, 'bank_id')));
    }

    public function test_fenced_code_and_bold_render_safely(): void
    {
        $html = (string) \App\Support\SafeMarkdown::render("Use **GROUP BY**.\n```sql\nSELECT '<b>' FROM t;\n```\n<script>alert(1)</script>");

        $this->assertStringContainsString('<strong>GROUP BY</strong>', $html);
        $this->assertStringContainsString('<pre class="iks-code" data-lang="sql"', $html);
        $this->assertStringContainsString('SELECT &#039;&lt;b&gt;&#039; FROM t;', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertSame("SELECT 1;\nFROM x", \App\Support\SafeMarkdown::code("Start here:\n```sql\nSELECT 1;\nFROM x\n```"));
    }

    // ---------------------------------------------------------------- the turn loop

    public function test_full_turn_loop_with_follow_ups_adaptive_questions_scoring_and_review_queue(): void
    {
        $this->importBlueprint();
        $this->ai['plan'] = ['questions' => [
            ['round' => 'sql', 'question' => 'Write a query that counts sales per branch.', 'setup' => 'Table sales(branch, amount).', 'bank_id' => 'ds-sql-1', 'strong_answer' => 'x', 'skills' => ['sql']],
            ['round' => 'communication', 'question' => 'Explain your query to a shop manager.', 'strong_answer' => 'Plain words, an example.', 'skills' => ['communication']],
        ]];
        $this->ai['turns'] = [
            ['notes' => ['Correct grouping'], 'provisional' => ['sql_skill' => 3, 'bogus' => 4], 'quality' => 'adequate', 'action' => 'follow_up',
                'follow_up' => 'What happens to a branch with no sales?', 'acknowledgement' => 'Correct, great answer!', 'next_question' => null],
            ['notes' => ['Knows LEFT JOIN'], 'provisional' => ['reasoning' => 4], 'quality' => 'strong', 'action' => 'next', 'follow_up' => null,
                'acknowledgement' => 'Okay, let us go on.', 'next_question' => 'Explain your query to a shop manager, and how you would check that the totals are right.'],
            ['notes' => [], 'provisional' => [], 'quality' => 'adequate', 'action' => 'follow_up', 'follow_up' => 'Which example would you show first?', 'acknowledgement' => 'Thanks.'],
            ['notes' => [], 'provisional' => [], 'quality' => 'adequate', 'action' => 'follow_up', 'follow_up' => 'And if the manager disagrees?', 'acknowledgement' => 'Thanks.'],
            ['notes' => [], 'provisional' => [], 'quality' => 'adequate', 'action' => 'follow_up', 'follow_up' => 'A third follow-up that must not be asked?', 'acknowledgement' => 'Thanks.'],
        ];
        $this->ai['score'] = $this->scoreReply();

        [, $progress, $interview] = $this->startInterview();
        $this->assertSame('ai', $interview->plan['source']);
        $this->actingAs($this->fellow);

        $begin = $this->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk()
            ->assertJsonPath('prompt.turn', 1)->assertJsonPath('prompt.answer_mode', 'sql')
            ->assertJsonPath('prompt.text', 'Write a query that counts sales per branch.');
        $this->assertStringNotContainsString('shop manager', $begin->getContent()); // never sent ahead

        // Turn 1 (SQL): the browser's own "question" and "score" are ignored.
        $this->postJson(route('interviews.answer', $interview), [
            'turn' => 1, 'code' => 'SELECT branch, COUNT(*) FROM sales GROUP BY branch;', 'answer' => 'I group by branch and count.',
            'explanation_mode' => 'voice', 'question' => 'Fake question', 'score' => 100, 'run_output' => "branch | COUNT(*)\nBamenda | 12",
            'events' => [['type' => 'paste_blocked', 'chars' => 300], ['type' => 'fullscreen_exit', 'duration_ms' => 4000], ['type' => 'not_a_real_event']],
            'stats' => ['keystrokes' => 80, 'max_burst' => 0],
        ])->assertOk()->assertJsonPath('prompt.kind', 'follow_up')->assertJsonPath('prompt.turn', 2)
            ->assertJsonPath('prompt.text', 'What happens to a branch with no sales?')
            ->assertJsonPath('prompt.starter', 'SELECT branch, COUNT(*) FROM sales GROUP BY branch;');

        $interview->refresh();
        $this->assertSame('Write a query that counts sales per branch.', $interview->responses[0]['question']);
        $this->assertSame(['sql_skill' => 3], $interview->responses[0]['evaluation']['provisional']);
        // The interviewer never says whether an answer was right.
        $this->assertStringStartsWith('Thank you.', collect($interview->transcript)->last()['text']);

        // Turn 2 (follow-up): strong answer -> the next main question gets harder.
        $this->postJson(route('interviews.answer', $interview), ['turn' => 2, 'code' => 'SELECT b.name, COUNT(s.id) FROM branches b LEFT JOIN sales s ON s.branch = b.name GROUP BY b.name;', 'answer' => 'A LEFT JOIN keeps it with zero.', 'explanation_mode' => 'voice'])
            ->assertOk()->assertJsonPath('prompt.kind', 'main')->assertJsonPath('prompt.n', 2)->assertJsonPath('prompt.answer_mode', 'voice')
            ->assertJsonPath('prompt.text', 'Explain your query to a shop manager, and how you would check that the totals are right.');
        $interview->refresh();
        $this->assertSame('harder', $interview->plan['items'][1]['adjusted']);
        $this->assertSame('Explain your query to a shop manager.', $interview->plan['items'][1]['original_question']);

        // Turn 3 (voice): transcribed on the server first.
        $this->post(route('interviews.transcribe', $interview), ['audio' => UploadedFile::fake()->create('answer.webm', 40, 'audio/webm'), 'turn' => 3], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('text', 'Umm, so I would group the rows by branch and count them.')->assertJsonPath('words_per_minute', 120);
        $this->postJson(route('interviews.answer', $interview), ['turn' => 3, 'answer' => 'Umm, so I would group the rows by branch and count them.', 'explanation_mode' => 'voice', 'transcription_source' => 'groq'])
            ->assertOk()->assertJsonPath('prompt.kind', 'follow_up');
        $this->postJson(route('interviews.answer', $interview), ['turn' => 4, 'answer' => 'The Bamenda branch, because it is the biggest.', 'explanation_mode' => 'typed', 'mic_off' => true])
            ->assertOk()->assertJsonPath('prompt.kind', 'follow_up');
        // Two follow-ups per main question at most: the third is not asked, and this was the last question.
        $this->postJson(route('interviews.answer', $interview), ['turn' => 5, 'answer' => 'I would show the data.', 'explanation_mode' => 'voice'])
            ->assertOk()->assertJsonPath('done', true);

        $interview->refresh();
        $this->assertSame(InterviewStatus::COMPLETED, $interview->status);
        $this->assertCount(5, $interview->responses);
        $this->assertEquals(6.0, $interview->responses[2]['voice_seconds']);
        $this->assertSame('groq', $interview->responses[2]['transcription_source']);
        $this->assertSame(1, $interview->communication_metrics['typed_answers']);
        $this->assertGreaterThan(0, $interview->filler_word_count);
        $this->assertNotNull($interview->speaking_pace_wpm);

        // Final scoring (queued job, sync in tests): 25 x (0.4x3 + 0.3x4 + 0.2x2 + 0.1x3) = 77.5 -> "hire" (>= 75).
        $this->assertSame('done', $interview->scoring_status);
        $this->assertEquals(77.5, (float) $interview->score);
        $this->assertSame(78, (int) $interview->overall_score);
        $this->assertSame('hire', $interview->hire_signal);
        $this->assertSame(['GROUP BY branch'], $interview->scorecard['dimensions'][0]['evidence']); // invented quote dropped
        $this->assertStringContainsString('A strong answer for ds-sql-1', $interview->scorecard['questions'][0]['strong_answer']);
        $this->assertSame(route('curriculum.activity.show', $this->lesson->id), $interview->scorecard['next_steps'][0]['links'][0]['url']);

        // Integrity: one blocked paste and one full screen exit -> review, never a fail.
        $this->assertSame('review', $interview->integrity_level);
        $this->assertContains('1 paste attempt was blocked.', $interview->integrity['reasons']);
        $this->assertSame(1, $interview->integrity['counts']['typed_explanations']);

        // Passed (77.5 >= 60): submitted to the human review queue with the interview as evidence.
        $progress->refresh();
        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->status);
        $this->assertSame($interview->id, $progress->evidence['interview']['id']);
        $this->assertSame('hire', $progress->evidence['interview']['hire_signal']);

        // Results page for the fellow.
        $this->get(route('interviews.show', $interview))->assertOk()->assertSee('Hire')->assertSee('77.5')->assertSee('What a strong answer includes')
            ->assertSee('Practise GROUP BY with HAVING.')->assertSee('Logged: 1 full screen exit, 1 blocked paste, 1 typed explanation.', false);
    }

    public function test_when_the_ai_fails_during_a_turn_the_interview_continues_without_a_follow_up(): void
    {
        $this->importBlueprint();
        [, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow)->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk();

        $this->postJson(route('interviews.answer', $interview), ['turn' => 1, 'code' => 'SELECT 1;'])
            ->assertOk()->assertJsonPath('prompt.kind', 'main')->assertJsonPath('prompt.n', 2);
        $this->assertFalse($interview->fresh()->responses[0]['evaluation']['evaluated']);
    }

    public function test_answers_are_rejected_for_the_wrong_turn_after_completion_and_after_expiry(): void
    {
        $this->importBlueprint();
        [, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow);

        $this->postJson(route('interviews.answer', $interview), ['turn' => 1, 'answer' => 'Too early'])->assertStatus(409)->assertJsonPath('reason', 'not_started');
        $this->postJson(route('interviews.begin', $interview), ['consent' => 0])->assertStatus(422);
        $this->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk();
        $this->postJson(route('interviews.answer', $interview), ['turn' => 7, 'code' => 'SELECT 1;'])->assertStatus(409)->assertJsonPath('reason', 'wrong_turn');
        $this->postJson(route('interviews.answer', $interview), ['turn' => 1])->assertStatus(422);

        // Another fellow cannot touch it.
        $other = \App\Models\User::factory()->create(['role' => 'fellow']);
        $other->assignRole('fellow');
        $this->actingAs($other)->postJson(route('interviews.answer', $interview), ['turn' => 1, 'code' => 'SELECT 1;'])->assertForbidden();
        $this->actingAs($other)->get(route('interviews.room', $interview))->assertForbidden();

        // Finished: no more answers.
        $this->actingAs($this->fellow)->postJson(route('interviews.finish', $interview))->assertOk()->assertJsonPath('done', true);
        $this->postJson(route('interviews.answer', $interview), ['turn' => 1, 'code' => 'SELECT 1;'])->assertStatus(409)->assertJsonPath('reason', 'finished');

        // Expired (30 minutes without activity): cancelled as abandoned, never scored.
        [, , $second] = $this->startInterview();
        $this->postJson(route('interviews.begin', $second), ['consent' => 1])->assertOk();
        $second->forceFill(['last_activity_at' => now()->subMinutes(31)])->save();
        $this->postJson(route('interviews.answer', $second), ['turn' => 1, 'code' => 'SELECT 1;'])->assertStatus(410)->assertJsonPath('reason', 'expired');
        $second->refresh();
        $this->assertSame(InterviewStatus::CANCELLED, $second->status);
        $this->assertSame('abandoned', $second->cancellation_reason);
        $this->assertNull($second->score);
    }

    public function test_an_interview_with_no_answers_scores_zero_without_asking_the_ai(): void
    {
        $this->importBlueprint();
        [, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow)->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk();
        $this->postJson(route('interviews.finish', $interview))->assertOk();

        $interview->refresh();
        $this->assertEquals(0, (float) $interview->score);
        $this->assertSame('no_hire', $interview->hire_signal);
        $this->assertNotContains('score', array_column($this->aiCalls, 'kind'));
    }

    // ---------------------------------------------------------------- transcription

    public function test_transcription_returns_text_and_falls_back_when_groq_fails(): void
    {
        $this->importBlueprint();
        [, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow);
        $audio = fn () => UploadedFile::fake()->create('answer.webm', 40, 'audio/webm');

        // Microphone test before the start (turn 0).
        $this->post(route('interviews.transcribe', $interview), ['audio' => $audio(), 'turn' => 0], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonStructure(['text', 'duration_seconds', 'words_per_minute']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/audio/transcriptions')
            && collect($r->data())->contains(fn ($part) => ($part['name'] ?? null) === 'model' && $part['contents'] === 'whisper-large-v3-turbo')
            && collect($r->data())->contains(fn ($part) => ($part['name'] ?? null) === 'language' && $part['contents'] === 'en'));

        // Groq rate-limited: the browser uses its own speech text.
        $this->whisper = fn () => Http::response(['error' => ['message' => 'rate limited']], 429);
        $this->post(route('interviews.transcribe', $interview), ['audio' => $audio(), 'turn' => 0], ['Accept' => 'application/json'])
            ->assertStatus(503)->assertJsonPath('fallback', true);

        // No key at all: same fallback.
        config(['services.groq.key' => null]);
        $this->post(route('interviews.transcribe', $interview), ['audio' => $audio(), 'turn' => 0], ['Accept' => 'application/json'])
            ->assertStatus(503)->assertJsonPath('fallback', true);

        // Wrong files and oversized uploads are refused.
        $this->post(route('interviews.transcribe', $interview), ['audio' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'), 'turn' => 0], ['Accept' => 'application/json'])->assertStatus(422);
        $this->post(route('interviews.transcribe', $interview), ['audio' => UploadedFile::fake()->create('big.webm', 26 * 1024, 'audio/webm'), 'turn' => 0], ['Accept' => 'application/json'])->assertStatus(422);
        $this->post(route('interviews.transcribe', $interview), ['audio' => $audio(), 'turn' => 5], ['Accept' => 'application/json'])->assertStatus(409);
    }

    // ---------------------------------------------------------------- integrity

    public function test_integrity_level_is_computed_from_events_and_server_timings(): void
    {
        $analyzer = app(IntegrityAnalyzer::class);
        $voice = ['explanation_mode' => 'voice'];

        $this->assertSame('clean', $analyzer->analyse(['events' => []], [$voice, $voice])['level']);

        $review = $analyzer->analyse(['events' => $analyzer->sanitise([['type' => 'paste_blocked'], ['type' => 'fullscreen_exit', 'duration_ms' => 3000]], 1)], [$voice]);
        $this->assertSame('review', $review['level']);
        $this->assertSame(1, $review['integrity']['paste_attempts']);
        $this->assertSame(1, $review['integrity']['fullscreen_exits']);

        $concern = $analyzer->analyse(['events' => $analyzer->sanitise(array_merge(
            array_fill(0, 3, ['type' => 'paste_blocked']),
            [['type' => 'fullscreen_exit', 'duration_ms' => 5000], ['type' => 'visibility_hidden', 'duration_ms' => 90000]],
        ), 1)], [$voice, ['explanation_mode' => 'typed'], ['explanation_mode' => 'voice', 'late' => true, 'burst' => true]]);
        $this->assertSame('concern', $concern['level']);
        $this->assertContains('3 paste attempts were blocked.', $concern['integrity']['reasons']);
        $this->assertSame(95, $concern['integrity']['durations']['away_seconds']);
        $this->assertSame(1, $concern['integrity']['large_bursts']);

        // The beacon endpoint records events against the session.
        $this->importBlueprint();
        [, , $interview] = $this->startInterview();
        $this->actingAs($this->fellow)->post(route('interviews.events', $interview), ['events' => json_encode([['type' => 'visibility_hidden', 'duration_ms' => 2000], ['type' => 'evil', 'x' => 1]])])
            ->assertOk();
        $this->assertSame(['visibility_hidden'], array_column($interview->fresh()->integrity['events'], 'type'));
    }

    // ---------------------------------------------------------------- scoring failure

    public function test_scoring_failure_never_invents_a_score_and_a_reviewer_can_score_it(): void
    {
        $this->importBlueprint();
        $this->ai['score'] = 'this is not JSON at all';
        [, $progress, $interview] = $this->startInterview();
        $this->actingAs($this->fellow)->postJson(route('interviews.begin', $interview), ['consent' => 1])->assertOk();
        $this->postJson(route('interviews.answer', $interview), ['turn' => 1, 'code' => 'SELECT branch, COUNT(*) FROM sales GROUP BY branch;'])->assertOk();
        $this->postJson(route('interviews.finish', $interview))->assertOk();

        $interview->refresh();
        $this->assertSame('failed', $interview->scoring_status);
        $this->assertNull($interview->score);
        $this->assertNull($interview->hire_signal);
        $this->assertSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);
        $this->assertCount(0, app(\App\Services\CurriculumService::class)->countedInterviews($this->fellow->id, $interview->curriculum_activity_id));
        $this->getJson(route('interviews.score-status', $interview))->assertJsonPath('ready', true);
        $this->get(route('interviews.show', $interview))->assertOk()->assertSee('Not scored');

        // The reviewer scores it by hand: then it counts like any other.
        $this->actingAs($this->admin)->get(route('admin.interviews.show', $interview))->assertOk()->assertSee('Score this interview');
        $this->post(route('admin.interviews.score', $interview), ['score' => 72, 'feedback' => 'Solid query, explain more slowly.'])->assertRedirect();
        $interview->refresh();
        $this->assertEquals(72, (float) $interview->score);
        $this->assertSame('manual', $interview->scoring_status);
        $this->assertSame(CurriculumStatus::SUBMITTED, $progress->fresh()->status);
    }

    // ---------------------------------------------------------------- expiry

    public function test_the_expiry_command_closes_abandoned_interviews_with_a_dry_run(): void
    {
        $this->importBlueprint();
        [, , $idle] = $this->startInterview();
        $idle->forceFill(['last_activity_at' => now()->subMinutes(40)])->save();
        $legacy = InterviewSession::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'behavioral', 'mode' => 'ai',
            'status' => 'in_progress', 'started_at' => now()->subDays(3)]);

        $this->artisan('interviews:expire', ['--dry-run' => true, '--stuck' => true])->expectsOutputToContain('Would close 2')->assertSuccessful();
        $this->assertSame(InterviewStatus::IN_PROGRESS, $idle->fresh()->status);

        $this->artisan('interviews:expire')->expectsOutputToContain('Closed 1')->assertSuccessful();
        $this->assertSame('abandoned', $idle->fresh()->cancellation_reason);
        $this->assertSame(InterviewStatus::IN_PROGRESS, $legacy->fresh()->status);

        $this->artisan('interviews:expire', ['--stuck' => true])->expectsOutputToContain('Closed 1')->assertSuccessful();
        $this->assertSame(InterviewStatus::CANCELLED, $legacy->fresh()->status);
    }
}
