<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\InterviewSession;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\Interview\BlueprintService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

/**
 * Shared setup for the mock interview tests: a Data Science fellow, a chapter with a
 * project lesson and a mock interview, a small blueprint, and a faked AI / Whisper.
 */
trait InterviewFixtures
{
    protected User $fellow;
    protected User $admin;
    protected User $mentor;
    protected Track $track;
    protected TrackMilestone $milestone;
    protected TrackCurriculumActivity $lesson;

    /** Queued fake AI answers: plan, turns (in order), score. null = provider down. */
    protected array $ai = ['plan' => null, 'turns' => [], 'score' => null];
    /** @var callable|null */
    protected $whisper = null;
    protected array $aiCalls = [];

    protected function setUpInterviews(): void
    {
        Http::preventStrayRequests();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => null, 'services.groq.key' => 'gsk_test_key_0000000000', 'services.openai.key' => null, 'services.gemini.key' => null]);

        foreach (['fellow', 'admin', 'mentor'] as $role) {
            Role::findOrCreate($role);
        }
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Mbah']);
        $this->mentor->assignRole('mentor');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Amina Fon', 'profile_completed_at' => now(), 'onboarding_completed_at' => now()]);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'Data.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true,
            'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subDays(60)]);
        $this->milestone = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'SQL Basics', 'description' => 'SELECT, WHERE, GROUP BY.', 'sequence_order' => 1]);
        $this->lesson = $this->activity(['title' => 'GROUP BY: totals per branch', 'skills' => ['sql'], 'sequence_order' => 1]);

        $this->fakeAi();
    }

    protected function activity(array $attributes = []): TrackCurriculumActivity
    {
        return TrackCurriculumActivity::create($attributes + [
            'track_id' => $this->track->id, 'milestone_id' => $this->milestone->id, 'title' => 'Lesson',
            'description' => 'Learn it.', 'type' => 'research', 'career_capital_category' => 'technical',
            'points' => 20, 'is_active' => true, 'sequence_order' => 1, 'deadline_days' => 3, 'grace_period_days' => 1, 'late_penalty_percent' => 15,
        ]);
    }

    protected function mockActivity(array $config = []): TrackCurriculumActivity
    {
        return $this->activity([
            'title' => 'Mock Interview: SQL Basics Check', 'type' => 'mock_interview', 'career_capital_category' => 'interview',
            'points' => 10, 'sequence_order' => 9, 'skills' => ['sql', 'communication'],
            'interview_config' => $config + ['mode' => 'text', 'type' => 'technical_concepts', 'count' => 1, 'min_score' => 60, 'topics' => ['GROUP BY', 'WHERE vs HAVING']],
        ]);
    }

    /** Start the chapter interview the way the activity page does. */
    protected function startInterview(array $config = []): array
    {
        $activity = $this->mockActivity($config);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $activity->id, 'status' => CurriculumStatus::AVAILABLE]);
        $result = app(CurriculumService::class)->startActivity($this->fellow, $activity->id);

        return [$activity, FellowCurriculumProgress::where('curriculum_activity_id', $activity->id)->first(), $result['interview']];
    }

    protected function blueprint(array $overrides = []): array
    {
        $round = fn (string $label, string $mode, ?string $lang, int $time) => [
            'label' => $label, 'purpose' => "The {$label} round.", 'answer_mode' => $mode, 'language' => $lang, 'time_limit_seconds' => $time,
            'how_to_probe' => ['Ask why.', 'Ask for an edge case.'], 'strong_answer_signals' => ['Correct', 'Clear'], 'red_flags' => ['Guessing', 'Vague'],
        ];
        $q = fn (string $id, string $round, string $text, string $difficulty = 'beginner', array $titles = ['SQL Basics']) => [
            'id' => $id, 'round' => $round, 'milestone_titles' => $titles, 'pathways' => null, 'difficulty' => $difficulty, 'skills' => ['sql'],
            'question' => $text, 'setup' => $round === 'sql' ? 'Table sales(branch, amount).' : null, 'starter' => $round === 'sql' ? 'SELECT ' : null,
            'follow_ups' => ['What about NULL?', 'How would you check it?'], 'strong_answer' => "A strong answer for {$id}: uses GROUP BY branch and explains COUNT versus SUM clearly.",
            'common_mistakes' => ['Forgetting GROUP BY'],
        ];

        return array_replace([
            'track' => 'data-science', 'version' => 3,
            'role' => ['title' => 'Junior Data Analyst', 'summary' => 'Analyst at a retailer in Bamenda.'],
            'interviewer' => ['name' => 'Ngozi Tabe', 'title' => 'Analytics lead', 'style' => 'Calm and direct.'],
            'rubric' => [
                ['key' => 'sql_skill', 'label' => 'SQL', 'weight' => 0.4, 'what_it_means' => 'Writes correct SQL.', 'levels' => ['1' => 'No', '2' => 'Partly', '3' => 'Solid', '4' => 'Strong']],
                ['key' => 'reasoning', 'label' => 'Reasoning', 'weight' => 0.3, 'what_it_means' => 'Thinks in steps.', 'levels' => ['1' => 'No', '2' => 'Partly', '3' => 'Solid', '4' => 'Strong']],
                ['key' => 'communication', 'label' => 'Communication', 'weight' => 0.2, 'what_it_means' => 'Explains clearly.', 'levels' => ['1' => 'No', '2' => 'Partly', '3' => 'Solid', '4' => 'Strong']],
                ['key' => 'ownership', 'label' => 'Ownership', 'weight' => 0.1, 'what_it_means' => 'Owns their work.', 'levels' => ['1' => 'No', '2' => 'Partly', '3' => 'Solid', '4' => 'Strong']],
            ],
            'hire_signal' => ['strong_hire' => 85, 'hire' => 75, 'lean_hire' => 60, 'lean_no' => 45],
            'rounds' => [
                'sql' => $round('SQL', 'sql', 'sql', 300),
                'communication' => $round('Communication', 'voice', null, 180),
            ],
            'chapters' => [[
                'milestone_title' => 'SQL Basics', 'pathways' => null, 'stage' => 'screening', 'difficulty' => 'beginner',
                'rounds' => ['sql', 'communication'], 'main_questions' => 2, 'minutes' => 20, 'topics' => ['GROUP BY', 'WHERE', 'COUNT'],
                'skills' => ['sql', 'communication'], 'pass_mark' => 65,
            ]],
            'bank' => [
                $q('ds-sql-1', 'sql', 'Count the sales rows per branch.'),
                $q('ds-sql-2', 'sql', 'Total the amount per branch, biggest first.'),
                $q('ds-com-1', 'communication', 'Explain GROUP BY to a shop manager.'),
                $q('ds-com-2', 'communication', 'Tell me about a query that gave a wrong result.'),
            ],
        ], $overrides);
    }

    protected function importBlueprint(?array $bp = null): void
    {
        $data = $bp ?? $this->blueprint();
        $errors = app(BlueprintService::class)->validate($data);
        $this->assertSame([], $errors);
        app(BlueprintService::class)->store($this->track, $data);
    }

    protected function fakeAi(): void
    {
        Http::fake(function (HttpRequest $request) {
            if (str_contains($request->url(), '/audio/transcriptions')) {
                return $this->whisper ? ($this->whisper)($request) : Http::response(['text' => 'Umm, so I would group the rows by branch and count them.', 'duration' => 6.0], 200);
            }
            $system = (string) ($request->data()['messages'][0]['content'] ?? '');
            $kind = match (true) {
                str_contains($system, 'You plan a mock job interview') => 'plan',
                str_contains($system, 'You are the interviewer') => 'turn',
                str_contains($system, 'You score a mock job interview') => 'score',
                default => 'other',
            };
            $this->aiCalls[] = ['kind' => $kind, 'user' => (string) ($request->data()['messages'][1]['content'] ?? '')];
            $reply = match ($kind) {
                'plan' => $this->ai['plan'],
                'turn' => array_shift($this->ai['turns']),
                'score' => $this->ai['score'],
                default => null,
            };
            if ($reply === null) {
                return Http::response(['error' => ['message' => 'Service unavailable']], 503);
            }

            return Http::response(['choices' => [['message' => ['content' => is_string($reply) ? $reply : json_encode($reply)], 'finish_reason' => 'stop']],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 10]], 200);
        });
    }

    protected function scoreReply(array $levels = ['sql_skill' => 3, 'reasoning' => 4, 'communication' => 2, 'ownership' => 3]): array
    {
        return [
            'dimensions' => collect($levels)->map(fn ($level, $key) => ['key' => $key, 'level' => $level,
                'evidence' => ['GROUP BY branch', 'a quote the fellow never said at all'], 'comment' => "Comment on {$key}."])->values()->all(),
            'summary' => 'You wrote a working query and explained it.',
            'strengths' => ['Correct GROUP BY', 'Clear steps'],
            'improvements' => ['Check NULL values', 'Speak a little slower'],
            'questions' => [['n' => 1, 'strong_answer_includes' => 'AI view of a strong answer 1.', 'assessment' => 'Good query.'], ['n' => 2, 'strong_answer_includes' => 'Names the audience and uses an example.', 'assessment' => 'Clear.']],
            'next_steps' => [['skill' => 'sql', 'text' => 'Practise GROUP BY with HAVING.']],
        ];
    }

    /** A completed, scored session for an activity (as if the AI had scored it). */
    protected function scoredSession(TrackCurriculumActivity $activity, FellowCurriculumProgress $progress, float $score, array $attributes = []): InterviewSession
    {
        return InterviewSession::create($attributes + [
            'fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'technical_coding', 'mode' => 'ai',
            'status' => 'completed', 'completed_at' => now(), 'score' => $score, 'overall_score' => (int) round($score), 'scoring_status' => 'done',
            'engine_version' => '2', 'plan' => ['items' => []], 'title' => $activity->title,
            'curriculum_activity_id' => $activity->id, 'curriculum_progress_id' => $progress->id,
        ]);
    }
}
