<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\SkillCertificate;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackFeedEvent;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortfolioAndCertificatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected Track $track;
    protected array $activities = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace', 'headline' => 'Aspiring Data Analyst', 'is_public' => true,
            'linkedin_url' => 'https://www.linkedin.com/in/ada', 'github_url' => 'javascript:alert(1)']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['emblem' => '📊', 'role' => 'Data Analyst', 'levels' => ['rookie' => 'Data Rookie'],
                'skills' => [['key' => 'python', 'label' => 'Python'], ['key' => 'sql', 'label' => 'SQL'], ['key' => 'statistics', 'label' => 'Statistics']]]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 3, 'tier' => 'rookie', 'started_at' => now()]);
        $m = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Python', 'description' => 'P.', 'sequence_order' => 1, 'story' => ['chapter' => 'Chapter 2: Learning the language of data']]);
        foreach (['Lists & Loops' => 'research', 'Project: Sales Calculator' => 'project'] as $title => $type) {
            $this->activities[] = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => $title, 'type' => $type,
                'description' => 'Build the **weekly sales calculator** & "report".', 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true,
                'is_required' => true, 'sequence_order' => count($this->activities) + 1, 'skills' => ['python']]);
        }
    }

    protected function approveAll(): void
    {
        foreach ($this->activities as $a) {
            $p = FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $a->id, 'status' => CurriculumStatus::SUBMITTED,
                'submitted_at' => now(), 'attempt_number' => 1, 'evidence' => ['evidence_url' => 'https://github.com/ada/sales-calculator']]);
            app(CurriculumService::class)->reviewSubmission($this->admin, $p->id, 'approve', ['points' => 10]);
        }
    }

    public function test_completing_a_skill_issues_one_verifiable_certificate(): void
    {
        $this->approveAll();

        $certificate = SkillCertificate::sole();
        $this->assertSame('python', $certificate->skill_key);
        $this->assertSame(2, $certificate->activities_completed);
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', '🎓 Certificate earned: Python')->count());
        $this->assertSame(1, TrackFeedEvent::where('type', 'certificate')->count());

        // Public verification page, with the LinkedIn button only for the owner.
        $this->get(route('certificates.show', $certificate))->assertOk()->assertSee('Certificate of Skill')->assertSee('Ada Lovelace')
            ->assertSee($certificate->code())->assertSee('Valid certificate')->assertDontSee('Add to LinkedIn');
        $this->actingAs($this->fellow)->get(route('certificates.show', $certificate))->assertSee('Add to LinkedIn')
            ->assertSee('linkedin.com/profile/add?startTask=CERTIFICATION_NAME', false);

        $this->assertSame(0, \Illuminate\Support\Facades\Artisan::call('certificates:award'));
        $this->assertSame(1, SkillCertificate::count(), 'Never issued twice.');
    }

    public function test_the_public_portfolio_shows_reviewed_work_only_and_respects_privacy(): void
    {
        $this->approveAll();

        $html = $this->get(route('public.profile.show', $this->fellow))->assertOk()
            ->assertSee('Ada Lovelace')->assertSee('Data Rookie · Data Science')
            ->assertSee('Project: Sales Calculator')->assertSee('Chapter 2: Learning the language of data')
            ->assertSee("Ada's Data Analyst skills")
            ->assertSee('https://github.com/ada/sales-calculator', false)
            ->assertSee('https://www.linkedin.com/in/ada', false)
            ->getContent();
        $this->assertStringNotContainsString('javascript:alert', $html, 'Unsafe profile links are dropped.');
        $this->assertStringNotContainsString('&amp;quot;', $html);
        $this->assertStringNotContainsString('Lists &amp; Loops', $html, 'Lessons are not projects.');

        $this->fellow->update(['is_public' => false]);
        $this->get(route('public.profile.show', $this->fellow))->assertNotFound();
    }
}
