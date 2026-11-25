<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\PathwayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PathwayTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    /** @var array<string, TrackMilestone> */
    protected array $m = [];
    /** @var array<string, TrackCurriculumActivity> */
    protected array $a = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        $this->track = Track::create(['name' => 'Software Test', 'slug' => 'software-test', 'category' => 'technical', 'description' => 'D.', 'is_active' => true, 'experience' => [
            'pathways' => [
                ['key' => 'frontend', 'title' => 'Frontend', 'icon' => '🎨', 'stack' => 'React + TypeScript', 'alternatives' => ['Vue'], 'summary' => 'Interfaces.'],
                ['key' => 'backend', 'title' => 'Backend', 'icon' => '🛠️', 'stack' => 'PHP + Laravel', 'alternatives' => ['Node/Express', 'Django'], 'summary' => 'APIs.', 'demand' => 'In 19% of African adverts.'],
                ['key' => 'fullstack', 'title' => 'Full-stack', 'icon' => '🧩', 'stack' => 'React + Laravel', 'summary' => 'Both.'],
            ],
            'pathway_quiz' => [['question' => 'What do you enjoy?', 'options' => [['text' => 'Design', 'pathway' => 'frontend'], ['text' => 'Data', 'pathway' => 'backend']]]],
        ]]);
        $make = function (string $code, int $order, ?array $pathways) {
            $this->m[$code] = TrackMilestone::create(['track_id' => $this->track->id, 'title' => "Chapter {$code}", 'description' => 'D.', 'sequence_order' => $order, 'is_active' => true, 'pathways' => $pathways]);
            $this->a[$code] = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->m[$code]->id, 'title' => "Lesson {$code}", 'description' => 'Do it.',
                'type' => 'research', 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => $order]);
        };
        $make('core', 1, null);
        $make('back', 2, ['backend', 'fullstack']);
        $make('front', 3, ['frontend', 'fullstack']);
        $make('fs', 4, ['fullstack']);
        $make('team', 5, null);
        $this->a['teamBack'] = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->m['team']->id, 'title' => 'Backend build', 'description' => 'Role work.',
            'type' => 'project', 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => 6, 'pathways' => ['backend', 'fullstack']]);
    }

    private function fellow(?string $pathway = null, array $minors = []): User
    {
        $u = User::factory()->create(['role' => 'fellow']);
        $u->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $u->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now(),
            'pathway' => $pathway, 'minors' => array_map(fn ($c) => $this->m[$c]->id, $minors)]);
        TrackOrientation::create(['fellow_id' => $u->id, 'track_id' => $this->track->id, 'completed_at' => now()]);

        return $u;
    }

    private function complete(User $u, string $code): void
    {
        FellowCurriculumProgress::updateOrCreate(['fellow_id' => $u->id, 'curriculum_activity_id' => $this->a[$code]->id], ['status' => CurriculumStatus::COMPLETED, 'completed_at' => now()]);
        app()->forgetScopedInstances();
    }

    private function unlocked(User $u): array
    {
        app()->forgetScopedInstances();

        return collect($this->m)->filter(fn ($m) => $m->fresh()->isUnlockedFor($u))->keys()->all();
    }

    public function test_tracks_without_pathways_behave_as_before(): void
    {
        $plain = Track::create(['name' => 'Plain', 'slug' => 'plain', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        $m1 = TrackMilestone::create(['track_id' => $plain->id, 'title' => 'One', 'description' => 'D.', 'sequence_order' => 1, 'is_active' => true]);
        $m2 = TrackMilestone::create(['track_id' => $plain->id, 'title' => 'Two', 'description' => 'D.', 'sequence_order' => 2, 'is_active' => true, 'unlock_after_milestone_id' => $m1->id]);
        $a = TrackCurriculumActivity::create(['track_id' => $plain->id, 'milestone_id' => $m1->id, 'title' => 'L', 'description' => 'D.', 'type' => 'research', 'career_capital_category' => 'technical', 'points' => 5, 'is_active' => true, 'is_required' => true, 'sequence_order' => 1]);
        $u = User::factory()->create(['role' => 'fellow']);
        $this->assertFalse(app(PathwayService::class)->hasPathways($plain));
        $this->assertTrue($m1->isUnlockedFor($u));
        $this->assertFalse($m2->isUnlockedFor($u));
        FellowCurriculumProgress::create(['fellow_id' => $u->id, 'curriculum_activity_id' => $a->id, 'status' => CurriculumStatus::COMPLETED]);
        $this->assertTrue($m2->fresh()->isUnlockedFor($u));
    }

    public function test_before_choosing_only_shared_chapters_apply(): void
    {
        $u = $this->fellow();
        $this->assertSame(['core'], $this->unlocked($u));
        $dash = app(CurriculumService::class)->getFellowCurriculumDashboard($u, $this->track);
        $this->assertSame(['Chapter core', 'Chapter team'], $dash['milestones']->pluck('title')->all());
        $this->assertCount(3, $dash['pathwayInfo']['other_chapters']);
        $this->actingAs($u)->get(route('curriculum.index'))->assertOk()->assertSee('Choose your pathway');
    }

    public function test_backend_major_unlocks_after_core_and_team_after_pathway(): void
    {
        $u = $this->fellow('backend');
        $this->assertSame(['core'], $this->unlocked($u));
        $this->complete($u, 'core');
        $this->assertSame(['core', 'back'], $this->unlocked($u));
        $this->complete($u, 'back');
        $this->assertSame(['core', 'back', 'team'], $this->unlocked($u));
        // Team chapter: the backend role activity counts for this fellow.
        $this->assertEqualsCanonicalizing([$this->a['team']->id, $this->a['teamBack']->id], app(PathwayService::class)->requiredIdsIn($u, $this->m['team'])->all());
    }

    public function test_fullstack_goes_backend_then_frontend_then_its_own_chapter(): void
    {
        $u = $this->fellow('fullstack');
        $this->complete($u, 'core');
        $this->assertSame(['core', 'back'], $this->unlocked($u));
        $this->complete($u, 'back');
        $this->assertSame(['core', 'back', 'front'], $this->unlocked($u));
        $this->complete($u, 'front');
        $this->assertSame(['core', 'back', 'front', 'fs'], $this->unlocked($u));
        $this->assertCount(6, app(PathwayService::class)->requiredActivityIds($u, $this->track));
    }

    public function test_minors_open_after_the_core_and_never_block(): void
    {
        $u = $this->fellow('frontend', ['back']);
        $this->complete($u, 'core');
        $this->assertSame(['core', 'back', 'front'], $this->unlocked($u));
        $this->complete($u, 'front');
        // Team opens although the backend minor is unfinished; the backend-only squad role is not theirs.
        $this->assertContains('team', $this->unlocked($u));
        $this->assertSame([$this->a['team']->id], app(PathwayService::class)->requiredIdsIn($u, $this->m['team'])->all());
        $this->assertNotContains($this->a['back']->id, app(PathwayService::class)->requiredActivityIds($u, $this->track)->all());
    }

    public function test_role_specific_activities_are_not_created_for_other_pathways(): void
    {
        $u = $this->fellow('frontend');
        $this->complete($u, 'core');
        $this->complete($u, 'front');
        app(CurriculumService::class)->initializeFellowCurriculum($u, $this->track);
        $this->assertTrue(FellowCurriculumProgress::where('fellow_id', $u->id)->where('curriculum_activity_id', $this->a['team']->id)->exists());
        $this->assertFalse(FellowCurriculumProgress::where('fellow_id', $u->id)->where('curriculum_activity_id', $this->a['teamBack']->id)->exists());
    }

    public function test_catch_up_lessons_do_not_block_a_finished_chapter(): void
    {
        $u = $this->fellow('backend');
        $this->complete($u, 'core');
        $new = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->m['core']->id, 'title' => 'New lesson', 'description' => 'D.', 'type' => 'research',
            'career_capital_category' => 'technical', 'points' => 5, 'is_active' => true, 'is_required' => true, 'sequence_order' => 9]);
        $this->assertFalse($this->m['core']->fresh()->isCompletedBy($u));
        FellowCurriculumProgress::create(['fellow_id' => $u->id, 'curriculum_activity_id' => $new->id, 'status' => CurriculumStatus::AVAILABLE, 'catch_up' => true]);
        $this->assertTrue($this->m['core']->fresh()->isCompletedBy($u));
        $this->assertContains('back', $this->unlocked($u));
    }

    public function test_choosing_a_pathway_with_minor_and_stack(): void
    {
        $u = $this->fellow();
        $this->actingAs($u)->get(route('curriculum.pathway'))->assertOk()->assertSee('Choose your pathway')->assertSee('In 19% of African adverts.');
        $this->actingAs($u)->post(route('curriculum.pathway.store'), ['pathway' => 'backend', 'minors' => [$this->m['front']->id, $this->m['back']->id], 'stack' => 'Django'])
            ->assertRedirect(route('curriculum.index'));
        $ft = FellowTrack::where('fellow_id', $u->id)->first();
        $this->assertSame('backend', $ft->pathway);
        $this->assertSame([(string) $this->m['front']->id], $ft->minors, 'A chapter of the major cannot also be a minor.');
        $this->assertSame(['backend' => 'Django'], $ft->stacks);
        $this->actingAs($u)->post(route('curriculum.pathway.store'), ['pathway' => 'nope'])->assertSessionHasErrors('pathway');
        $this->actingAs($u)->get(route('curriculum.index'))->assertOk()->assertSee('Your pathway: Backend');
    }
}
