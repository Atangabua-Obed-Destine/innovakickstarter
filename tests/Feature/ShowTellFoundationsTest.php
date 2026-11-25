<?php

namespace Tests\Feature;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FoundationArea;
use App\Models\FoundationResult;
use App\Models\Track;
use App\Models\TrackCurriculumActivity;
use App\Models\TrackMilestone;
use App\Models\TrackOrientation;
use App\Models\User;
use App\Services\FoundationLinkService;
use App\Services\PreSubmitCheckService;
use App\Services\YouTubeVideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShowTellFoundationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $fellow;
    protected Track $track;
    protected TrackCurriculumActivity $project;
    protected TrackCurriculumActivity $lesson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\App\Http\Middleware\EnsureProfileCompleted::class, \App\Http\Middleware\EnsureInternshipApproved::class]);
        Http::preventStrayRequests();
        Role::findOrCreate('fellow');
        $this->fellow = User::factory()->create(['role' => 'fellow', 'name' => 'Ada Lovelace']);
        $this->fellow->assignRole('fellow');
        $this->track = Track::create(['name' => 'Video Test', 'slug' => 'video-test', 'category' => 'technical', 'description' => 'D.', 'is_active' => true]);
        FellowTrack::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'status' => 'approved', 'is_primary' => true, 'score' => 0, 'tier' => 'rookie', 'started_at' => now()]);
        TrackOrientation::create(['fellow_id' => $this->fellow->id, 'track_id' => $this->track->id, 'completed_at' => now()]);
        $m1 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Basics', 'description' => 'B.', 'sequence_order' => 1, 'is_active' => true]);
        $m2 = TrackMilestone::create(['track_id' => $this->track->id, 'title' => 'Stats', 'description' => 'S.', 'sequence_order' => 2, 'is_active' => true]);
        $make = fn ($m, $title, $type, $skills, $order, $text = 'Do it.') => TrackCurriculumActivity::create(['track_id' => $this->track->id, 'milestone_id' => $m->id, 'title' => $title,
            'description' => $text, 'type' => $type, 'skills' => $skills, 'career_capital_category' => 'technical', 'points' => 10, 'is_active' => true, 'is_required' => true, 'sequence_order' => $order]);
        $make($m1, 'First project', 'project', ['python'], 1);
        $make($m1, 'Closing project of chapter 1', 'project', ['python'], 2);
        $this->lesson = $make($m2, 'Averages', 'research', ['statistics'], 3, 'Work out the average and the median, then round to 2 decimals.');
        $this->project = $make($m2, 'Closing project of chapter 2', 'project', ['statistics', 'communication'], 4);
    }

    private function areas(): void
    {
        foreach ([['maths', 'Maths', '🔢', ['maths-averages-ratios' => 'Averages and ratios', 'maths-percentages' => 'Percentages', 'maths-fractions-decimals-rounding' => 'Decimals and rounding']],
                  ['english', 'English', '📖', ['english-clear-answers' => 'Clear answers']]] as $i => [$key, $title, $icon, $lessons]) {
            FoundationArea::create(['key' => $key, 'title' => $title, 'icon' => $icon, 'summary' => "{$title} basics.", 'sort' => $i, 'is_active' => true,
                'content' => ['lessons' => collect($lessons)->map(fn ($t, $k) => ['key' => $k, 'title' => $t, 'minutes' => 8])->values()->all()]]);
        }
    }

    private function start(TrackCurriculumActivity $a): FellowCurriculumProgress
    {
        return FellowCurriculumProgress::create(['fellow_id' => $this->fellow->id, 'curriculum_activity_id' => $a->id, 'status' => CurriculumStatus::IN_PROGRESS, 'started_at' => now()]);
    }

    public function test_youtube_links_are_parsed_and_others_refused(): void
    {
        $yt = app(YouTubeVideoService::class);
        foreach (['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://youtu.be/dQw4w9WgXcQ?si=abc', 'youtube.com/shorts/dQw4w9WgXcQ', 'https://m.youtube.com/watch?v=dQw4w9WgXcQ&t=10s', 'https://www.youtube.com/live/dQw4w9WgXcQ'] as $url) {
            $this->assertSame('dQw4w9WgXcQ', $yt->videoId($url), $url);
        }
        foreach (['https://vimeo.com/123', 'https://www.youtube.com/channel/UC123', 'https://evil.com/watch?v=dQw4w9WgXcQ', 'https://youtube.com.evil.com/watch?v=dQw4w9WgXcQ', 'https://youtu.be/short', ''] as $url) {
            $this->assertNull($yt->videoId($url), $url);
        }
    }

    public function test_youtube_check_reports_found_private_missing_and_unreachable(): void
    {
        Http::fake([
            'www.youtube.com/oembed?*aaaaaaaaaaa*' => Http::response(['title' => 'My Sales Calculator', 'author_name' => 'Ada Data', 'author_url' => 'https://www.youtube.com/@ada'], 200),
            'www.youtube.com/oembed?*bbbbbbbbbbb*' => Http::response('Unauthorized', 401),
            'www.youtube.com/oembed?*ccccccccccc*' => Http::response('Not Found', 404),
            'www.youtube.com/oembed?*ddddddddddd*' => fn () => throw new ConnectionException('down'),
        ]);
        $yt = app(YouTubeVideoService::class);
        $ok = $yt->check('https://youtu.be/aaaaaaaaaaa');
        $this->assertTrue($ok['ok']);
        $this->assertTrue($ok['verified']);
        $this->assertSame('My Sales Calculator', $ok['title']);
        $this->assertSame('Ada Data', $ok['channel']);
        $this->assertStringContainsString('Private', $yt->check('https://youtu.be/bbbbbbbbbbb')['error']);
        $this->assertStringContainsString('cannot find', $yt->check('https://youtu.be/ccccccccccc')['error']);
        $down = $yt->check('https://youtu.be/ddddddddddd');
        $this->assertTrue($down['ok']);
        $this->assertFalse($down['verified']);
        $this->assertFalse($yt->check('https://example.com/video')['ok']);
    }

    public function test_command_marks_the_closing_project_of_each_chapter(): void
    {
        $this->artisan('curriculum:video-projects', ['slug' => 'video-test'])->assertSuccessful();
        $this->assertSame(['Closing project of chapter 1', 'Closing project of chapter 2'],
            TrackCurriculumActivity::where('requires_video', true)->orderBy('sequence_order')->pluck('title')->all());
        $this->assertFalse((bool) TrackCurriculumActivity::where('title', 'First project')->value('requires_video'));
    }

    public function test_submitting_a_video_project_needs_a_working_public_youtube_link(): void
    {
        $this->project->update(['requires_video' => true]);
        $progress = $this->start($this->project);
        Http::fake([
            'www.youtube.com/oembed?*aaaaaaaaaaa*' => Http::response(['title' => 'Chapter 2 project explained', 'author_name' => 'Ada Data', 'author_url' => 'https://www.youtube.com/@ada'], 200),
            'www.youtube.com/oembed?*bbbbbbbbbbb*' => Http::response('Unauthorized', 401),
        ]);
        $base = ['evidence_text' => 'WHAT I FOUND: the median sale was 12,500 FCFA.', 'ai_used' => 'no'];

        $this->actingAs($this->fellow)->get(route('curriculum.submit.form', $progress))->assertOk()->assertSee('Show &amp; Tell video', false)->assertSee('Use the 4-part structure');

        $this->actingAs($this->fellow)->post(route('curriculum.submit', $progress), $base)->assertSessionHasErrors(['video_url', 'video_public']);
        $this->actingAs($this->fellow)->post(route('curriculum.submit', $progress), $base + ['video_url' => 'https://youtu.be/bbbbbbbbbbb', 'video_public' => '1'])->assertSessionHasErrors('video_url');
        $this->assertSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);

        $this->actingAs($this->fellow)->post(route('curriculum.submit', $progress), $base + ['video_url' => 'https://www.youtube.com/watch?v=aaaaaaaaaaa&t=3s', 'video_public' => '1'])->assertSessionHasNoErrors();
        $progress->refresh();
        $this->assertNotSame(CurriculumStatus::IN_PROGRESS, $progress->status);
        $this->assertSame('aaaaaaaaaaa', $progress->evidence['video']['id']);
        $this->assertSame('Chapter 2 project explained', $progress->show_tell_video['title']);
        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/aaaaaaaaaaa', $progress->show_tell_video['embed']);

        // The activity page shows the fellow's own video.
        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->project))->assertOk()->assertSee('Chapter 2 project explained');

        // The live check endpoint used by the form.
        $this->actingAs($this->fellow)->postJson(route('curriculum.video-check', $progress), ['url' => 'https://youtu.be/bbbbbbbbbbb'])->assertStatus(422)->assertJsonPath('ok', false);
    }

    public function test_activities_without_the_flag_do_not_ask_for_a_video(): void
    {
        $progress = $this->start($this->lesson);
        $this->actingAs($this->fellow)->get(route('curriculum.submit.form', $progress))->assertOk()->assertDontSee('name="video_url"', false);
        $this->actingAs($this->fellow)->post(route('curriculum.submit', $progress), ['evidence_text' => 'Done.', 'ai_used' => 'no'])->assertSessionHasNoErrors();
        $this->assertNotSame(CurriculumStatus::IN_PROGRESS, $progress->fresh()->status);
    }

    public function test_lesson_lists_the_base_camp_basics_it_needs_until_they_are_ready(): void
    {
        $this->areas();
        $links = app(FoundationLinkService::class);
        $needs = $links->needs($this->lesson);
        $this->assertArrayHasKey('maths', $needs);
        $this->assertContains('maths-averages-ratios', $needs['maths']['lessons']);
        $this->assertContains('maths-fractions-decimals-rounding', $needs['maths']['lessons']);

        $this->start($this->lesson);
        $card = $links->forFellow($this->fellow, $this->lesson);
        $this->assertSame(['maths'], array_column($card, 'key'));
        $this->assertStringContainsString('/base-camp/maths/lessons/maths-averages-ratios', collect($card[0]['lessons'])->pluck('url')->implode(' '));
        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->lesson))->assertOk()->assertSee('Before you start: basics this lesson uses')->assertSee('Averages and ratios');

        // Base Camp says where each area is needed next.
        $this->actingAs($this->fellow)->get(route('base-camp.index'))->assertOk()->assertSee('Needed in Chapter 2');

        // Once maths is ready, the card disappears.
        FoundationResult::create(['fellow_id' => $this->fellow->id, 'area' => 'maths', 'kind' => 'check', 'score' => 9, 'total' => 10, 'percent' => 90, 'passed' => true]);
        $this->assertSame([], $links->forFellow($this->fellow, $this->lesson));
        $this->actingAs($this->fellow)->get(route('curriculum.activity.show', $this->lesson))->assertOk()->assertDontSee('Before you start: basics this lesson uses');
    }

    public function test_clarity_feedback_keeps_only_words_the_fellow_wrote(): void
    {
        $c = app(PreSubmitCheckService::class)->clarity(['rating' => 'unclear', 'jargon' => ['IQR', 'p-value', 'groupby', 42], 'tip' => 'Say what the IQR means.'],
            'I used groupby and the IQR to find outliers.');
        $this->assertSame('unclear', $c['rating']);
        $this->assertSame(['IQR', 'groupby'], $c['jargon']);
        $this->assertNull(app(PreSubmitCheckService::class)->clarity(['rating' => 'clear'], '  '));
        $this->assertSame('okay', app(PreSubmitCheckService::class)->clarity(['rating' => 'brilliant'], 'text')['rating']);
    }
}
