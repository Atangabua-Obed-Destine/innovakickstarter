<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\AdminSetting;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\InterviewSession;
use App\Models\Notification;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\CareerCapitalCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CareerCapitalScoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected Track $track;
    protected FellowTrack $fellowTrack;
    protected TrackMilestone $milestone;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('fellow');

        $this->fellow = User::factory()->create(['role' => 'fellow']);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create([
            'name' => 'Software Engineering',
            'slug' => 'software-engineering',
            'category' => 'technical',
            'description' => 'Build software.',
            'is_active' => true,
        ]);
        // Existing tracks carry an older rubric format that is not keyed by category.
        $this->track->update(['scoring_rubric' => ['human_capital' => 40, 'social_capital' => 20, 'psychological_capital' => 20, 'cultural_capital' => 20]]);

        $this->fellowTrack = FellowTrack::create([
            'fellow_id' => $this->fellow->id,
            'track_id' => $this->track->id,
            'status' => 'approved',
            'is_primary' => true,
            'score' => 0,
            'tier' => 'rookie',
        ]);

        $this->milestone = TrackMilestone::create([
            'track_id' => $this->track->id,
            'title' => 'Foundations',
            'description' => 'The basics.',
            'order' => 1,
        ]);
    }

    protected function calculator(): CareerCapitalCalculator
    {
        return app(CareerCapitalCalculator::class);
    }

    /**
     * A technical curriculum activity worth $points, completed with a rubric score.
     */
    protected function completeCurriculum(int $points, ?int $rubricScore, string $type = 'project'): void
    {
        $activity = TrackCurriculumActivity::create([
            'track_id' => $this->track->id,
            'milestone_id' => $this->milestone->id,
            'title' => "Activity {$points}",
            'description' => 'Do the work.',
            'type' => $type,
            'career_capital_category' => 'technical',
            'points' => $points,
            'is_active' => true,
        ]);

        FellowCurriculumProgress::create([
            'fellow_id' => $this->fellow->id,
            'curriculum_activity_id' => $activity->id,
            'status' => CurriculumStatus::COMPLETED,
            'points_awarded' => $points,
            'score_awarded' => $rubricScore,
            'completed_at' => now(),
        ]);
    }

    protected function mockInterviews(int $count, float $score, bool $practice = false): void
    {
        for ($i = 0; $i < $count; $i++) {
            InterviewSession::create([
                'fellow_id' => $this->fellow->id,
                'track_id' => $this->track->id,
                'type' => 'behavioral',
                'mode' => 'ai',
                'status' => 'completed',
                'score' => $score,
                'overall_score' => null,
                'is_practice' => $practice,
                'completed_at' => now()->subDays($i),
            ]);
        }
    }

    public function test_the_curve_is_deliberately_hard(): void
    {
        $calc = $this->calculator();

        // Ceiling = max(10,000, 10 x curriculum); score = 100 x (1 - e^(-0.1 x earned / ceiling))
        $this->assertEqualsWithDelta(0.499, $calc->calculateHardScaledScore(500, 500), 0.001);     // whole 500-point curriculum
        $this->assertEqualsWithDelta(9.516, $calc->calculateHardScaledScore(30000, 3000), 0.001);  // 10x a 3,000-point curriculum
        $this->assertEqualsWithDelta(63.212, $calc->calculateHardScaledScore(100000, 500), 0.001); // 100,000 points
        $this->assertSame(0.0, $calc->calculateHardScaledScore(0, 500));
    }

    public function test_curriculum_points_count_as_awarded_whatever_the_review_score(): void
    {
        $this->completeCurriculum(200, 100);
        $this->completeCurriculum(200, 50);

        $calc = $this->calculator();
        $this->assertEqualsWithDelta($calc->calculateHardScaledScore(400, 400), $calc->calculateTechnicalScore($this->fellow, $this->track), 0.001);
    }

    public function test_mock_interviews_count_from_the_score_column(): void
    {
        // Last 10 at 80, volume 10 / 20 = half credit, consistent (+5%) = 42.
        $this->mockInterviews(10, 80);

        $this->assertEqualsWithDelta(42.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
        $this->assertEqualsWithDelta(42.0, $this->calculator()->calculateInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_only_the_last_ten_interviews_count(): void
    {
        $this->mockInterviews(20, 80);

        $this->assertEqualsWithDelta(42.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_interviews_the_ai_could_not_score_do_not_count(): void
    {
        $this->mockInterviews(10, 80);
        // Newer interviews the AI could not score: they must not pull the average to 0.
        for ($i = 0; $i < 3; $i++) {
            InterviewSession::create([
                'fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'type' => 'behavioral', 'mode' => 'ai',
                'status' => 'completed', 'score' => null, 'overall_score' => null, 'is_practice' => false, 'completed_at' => now()->addHour(),
            ]);
        }

        $this->assertEqualsWithDelta(42.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_practice_interviews_never_count(): void
    {
        // Programme decision: practice interviews never affect Career Capital.
        $this->mockInterviews(5, 80, practice: true);
        $this->assertEqualsWithDelta(0.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);

        // 5 / 20 volume x 80, consistent (+5%) = 21, from the real ones only.
        $this->mockInterviews(5, 80);
        $this->assertEqualsWithDelta(21.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_default_weights_are_fixed_and_settings_page_weights_are_not_used(): void
    {
        AdminSetting::set('weight_interview', 100);
        $this->mockInterviews(10, 80);

        // Interview 42 x 25% = 10.5
        $this->assertEqualsWithDelta(10.5, $this->calculator()->calculateScore($this->fellow, $this->track), 0.01);
        $this->assertSame(['technical' => 25.0, 'interview' => 25.0, 'portfolio' => 20.0, 'collaboration' => 15.0, 'learning' => 15.0],
            $this->calculator()->categoryWeights($this->track));
    }

    public function test_a_track_rubric_with_category_weights_overrides_the_defaults(): void
    {
        $this->track->update(['scoring_rubric' => [
            'technical' => ['weight' => 0], 'interview' => ['weight' => 100], 'portfolio' => ['weight' => 0],
            'collaboration' => ['weight' => 0], 'learning' => ['weight' => 0],
        ]]);
        $this->mockInterviews(10, 80);

        $this->assertEqualsWithDelta(42.0, $this->calculator()->calculateScore($this->fellow, $this->track->fresh()), 0.01);
    }

    public function test_promotion_is_notified_and_demotion_is_not(): void
    {
        $this->track->update(['scoring_rubric' => [
            'technical' => ['weight' => 0], 'interview' => ['weight' => 100], 'portfolio' => ['weight' => 0],
            'collaboration' => ['weight' => 0], 'learning' => ['weight' => 0],
        ]]);
        $this->mockInterviews(10, 80);

        $updated = $this->calculator()->updateScore($this->fellow, $this->track->fresh());

        $this->assertSame('intern', $updated->tier);
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('type', Notification::TYPE_TIER_PROMOTION)->count());

        // Coming down from Elite must not send a "promotion".
        $this->fellowTrack->update(['tier' => 'elite']);
        $this->calculator()->updateScore($this->fellow, $this->track->fresh());

        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('type', Notification::TYPE_TIER_PROMOTION)->count());
    }

    public function test_recalculate_command_previews_then_applies(): void
    {
        $this->completeCurriculum(300, 100);
        $lastActive = now()->subMonth()->startOfSecond();
        $this->fellowTrack->update(['last_active_at' => $lastActive]);

        $this->artisan('career-capital:recalculate', ['--dry-run' => true])->assertSuccessful();
        $this->assertEquals(0, (float) $this->fellowTrack->fresh()->score);

        $this->artisan('career-capital:recalculate', ['--no-notify' => true])->assertSuccessful();

        $fresh = $this->fellowTrack->fresh();
        $this->assertGreaterThan(0, (float) $fresh->score);
        $this->assertEquals($lastActive, $fresh->last_active_at, 'Bulk recalculation must not mark fellows as active.');
        $this->assertSame(0, Notification::where('user_id', $this->fellow->id)->count());
    }
}
