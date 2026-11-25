<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\AccountabilityPair;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CurriculumService;
use App\Services\ReviewPartnerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewPartnerTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected TrackCurriculumActivity $phase2;
    protected TrackCurriculumActivity $review;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);
        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        $milestone = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Capstone', 'description' => 'C.', 'sequence_order' => 8]);
        $base = ['track_id' => $this->track->id, 'milestone_id' => $milestone->id, 'type' => 'project', 'career_capital_category' => 'technical', 'points' => 20, 'is_active' => true];
        $this->phase2 = TrackCurriculumActivity::create($base + ['title' => 'EDA Phase 2', 'description' => 'Analyse.', 'sequence_order' => 1]);
        $this->review = TrackCurriculumActivity::create($base + ['title' => 'Collaborate: Peer EDA Review', 'description' => 'Review.', 'sequence_order' => 2,
            'career_capital_category' => 'collaboration', 'is_collaborative' => true, 'prerequisites' => [$this->phase2->id]]);
    }

    protected function fellow(string $name): User
    {
        $fellow = User::factory()->create(['role' => 'fellow', 'name' => $name, 'github_url' => 'https://github.com/' . strtolower($name)]);
        $fellow->assignRole('fellow');
        FellowTrack::create(['fellow_id' => $fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        FellowCurriculumProgress::create(['fellow_id' => $fellow->id, 'curriculum_activity_id' => $this->review->id, 'status' => CurriculumStatus::LOCKED]);
        FellowCurriculumProgress::create(['fellow_id' => $fellow->id, 'curriculum_activity_id' => $this->phase2->id, 'status' => CurriculumStatus::COMPLETED, 'completed_at' => now()]);

        return $fellow;
    }

    /** Phase 2 approved: the review opens (as after a real approval). */
    protected function reachReview(User $fellow): void
    {
        app(CurriculumService::class)->unlockDependentActivities($fellow, $this->phase2);
    }

    public function test_fellows_reaching_the_review_are_paired_and_notified(): void
    {
        $ada = $this->fellow('Ada');
        $ben = $this->fellow('Ben');

        $this->reachReview($ada);
        $this->assertSame(0, AccountabilityPair::count(), 'Nobody else is waiting yet.');

        $this->actingAs($ada)->get(route('curriculum.activity.show', $this->review))
            ->assertOk()->assertSee('We pair you automatically');

        $this->reachReview($ben);
        $this->assertSame(1, AccountabilityPair::where('milestone_id', $this->review->milestone_id)->count());
        $this->assertSame(1, Notification::where('user_id', $ada->id)->where('title', 'You have a review partner')->count());
        $this->assertStringContainsString('https://github.com/ben', Notification::where('user_id', $ada->id)->where('title', 'You have a review partner')->value('message'));

        $this->actingAs($ada)->get(route('curriculum.activity.show', $this->review))
            ->assertOk()->assertSee('Your review partner')->assertSee('Ben')->assertSee('https://github.com/ben', false);
        $this->assertSame('Ben', app(ReviewPartnerService::class)->partnerFor($ada, $this->review)->name);
    }

    public function test_daily_matching_pairs_waiting_fellows_and_alerts_the_team_once_after_three_days(): void
    {
        $fellows = collect(['Ada', 'Ben', 'Cy'])->map(fn ($n) => $this->fellow($n));
        // They reached the review while matching was not running (e.g. right after an import).
        FellowCurriculumProgress::where('curriculum_activity_id', $this->review->id)->update(['status' => CurriculumStatus::AVAILABLE->value, 'updated_at' => now()->subDays(4)]);

        $result = app(ReviewPartnerService::class)->matchWaiting();

        $this->assertSame(1, $result['paired']);
        $this->assertSame(1, $result['alerted'], 'The odd one out has waited over 3 days.');
        $this->assertSame(1, Notification::where('user_id', $this->admin->id)->where('title', 'A fellow needs a review partner')->count());

        app(ReviewPartnerService::class)->matchWaiting();
        $this->assertSame(1, Notification::where('user_id', $this->admin->id)->where('title', 'A fellow needs a review partner')->count(), 'Not alerted again the next day.');

        $this->artisan('curriculum:match-review-partners')->assertSuccessful();
    }
}
