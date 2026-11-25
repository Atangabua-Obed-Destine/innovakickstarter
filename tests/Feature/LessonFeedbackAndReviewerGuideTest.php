<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\LessonFeedback;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\AiReview\AiGrader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LessonFeedbackAndReviewerGuideTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected TrackCurriculumActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();
        // The fee check in the internship middleware uses MySQL date functions (tests run on SQLite).
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);

        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Fellow']);
        $this->fellow->assignRole('fellow');

        $track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        $milestone = TrackMilestone::create(['track_id' => $track->id, 'title' => 'Pandas', 'description' => 'P.', 'sequence_order' => 1]);
        $this->activity = TrackCurriculumActivity::create([
            'track_id' => $track->id, 'milestone_id' => $milestone->id, 'title' => 'GroupBy: Aggregating Data', 'description' => 'Aggregate sales by branch.',
            'type' => 'research', 'career_capital_category' => 'technical', 'points' => 20, 'is_active' => true, 'sequence_order' => 1,
            'reviewer_guide' => 'MODEL ANSWER: Bamenda has the highest revenue (4.2M FCFA).',
        ]);
    }

    public function test_a_fellow_says_whether_a_lesson_was_clear_and_can_change_it(): void
    {
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->activity->id, 'status' => CurriculumStatus::AVAILABLE]);

        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->activity))
            ->assertOk()->assertSee('Was this lesson clear?')
            ->assertDontSee('MODEL ANSWER'); // the reviewer guide never reaches fellows

        $this->post(route('curriculum.activity.feedback', $this->activity), ['is_clear' => 0, 'comment' => 'The merge part needs an example.'])->assertRedirect();
        $this->post(route('curriculum.activity.feedback', $this->activity), ['is_clear' => 0, 'comment' => 'The merge part needs a worked example.']);

        $this->assertSame(1, LessonFeedback::count());
        $this->assertSame('The merge part needs a worked example.', LessonFeedback::first()->comment);

        $this->actingAs($this->admin)->get(route('admin.curriculum.lesson-feedback'))
            ->assertOk()->assertSee('GroupBy: Aggregating Data')->assertSee('1 unclear (100%)')->assertSee('needs a worked example');
    }

    public function test_a_fellow_outside_the_track_cannot_send_feedback(): void
    {
        $other = User::factory()->create(['role' => 'fellow']);
        $other->assignRole('fellow');

        $this->actingAs($other)->post(route('curriculum.activity.feedback', $this->activity), ['is_clear' => 1])->assertForbidden();
    }

    public function test_the_reviewer_guide_is_hidden_from_serialisation_and_given_to_the_ai_review(): void
    {
        $this->assertArrayNotHasKey('reviewer_guide', $this->activity->toArray());

        $method = new \ReflectionMethod(AiGrader::class, 'userPrompt');
        $package = [
            'activity' => ['title' => 'T', 'track' => 'DS', 'milestone' => 'M', 'type' => 'research', 'difficulty' => 'beginner', 'points' => 20,
                'evidence_requirements' => [], 'description' => 'D', 'instructions' => '', 'reviewer_guide' => $this->activity->reviewer_guide,
                'rubric' => [['criterion' => 'Correct', 'weight' => 100, 'description' => 'Right numbers']]],
            'submission' => ['attempt' => 1, 'started_at' => null, 'submitted_at' => null, 'minutes_from_start_to_submit' => null,
                'url' => null, 'text' => 'Bamenda wins.', 'reflection' => '', 'file_names' => [], 'ai_assistance' => null],
            'github' => [], 'links' => [], 'files' => [], 'unread' => [],
        ];
        $prompt = $method->invoke(app(AiGrader::class), $package, [], null);

        $this->assertStringContainsString('REVIEWER GUIDE', $prompt);
        $this->assertStringContainsString('MODEL ANSWER: Bamenda', $prompt);
        $this->assertStringContainsString('Never quote it', $prompt);
    }
}
