@extends('layouts.app')

@section('title', 'My Curriculum')

@section('content')
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
            <a href="{{ route('curriculum.badges') }}" class="btn-secondary text-sm">🏅 Badges</a>
        </div>
    </div>

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

        {{-- Peer Reviews Pending --}}
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-2xl">📝</div>
                <div>
                    <p class="text-white font-bold text-lg">{{ $peerReviews->count() }}</p>
                    <p class="text-dark-400 text-sm">Peer Reviews Pending</p>
                </div>
            </div>
            @if($peerReviews->count() > 0)
            <a href="{{ route('curriculum.peer-review.form', $peerReviews->first()->id) }}"
               class="mt-3 block w-full text-center px-3 py-1.5 bg-purple-500/20 text-purple-400 border border-purple-500/30 rounded-lg hover:bg-purple-500/30 text-sm transition">
                Review Now →
            </a>
            @endif
        </div>
    </div>

    <!-- Overall Progress -->
    @php
        $totalActivities = $progress->count();
        $completedActivities = $progress->where('status', 'completed')->count();
        $overallPct = $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;
    @endphp
    <div class="card p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-white font-semibold">Overall Progress</h3>
            <span class="text-primary-400 font-bold">{{ $completedActivities }}/{{ $totalActivities }} activities</span>
        </div>
        <div class="w-full bg-dark-800 rounded-full h-3">
            <div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-full h-3 transition-all duration-500"
                 style="width: {{ $overallPct }}%"></div>
        </div>
        <div class="flex items-center justify-between text-sm text-dark-400 mt-2">
            <span>{{ number_format($overallPct, 0) }}% complete</span>
            <span>{{ $totalPoints ?? 0 }} points earned</span>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400">
        {{ session('error') }}
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
    @endphp
    <div class="card overflow-hidden {{ !$isUnlocked && !$isComplete ? 'opacity-60' : '' }}">
        <!-- Milestone Header -->
        <div class="p-5 border-b border-dark-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl
                    {{ $isComplete ? 'bg-green-500/20' : ($isUnlocked ? 'bg-primary-500/20' : 'bg-dark-700') }}">
                    {{ $isComplete ? '✅' : ($milestone->badge_icon ?? '🏁') }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-dark-400 text-sm font-mono">M{{ $milestone->sequence_order }}</span>
                        <h3 class="text-lg font-semibold text-white">{{ $milestone->title }}</h3>
                        @if($isComplete)
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Complete</span>
                        @elseif(!$isUnlocked)
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">🔒 Locked</span>
                        @endif
                    </div>
                    @if($milestone->description)
                    <p class="text-dark-400 text-sm mt-0.5">{{ Str::limit($milestone->description, 120) }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-white font-bold">{{ $msCompleted }}/{{ $msTotal }}</p>
                    <p class="text-dark-500 text-xs">activities</p>
                </div>
            </div>
            @if($msTotal > 0)
            <div class="w-full bg-dark-800 rounded-full h-1.5 mt-4">
                <div class="rounded-full h-1.5 transition-all duration-500
                    {{ $isComplete ? 'bg-green-500' : 'bg-primary-500' }}"
                     style="width: {{ $msPct }}%"></div>
            </div>
            @endif
        </div>

        <!-- Activities -->
        @if($isUnlocked || $isComplete)
        <div class="divide-y divide-dark-700/50">
            @foreach($msActivities->sortBy('sequence_order') as $activity)
            @php
                $actProgress = $msProgress->firstWhere('curriculum_activity_id', $activity->id);
                $actStatus = $actProgress?->status?->value ?? $actProgress?->status ?? 'locked';
                $actStatusLabel = $actProgress?->status?->label() ?? ucfirst($actStatus);
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
                        <p class="text-white text-sm font-medium truncate">{{ $activity->title }}</p>
                        @if($activity->is_required)
                            <span class="text-red-400 text-xs">*</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-0.5">
                        <span class="text-dark-500 text-xs">{{ $activity->type?->icon() ?? '' }} {{ $activity->type?->label() ?? '' }}</span>
                        <span class="text-dark-600 text-xs">•</span>
                        <span class="text-dark-500 text-xs">{{ $activity->difficulty_level?->icon() ?? '' }} {{ $activity->difficulty_level?->label() ?? '' }}</span>
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
        <div class="px-5 py-6 text-center">
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

    @if($milestones->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-4xl mb-4">📚</div>
        <h3 class="text-white font-semibold text-lg">Curriculum Coming Soon</h3>
        <p class="text-dark-400 mt-1">The curriculum for this track is being prepared. Check back soon!</p>
    </div>
    @endif
</div>
@endsection
