{{--
    On-screen view of an attestation record (the snapshot array).
    Shared by the admin review page and the public verification page.
    Expects: $record. Contact details and student ID are deliberately not shown.
--}}
@php
    $tracks = $record['tracks'] ?? [];
    $projects = $record['projects'] ?? [];
    $interviews = $record['interviews'] ?? ['count' => 0, 'items' => [], 'average_score' => null];
    $attendance = $record['attendance'] ?? null;
    $primary = $tracks[0] ?? null;
    $activitiesDone = collect($tracks)->sum(fn ($t) => $t['curriculum']['activities_completed']);
@endphp

<div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    @foreach([
        ['Curriculum activities', $activitiesDone],
        ['Projects & contributions', count($projects)],
        ['Mock interviews', $interviews['count']],
        ['Attendance', isset($attendance['rate']) ? $attendance['rate'] . '%' : '—'],
        ['Career Capital', $primary ? $primary['career_capital']['score'] . '% · ' . $primary['career_capital']['tier_label'] : '—'],
    ] as [$label, $value])
        <div class="rounded-lg border border-dark-700 bg-dark-800/40 p-3 text-center">
            <p class="text-lg font-bold text-white">{{ $value }}</p>
            <p class="text-dark-400 text-xs mt-1">{{ $label }}</p>
        </div>
    @endforeach
</div>

@foreach($tracks as $track)
    @if(!empty($track['curriculum']['milestones']))
        <div class="mt-6">
            <h4 class="text-white font-semibold">{{ $track['name'] }} curriculum</h4>
            <p class="text-dark-400 text-xs mt-1">
                {{ $track['curriculum']['required_completed'] }} of {{ $track['curriculum']['required_total'] }} required activities
                @if($track['curriculum']['average_score'] !== null) · average score {{ $track['curriculum']['average_score'] }}% @endif
            </p>
            @foreach($track['curriculum']['milestones'] as $milestone)
                <p class="text-dark-300 text-sm font-medium mt-3">{{ $milestone['title'] }} <span class="text-dark-500">({{ $milestone['completed'] }}/{{ $milestone['total'] }})</span></p>
                <ul class="mt-1 divide-y divide-dark-700/60">
                    @foreach($milestone['activities'] as $activity)
                        <li class="flex justify-between gap-4 py-1.5 text-sm">
                            <span class="text-dark-200">{{ $activity['title'] }} <span class="text-dark-500 text-xs">· {{ $activity['type'] }}</span></span>
                            <span class="text-dark-300 whitespace-nowrap">{{ $activity['score'] }}%</span>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    @endif
@endforeach

@if($projects)
    <div class="mt-6">
        <h4 class="text-white font-semibold">Projects &amp; contributions</h4>
        <ul class="mt-2 divide-y divide-dark-700/60">
            @foreach($projects as $project)
                <li class="py-1.5 text-sm">
                    <span class="text-dark-200">{{ $project['title'] }}</span>
                    <span class="text-dark-500 text-xs">· {{ $project['type'] }}@if($project['tech_stack']) · {{ implode(', ', $project['tech_stack']) }}@endif</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if($interviews['count'] > 0)
    <div class="mt-6">
        <h4 class="text-white font-semibold">Mock interviews</h4>
        <p class="text-dark-400 text-xs mt-1">
            {{ $interviews['count'] }} completed
            @if($interviews['average_score'] !== null) · average score {{ $interviews['average_score'] }}% @endif
        </p>
        <ul class="mt-2 divide-y divide-dark-700/60">
            @foreach($interviews['items'] as $interview)
                <li class="flex justify-between gap-4 py-1.5 text-sm">
                    <span class="text-dark-200">{{ $interview['title'] }}</span>
                    <span class="text-dark-300 whitespace-nowrap">{{ $interview['score'] !== null ? $interview['score'] . '%' : '—' }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if($attendance)
    <div class="mt-6">
        <h4 class="text-white font-semibold">Attendance</h4>
        <p class="text-dark-300 text-sm mt-1">
            {{ $attendance['present'] }} present, {{ $attendance['late'] }} late, {{ $attendance['absent'] }} absent, {{ $attendance['on_leave'] }} on leave
            out of {{ $attendance['sessions'] }} sessions · {{ $attendance['hours'] }} hours on site
        </p>
    </div>
@endif

@if(!empty($record['technologies']))
    <div class="mt-6">
        <h4 class="text-white font-semibold">Technologies used</h4>
        <div class="flex flex-wrap gap-1.5 mt-2">
            @foreach($record['technologies'] as $technology)
                <span class="px-2 py-1 text-xs bg-dark-700 text-dark-300 rounded-lg">{{ $technology }}</span>
            @endforeach
        </div>
    </div>
@endif
