<?php

namespace Tests\Feature;

use App\Models\FellowTrack;
use App\Models\PracticeAttempt;
use App\Models\PracticeChallenge;
use App\Models\Track;
use App\Models\User;
use App\Services\PracticeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PracticeLabTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected User $fellow;
    protected PracticeChallenge $sql;
    protected PracticeChallenge $quiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Role::findOrCreate('fellow');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Fellow']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true,
            'experience' => ['practice' => ['sql_tables' => ['sales' => '/course-files/data-science/data/nkwen_traders_ch2_sales.csv']]]]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);

        $this->sql = PracticeChallenge::create(['track_id' => $this->track->id, 'slug' => 'sql-branches', 'title' => 'Revenue by branch', 'skill' => 'sql', 'kind' => 'sql',
            'difficulty' => 1, 'xp' => 10, 'prompt' => 'Total revenue per branch.', 'explanation' => 'Use **GROUP BY**.', 'hint' => 'Group by branch.',
            'expected_hash' => hash('sha256', '[["Mile 4",100],["Nkwen",250]]'), 'sort' => 0]);
        $this->quiz = PracticeChallenge::create(['track_id' => $this->track->id, 'slug' => 'quiz-median', 'title' => 'Mean or median?', 'skill' => 'statistics', 'kind' => 'quiz',
            'difficulty' => 1, 'xp' => 10, 'prompt' => 'Which is robust to outliers?', 'options' => ['The mean', 'The median', 'The maximum'], 'answer_index' => 1,
            'explanation' => 'The median ignores extremes.', 'sort' => 1]);
    }

    public function test_pages_never_contain_the_answers(): void
    {
        $this->actingAs($this->fellow)->get(route('practice.index'))->assertOk()->assertSee('Revenue by branch')->assertSee('Practice streak');

        $html = $this->get(route('practice.show', $this->quiz))->assertOk()->assertSee('Mean or median?')->getContent();
        $this->assertStringNotContainsString('answer_index', $html);
        $this->assertStringNotContainsString('The median ignores extremes', $html, 'The explanation only shows after solving.');

        $html = $this->get(route('practice.show', $this->sql))->assertOk()->getContent();
        $this->assertStringNotContainsString($this->sql->expected_hash, $html);
        $this->assertStringContainsString('practice-lab.js', $html);
    }

    public function test_checking_awards_xp_once_and_the_daily_bonus(): void
    {
        $daily = app(PracticeService::class)->dailyChallenge($this->track);
        $other = $daily->is($this->sql) ? $this->quiz : $this->sql;
        $this->actingAs($this->fellow);

        // Wrong, then right (order-insensitive canonical computed by the browser).
        $this->postJson(route('practice.check', $this->sql), ['canonical' => '[["Nkwen",999]]'])
            ->assertOk()->assertJson(['correct' => false, 'xp' => 0, 'hint' => 'Group by branch.', 'explanation_html' => null]);
        $res = $this->postJson(route('practice.check', $this->sql), ['canonical' => '[["Mile 4",100],["Nkwen",250]]'])->assertOk()->assertJson(['correct' => true]);
        $this->assertSame($daily->is($this->sql) ? 15 : 10, $res->json('xp'));
        $this->assertStringContainsString('<strong>GROUP BY</strong>', $res->json('explanation_html'));

        // Solving again gives no more XP.
        $this->postJson(route('practice.check', $this->sql), ['canonical' => '[["Mile 4",100],["Nkwen",250]]'])->assertJson(['correct' => true, 'xp' => 0]);

        $this->postJson(route('practice.check', $this->quiz), ['option' => 1])->assertJson(['correct' => true]);
        $stats = app(PracticeService::class)->stats($this->fellow, $this->track);
        $this->assertSame(25, $stats['xp']);
        $this->assertSame(2, $stats['solved']);
        $this->assertSame(1, $stats['streak']);
        $this->assertTrue($stats['practiced_today']);
        $this->assertSame('Ada F.', app(PracticeService::class)->leaderboard($this->track)->first()->display_name);
    }

    public function test_the_explanation_unlocks_after_three_tries(): void
    {
        $this->actingAs($this->fellow);
        $this->postJson(route('practice.reveal', $this->quiz))->assertForbidden();

        foreach ([0, 2] as $wrong) {
            $this->postJson(route('practice.check', $this->quiz), ['option' => $wrong])->assertJson(['correct' => false, 'can_reveal' => false]);
        }
        $this->postJson(route('practice.check', $this->quiz), ['option' => 0])->assertJson(['can_reveal' => true]);
        $this->postJson(route('practice.reveal', $this->quiz))->assertOk()->assertJsonFragment(['explanation_html' => "<p>The median ignores extremes.</p>\n"]);
    }

    public function test_streak_counts_consecutive_days_and_survives_until_today_ends(): void
    {
        foreach ([1, 2, 4] as $daysAgo) {
            PracticeAttempt::create(['challenge_id' => $this->quiz->id, 'fellow_id' => $this->fellow->id, 'correct' => true, 'xp_awarded' => 0, 'practiced_on' => now()->subDays($daysAgo)->toDateString()]);
        }

        $this->assertSame(2, app(PracticeService::class)->stats($this->fellow, $this->track)['streak'], 'Yesterday and the day before; today is not over yet.');
    }

    public function test_fellows_of_other_tracks_cannot_use_a_challenge(): void
    {
        $other = User::factory()->create(['role' => 'fellow']);
        $other->assignRole('fellow');

        $this->actingAs($other)->postJson(route('practice.check', $this->quiz), ['option' => 1])->assertForbidden();
    }
}
