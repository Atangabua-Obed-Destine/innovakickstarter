<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportCurriculumDraftTest extends TestCase
{
    use RefreshDatabase;

    protected Track $track;
    protected TrackMilestone $milestone;
    protected TrackCurriculumActivity $first;
    protected TrackCurriculumActivity $second;
    protected User $fellow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);
        Role::findOrCreate('admin');
        Role::findOrCreate('fellow');
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow']);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        $this->milestone = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Old title', 'description' => 'Old.', 'sequence_order' => 1]);
        $base = ['track_id' => $this->track->id, 'milestone_id' => $this->milestone->id, 'type' => 'research', 'career_capital_category' => 'interview', 'points' => 10, 'is_active' => true];
        $this->first = TrackCurriculumActivity::create($base + ['title' => 'Lists', 'description' => 'Old.', 'sequence_order' => 1]);
        $this->second = TrackCurriculumActivity::create($base + ['title' => 'OOP', 'description' => 'Old.', 'sequence_order' => 2, 'chain_parent_id' => $this->first->id]);

        // The fellow is waiting on OOP, which was chained to Lists.
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->first->id, 'status' => CurriculumStatus::IN_PROGRESS]);
        FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $this->second->id, 'status' => CurriculumStatus::LOCKED]);
    }

    protected function activity(string $key, int $order, array $overrides = []): array
    {
        return $overrides + [
            'key' => $key, 'order' => $order, 'title' => "Activity {$order}", 'type' => 'research', 'difficulty_level' => 'beginner', 'points' => 10,
            'career_capital_category' => 'technical', 'is_required' => true, 'deadline_days' => 3, 'grace_period_days' => 1, 'late_penalty_percent' => 10,
            'prerequisites' => [], 'description' => 'What and why.', 'instructions' => "### Steps\n1. Do it.", 'evidence_requirements' => ['url', 'text'],
            'evaluation_rubric' => [['criterion' => 'Correct', 'weight' => 60, 'description' => 'c'], ['criterion' => 'Clear', 'weight' => 40, 'description' => 'd']],
            'resources' => [['type' => 'link', 'title' => 'pandas', 'content' => 'https://pandas.pydata.org/docs/']],
            'interview_config' => null, 'reviewer_guide' => 'Model answer: 42.', 'starter_notebook' => null,
        ];
    }

    protected function draftFile(array $draft): string
    {
        $path = tempnam(sys_get_temp_dir(), 'draft');
        file_put_contents($path, json_encode($draft));

        return $path;
    }

    protected function validDraft(): array
    {
        return ['milestones' => [
            ['key' => $this->milestone->id, 'sequence_order' => 1, 'title' => 'Python Fundamentals', 'description' => 'New.', 'estimated_duration_days' => 14, 'activities' => [
                $this->activity($this->first->id, 1, ['title' => 'Lists & Loops']),
                $this->activity($this->second->id, 3, ['title' => 'OOP (optional)', 'is_required' => false]),
                $this->activity('new-list-comprehensions', 2, ['title' => 'List comprehensions', 'prerequisites' => [$this->first->id]]),
            ]],
            ['key' => 'new-sql', 'sequence_order' => 2, 'title' => 'SQL', 'description' => 'Queries.', 'estimated_duration_days' => 14, 'activities' => [
                $this->activity('new-sql-basics', 1, ['title' => 'SQL basics', 'prerequisites' => ['new-list-comprehensions']]),
            ]],
        ]];
    }

    public function test_dry_run_validates_and_changes_nothing(): void
    {
        $this->artisan('curriculum:import-draft', ['file' => $this->draftFile($this->validDraft()), '--dry-run' => true])->assertSuccessful();

        $this->assertSame('Lists', $this->first->fresh()->title);
        $this->assertSame(1, TrackMilestone::count());
    }

    public function test_import_updates_adds_renumbers_and_opens_reachable_activities(): void
    {
        $this->artisan('curriculum:import-draft', ['file' => $this->draftFile($this->validDraft())])->assertSuccessful();

        $first = $this->first->fresh();
        $this->assertSame('Lists & Loops', $first->title);
        $this->assertSame('Model answer: 42.', $first->reviewer_guide);
        $this->assertSame(1, $first->sequence_order);
        $this->assertSame('technical', $first->career_capital_category->value);

        $oop = $this->second->fresh();
        $this->assertFalse($oop->is_required);
        $this->assertNull($oop->chain_parent_id, 'Old chain links are replaced by the draft prerequisites.');
        $this->assertSame(3, $oop->sequence_order);

        $new = TrackCurriculumActivity::where('title', 'List comprehensions')->firstOrFail();
        $this->assertSame([$this->first->id], $new->prerequisites);
        $this->assertSame(2, $new->sequence_order);
        $sql = TrackMilestone::where('title', 'SQL')->firstOrFail();
        $this->assertSame(2, $sql->sequence_order);
        $this->assertSame(4, TrackCurriculumActivity::where('title', 'SQL basics')->value('sequence_order'));

        // OOP no longer waits on Lists: the fellow's locked row opens; new activities get progress rows.
        $this->assertSame(CurriculumStatus::AVAILABLE, FellowCurriculumProgress::where('curriculum_activity_id', $oop->id)->first()->status);
        $this->assertSame(CurriculumStatus::LOCKED, FellowCurriculumProgress::where('curriculum_activity_id', $new->id)->first()->status);
    }

    public function test_import_refuses_drafts_that_drop_activities_or_break_rules(): void
    {
        $draft = $this->validDraft();
        unset($draft['milestones'][0]['activities'][1]); // drops OOP
        $draft['milestones'][0]['activities'] = array_values($draft['milestones'][0]['activities']);
        $draft['milestones'][0]['activities'][0]['evaluation_rubric'][0]['weight'] = 30; // 70 total
        $draft['milestones'][1]['activities'][0]['prerequisites'] = ['new-missing'];

        $this->artisan('curriculum:import-draft', ['file' => $this->draftFile($draft)])
            ->expectsOutputToContain('is missing from the draft (fellows have progress on it)')
            ->expectsOutputToContain('rubric weights add up to 70')
            ->expectsOutputToContain('prerequisite new-missing is not in the draft')
            ->assertFailed();

        $this->assertSame('Lists', $this->first->fresh()->title);
    }

    public function test_lesson_text_renders_as_safe_markdown(): void
    {
        $this->first->update(['instructions' => "### Load the data\nUse `pd.read_csv`.\n\n<script>alert(1)</script>"]);
        FellowCurriculumProgress::where('curriculum_activity_id', $this->first->id)->update(['status' => CurriculumStatus::AVAILABLE->value]);

        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->first))
            ->assertOk()
            ->assertSee('<h3>Load the data</h3>', false)
            ->assertSee('<code>pd.read_csv</code>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
