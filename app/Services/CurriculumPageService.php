<?php

namespace App\Services;

use App\Enums\CurriculumStatus;
use App\Models\FellowCurriculumProgress;
use App\Models\FellowTrack;
use App\Models\FlashCard;
use App\Models\Track;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The interactive curriculum page: the fellow's single next step, where
 * classmates are on the chapter map, and who recently finished each activity.
 * Classmates who opted out of the track feed are never shown.
 */
class CurriculumPageService
{
    protected const WAITING = ['submitted', 'peer_review', 'under_review'];

    protected function status($progress): string
    {
        return $progress?->status?->value ?? (string) ($progress?->status ?? 'locked');
    }

    /** Activities in curriculum order with their chapter number and progress. */
    protected function ordered(Collection $milestones, Collection $progress): Collection
    {
        return $milestones->values()->flatMap(fn ($m, $i) => ($m->curriculumActivities ?? collect())->sortBy('sequence_order')
            ->map(fn ($a) => ['activity' => $a, 'milestone' => $m, 'chapter' => $i + 1, 'progress' => $progress->firstWhere('curriculum_activity_id', $a->id)]))->values();
    }

    /** The one thing to do now. */
    public function nextStep(User $fellow, Track $track, Collection $milestones, Collection $progress, bool $orientationRequired): array
    {
        if ($orientationRequired) {
            return ['kind' => 'orientation', 'title' => 'Start your orientation', 'url' => route('curriculum.orientation'), 'cta' => 'Begin orientation',
                'note' => 'About 20 minutes. Meet the team, see the whole path to senior level, and plan your weeks. Chapter 1 opens when you finish.'];
        }

        $items = $this->ordered($milestones, $progress);
        $with = fn (string $status) => $items->filter(fn ($i) => $this->status($i['progress']) === $status);

        if ($fix = $with('rejected')->first()) {
            return $this->card('fix', $fix, 'Fix and resubmit', 'The team sent this back with feedback. Read it, improve your work and resubmit.');
        }
        $working = $with('in_progress')->merge($with('overdue'))->sortBy(fn ($i) => $i['progress']->deadline_at?->timestamp ?? PHP_INT_MAX);
        if ($go = $working->first()) {
            return $this->card('continue', $go, 'Continue', null);
        }
        if ($start = $with('available')->first()) {
            return $this->card('start', $start, 'Start', null);
        }
        $waiting = $items->filter(fn ($i) => in_array($this->status($i['progress']), self::WAITING, true))->count();
        if ($waiting) {
            return ['kind' => 'waiting', 'title' => $waiting . ' ' . ($waiting === 1 ? 'activity is' : 'activities are') . ' with the reviewers', 'url' => null, 'cta' => null,
                'note' => 'Nothing new is open yet. Use the time for your daily review, the Practice Lab or this week\'s case.'];
        }
        if ($items->isNotEmpty() && $items->every(fn ($i) => $this->status($i['progress']) === 'completed' || !$i['activity']->is_required)) {
            return ['kind' => 'done', 'title' => 'You finished the track!', 'url' => null, 'cta' => null,
                'note' => 'Present your capstone at the next Presentation Day and share your portfolio with employers.'];
        }

        return ['kind' => 'none', 'title' => 'Your next activity opens soon', 'url' => null, 'cta' => null, 'note' => null];
    }

    protected function card(string $kind, array $item, string $cta, ?string $note): array
    {
        $p = $item['progress'];
        $deadline = $p?->deadline_at;
        $left = null;
        if ($deadline && $kind !== 'fix') {
            $hours = (int) now()->diffInHours($deadline, false);
            $left = $hours < 0 ? 'Past the deadline: submit as soon as you can (late penalty applies)'
                : ($hours < 24 ? 'Due in ' . max(1, $hours) . ' ' . ($hours === 1 ? 'hour' : 'hours') : 'Due in ' . intdiv($hours, 24) . ' ' . (intdiv($hours, 24) === 1 ? 'day' : 'days'));
        }

        return [
            'kind' => $kind, 'title' => $item['activity']->title, 'url' => route('curriculum.activity.show', $item['activity']), 'cta' => $cta,
            'chapter' => $item['chapter'],
            'chapter_title' => preg_replace('/^Chapter\s+\d+\s*[:·.-]\s*/i', '', (string) ($item['milestone']->story['chapter'] ?? $item['milestone']->title)),
            'type' => trim(($item['activity']->type?->icon() ?? '') . ' ' . ($item['activity']->type?->label() ?? '')),
            'points' => (int) $item['activity']->points, 'deadline' => $left, 'urgent' => $deadline && now()->diffInHours($deadline, false) < 24,
            'note' => $note,
        ];
    }

    /** The current chapter of the fellow (index into $milestones), i.e. the first with unfinished required work. */
    public function currentChapterIndex(Collection $milestones, Collection $completedIds): ?int
    {
        foreach ($milestones->values() as $i => $m) {
            $ids = ($m->curriculumActivities ?? collect())->where('is_required', true)->pluck('id');
            if ($ids->isEmpty() || $ids->contains(fn ($id) => !$completedIds->has($id))) {
                return $i;
            }
        }

        return null; // finished
    }

    /**
     * Classmates per chapter (by milestone id) plus who finished each activity.
     *
     * @return array{chapters: array<string, array>, finished: array, activities: array<string, array{count: int, latest: ?string, when: ?string}>}
     */
    public function classmates(User $fellow, Track $track, Collection $milestones): array
    {
        $fellowIds = FellowTrack::where('track_id', $track->id)->where('fellow_id', '!=', $fellow->id)->pluck('fellow_id');
        $people = User::whereIn('id', $fellowIds)->where('show_in_track_feed', true)->get(['id', 'name'])->keyBy('id');
        $activityIds = $milestones->flatMap(fn ($m) => ($m->curriculumActivities ?? collect())->pluck('id'));
        if ($people->isEmpty() || $activityIds->isEmpty()) {
            return ['chapters' => [], 'finished' => [], 'activities' => []];
        }

        $done = FellowCurriculumProgress::whereIn('fellow_id', $people->keys())->whereIn('curriculum_activity_id', $activityIds)
            ->where('status', CurriculumStatus::COMPLETED->value)->get(['fellow_id', 'curriculum_activity_id', 'completed_at', 'reviewed_at']);

        $chapters = [];
        $finished = [];
        foreach ($done->groupBy('fellow_id')->union($people->keys()->mapWithKeys(fn ($id) => [$id => collect()])) as $id => $rows) {
            if (!$people->has($id)) {
                continue;
            }
            $person = $this->person($people[$id]);
            $index = $this->currentChapterIndex($milestones, collect($rows)->pluck('curriculum_activity_id')->flip());
            if ($index === null) {
                $finished[] = $person;
            } else {
                $chapters[$milestones->values()[$index]->id][] = $person;
            }
        }

        $activities = [];
        foreach ($done->groupBy('curriculum_activity_id') as $activityId => $rows) {
            $latest = $rows->sortByDesc(fn ($r) => ($r->completed_at ?? $r->reviewed_at)?->timestamp ?? 0)->first();
            $when = $latest->completed_at ?? $latest->reviewed_at;
            $recent = $when && $when->gt(now()->subDays(30));
            $activities[$activityId] = [
                'count' => $rows->count(),
                'latest' => $recent ? $this->person($people[$latest->fellow_id])['first'] : null,
                'when' => $recent ? $when->diffForHumans(['parts' => 1]) : null,
            ];
        }

        return ['chapters' => $chapters, 'finished' => $finished, 'activities' => $activities];
    }

    protected function person(User $u): array
    {
        $parts = preg_split('/\s+/', trim($u->name));

        return ['first' => $parts[0] ?? '', 'initials' => mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1))];
    }

    /** Small extras for the next-step card: daily review left and this week's case. */
    public function extras(User $fellow, Track $track): array
    {
        $out = [];
        if (FlashCard::where('track_id', $track->id)->where('is_active', true)->whereNull('curriculum_activity_id')->exists()) {
            $out['review_left'] = app(FlashReviewService::class)->todaysCards($fellow, $track)->count();
        }
        $detective = app(DetectiveService::class);
        if ($case = $detective->caseOfTheWeek($track)) {
            $out['case'] = ['title' => $case->title, 'url' => route('detective.show', $case), 'solved' => $detective->isSolved($fellow, $case)];
        }

        return $out;
    }
}
