<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FellowTrack;
use App\Models\Notification;
use App\Models\Presentation;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PresentationTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected User $admin;
    protected Track $track;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));
        // The fee check in the internship middleware uses MySQL date functions (tests run on SQLite).
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class, \App\Http\Middleware\ResolveActiveTrack::class]);

        Role::findOrCreate('fellow');
        Role::findOrCreate('admin');
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Team Reviewer']);
        $this->admin->assignRole('admin');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Fellow']);
        $this->fellow->assignRole('fellow');

        $this->track = Track::create(['name' => 'Data Science', 'slug' => 'data-science', 'category' => 'technical', 'description' => 'Data.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true,
            'score' => 0, 'tier' => 'rookie', 'started_at' => now()->subMonth()]);
    }

    protected function form(array $overrides = []): array
    {
        return $overrides + [
            'track_id' => $this->track->id,
            'title' => 'Cleaning Nkwen Traders sales data with pandas',
            'kind' => 'project_demo',
            'presented_at' => now()->subDays(2)->toDateString(),
            'event_name' => 'I-NNOVA Presentation Day',
            'audience' => 'About 40 fellows',
            'summary' => 'I showed how I found and fixed date formats, duplicates and outliers in the sales data, then answered questions.',
            'slides_url' => 'https://docs.google.com/presentation/d/abc',
        ];
    }

    protected function submitted(): Presentation
    {
        $this->actingAs($this->fellow)->post(route('presentations.store'), $this->form())->assertRedirect();

        return Presentation::firstOrFail();
    }

    protected function score(): float
    {
        return (float) FellowTrack::where('fellow_id', $this->fellow->id)->value('score');
    }

    public function test_a_fellow_submits_a_presentation_with_proof(): void
    {
        $this->actingAs($this->fellow)->get(route('presentations.create'))->assertOk()->assertSee('Submit a presentation');

        $this->post(route('presentations.store'), $this->form(['slides_url' => null, 'files' => [UploadedFile::fake()->create('slides.pdf', 300, 'application/pdf')]]))
            ->assertRedirect();

        $presentation = Presentation::firstOrFail();
        $this->assertSame(Presentation::STATUS_SUBMITTED, $presentation->status);
        $this->assertCount(1, $presentation->files);
        Storage::assertExists($presentation->files[0]);

        $this->get(route('presentations.show', $presentation))->assertOk()->assertSee('Awaiting review');
        $this->get(route('presentations.file', [$presentation, 0]))->assertOk();
        $this->get(route('presentations.index'))->assertOk()->assertSee('Cleaning Nkwen Traders');
    }

    public function test_proof_and_a_past_date_are_required(): void
    {
        $this->actingAs($this->fellow)->post(route('presentations.store'), $this->form(['slides_url' => null]))
            ->assertSessionHasErrors('slides_url');
        $this->post(route('presentations.store'), $this->form(['presented_at' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors('presented_at');

        $otherTrack = Track::create(['name' => 'Finance', 'slug' => 'finance', 'category' => 'non-technical', 'description' => 'F.', 'is_active' => true]);
        $this->post(route('presentations.store'), $this->form(['track_id' => $otherTrack->id]))->assertSessionHasErrors('track_id');

        $this->assertSame(0, Presentation::count());
    }

    public function test_fellows_only_see_their_own_and_cannot_reach_the_review(): void
    {
        $presentation = $this->submitted();
        $other = User::factory()->create(['role' => 'fellow']);
        $other->assignRole('fellow');

        $this->actingAs($other)->get(route('presentations.show', $presentation))->assertForbidden();
        $this->get(route('presentations.file', [$presentation, 0]))->assertForbidden();
        $this->actingAs($this->fellow)->get(route('admin.presentations.show', $presentation))->assertForbidden();
    }

    public function test_the_review_page_previews_the_effect_on_the_score(): void
    {
        $presentation = $this->submitted();

        $this->actingAs($this->admin)->get(route('admin.presentations.index'))->assertOk()->assertSee('Ada Fellow');
        $this->get(route('admin.presentations.show', $presentation))
            ->assertOk()
            ->assertSee("Effect on Ada Fellow's score", false)
            ->assertSee('Technical 25%')
            ->assertSee('Intern from 25');
    }

    public function test_approving_adds_the_chosen_amounts_to_the_ticked_categories(): void
    {
        $presentation = $this->submitted();

        $this->actingAs($this->admin)->post(route('admin.presentations.review', $presentation), [
            'decision' => 'approve',
            'categories' => ['technical', 'portfolio'],
            // Learning has an amount but is not ticked: it does not count.
            'boosts' => ['technical' => 20, 'portfolio' => 10, 'learning' => 50],
            'feedback' => 'Clear demo, good answers.',
        ])->assertRedirect(route('admin.presentations.index'));

        $presentation->refresh();
        $this->assertSame(Presentation::STATUS_APPROVED, $presentation->status);
        $this->assertEquals(['technical' => 20, 'portfolio' => 10], $presentation->category_boosts);

        // 20 x 25% + 10 x 20% = 7
        $this->assertEqualsWithDelta(7.0, $this->score(), 0.001);
        $ft = FellowTrack::where('fellow_id', $this->fellow->id)->first();
        $this->assertEqualsWithDelta(20.0, (float) $ft->technical_score, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $ft->learning_score, 0.001);

        $this->assertSame(1, Notification::where('user_id', $this->fellow->id)->where('title', 'Presentation approved')->count());
        $this->assertSame(1, AuditLog::where('auditable_id', $presentation->id)->where('action', 'presentation_approve')->count());

        // The fellow sees what it added, and can no longer edit it.
        $this->actingAs($this->fellow)->get(route('presentations.show', $presentation))->assertOk()->assertSee('+20')->assertSee('Approved');
        $this->get(route('presentations.edit', $presentation))->assertRedirect(route('presentations.show', $presentation));
    }

    public function test_a_category_never_goes_above_100(): void
    {
        $first = $this->submitted();
        $this->actingAs($this->admin)->post(route('admin.presentations.review', $first), ['decision' => 'approve', 'categories' => ['technical'], 'boosts' => ['technical' => 80]]);

        $this->actingAs($this->fellow)->post(route('presentations.store'), $this->form(['title' => 'Second talk']));
        $second = Presentation::where('title', 'Second talk')->firstOrFail();
        $this->actingAs($this->admin)->post(route('admin.presentations.review', $second), ['decision' => 'approve', 'categories' => ['technical'], 'boosts' => ['technical' => 50]]);

        $this->assertEqualsWithDelta(100.0, (float) FellowTrack::where('fellow_id', $this->fellow->id)->value('technical_score'), 0.001);
        $this->assertEqualsWithDelta(25.0, $this->score(), 0.001);
    }

    public function test_approval_needs_an_amount_and_amounts_are_limited(): void
    {
        $presentation = $this->submitted();
        $this->actingAs($this->admin);

        $this->post(route('admin.presentations.review', $presentation), ['decision' => 'approve', 'categories' => [], 'boosts' => []])
            ->assertSessionHas('error');
        $this->post(route('admin.presentations.review', $presentation), ['decision' => 'approve', 'categories' => ['technical'], 'boosts' => ['technical' => 150]])
            ->assertSessionHasErrors('boosts.technical');

        $this->assertSame(Presentation::STATUS_SUBMITTED, $presentation->fresh()->status);
        $this->assertEqualsWithDelta(0.0, $this->score(), 0.001);
    }

    public function test_changes_requested_then_resubmitted(): void
    {
        $presentation = $this->submitted();

        $this->actingAs($this->admin)->post(route('admin.presentations.review', $presentation), ['decision' => 'changes'])
            ->assertSessionHasErrors('feedback');
        $this->post(route('admin.presentations.review', $presentation), ['decision' => 'changes', 'feedback' => 'Please add the recording.'])
            ->assertRedirect();
        $this->assertSame(Presentation::STATUS_CHANGES_REQUESTED, $presentation->fresh()->status);

        $this->actingAs($this->fellow)->get(route('presentations.edit', $presentation))->assertOk()->assertSee('Please add the recording.');
        $this->put(route('presentations.update', $presentation), $this->form(['recording_url' => 'https://youtube.com/watch?v=x']))->assertRedirect();

        $presentation->refresh();
        $this->assertSame(Presentation::STATUS_SUBMITTED, $presentation->status);
        $this->assertSame(2, $presentation->submission_count);
        $this->assertSame('https://youtube.com/watch?v=x', $presentation->links['recording']);
    }

    public function test_reopening_an_approval_removes_what_it_added(): void
    {
        $presentation = $this->submitted();
        $this->actingAs($this->admin)->post(route('admin.presentations.review', $presentation), ['decision' => 'approve', 'categories' => ['interview'], 'boosts' => ['interview' => 40]]);
        $this->assertEqualsWithDelta(10.0, $this->score(), 0.001);

        $this->post(route('admin.presentations.reopen', $presentation), ['reason' => 'Wrong fellow credited.'])->assertRedirect();

        $this->assertSame(Presentation::STATUS_SUBMITTED, $presentation->fresh()->status);
        $this->assertNull($presentation->fresh()->category_boosts);
        $this->assertEqualsWithDelta(0.0, $this->score(), 0.001);
    }
}
