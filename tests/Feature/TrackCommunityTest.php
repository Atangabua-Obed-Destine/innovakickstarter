<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackFeedEvent;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\TrackFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrackCommunityTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected User $ada;
    protected User $ben;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['levels' => ['intern' => 'Junior Data Analyst']]]);
        foreach (['ada' => 'Ada Lovelace', 'ben' => 'Ben Fon'] as $key => $name) {
            $this->$key = User::factory()->create(['role' => 'fellow', 'name' => $name]);
            $this->$key->assignRole('fellow');
            FellowTrack::create(['fellow_id' => $this->$key->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        }
    }

    public function test_an_approved_brand_post_appears_in_the_feed_with_its_link_and_gets_kudos(): void
    {
        $m = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Python', 'description' => 'P.', 'sequence_order' => 1]);
        $post = TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => 'Brand: Python Concepts Explained', 'description' => 'B.',
            'type' => 'blog_post', 'career_capital_category' => 'portfolio', 'points' => 10, 'is_active' => true, 'sequence_order' => 1]);
        $progress = FellowCurriculumProgress::create(['fellow_id' => $this->ada->id, 'curriculum_activity_id' => $post->id, 'status' => CurriculumStatus::SUBMITTED,
            'submitted_at' => now(), 'attempt_number' => 1, 'evidence' => ['evidence_url' => 'https://www.linkedin.com/posts/ada-python-123']]);

        app(CurriculumService::class)->reviewSubmission($this->admin, $progress->id, 'approve', ['points' => 10]);

        $event = TrackFeedEvent::where('type', 'brand_post')->firstOrFail();
        $this->assertSame('Ada L. published a Brand post: Brand: Python Concepts Explained', $event->title);
        $this->assertSame('https://www.linkedin.com/posts/ada-python-123', $event->url);

        $this->actingAs($this->ben)->get(route('community.index'))->assertOk()->assertSee('Ada L. published a Brand post')->assertSee('linkedin.com/posts/ada-python-123', false);
        $this->postJson(route('community.kudos', $event))->assertOk()->assertJson(['given' => true, 'count' => 1]);
        $this->postJson(route('community.kudos', $event))->assertOk()->assertJson(['given' => false, 'count' => 0]);
        $this->actingAs($this->ada)->postJson(route('community.kudos', $event))->assertStatus(422);
    }

    public function test_fellows_can_hide_themselves_and_level_ups_are_shared(): void
    {
        app(TrackFeedService::class)->levelUp($this->ada, $this->track, 'intern');
        $this->assertSame('Ada L. is now a Junior Data Analyst', TrackFeedEvent::value('title'));

        $this->actingAs($this->ada)->post(route('community.visibility'), ['show_in_track_feed' => 0])->assertRedirect();
        $this->assertFalse($this->ada->fresh()->show_in_track_feed);

        $this->actingAs($this->ben)->get(route('community.index'))->assertOk()->assertDontSee('Ada L. is now');
        $this->actingAs($this->ada)->get(route('community.index'))->assertOk()->assertSee('Ada L. is now')->assertSee('You are hidden from this feed.');

        // Recording the same achievement twice keeps one event.
        app(TrackFeedService::class)->levelUp($this->ada, $this->track, 'intern');
        $this->assertSame(1, TrackFeedEvent::count());
    }

    public function test_only_safe_links_are_kept(): void
    {
        $event = app(TrackFeedService::class)->record($this->ada, $this->track->id, 'project', 'x', null, 'javascript:alert(1)', 'k');
        $this->assertNull($event->url);
    }
}
