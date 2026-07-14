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

    <!-- Streak, Partner, & Leaderboard Bar -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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

        {{-- Mentorship Pod --}}
        <div class="card p-4 flex flex-col">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center text-xl">👥</div>
                <div>
                    @php $pod = auth()->user()->activeMentorshipPod(); @endphp
                    @if($pod)
                        <p class="text-white font-bold">{{ $pod->name }}</p>
                        <p class="text-dark-400 text-xs">Mentorship Pod ({{ $pod->activeMembers()->count() }} members)</p>
                    @else
                        <p class="text-dark-400 font-medium">No Mentorship Pod Yet</p>
                        <p class="text-dark-500 text-xs">You will be assigned to a pod soon</p>
                    @endif
                </div>
            </div>
            
            @if($pod)
            <div class="space-y-2 mt-auto">
                {{-- Pod Lead (Mentor) --}}
                @if($pod->lead)
                <div class="flex items-center justify-between text-sm p-1.5 rounded-lg border border-amber-500/20 bg-amber-500/5">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center text-[10px] font-bold text-amber-400">
                            {{ strtoupper(substr($pod->lead->name, 0, 1)) }}
                        </div>
                        <span class="text-amber-400 font-medium truncate max-w-[100px]" title="{{ $pod->lead->name }}">
                            {{ explode(' ', $pod->lead->name)[0] }}
                            <span class="text-amber-400 ml-0.5 text-[10px]" title="Pod Lead">👑</span>
                        </span>
                    </div>
                    <span class="text-amber-500/50 text-xs">Mentor</span>
                </div>
                @endif
                
                {{-- Fellow Members --}}
                @foreach($pod->activeMembers as $membership)
                    @php $member = $membership->fellow; @endphp
                    <div class="flex items-center justify-between text-sm p-1.5 rounded-lg {{ $member->id === auth()->id() ? 'bg-blue-500/10 border border-blue-500/20' : '' }}">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-[10px] font-bold text-blue-400">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <span class="{{ $member->id === auth()->id() ? 'text-blue-400 font-medium' : 'text-dark-300' }} truncate max-w-[100px]" title="{{ $member->name }}">
                                {{ explode(' ', $member->name)[0] }}
                                @if($member->id === $pod->lead_id)
                                    <span class="text-amber-400 ml-0.5 text-[10px]" title="Pod Lead">👑</span>
                                @endif
                            </span>
                        </div>
                        <span class="text-dark-400 text-xs">{{ number_format($member->fellowTracks->firstWhere('track_id', $currentTrack->id)?->score ?? 0, 3) }}</span>
                    </div>
                @endforeach
            </div>
            @endif
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

        {{-- Leaderboard --}}
        <div class="card p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center text-xl">🏆</div>
                    <div>
                        <p class="text-white font-bold">Top Fellows</p>
                        <p class="text-dark-400 text-xs">Track Leaderboard</p>
                    </div>
                </div>
            </div>
            <div class="space-y-2 mt-2">
                @foreach($leaderboard as $index => $user)
                    <div class="flex items-center justify-between text-sm p-1.5 rounded-lg {{ $user->id === auth()->id() ? 'bg-primary-500/10 border border-primary-500/20' : '' }}">
                        <div class="flex items-center gap-2">
                            <span class="text-dark-500 font-mono text-xs w-4 text-center">{{ $index + 1 }}</span>
                            <span class="{{ $user->id === auth()->id() ? 'text-primary-400 font-medium' : 'text-dark-300' }} truncate max-w-[100px]" title="{{ $user->name }}">
                                {{ explode(' ', $user->name)[0] }}
                            </span>
                        </div>
                        <span class="text-amber-400 font-medium text-xs">{{ number_format($user->fellowTracks->first()->score ?? 0, 3) }}</span>
                    </div>
                @endforeach
            </div>
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
            <span>{{ number_format($overallPct, 3) }}% complete</span>
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

    <!-- FOCUS MODE: Current Active Milestone -->
    @if(isset($activeMilestone))
    <div class="mb-8">
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
            <span class="text-primary-400">🎯</span> Focus: Current Milestone
        </h2>
        @php
            $msActivities = $activeMilestone->curriculumActivities ?? collect();
            $msProgress = $progress->whereIn('curriculum_activity_id', $msActivities->pluck('id'));
            $msCompleted = $msProgress->where('status', 'completed')->count();
            $msTotal = $msActivities->count();
            $msPct = $msTotal > 0 ? ($msCompleted / $msTotal) * 100 : 0;
            $isUnlocked = true;
            $isComplete = $msTotal > 0 && $msCompleted === $msTotal;
        @endphp
        <div class="card overflow-hidden border border-primary-500/30 shadow-[0_0_15px_rgba(59,130,246,0.1)]">
            <!-- Milestone Header -->
            <div class="p-6 border-b border-dark-700 bg-gradient-to-r from-primary-500/10 to-transparent">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl bg-primary-500/20">
                        {{ $activeMilestone->badge_icon ?? '🏁' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 bg-primary-500/20 text-primary-400 text-xs rounded-full font-bold">M{{ $activeMilestone->sequence_order }}</span>
                            <h3 class="text-xl font-bold text-white">{{ $activeMilestone->title }}</h3>
                        </div>
                        @if($activeMilestone->description)
                        <p class="text-dark-300 text-sm mt-1">{{ $activeMilestone->description }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-white font-bold text-lg">{{ $msCompleted }}/{{ $msTotal }}</p>
                        <p class="text-dark-500 text-xs uppercase tracking-wider">Activities</p>
                    </div>
                </div>
                @if($msTotal > 0)
                <div class="w-full bg-dark-800 rounded-full h-2 mt-5">
                    <div class="bg-primary-500 rounded-full h-2 transition-all duration-500" style="width: {{ $msPct }}%"></div>
                </div>
                @endif
            </div>

            <!-- Activities -->
            <div class="divide-y divide-dark-700/50 bg-dark-800/20">
                @foreach($msActivities->sortBy('sequence_order') as $activity)
                @php
                    $actProgress = $msProgress->firstWhere('curriculum_activity_id', $activity->id);
                    $actStatus = $actProgress?->status?->value ?? $actProgress?->status ?? 'locked';
                    $actStatusLabel = $actProgress?->status?->label() ?? ucfirst($actStatus);
                @endphp
                <div class="px-6 py-5 flex items-center gap-4 hover:bg-dark-800/50 transition relative group">
                    {{-- Status Icon --}}
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg shadow-sm
                        @switch($actStatus)
                            @case('completed') bg-green-500/20 text-green-400 @break
                            @case('in_progress') bg-blue-500/20 text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.3)] @break
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
                            <p class="text-white font-medium text-base group-hover:text-primary-400 transition">{{ $activity->title }}</p>
                            @if($activity->is_required)
                                <span class="px-1.5 py-0.5 bg-red-500/10 text-red-400 text-[10px] rounded uppercase font-bold">*Req</span>
                            @endif
                        </div>
                        <div class="flex items-center flex-wrap gap-2 mt-1.5">
                            <span class="inline-flex items-center gap-1 text-dark-400 text-xs bg-dark-800 px-2 py-0.5 rounded">{{ $activity->type?->icon() ?? '' }} {{ $activity->type?->label() ?? '' }}</span>
                            <span class="inline-flex items-center gap-1 text-dark-400 text-xs bg-dark-800 px-2 py-0.5 rounded">{{ $activity->difficulty_level?->icon() ?? '' }} {{ $activity->difficulty_level?->label() ?? '' }}</span>
                            
                            @php
                                $hasVideo = collect($activity->resources ?? [])->contains(function ($res) {
                                    $content = is_array($res) ? ($res['content'] ?? '') : $res;
                                    $type = is_array($res) ? ($res['type'] ?? '') : '';
                                    return $type === 'youtube' || (is_string($content) && (str_contains($content, 'youtube.com') || str_contains($content, 'youtu.be')));
                                });
                            @endphp
                            @if($hasVideo)
                                <span class="inline-flex items-center gap-1 text-red-400 text-[10px] uppercase font-bold tracking-wider bg-red-500/10 px-2 py-0.5 rounded">▶ Video</span>
                            @endif

                            @if($activity->comments_count > 0)
                                <span class="inline-flex items-center gap-1 text-blue-400 text-[10px] uppercase font-bold tracking-wider bg-blue-500/10 px-2 py-0.5 rounded">💬 {{ $activity->comments_count }}</span>
                            @endif

                            @if($activity->requiresInterviewSession())
                                <span class="inline-flex items-center gap-1 text-purple-400 text-[10px] uppercase font-bold tracking-wider bg-purple-500/10 px-2 py-0.5 rounded">🎤 Interview</span>
                            @endif
                        </div>
                    </div>

                    {{-- Points --}}
                    <div class="text-right mr-4 hidden sm:block">
                        <span class="text-dark-300 font-medium text-sm">{{ $activity->points ?? 0 }} pts</span>
                        @if($actProgress && $actStatus === 'completed')
                            <p class="text-green-400 text-xs font-bold mt-0.5">+{{ $actProgress->points_awarded ?? 0 }}</p>
                        @endif
                    </div>

                    {{-- Deadline --}}
                    @if($actProgress && $actProgress->deadline_at)
                    <div class="text-right mr-4 hidden md:block">
                        @if($actProgress->isPastDeadline && $actStatus !== 'completed')
                            <span class="text-red-400 text-xs font-medium bg-red-500/10 px-2 py-1 rounded">Overdue</span>
                        @elseif($actStatus !== 'completed' && $actStatus !== 'locked')
                            <span class="text-dark-400 text-xs bg-dark-800 px-2 py-1 rounded flex items-center gap-1">⏰ {{ $actProgress->daysRemaining ?? '' }}d left</span>
                        @endif
                    </div>
                    @endif

                    {{-- Action --}}
                    <div>
                        @if($actStatus === 'available')
                            <a href="{{ route('curriculum.activity.show', $activity) }}" class="btn-primary py-2 px-4 shadow-lg shadow-primary-500/20">Start</a>
                        @elseif($actStatus === 'in_progress')
                            <a href="{{ route('curriculum.activity.show', $activity) }}" class="btn-primary py-2 px-4 shadow-lg shadow-blue-500/20">Continue</a>
                        @elseif($actStatus === 'rejected')
                            <a href="{{ route('curriculum.activity.show', $activity) }}" class="px-4 py-2 bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg font-medium hover:bg-red-500/30 transition">Resubmit</a>
                        @elseif(in_array($actStatus, ['submitted', 'peer_review', 'under_review']))
                            <span class="text-dark-400 text-sm px-4 py-2 bg-dark-800 rounded-lg border border-dark-700">In Review</span>
                        @elseif($actStatus === 'completed')
                            <a href="{{ route('curriculum.activity.show', $activity) }}" class="text-dark-400 hover:text-white text-sm px-3 py-2 transition bg-dark-800 hover:bg-dark-700 rounded-lg">View</a>
                        @else
                            <span class="text-dark-600 text-sm px-4 py-2 bg-dark-800/50 rounded-lg cursor-not-allowed">Locked</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- All Milestones (Track Map) -->
    <div x-data="{ expanded: false }">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-dark-300">Track Map (All Milestones)</h2>
            <button @click="expanded = !expanded" class="text-sm text-primary-400 hover:text-primary-300 transition flex items-center gap-1">
                <span x-text="expanded ? 'Collapse All' : 'Expand All'">Expand All</span>
                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
        </div>
        
        <div class="space-y-4" x-show="expanded" x-collapse>
            @foreach($milestones as $milestone)
            @if(isset($activeMilestone) && $activeMilestone->id === $milestone->id)
                @continue
            @endif
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
                    <div class="flex items-center flex-wrap gap-2 mt-1">
                        <span class="inline-flex items-center gap-1 text-dark-400 text-[10px] bg-dark-800 px-1.5 py-0.5 rounded">{{ $activity->type?->icon() ?? '' }} {{ $activity->type?->label() ?? '' }}</span>
                        <span class="inline-flex items-center gap-1 text-dark-400 text-[10px] bg-dark-800 px-1.5 py-0.5 rounded">{{ $activity->difficulty_level?->icon() ?? '' }} {{ $activity->difficulty_level?->label() ?? '' }}</span>
                        
                        @php
                            $hasVideo = collect($activity->resources ?? [])->contains(function ($res) {
                                $content = is_array($res) ? ($res['content'] ?? '') : $res;
                                $type = is_array($res) ? ($res['type'] ?? '') : '';
                                return $type === 'youtube' || (is_string($content) && (str_contains($content, 'youtube.com') || str_contains($content, 'youtu.be')));
                            });
                        @endphp
                        @if($hasVideo)
                            <span class="inline-flex items-center gap-1 text-red-400 text-[9px] uppercase font-bold tracking-wider bg-red-500/10 px-1.5 py-0.5 rounded">▶ Video</span>
                        @endif

                        @if($activity->comments_count > 0)
                            <span class="inline-flex items-center gap-1 text-blue-400 text-[9px] uppercase font-bold tracking-wider bg-blue-500/10 px-1.5 py-0.5 rounded">💬 {{ $activity->comments_count }}</span>
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
        </div>
    </div>

    @if($milestones->isEmpty())
    <div class="card p-12 text-center">
        <div class="text-4xl mb-4">📚</div>
        <h3 class="text-white font-semibold text-lg">Curriculum Coming Soon</h3>
        <p class="text-dark-400 mt-1">The curriculum for this track is being prepared. Check back soon!</p>
    </div>
    @endif
</div>
@endsection
