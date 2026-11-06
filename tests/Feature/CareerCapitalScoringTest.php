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

    public function test_the_curve_rewards_completing_the_curriculum(): void
    {
        $calc = $this->calculator();

        $this->assertEqualsWithDelta(63.212, $calc->scaleScore(300, 300), 0.01);   // all of it
        $this->assertEqualsWithDelta(39.347, $calc->scaleScore(150, 300), 0.01);   // half of it
        $this->assertEqualsWithDelta(86.466, $calc->scaleScore(600, 300), 0.01);   // twice as much
        $this->assertSame(0.0, $calc->scaleScore(0, 300));
        $this->assertSame(0.0, $calc->scaleScore(50, 0));
    }

    public function test_curriculum_points_are_weighted_by_review_quality(): void
    {
        $this->completeCurriculum(200, 100);
        $this->completeCurriculum(200, 50);

        // 200 + 100 quality-weighted points out of 400 offered: ratio 0.75.
        $this->assertEqualsWithDelta(52.763, $this->calculator()->calculateTechnicalScore($this->fellow, $this->track), 0.01);
    }

    public function test_work_approved_without_a_rubric_score_gets_full_credit(): void
    {
        $this->completeCurriculum(300, 0); // approved with no rubric scores: stored as 0

        $this->assertEqualsWithDelta(63.212, $this->calculator()->calculateTechnicalScore($this->fellow, $this->track), 0.01);
    }

    public function test_mock_interviews_count_from_the_score_column(): void
    {
        // 10 interviews at 80: full volume, consistent (+5%) = 84, which is half the category.
        $this->mockInterviews(10, 80);

        $this->assertEqualsWithDelta(84.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
        $this->assertEqualsWithDelta(42.0, $this->calculator()->calculateInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_interview_volume_counts_beyond_the_last_ten(): void
    {
        AdminSetting::set('score_interview_full_volume', 20);

        $this->mockInterviews(20, 80);

        // Previously capped at 10 / 20 = half credit; 20 interviews now give full credit.
        $this->assertEqualsWithDelta(84.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track), 0.01);
    }

    public function test_practice_interviews_do_not_count(): void
    {
        $this->mockInterviews(5, 80, practice: true);

        $this->assertSame(0.0, $this->calculator()->mockInterviewScore($this->fellow, $this->track));
    }

    public function test_category_weights_come_from_the_settings_page(): void
    {
        AdminSetting::set('weight_technical', 100);
        AdminSetting::set('weight_interview', 0);
        AdminSetting::set('weight_portfolio', 0);
        AdminSetting::set('weight_collaboration', 0);
        AdminSetting::set('weight_learning', 0);

        $this->completeCurriculum(300, 100);

        $this->assertEqualsWithDelta(63.212, $this->calculator()->calculateScore($this->fellow, $this->track), 0.01);
    }

    public function test_a_track_rubric_keyed_by_category_overrides_the_settings(): void
    {
        $this->track->update(['scoring_rubric' => ['technical' => 50, 'interview' => 50, 'portfolio' => 0, 'collaboration' => 0, 'learning' => 0]]);

        $this->completeCurriculum(300, 100);

        $this->assertEqualsWithDelta(31.606, $this->calculator()->calculateScore($this->fellow, $this->track->fresh()), 0.01);
    }

    public function test_promotion_is_notified_and_demotion_is_not(): void
    {
        AdminSetting::set('weight_technical', 100);
        AdminSetting::set('weight_interview', 0);
        AdminSetting::set('weight_portfolio', 0);
        AdminSetting::set('weight_collaboration', 0);
        AdminSetting::set('weight_learning', 0);

        $this->completeCurriculum(300, 100);

        $updated = $this->calculator()->updateScore($this->fellow, $this->track);

        $this->assertSame('professional', $updated->tier);
        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('type', Notification::TYPE_TIER_PROMOTION)->count());

        // Coming down from Elite must not send a "promotion".
        $this->fellowTrack->update(['tier' => 'elite']);
        $this->calculator()->updateScore($this->fellow, $this->track);

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
