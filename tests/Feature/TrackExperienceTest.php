<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Enums\Tier;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\SkillMapService;
use App\Support\TrackExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrackExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected User $fellow;
    protected User $admin;
    protected TrackMilestone $m1;
    protected TrackMilestone $m2;
    protected TrackCurriculumActivity $lesson;
    protected TrackCurriculumActivity $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Fellow', 'profile_completed_at' => now()]);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => [
                'emblem' => '📊', 'role' => 'Data Analyst', 'team' => 'Analytics Team', 'company' => 'Nkwen Traders Ltd.',
                'greeting' => 'The team is counting on your numbers today.',
                'theme' => ['500' => '#14b8a6', '600' => '#0d9488', 'bad' => 'red', '700' => 'javascript:alert(1)'],
                'levels' => ['rookie' => 'Data Rookie', 'intern' => 'Junior Data Analyst'],
                'manager' => ['name' => 'Mrs. Ngum Esther', 'title' => 'Head of Analytics', 'initials' => 'NE'],
                'skills' => [
                    ['key' => 'python', 'label' => 'Python'], ['key' => 'statistics', 'label' => 'Statistics'], ['key' => 'communication', 'label' => 'Communication'],
                ],
            ]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 30, 'tier' => 'intern', 'started_at' => now()]);
        $this->m1 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Python Fundamentals', 'description' => 'P.', 'sequence_order' => 1,
            'story' => ['chapter' => 'Chapter 1: Learning the language of data', 'brief' => 'Build the **weekly sales calculator**.', 'debrief' => 'The supervisors love the calculator.']]);
        $this->m2 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Statistics', 'description' => 'S.', 'sequence_order' => 2,
            'story' => ['chapter' => 'Chapter 2: Numbers that tell the truth', 'brief' => 'Was this a normal day?', 'debrief' => 'Great report.']]);
        $base = ['track_id' => $this->track->id, 'milestone_id' => $this->m1->id, 'type' => 'research', 'career_capital_category' => 'technical', 'is_active' => true, 'is_required' => true];
        $this->lesson = TrackCurriculumActivity::create($base + ['title' => 'Loops', 'description' => 'L.', 'points' => 10, 'sequence_order' => 1, 'skills' => ['python']]);
        $this->project = TrackCurriculumActivity::create($base + ['title' => 'Sales Calculator', 'description' => 'C.', 'points' => 30, 'sequence_order' => 2, 'skills' => ['python', 'communication']]);
        TrackCurriculumActivity::create(['milestone_id' => $this->m2->id, 'title' => 'Spread', 'description' => 'S.', 'points' => 20, 'sequence_order' => 3, 'skills' => ['statistics']] + $base);
    }

    public function test_identity_titles_and_theme(): void
    {
        $x = TrackExperience::for($this->track);

        $this->assertTrue($x->hasStory());
        $this->assertSame('Junior Data Analyst', $x->levelTitle(Tier::INTERN));
        $this->assertSame('Professional', $x->levelTitle('professional'), 'Missing titles fall back to the tier name.');
        $css = (string) $x->themeStyle();
        $this->assertStringContainsString('--color-primary-500:#14b8a6', $css);
        $this->assertStringContainsString('.btn-primary{background-color:var(--color-primary-600)}', $css);
        $this->assertStringNotContainsString('javascript', $css, 'Only #rrggbb colours are accepted.');

        $plain = Track::create(['name' => 'Finance', 'slug' => 'finance', 'category' => 'non-technical', 'description' => 'F.', 'is_active' => true]);
        $this->assertFalse(TrackExperience::for($plain)->hasStory());
        $this->assertSame('', (string) TrackExperience::for($plain)->themeStyle());
    }

    public function test_skill_map_counts_approved_work(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::COMPLETED, 'points_awarded' => 10, 'completed_at' => now()]);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->project->id, 'status' => CurriculumStatus::SUBMITTED]);

        $skills = collect(app(SkillMapService::class)->forFellow($this->fellow, $this->track))->keyBy('key');

        $this->assertSame(25, $skills['python']['percent'], '10 of 40 Python points approved.');
        $this->assertSame('1', (string) $skills['python']['completed']);
        $this->assertSame(0, $skills['statistics']['percent']);
    }

    public function test_dashboard_and_curriculum_show_the_track_world(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->lesson->id, 'status' => CurriculumStatus::AVAILABLE]);

        $this->actingAs($this->fellow)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('--color-primary-500:#14b8a6', false)
            ->assertSee('Junior Data Analyst')
            ->assertSee('Mrs. Ngum Esther')
            ->assertSee('My Data Analyst skills');

        $this->get(route('curriculum.index'))
            ->assertOk()
            ->assertSee('Your journey at Nkwen Traders Ltd.')
            ->assertSee('Chapter 1: Learning the language of data')
            ->assertSee('<strong>weekly sales calculator</strong>', false);
    }

    public function test_completing_a_chapter_sends_the_managers_debrief(): void
    {
        foreach ([$this->lesson, $this->project] as $activity) {
            FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $activity->id, 'status' => CurriculumStatus::SUBMITTED, 'submitted_at' => now(), 'attempt_number' => 1]);
        }
        $service = app(CurriculumService::class);
        foreach (FellowCurriculumProgress::all() as $progress) {
            $service->reviewSubmission($this->admin, $progress->id, 'approve', ['points' => 10]);
        }

        $message = Notification::where('user_id', $this->fellow->id)->where('title', 'Message from Mrs. Ngum Esther')->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('The supervisors love the calculator. Next: Chapter 2: Numbers that tell the truth.', $message->message);
    }
}
