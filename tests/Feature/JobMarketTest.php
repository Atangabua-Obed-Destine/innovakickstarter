<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\JobAdvertCheck;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\JobMarketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobMarketTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected Track $track;
    protected string $file;
    /** @var array<string, TrackCurriculumActivity> */
    protected array $acts = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        config(['services.ai.provider' => 'groq', 'services.ai.backup' => null, 'services.groq.key' => 'gsk_test', 'services.openai.key' => null]);
        Role::findOrCreate('fellow');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace', 'headline' => 'Aspiring analyst', 'bio' => 'I love data.']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Market Test', 'slug' => 'market-test', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['company' => 'Nkwen Traders Ltd.', 'team' => 'Analytics Team', 'manager' => ['name' => 'Mrs. Ngum Esther', 'title' => 'Head of Analytics', 'initials' => 'NE']]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        TrackOrientation::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        $m = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'SQL', 'description' => 'S.', 'sequence_order' => 1]);
        $make = fn ($title, $type, $skills, $order) => TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => $title, 'description' => 'Do it.',
            'type' => $type, 'skills' => $skills, 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => $order]);
        $this->acts = [
            'sql1' => $make('SQL Basics', 'research', ['sql'], 1),
            'sql2' => $make('SQL Joins Project', 'project', ['sql'], 2),
            'talk' => $make('Explain your findings', 'project', ['communication'], 3),
            'p3' => $make('Third project', 'project', ['communication'], 4),
            'interview' => $make('Mock Interview: Check-in', 'mock_interview', [], 5),
        ];
        $this->acts['interview']->update(['interview_config' => ['type' => 'behavioral']]);

        $this->file = database_path('data/job-market/market-test.json');
        @mkdir(dirname($this->file), 0777, true);
        file_put_contents($this->file, json_encode([
            'track' => 'market-test', 'researched_on' => '2026-11-25', 'summary' => ['postings' => 10, 'by_market' => ['US' => 5, 'Africa' => 5]],
            'roles' => [
                ['key' => 'junior-data-analyst', 'title' => 'Junior Data Analyst', 'level' => 1, 'description' => 'First job.', 'postings' => 8, 'skills' => ['sql' => 0.8, 'communication' => 0.5, 'machine_learning' => 0.2]],
                ['key' => 'junior-data-scientist', 'title' => 'Junior Data Scientist', 'level' => 2, 'description' => 'Later.', 'postings' => 2, 'skills' => ['sql' => 0.5, 'machine_learning' => 0.9]],
            ],
            'skills' => [
                ['key' => 'sql', 'label' => 'SQL', 'frequency' => 0.8, 'why' => 'Databases.', 'curriculum' => ['skills' => ['sql']], 'quotes' => [['text' => 'Strong SQL skills', 'company' => 'Acme Bank', 'market' => 'US', 'url' => 'https://example.com/job1']]],
                ['key' => 'communication', 'label' => 'Communicating results', 'frequency' => 0.5, 'curriculum' => ['skills' => ['communication']], 'quotes' => []],
                ['key' => 'machine_learning', 'label' => 'Machine learning basics', 'frequency' => 0.2, 'beyond' => [['title' => 'Kaggle Learn', 'url' => 'https://www.kaggle.com/learn/intro-to-machine-learning']]],
                ['key' => 'degree', 'label' => 'A degree', 'frequency' => 0.6, 'kind' => 'info', 'why' => 'Often listed.'],
            ],
            'real_world' => ['sql' => [['company' => 'Spotify', 'example' => 'Analysts query listening data.', 'source_title' => 'Spotify blog', 'source_url' => 'https://example.com/spotify']]],
            'interview_loop' => ['typical' => [
                ['key' => 'screen', 'name' => 'Recruiter screen', 'what_happens' => 'A short call.', 'how_to_prepare' => 'Pitch.'],
                ['key' => 'take_home', 'name' => 'Take-home task', 'what_happens' => 'A small project.', 'how_to_prepare' => 'Projects.'],
                ['key' => 'behavioural', 'name' => 'Behavioural interview', 'what_happens' => 'STAR questions.', 'how_to_prepare' => 'STAR.'],
                ['key' => 'final', 'name' => 'Final', 'what_happens' => 'Hiring manager.', 'how_to_prepare' => 'Be ready.'],
            ], 'examples' => []],
            'postings' => [['id' => 'us-01', 'title' => 'Junior Data Analyst', 'company' => 'Acme Bank', 'location' => 'New York, US', 'market' => 'US', 'url' => 'https://example.com/job1', 'skills' => ['sql']]],
        ]));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    protected function complete(string ...$keys): void
    {
        foreach ($keys as $k) {
            FellowCurriculumProgress::updateOrCreate(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->acts[$k]->id],
                ['status' => CurriculumStatus::COMPLETED, 'completed_at' => now(), 'evidence' => ['evidence_url' => 'https://github.com/ada/work']]);
        }
    }

    public function test_readiness_counts_only_reviewed_work_and_finds_the_biggest_gap(): void
    {
        $market = app(JobMarketService::class);
        $this->assertSame(0, $market->readiness($this->fellow, $this->track)['percent']);

        $this->complete('sql1');
        $ready = $market->readiness($this->fellow, $this->track);
        // SQL 0.8 * 1/2 proven + communication 0.5 * 0 = 0.4 / 1.3
        $this->assertSame(31, $ready['percent']);
        $this->assertSame('communication', $ready['gap']['key']);
        $this->assertSame(['machine_learning'], array_column($ready['beyond'], 'key'), 'Untaught skills are listed apart, not scored.');
        $this->assertSame('Explain your findings', $ready['gap']['p']['next'][0]['title']);

        // Pending work doesn't count; only completed.
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->acts['sql2']->id, 'status' => CurriculumStatus::SUBMITTED]);
        $this->assertSame(31, $market->readiness($this->fellow, $this->track)['percent']);

        // A different target role changes the weights.
        $this->actingAs($this->fellow)->post(route('career.role'), ['role' => 'junior-data-scientist'])->assertRedirect();
        $this->assertSame('junior-data-scientist', $market->role($this->fellow, $this->track)['key']);
        $this->assertSame(50, $market->readiness($this->fellow, $this->track)['percent']);
    }

    public function test_pages_show_readiness_why_it_matters_and_work_tickets(): void
    {
        $this->complete('sql1');
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->acts['sql2']->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        $this->actingAs($this->fellow);

        $this->get(route('career.index'))->assertOk()->assertSee('You have proof for 31% of what Junior Data Analyst adverts ask for')
            ->assertSee('Biggest gap')->assertSee('Strong SQL skills')->assertSee('Machine learning basics')->assertSee('Recruiter screen')->assertSee('Acme Bank');
        $this->get(route('curriculum.index'))->assertOk()->assertSee('Job-ready for Junior Data Analyst: 31%');

        $lesson = $this->get(route('curriculum.activity.show', $this->acts['sql2']))->assertOk()
            ->assertSee('Why employers care')->assertSee('80%')->assertSee('Spotify')->assertSee('Mrs. Ngum Esther')->assertSee('This is how real teams hand out work');
        $this->get(route('career.offer'))->assertNotFound();
    }

    public function test_job_advert_check_maps_requirements_to_proven_skills(): void
    {
        $this->complete('sql1', 'sql2');
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode([
            'title' => 'Data Analyst', 'company' => 'Flutterwave', 'seniority' => 'junior', 'years' => 1,
            'requirements' => [
                ['text' => 'Strong SQL', 'skill' => 'sql', 'importance' => 'must'],
                ['text' => 'Present insights to stakeholders', 'skill' => 'communication', 'importance' => 'must'],
                ['text' => 'Machine learning a plus', 'skill' => 'machine_learning', 'importance' => 'nice'],
                ['text' => 'Bachelor degree', 'skill' => 'degree', 'importance' => 'must'],
                ['text' => 'Loves football', 'skill' => 'invented', 'importance' => 'nice'],
            ]])]]]])]);
        $this->actingAs($this->fellow);
        $this->post(route('career.advert'), ['advert' => 'short'])->assertSessionHasErrors('advert');
        $res = $this->post(route('career.advert'), ['advert' => str_repeat('We are hiring a Data Analyst with strong SQL and great communication. ', 6)]);
        $check = JobAdvertCheck::sole();
        $res->assertRedirect(route('career.index', ['check' => $check->id]));
        // SQL proven (must, 2) + communication 0 (must, 2) + ML beyond 0 (nice, 1) = 2/5
        $this->assertSame(40, $check->match);
        $states = collect($check->result['requirements'])->pluck('state', 'text');
        $this->assertSame(['Strong SQL' => 'have', 'Present insights to stakeholders' => 'missing', 'Machine learning a plus' => 'beyond', 'Bachelor degree' => 'unmeasured', 'Loves football' => 'unmeasured'], $states->all());
        $this->assertSame('Explain your findings', $check->result['plan'][0]['title']);
        $this->get(route('career.index', ['check' => $check->id]))->assertOk()->assertSee('Flutterwave')->assertSee('40%')->assertSee('Kaggle Learn');
    }

    public function test_stakeholder_answers_clarifying_questions(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->acts['sql2']->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'The branch managers will use it on Monday mornings. What would you put first?']]]])]);
        $this->actingAs($this->fellow)->postJson(route('curriculum.activity.stakeholder', $this->acts['sql2']), ['question' => 'Who will use this report?'])
            ->assertOk()->assertJson(['ok' => true])->assertJsonPath('reply', 'The branch managers will use it on Monday mornings. What would you put first?');
        Http::assertSent(fn ($r) => str_contains($r['messages'][0]['content'] ?? '', 'Never give the technical solution'));
    }

    public function test_mock_offer_unlocks_when_ready_and_through_the_loop(): void
    {
        $market = app(JobMarketService::class);
        $this->complete('sql1', 'sql2', 'talk');
        $this->assertFalse($market->checkOffer($this->fellow, $this->track), 'Ready on skills but not through enough interview stages.');

        $this->complete('p3', 'interview'); // 3 projects (take-home) + behavioural interview
        $loop = collect($market->interviewLoop($this->fellow, $this->track))->pluck('status', 'key');
        $this->assertSame(['screen' => 'passed', 'take_home' => 'passed', 'behavioural' => 'passed', 'final' => 'passed'], $loop->all());
        $this->assertTrue($market->checkOffer($this->fellow, $this->track));
        $this->assertFalse($market->checkOffer($this->fellow, $this->track), 'Only once.');
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '📨 You unlocked a mock job offer!')->count());
        $this->actingAs($this->fellow)->get(route('career.offer'))->assertOk()->assertSee('MOCK-')->assertSee('This is not an employment contract');
    }
}
