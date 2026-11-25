{{--
    Story journey: chapters (milestones) as a path, plus the manager's brief for the current chapter.
    @param \App\Support\TrackExperience $experience
    @param \Illuminate\Support\Collection $milestones
    @param \Illuminate\Support\Collection $progress   fellow progress rows
    @param \App\Models\TrackMilestone|null $activeMilestone
--}}
@include('partials.track.styles')
@php
    $user = auth()->user();
    $chapters = $milestones->map(function ($m, $i) use ($progress, $user, $activeMilestone) {
        $ids = ($m->curriculumActivities ?? collect())->where('is_required', true)->pluck('id');
        $done = $ids->isNotEmpty() && $progress->whereIn('curriculum_activity_id', $ids)->where('status', \App\Enums\CurriculumStatus::COMPLETED)->count() >= $ids->count();
        return ['milestone' => $m, 'number' => $i + 1, 'done' => $done, 'current' => false];
    });
    // The current chapter is the first one whose required work is not finished.
    $firstOpen = $chapters->search(fn ($c) => !$c['done']);
    if ($firstOpen !== false) {
        $chapters = $chapters->map(fn ($c, $i) => $i === $firstOpen ? ['current' => true] + $c : $c);
    }
    $manager = $experience->manager();
    $current = $chapters->firstWhere('current', true);
    $story = $current ? ($current['milestone']->story ?? []) : [];
    $allDone = $chapters->isNotEmpty() && $chapters->every(fn ($c) => $c['done']);
@endphp
@if($chapters->isNotEmpty())
<div class="card p-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="text-white font-semibold text-lg">Your journey{{ $experience->company() ? ' at ' . $experience->company() : '' }}</h3>
            <p class="text-dark-400 text-sm">{{ $chapters->where('done', true)->count() }} of {{ $chapters->count() }} chapters completed</p>
        </div>
    </div>
    <div class="tx-journey" role="list">
        @foreach($chapters as $c)
            <a href="#milestone-{{ $c['milestone']->id }}" role="listitem"
               class="tx-step {{ $c['done'] ? 'is-done' : '' }} {{ $c['current'] ? 'is-current' : '' }}"
               title="{{ $c['milestone']->story['chapter'] ?? $c['milestone']->title }}">
                <div class="tx-node">{{ $c['done'] ? '✓' : $c['number'] }}</div>
                <div class="tx-step-label">{{ \Illuminate\Support\Str::limit($c['milestone']->title, 34) }}</div>
            </a>
        @endforeach
    </div>

    @if($manager && ($allDone || !empty($story['brief'])))
        <div class="tx-brief">
            <div class="flex items-start gap-3">
                <div class="tx-avatar" aria-hidden="true">{{ $manager['initials'] }}</div>
                <div class="min-w-0">
                    <p class="text-white font-semibold text-sm">{{ $manager['name'] }} <span class="text-dark-400 font-normal">· {{ $manager['title'] }}</span></p>
                    @if($allDone)
                        <p class="text-dark-200 mt-2">You've completed every chapter. Thank you for everything you built with the team: your portfolio is ready to show employers. Present your capstone at the next Presentation Day!</p>
                    @else
                        <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider mt-1">{{ $story['chapter'] ?? $current['milestone']->title }}</p>
                        <div class="lesson-md mt-2">{{ \App\Support\LessonMarkdown::render($story['brief']) }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endif
