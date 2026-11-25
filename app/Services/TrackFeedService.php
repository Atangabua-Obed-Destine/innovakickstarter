<?php

namespace App\Services;

use App\Models\FellowCurriculumProgress;
use App\Models\Presentation;
use App\Models\Track;
use App\Models\TrackFeedEvent;
use App\Models\TrackFeedKudo;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Support\TrackExperience;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The track community feed. Events are recorded once per achievement; fellows
 * who hide themselves from the feed are filtered out when it is shown.
 * Recording never breaks the action that triggered it.
 */
class TrackFeedService
{
    public const STREAK_MILESTONES = [7, 14, 30, 60, 100];

    public function record(User $fellow, string $trackId, string $type, string $title, ?string $body = null, ?string $url = null, ?string $subjectKey = null): ?TrackFeedEvent
    {
        try {
            return TrackFeedEvent::firstOrCreate(
                ['fellow_id' => $fellow->id, 'type' => $type, 'subject_key' => $subjectKey ?? md5($title)],
                ['track_id' => $trackId, 'title' => $title, 'body' => $body, 'url' => $this->safeUrl($url)]
            );
        } catch (QueryException $e) {
            report($e);

            return null;
        }
    }

    public function chapterCompleted(User $fellow, TrackMilestone $milestone): void
    {
        $chapter = $milestone->story['chapter'] ?? $milestone->title;
        $this->record($fellow, $milestone->track_id, 'chapter', "{$this->firstName($fellow)} completed {$chapter}", null, null, "milestone:{$milestone->id}");
    }

    /** Approved projects, case studies and Brand posts (with the public link when there is one). */
    public function workApproved(FellowCurriculumProgress $progress): void
    {
        $activity = $progress->curriculumActivity;
        $type = $activity?->type?->value ?? (string) $activity?->type;
        $name = $this->firstName($progress->fellow);
        $url = $progress->evidence_url;

        match (true) {
            $type === 'blog_post' => $this->record($progress->fellow, $activity->track_id, 'brand_post', "{$name} published a Brand post: {$activity->title}",
                $url ? 'Read it and leave a comment to support them.' : null, $url, "activity:{$activity->id}"),
            in_array($type, ['project', 'case_study'], true) && $progress->show_tell_video => $this->record($progress->fellow, $activity->track_id, 'project', "{$name} completed {$activity->title}",
                '🎬 Watch the Show & Tell video and leave a comment to support them.', $progress->show_tell_video['watch'], "activity:{$activity->id}"),
            in_array($type, ['project', 'case_study'], true) => $this->record($progress->fellow, $activity->track_id, 'project', "{$name} completed {$activity->title}",
                null, $this->isPortfolioLink($url) ? $url : null, "activity:{$activity->id}"),
            default => null,
        };
    }

    public function presentationApproved(Presentation $presentation): void
    {
        $this->record($presentation->fellow, $presentation->track_id, 'presentation',
            "{$this->firstName($presentation->fellow)} presented \"{$presentation->title}\"", "{$presentation->kindLabel()} at {$presentation->event_name}", null, "presentation:{$presentation->id}");
    }

    public function levelUp(User $fellow, Track $track, string $tier): void
    {
        $title = TrackExperience::for($track)?->levelTitle($tier) ?? ucfirst($tier);
        $this->record($fellow, $track->id, 'level_up', "{$this->firstName($fellow)} is now a {$title}", null, null, "tier:{$track->id}:{$tier}");
    }

    public function practiceStreak(User $fellow, string $trackId, int $streak): void
    {
        if (in_array($streak, self::STREAK_MILESTONES, true)) {
            $this->record($fellow, $trackId, 'practice_streak', "{$this->firstName($fellow)} reached a {$streak}-day practice streak", null, null, "streak:{$trackId}:{$streak}:" . now()->format('Y-m-d'));
        }
    }

    public function feed(Track $track, User $viewer, int $perPage = 20): LengthAwarePaginator
    {
        return TrackFeedEvent::with('fellow:id,name')
            ->withCount('kudos')
            ->withExists(['kudos as given_kudos' => fn ($q) => $q->where('fellow_id', $viewer->id)])
            ->where('track_id', $track->id)
            ->whereHas('fellow', fn ($q) => $q->where('show_in_track_feed', true)->orWhere('id', $viewer->id))
            ->latest()
            ->paginate($perPage);
    }

    /** Toggle kudos; you can't give kudos to yourself. Returns [given, count]. */
    public function toggleKudos(User $fellow, TrackFeedEvent $event): array
    {
        abort_if($event->fellow_id === $fellow->id, 422, "You can't give kudos to yourself.");
        $existing = TrackFeedKudo::where('event_id', $event->id)->where('fellow_id', $fellow->id)->first();
        $existing ? $existing->delete() : TrackFeedKudo::create(['event_id' => $event->id, 'fellow_id' => $fellow->id]);

        return [!$existing, TrackFeedKudo::where('event_id', $event->id)->count()];
    }

    protected function firstName(?User $fellow): string
    {
        $parts = preg_split('/\s+/', trim((string) $fellow?->name));

        return ($parts[0] ?? 'A fellow') . (isset($parts[1]) ? ' ' . mb_substr($parts[1], 0, 1) . '.' : '');
    }

    protected function safeUrl(?string $url): ?string
    {
        return $url && preg_match('#^https?://#i', $url) && mb_strlen($url) <= 500 ? $url : null;
    }

    protected function isPortfolioLink(?string $url): bool
    {
        return (bool) ($url && preg_match('#^https?://(www\.)?(github\.com|lookerstudio\.google\.com|datastudio\.google\.com|kaggle\.com|colab\.research\.google\.com)/#i', $url));
    }
}
