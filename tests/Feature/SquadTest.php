<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Squad;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\SquadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SquadTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected TrackMilestone $chapter;
    protected TrackCurriculumActivity $planning;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->track = Track::create(['name' => 'SE', 'slug' => 'se-squads', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        $this->chapter = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Squad Sprint', 'description' => 'D.', 'sequence_order' => 1, 'is_active' => true, 'squad_size' => 4]);
        $this->planning = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $this->chapter->id, 'title' => 'Sprint Planning', 'description' => 'Plan.',
            'type' => 'project', 'career_capital_category' => 'collaboration', 'points' => 10, 'is_active' => true, 'is_required' => true, 'is_collaborative' => true, 'sequence_order' => 1]);
    }

    private function fellow(string $name, ?string $pathway, bool $reached = true): User
    {
        $u = User::factory()->create(['role' => 'fellow', 'name' => $name]);
        $u->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $u->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now(), 'pathway' => $pathway]);
        TrackOrientation::create(['fellow_id' => $u->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        FellowCurriculumProgress::create(['fellow_id' => $u->id, 'curriculum_activity_id' => $this->planning->id, 'status' => $reached ? CurriculumStatus::AVAILABLE : CurriculumStatus::LOCKED]);

        return $u;
    }

    public function test_squads_mix_pathways_and_members_are_told(): void
    {
        $people = collect([['Ade', 'backend'], ['Bih', 'backend'], ['Che', 'frontend'], ['Dora', 'frontend'], ['Eyong', 'mobile'], ['Fai', 'mobile'], ['Gwan', 'fullstack'], ['Hans', 'backend']])
            ->map(fn ($p) => $this->fellow($p[0], $p[1]));
        $this->fellow('Late', 'backend', reached: false);
        $s = app(SquadService::class);
        $this->assertCount(8, $s->waiting($this->chapter));

        $squads = $s->form($this->chapter);
        $this->assertCount(2, $squads);
        foreach ($squads as $squad) {
            $this->assertCount(4, $squad->members);
            $this->assertGreaterThanOrEqual(3, $squad->members->pluck('pathway')->unique()->count(), 'Each squad mixes at least 3 pathways.');
        }
        $this->assertCount(0, $s->waiting($this->chapter));
        $this->assertSame(8, Notification::where('title', 'like', 'You are in Squad%')->count());
        $this->assertNull($s->squadFor(User::where('name', 'Late')->first(), $this->chapter), 'Fellows who have not reached the chapter wait.');
    }

    public function test_fellow_sees_squad_and_sets_the_repository(): void
    {
        $a = $this->fellow('Ade', 'backend');
        $this->actingAs($a)->get(route('curriculum.activity.show', $this->planning))->assertOk()->assertSee('Your squad is being formed');
        $b = $this->fellow('Bih', 'frontend');
        $squad = app(SquadService::class)->form($this->chapter)->first();
        $this->actingAs($a)->get(route('curriculum.activity.show', $this->planning))->assertOk()->assertSee($squad->name)->assertSee('Bih')->assertDontSee('Your review partner');

        $this->actingAs($b)->post(route('curriculum.squad.repo', $squad), ['repo_url' => 'https://github.com/squad/nkwen-orders-platform'])->assertRedirect();
        $this->assertSame('https://github.com/squad/nkwen-orders-platform', $squad->fresh()->repo_url);
        $outsider = $this->fellow('Zed', 'mobile');
        $this->actingAs($outsider)->post(route('curriculum.squad.repo', $squad), ['repo_url' => 'https://github.com/x/y'])->assertForbidden();
        $this->actingAs($a)->post(route('curriculum.squad.repo', $squad), ['repo_url' => 'http://insecure.example'])->assertSessionHasErrors('repo_url');
    }

    public function test_admin_forms_adjusts_and_dissolves_squads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        foreach (['Ade' => 'backend', 'Bih' => 'frontend', 'Che' => 'mobile'] as $n => $p) {
            $this->fellow($n, $p);
        }
        $this->actingAs($admin)->get(route('admin.squads.index'))->assertOk()->assertSee('Waiting for a squad: 3');
        $this->actingAs($admin)->post(route('admin.squads.form', $this->chapter), ['size' => 3])->assertRedirect()->assertSessionHas('success');
        $squad = Squad::first();
        $this->assertCount(3, $squad->members);

        $late = $this->fellow('Dora', 'fullstack');
        $this->actingAs($admin)->post(route('admin.squads.assign', $squad), ['fellow_id' => $late->id])->assertSessionHas('success');
        $this->assertCount(4, $squad->fresh()->members);
        $this->actingAs($admin)->delete(route('admin.squads.remove', $squad->members()->first()))->assertSessionHas('success');
        $this->assertCount(3, $squad->fresh()->members);
        $this->actingAs($admin)->delete(route('admin.squads.destroy', $squad))->assertSessionHas('success');
        $this->assertSame(0, Squad::count());
        $this->assertCount(4, app(SquadService::class)->waiting($this->chapter));
    }

    public function test_squad_chapters_do_not_pair_review_partners(): void
    {
        $a = $this->fellow('Ade', 'backend');
        $this->fellow('Bih', 'frontend');
        $this->actingAs($a)->get(route('curriculum.activity.show', $this->planning))->assertOk();
        $this->assertSame(0, \App\Models\AccountabilityPair::count());
    }
}
