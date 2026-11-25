@extends('layouts.app')

@section('title', 'My Curriculum')

@section('content')
@php
    $trackExperience = \App\Support\TrackExperience::for($currentTrack);
    $classmates = $classmates ?? ['chapters' => [], 'finished' => [], 'activities' => []];
    $ns = $nextStep ?? null;
@endphp
<style>
    .ns-card { position: relative; overflow: hidden; border-radius: 18px; padding: 22px; border: 1px solid color-mix(in srgb, var(--color-primary-500) 40%, transparent);
        background: radial-gradient(120% 140% at 0% 0%, color-mix(in srgb, var(--color-primary-600) 30%, #0f141d) 0%, #0f141d 60%); }
    .ns-card::after { content: ''; position: absolute; right: -60px; top: -60px; width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, color-mix(in srgb, var(--color-primary-400) 25%, transparent), transparent 70%); pointer-events: none; }
    .ns-icon { width: 58px; height: 58px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;
        background: color-mix(in srgb, var(--color-primary-500) 22%, transparent); border: 1px solid color-mix(in srgb, var(--color-primary-400) 35%, transparent); }
    .ns-kicker { color: var(--color-primary-300); font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .ns-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 99px; font-size: 12.5px; background: rgba(148,163,184,.1); color: rgb(203 213 225); }
    .ns-chip.urgent { background: rgba(248,113,113,.14); color: #fca5a5; }
    .ns-extra { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 10px; background: rgba(11,15,22,.55); border: 1px solid rgba(148,163,184,.12); font-size: 13.5px; color: rgb(203 213 225); }
    .ns-extra:hover { border-color: color-mix(in srgb, var(--color-primary-400) 50%, transparent); color: #fff; }
    .cm-avatars { display: flex; align-items: center; }
    .cm-avatars span { width: 26px; height: 26px; border-radius: 50%; margin-left: -7px; display: flex; align-items: center; justify-content: center; font-size: 10.5px; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-800)); border: 2px solid #0f141d; }
    .cm-avatars span:first-child { margin-left: 0; }
    .ms-toggle { width: 100%; text-align: left; }
    .ms-chev { transition: transform .2s; color: rgb(100 116 139); }
    .ms-chev.open { transform: rotate(90deg); }
    .cm-here { display: none; } @media (min-width: 640px) { .cm-here { display: flex; } }
    .ch0 { border-radius: 16px; padding: 16px 18px; border: 1px dashed color-mix(in srgb, var(--color-primary-400) 50%, transparent); background: color-mix(in srgb, var(--color-primary-600) 8%, #0f141d); }
</style>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">My Curriculum</h1>
            <p class="text-dark-400 mt-1">{{ $currentTrack->name }}: structured track progression</p>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($fellowTracks) && $fellowTracks->count() > 1)
                <span class="text-xs text-dark-500">Switch tracks from the header</span>
            @endif
            <a href="{{ route('curriculum.orientation') }}" class="btn-secondary text-sm">🪪 Orientation</a>
            <a href="{{ route('curriculum.badges') }}" class="btn-secondary text-sm">🏅 Badges</a>
        </div>
    </div>

    @foreach(['success' => 'green', 'error' => 'red', 'warning' => 'amber'] as $key => $color)
        @if(session($key))<div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>@endif
    @endforeach

    @php
        $onHold = app(\App\Services\StandingService::class)->onHold(auth()->user());
        $baseGaps = app(\App\Services\FoundationService::class)->gaps(auth()->user());
    @endphp
    @if($onHold)
        <div class="p-4 rounded-xl border bg-red-500/10 border-red-500/30 flex flex-wrap items-center justify-between gap-3">
            <p class="text-red-300 text-sm"><strong>⛔ Progress on hold.</strong> You can read lessons, review and ask for help, but starting and submitting activities waits until your weekly-bar penalty is paid or waived.</p>
            <a href="{{ route('standing.show') }}" class="btn-primary text-sm">What to do</a>
        </div>
    @endif
    @if($baseGaps && !$orientationRequired)
        <a href="{{ route($baseGaps === ['skills check'] ? 'base-camp.check' : 'base-camp.index') }}" class="ch0 flex flex-wrap items-center gap-3 hover:border-primary-400">
            <span class="text-2xl">⛺</span>
            <span class="flex-1 min-w-0 text-dark-200 text-sm">{!! $baseGaps === ['skills check'] ? '<strong class="text-white">Take your 15-minute skills check.</strong> Typing, computer basics, maths, reading and spreadsheets: we find gaps early so no chapter feels too steep.' : '<strong class="text-white">Base Camp:</strong> strengthen ' . e(implode(', ', $baseGaps)) . ' alongside your chapters.' !!}</span>
            <span class="text-primary-300 text-sm">Open →</span>
        </a>
    @endif

    {{-- YOUR NEXT STEP --}}
    @if($ns && $milestones->isNotEmpty())
    <div class="ns-card">
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-5" style="z-index: 1;">
            <div class="ns-icon" aria-hidden="true">{{ ['orientation' => '🪪', 'fix' => '🛠️', 'continue' => '▶️', 'start' => '🚀', 'waiting' => '⏳', 'done' => '🏆'][$ns['kind']] ?? '🧭' }}</div>
            <div class="flex-1 min-w-0">
                <p class="ns-kicker">{{ ['orientation' => 'Before Chapter 1', 'fix' => 'Needs your attention', 'continue' => 'Your next step', 'start' => 'Up next', 'waiting' => 'In review', 'done' => 'Track complete'][$ns['kind']] ?? 'Next' }}</p>
                <h2 class="text-xl font-bold text-white mt-1">{{ $ns['title'] }}</h2>
                @if(!empty($ns['chapter']))
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="ns-chip">Chapter {{ $ns['chapter'] }} · {{ \Illuminate\Support\Str::limit($ns['chapter_title'], 48) }}</span>
                        @if($ns['type'])<span class="ns-chip">{{ $ns['type'] }}</span>@endif
                        <span class="ns-chip">🏆 {{ $ns['points'] }} pts</span>
                        @if($ns['deadline'])<span class="ns-chip {{ $ns['urgent'] ? 'urgent' : '' }}">⏰ {{ $ns['deadline'] }}</span>@endif
                    </div>
                @endif
                @if(!empty($ns['note']))<p class="text-dark-300 text-sm mt-2">{{ $ns['note'] }}</p>@endif
                @if(!empty($ns['why']))
                    <p class="text-primary-200 text-sm mt-2">💼 Why it matters: builds <strong>{{ $ns['why']['skill']['label'] }}</strong>, asked for in {{ (int) round($ns['why']['skill']['frequency'] * 100) }}% of entry-level data job adverts.</p>
                @endif
            </div>
            @if($ns['url'])
                <a href="{{ $ns['url'] }}" class="btn-primary px-6 py-3 text-base flex-shrink-0">{{ $ns['cta'] }} →</a>
            @endif
        </div>
        @if(!empty($extras) && $ns['kind'] !== 'orientation')
            <div class="relative flex flex-wrap gap-2 mt-4" style="z-index: 1;">
                @isset($extras['review_left'])
                    <a href="{{ route('flash-review.index') }}" class="ns-extra">🔁 Daily review: <strong class="text-white">{{ $extras['review_left'] > 0 ? $extras['review_left'] . ' questions left today' : 'done for today ✓' }}</strong></a>
                @endisset
                @isset($extras['case'])
                    <a href="{{ $extras['case']['url'] }}" class="ns-extra">🕵️ This week's case: <strong class="text-white">{{ $extras['case']['title'] }}</strong>{{ $extras['case']['solved'] ? ' · solved ✓' : '' }}</a>
                @endisset
                @if($peerReviews->count() > 0)
                    <a href="{{ route('curriculum.peer-review.form', $peerReviews->first()->id) }}" class="ns-extra">📝 <strong class="text-white">{{ $peerReviews->count() }}</strong> peer {{ Str::plural('review', $peerReviews->count()) }} waiting for you</a>
                @endif
            </div>
        @endif
    </div>
    @endif

    @if(!empty($career))
        <a href="{{ route('career.index') }}" class="card p-4 flex flex-wrap items-center gap-4 hover:border-primary-500/40">
            <div class="relative w-14 h-14 flex-shrink-0">
                <svg viewBox="0 0 36 36" class="w-14 h-14"><circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(148,163,184,.2)" stroke-width="4"/>
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="var(--color-primary-400)" stroke-width="4" stroke-linecap="round" stroke-dasharray="{{ round(97.4 * $career['percent'] / 100, 1) }} 97.4" transform="rotate(-90 18 18)"/></svg>
                <span class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold">{{ $career['percent'] }}%</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold">💼 Job-ready for {{ $career['role']['title'] }}: {{ $career['percent'] }}%</p>
                <p class="text-dark-400 text-sm">{{ $career['offer'] ? '📨 Mock offer unlocked! ' : '' }}{{ $career['gap'] ? 'Biggest gap: ' . $career['gap']['label'] . ' (asked in ' . (int) round($career['gap']['role_frequency'] * 100) . '% of adverts).' : 'You have proof for everything the program teaches for this role.' }} Measured against real job adverts.</p>
            </div>
            <span class="text-primary-300 text-sm">Job Market →</span>
        </a>
    @endif

    @if($trackExperience?->hasStory())
        @include('partials.track.journey', ['experience' => $trackExperience, 'milestones' => $milestones, 'progress' => $progress, 'activeMilestone' => $activeMilestone ?? null])
    @endif

    <!-- Streak & Partner Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Streak --}}
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center text-2xl">
                    {{ $streakSummary['tier_icon'] ?? '🔥' }}
                </div>
                <div>
                    <p class="text-white font-bold text-lg">{{ $streakSummary['current_streak'] ?? 0 }} week streak</p>
                    <p class="text-dark-400 text-sm">
                        {{ $streakSummary['tier_label'] ?? 'Building' }}
                        · {{ $streakSummary['multiplier'] ?? '1.0' }}x multiplier
                    </p>
                </div>
            </div>
            @if(($streakSummary['weeks_to_next_tier'] ?? null) > 0)
            <div class="mt-3">
                <div class="flex justify-between text-xs text-dark-400 mb-1">
                    <span>Next tier</span>
                    <span>{{ $streakSummary['weeks_to_next_tier'] }} weeks</span>
                </div>
                <div class="w-full bg-dark-800 rounded-full h-1.5">
                    @php $streakPct = min(100, (($streakSummary['current_streak'] ?? 0) / max(1, ($streakSummary['current_streak'] ?? 0) + ($streakSummary['weeks_to_next_tier'] ?? 1))) * 100); @endphp
                    <div class="bg-amber-500 rounded-full h-1.5" style="width: {{ $streakPct }}%"></div>
                </div>
            </div>
            @endif
        </div>

        {{-- Accountability Partner --}}
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-2xl">🤝</div>
                <div>
                    @if($partner)
                        <p class="text-white font-bold">{{ $partner->name }}</p>
                        <p class="text-dark-400 text-sm">Accountability Partner</p>
                    @else
                        <p class="text-dark-400 font-medium">No Partner Yet</p>
                        <p class="text-dark-500 text-sm">Pairing happens automatically</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Overall progress --}}
        @php
            $totalActivities = $progress->count();
            $completedActivities = $progress->where('status', 'completed')->count();
            $overallPct = $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;
        @endphp
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-white font-semibold">Overall progress</p>
                <span class="text-primary-400 font-bold text-sm">{{ $completedActivities }}/{{ $totalActivities }}</span>
            </div>
            <div class="w-full bg-dark-800 rounded-full h-2.5 mt-3">
                <div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-full h-2.5 transition-all duration-500" style="width: {{ $overallPct }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-dark-400 mt-2">
                <span>{{ number_format($overallPct, 0) }}% complete</span>
                <span>{{ $totalPoints ?? 0 }} points earned</span>
            </div>
        </div>
    </div>

    {{-- CHAPTER 0: ORIENTATION --}}
    @if($milestones->isNotEmpty())
    <div class="ch0 flex flex-wrap items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl {{ $orientationDone ? 'bg-green-500/20' : 'bg-primary-500/20' }}">{{ $orientationDone ? '✅' : '🪪' }}</div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-dark-400 text-sm font-mono">M0</span>
                <h3 class="text-lg font-semibold text-white">Orientation: your path to senior level</h3>
                @if($orientationDone)
                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Complete</span>
                @elseif($orientationRequired)
                    <span class="px-2 py-0.5 bg-primary-500/20 text-primary-300 text-xs rounded-full">Start here</span>
                @else
                    <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 text-xs rounded-full">New · recommended</span>
                @endif
            </div>
            <p class="text-dark-400 text-sm mt-0.5">
                @if($orientationDone)
                    Revisit the career ladder, your toolkit and your commitment any time.
                @else
                    Meet the team, see what seniors master and where you'll learn it, learn how the program works, and plan your weeks. About 20 minutes.
                @endif
            </p>
        </div>
        <a href="{{ route('curriculum.orientation') }}" class="{{ $orientationDone ? 'btn-secondary' : 'btn-primary' }} text-sm px-4 py-2">{{ $orientationDone ? 'Open' : 'Begin' }} →</a>
    </div>
    @endif

    <!-- Milestones -->
    @foreach($milestones as $milestone)
    @php
        $msActivities = $milestone->curriculumActivities ?? collect();
        $msProgress = $progress->whereIn('curriculum_activity_id', $msActivities->pluck('id'));
        $msCompleted = $msProgress->where('status', 'completed')->count();
        $msTotal = $msActivities->count();
        $msPct = $msTotal > 0 ? ($msCompleted / $msTotal) * 100 : 0;

        $isUnlocked = $milestone->isUnlockedFor(auth()->user());
        $isComplete = $msTotal > 0 && $msCompleted === $msTotal;
        $needsAction = $msProgress->contains(fn ($p) => in_array($p->status?->value ?? $p->status, ['in_progress', 'rejected', 'overdue'], true));
        $openByDefault = ($isUnlocked || $isComplete) && (($currentMilestoneId ?? null) === $milestone->id || $needsAction);
        $here = $classmates['chapters'][$milestone->id] ?? [];
        $builds = $msActivities->filter(fn ($a) => in_array($a->type?->value, ['project', 'case_study'], true))->pluck('title');
    @endphp
    <div id="milestone-{{ $milestone->id }}" class="card overflow-hidden {{ !$isUnlocked && !$isComplete ? 'opacity-70' : '' }}" x-data="{ open: @js($openByDefault) }">
        <!-- Milestone Header -->
        <button type="button" class="ms-toggle p-5 {{ ($isUnlocked || $isComplete) ? 'cursor-pointer hover:bg-dark-800/30' : 'cursor-default' }}" @click="{{ ($isUnlocked || $isComplete) ? 'open = !open' : '' }}" :aria-expanded="open">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl
                    {{ $isComplete ? 'bg-green-500/20' : ($isUnlocked ? 'bg-primary-500/20' : 'bg-dark-700') }}">
                    {{ $isComplete ? '✅' : ($milestone->badge_icon ?? '🏁') }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-dark-400 text-sm font-mono">M{{ $milestone->sequence_order }}</span>
                        <h3 class="text-lg font-semibold text-white">{{ $milestone->title }}</h3>
                        @if($isComplete)
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Complete</span>
                        @elseif(!$isUnlocked)
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">🔒 Locked</span>
                        @elseif(($currentMilestoneId ?? null) === $milestone->id)
                            <span class="px-2 py-0.5 bg-primary-500/20 text-primary-300 text-xs rounded-full">You are here</span>
                        @endif
                    </div>
                    @if($milestone->description)
                    <p class="text-dark-400 text-sm mt-0.5">{{ \App\Support\LessonMarkdown::plain($milestone->description, 120) }}</p>
                    @endif
                    @if(!$isUnlocked && !$isComplete && $builds->isNotEmpty())
                        <p class="text-dark-300 text-sm mt-1">🔓 What you'll build here: <span class="text-white">{{ $builds->take(2)->implode(' · ') }}</span>{{ $builds->count() > 2 ? ' and more' : '' }}</p>
                    @endif
                </div>
                @if($here)
                    <div class="cm-here flex-col items-end gap-1" title="{{ collect($here)->pluck('first')->implode(', ') }}">
                        <div class="cm-avatars">
                            @foreach(array_slice($here, 0, 4) as $p)<span>{{ $p['initials'] }}</span>@endforeach
                        </div>
                        <span class="text-dark-500 text-xs">{{ count($here) }} {{ count($here) === 1 ? 'classmate is' : 'classmates are' }} here</span>
                    </div>
                @endif
                <div class="text-right">
                    <p class="text-white font-bold">{{ $msCompleted }}/{{ $msTotal }}</p>
                    <p class="text-dark-500 text-xs">activities</p>
                </div>
                @if($isUnlocked || $isComplete)
                    <span class="ms-chev text-xl" :class="open && 'open'" aria-hidden="true">›</span>
                @endif
            </div>
            @if($msTotal > 0)
            <div class="w-full bg-dark-800 rounded-full h-1.5 mt-4">
                <div class="rounded-full h-1.5 transition-all duration-500
                    {{ $isComplete ? 'bg-green-500' : 'bg-primary-500' }}"
                     style="width: {{ $msPct }}%"></div>
            </div>
            @endif
        </button>

        <!-- Activities -->
        @if($isUnlocked || $isComplete)
        <div class="divide-y divide-dark-700/50 border-t border-dark-700" x-show="open" x-cloak>
            @foreach($msActivities->sortBy('sequence_order') as $activity)
            @php
                $actProgress = $msProgress->firstWhere('curriculum_activity_id', $activity->id);
                $actStatus = $actProgress?->status?->value ?? $actProgress?->status ?? 'locked';
                $peers = $classmates['activities'][$activity->id] ?? null;
            @endphp
            <div class="px-5 py-4 flex items-center gap-4 hover:bg-dark-800/30 transition">
                {{-- Status Icon --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm
                    @switch($actStatus)
                        @case('completed') bg-green-500/20 text-green-400 @break
                        @case('in_progress') bg-blue-500/20 text-blue-400 @break
                        @case('submitted') @case('peer_review') @case('under_review') bg-purple-500/20 text-purple-400 @break
                        @case('available') bg-primary-500/20 text-primary-400 @break
                        @case('rejected') bg-red-500/20 text-red-400 @break
                        @case('overdue') bg-red-500/20 text-red-400 @break
                        @default bg-dark-700 text-dark-500 @break
                    @endswitch">
                    @switch($actStatus)
                        @case('completed') ✓ @break
                        @case('in_progress') ▶ @break
                        @case('submitted') @case('peer_review') @case('under_review') ⏳ @break
                        @case('available') ○ @break
                        @case('rejected') ✕ @break
                        @case('overdue') ⚠ @break
                        @default 🔒 @break
                    @endswitch
                </div>

                {{-- Activity Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-dark-500 text-xs font-mono">#{{ $activity->sequence_order }}</span>
                        <a href="{{ route('curriculum.activity.show', $activity) }}" class="text-white text-sm font-medium truncate hover:text-primary-300">{{ $activity->title }}</a>
                        @if($activity->is_required)
                            <span class="text-red-400 text-xs">*</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                        <span class="text-dark-500 text-xs">{{ $activity->type?->icon() ?? '' }} {{ $activity->type?->label() ?? '' }}</span>
                        <span class="text-dark-600 text-xs">•</span>
                        <span class="text-dark-500 text-xs">{{ $activity->difficulty_level?->icon() ?? '' }} {{ $activity->difficulty_level?->label() ?? '' }}</span>
                        @if($peers)
                            <span class="text-dark-600 text-xs">•</span>
                            <span class="text-dark-400 text-xs">👥 {{ $peers['count'] }} {{ $peers['count'] === 1 ? 'classmate' : 'classmates' }} done{{ $peers['latest'] ? ' · ' . $peers['latest'] . ', ' . $peers['when'] : '' }}</span>
                        @endif
                    </div>
                </div>

                {{-- Points --}}
                <div class="text-right mr-2">
                    <span class="text-dark-400 text-sm">{{ $activity->points ?? 0 }} pts</span>
                    @if($actProgress && $actStatus === 'completed')
                        <p class="text-green-400 text-xs font-medium">+{{ $actProgress->points_awarded ?? 0 }}</p>
                    @endif
                </div>

                {{-- Deadline --}}
                @if($actProgress && $actProgress->deadline_at)
                <div class="text-right mr-2 hidden sm:block">
                    @if($actProgress->isPastDeadline && $actStatus !== 'completed')
                        <span class="text-red-400 text-xs">Overdue</span>
                    @elseif($actStatus !== 'completed' && $actStatus !== 'locked')
                        <span class="text-dark-500 text-xs">{{ $actProgress->daysRemaining ?? '' }}d left</span>
                    @endif
                </div>
                @endif

                {{-- Action --}}
                <div>
                    @if($actStatus === 'available')
                        <a href="{{ route('curriculum.activity.show', $activity) }}" class="btn-primary text-xs px-3 py-1.5">Start</a>
                    @elseif($actStatus === 'in_progress')
                        <a href="{{ route('curriculum.activity.show', $activity) }}" class="btn-primary text-xs px-3 py-1.5">Continue</a>
                    @elseif($actStatus === 'rejected')
                        <a href="{{ route('curriculum.activity.show', $activity) }}" class="px-3 py-1.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg text-xs hover:bg-red-500/30 transition">Resubmit</a>
                    @elseif(in_array($actStatus, ['submitted', 'peer_review', 'under_review']))
                        <span class="text-dark-400 text-xs px-3 py-1.5 bg-dark-800 rounded-lg">In Review</span>
                    @elseif($actStatus === 'completed')
                        <a href="{{ route('curriculum.activity.show', $activity) }}" class="text-dark-400 hover:text-white text-xs transition">View</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 pb-5 text-center">
            <p class="text-dark-500 text-sm">
                🔒 Complete
                @if($milestone->prerequisiteMilestone)
                    <strong>{{ $milestone->prerequisiteMilestone->title }}</strong>
                @else
                    the previous milestone
                @endif
                to unlock this milestone.
            </p>
        </div>
        @endif
    </div>
    @endforeach

    @if(!empty($classmates['finished']))
        <div class="card p-4 flex items-center gap-3">
            <div class="cm-avatars">@foreach(array_slice($classmates['finished'], 0, 5) as $p)<span>{{ $p['initials'] }}</span>@endforeach</div>
            <p class="text-dark-300 text-sm">🏆 {{ collect($classmates['finished'])->pluck('first')->take(3)->implode(', ') }}{{ count($classmates['finished']) > 3 ? ' and ' . (count($classmates['finished']) - 3) . ' more' : '' }} finished the whole track.</p>
        </div>
    @endif

    @if($milestones->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-4xl mb-4">📚</div>
        <h3 class="text-white font-semibold text-lg">Curriculum Coming Soon</h3>
        <p class="text-dark-400 mt-1">The curriculum for this track is being prepared. Check back soon!</p>
    </div>
    @endif
</div>
@endsection
