@extends('layouts.app')

@section('title', 'Track Comparison')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-dark-400 hover:text-dark-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-white">Track Comparison</h1>
            </div>
            <p class="text-dark-400 mt-1 ml-8">Side-by-side view of your career tracks. Find your strengths and opportunities.</p>
        </div>
        {{-- Achievement Badges --}}
        @if(!empty($meta['achievements'] ?? []))
        <div class="flex items-center gap-2">
            @foreach($meta['achievements'] as $achievement)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-dark-800 border border-dark-700">
                    <span class="text-lg">{{ $achievement['icon'] }}</span>
                    <div>
                        <p class="text-xs font-semibold text-dark-100">{{ $achievement['name'] }}</p>
                        <p class="text-[10px] text-dark-500">{{ $achievement['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Summary Stats Row --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-dark-400 text-sm">Tracks Enrolled</p>
            <p class="text-3xl font-bold text-white mt-1">{{ $fellowTracks->count() }}</p>
            <p class="text-dark-500 text-xs mt-2">Career paths being explored</p>
        </div>
        <div class="card p-5">
            <p class="text-dark-400 text-sm">Average Score</p>
            <p class="text-3xl font-bold text-violet-400 mt-1">{{ $meta['averageScore'] ?? 0 }}%</p>
            <p class="text-dark-500 text-xs mt-2">Across all tracks</p>
        </div>
        <div class="card p-5">
            <p class="text-dark-400 text-sm">Strongest Track</p>
            <p class="text-lg font-bold text-emerald-400 mt-1 truncate">{{ $meta['strongestTrack'] ?? 'N/A' }}</p>
            <p class="text-dark-500 text-xs mt-2">Highest current score</p>
        </div>
        <div class="card p-5">
            <p class="text-dark-400 text-sm">Needs Attention</p>
            <p class="text-lg font-bold text-amber-400 mt-1 truncate">{{ $meta['weakestTrack'] ?? 'N/A' }}</p>
            <p class="text-dark-500 text-xs mt-2">Lowest current score</p>
        </div>
    </div>

    {{-- Visual Score Comparison --}}
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Score Comparison</h3>
        <div class="space-y-4">
            @foreach($trackStats as $stat)
            @php
                $ft = $stat['fellowTrack'];
                $tierColors = [
                    'rookie' => 'from-slate-600 to-slate-400',
                    'intern' => 'from-blue-600 to-blue-400',
                    'professional' => 'from-purple-600 to-purple-400',
                    'elite' => 'from-amber-600 to-amber-400',
                ];
                $barGradient = $tierColors[$ft->tier ?? 'rookie'] ?? $tierColors['rookie'];
                $healthColor = match($ft->health_status ?? 'dormant') {
                    'active'  => 'bg-emerald-400',
                    'cooling' => 'bg-amber-400',
                    default   => 'bg-red-400',
                };
            @endphp
            <div class="group">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full {{ $healthColor }} {{ $ft->health_status === 'active' ? 'animate-pulse' : '' }}"></span>
                        <span class="text-dark-100 font-medium">{{ $stat['track']?->name ?? 'Unknown' }}</span>
                        @if($ft->is_primary)
                            <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-emerald-600/20 text-emerald-400">Primary</span>
                        @endif
                        <span class="px-1.5 py-0.5 text-[9px] font-medium rounded bg-dark-700 text-dark-400 capitalize">{{ $ft->tier ?? 'rookie' }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-dark-400">{{ $stat['totalActivities'] }} activities</span>
                        <span class="text-dark-400">{{ $stat['interviewCount'] }} interviews</span>
                        <span class="text-white font-bold">{{ number_format($ft->score, 1) }}%</span>
                    </div>
                </div>
                <div class="h-4 bg-dark-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r {{ $barGradient }} rounded-full transition-all duration-1000 ease-out" 
                         style="width: {{ min(100, $ft->score) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Category Radar (Side by Side) --}}
    <div class="grid lg:grid-cols-{{ min(3, count($trackStats)) }} gap-6">
        @foreach($trackStats as $stat)
        @php
            $ft = $stat['fellowTrack'];
            $cats = $stat['categories'];
            $maxCat = max(array_values($cats) ?: [1]);
        @endphp
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-md font-semibold text-white">{{ $stat['track']?->name ?? 'Unknown' }}</h4>
                <span class="text-sm font-bold text-violet-400">{{ number_format($ft->score, 1) }}%</span>
            </div>

            {{-- Category Bars --}}
            <div class="space-y-3">
                @php
                    $catConfig = [
                        'technical' => ['label' => 'Technical', 'color' => 'purple', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                        'interview' => ['label' => 'Interview', 'color' => 'blue', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                        'portfolio' => ['label' => 'Portfolio', 'color' => 'teal', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                        'collaboration' => ['label' => 'Collaboration', 'color' => 'amber', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        'learning' => ['label' => 'Learning', 'color' => 'rose', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ];
                @endphp
                @foreach($catConfig as $catKey => $cfg)
                @php $catScore = $cats[$catKey] ?? 0; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded bg-{{ $cfg['color'] }}-600/20 flex items-center justify-center">
                                <svg class="w-3 h-3 text-{{ $cfg['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="text-xs text-dark-300">{{ $cfg['label'] }}</span>
                        </div>
                        <span class="text-xs font-medium text-{{ $cfg['color'] }}-400">{{ number_format($catScore, 0) }}%</span>
                    </div>
                    <div class="h-1.5 bg-dark-700 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $cfg['color'] }}-600 rounded-full transition-all duration-700" style="width: {{ min(100, $catScore) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Track Stats --}}
            <div class="grid grid-cols-3 gap-2 mt-5 pt-4 border-t border-dark-700">
                <div class="text-center">
                    <p class="text-lg font-bold text-white">{{ $stat['approvedCount'] }}</p>
                    <p class="text-[10px] text-dark-500">Approved</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-amber-400">{{ $stat['pendingCount'] }}</p>
                    <p class="text-[10px] text-dark-500">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-bold text-blue-400">{{ $stat['interviewCount'] }}</p>
                    <p class="text-[10px] text-dark-500">Interviews</p>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-dark-700">
                <form method="POST" action="{{ route('tracks.switch-active') }}" class="inline">
                    @csrf
                    <input type="hidden" name="track_id" value="{{ $ft->track_id }}">
                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-violet-300 bg-violet-600/10 hover:bg-violet-600/20 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Switch to This
                    </button>
                </form>
                <a href="{{ route('curriculum.track', $ft->track_id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-teal-300 bg-teal-600/10 hover:bg-teal-600/20 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Curriculum
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Monthly Activity Trend --}}
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Monthly Activity Trend</h3>
        <p class="text-dark-500 text-sm mb-6">Approved activities per month across all tracks</p>

        <div class="overflow-x-auto">
            <div class="min-w-[600px]">
                {{-- Chart legend --}}
                <div class="flex items-center gap-4 mb-4">
                    @php
                        $trackColors = ['violet', 'blue', 'teal', 'amber', 'rose', 'emerald'];
                    @endphp
                    @foreach($trackStats as $idx => $stat)
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-{{ $trackColors[$idx % count($trackColors)] }}-500"></div>
                            <span class="text-xs text-dark-400">{{ $stat['track']?->name ?? 'Unknown' }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Bar chart --}}
                <div class="flex items-end gap-1 h-40">
                    @for($m = 0; $m < 6; $m++)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full flex items-end justify-center gap-0.5" style="height: 120px">
                                @foreach($trackStats as $idx => $stat)
                                    @php
                                        $count = $stat['monthlyTrend'][$m]['count'] ?? 0;
                                        $maxTrend = collect($trackStats)->flatMap(fn($s) => collect($s['monthlyTrend'])->pluck('count'))->max() ?: 1;
                                        $height = max(4, ($count / $maxTrend) * 120);
                                        $color = $trackColors[$idx % count($trackColors)];
                                    @endphp
                                    <div class="flex-1 max-w-6 bg-{{ $color }}-600/60 hover:bg-{{ $color }}-500 rounded-t transition-colors cursor-help"
                                         style="height: {{ $height }}px"
                                         title="{{ $stat['track']?->name }}: {{ $count }} activities">
                                    </div>
                                @endforeach
                            </div>
                            <span class="text-[10px] text-dark-500 mt-1">{{ $trackStats[0]['monthlyTrend'][$m]['label'] ?? '' }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Recommendations --}}
    <div class="card p-6 border-l-4 border-violet-500">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Smart Recommendations
        </h3>
        <div class="grid md:grid-cols-2 gap-4">
            @php
                $weakest = collect($trackStats)->sortBy(fn($s) => $s['fellowTrack']->score)->first();
                $strongest = collect($trackStats)->sortByDesc(fn($s) => $s['fellowTrack']->score)->first();
                $leastInterviews = collect($trackStats)->sortBy(fn($s) => $s['interviewCount'])->first();
                $dormantTracks = collect($trackStats)->filter(fn($s) => ($s['fellowTrack']->health_status ?? 'dormant') === 'dormant');
            @endphp

            @if($weakest && $weakest['fellowTrack']->score < 20)
            <div class="p-4 rounded-lg bg-amber-600/10 border border-amber-500/20">
                <div class="flex items-start gap-3">
                    <span class="text-xl">🎯</span>
                    <div>
                        <p class="text-sm font-medium text-amber-300">Focus Area: {{ $weakest['track']?->name }}</p>
                        <p class="text-xs text-dark-400 mt-1">
                            Score at {{ number_format($weakest['fellowTrack']->score, 1) }}%. 
                            Log technical activities or schedule mock interviews to boost this track.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if($leastInterviews && $leastInterviews['interviewCount'] < 3)
            <div class="p-4 rounded-lg bg-blue-600/10 border border-blue-500/20">
                <div class="flex items-start gap-3">
                    <span class="text-xl">🎤</span>
                    <div>
                        <p class="text-sm font-medium text-blue-300">Interview Practice: {{ $leastInterviews['track']?->name }}</p>
                        <p class="text-xs text-dark-400 mt-1">
                            Only {{ $leastInterviews['interviewCount'] }} interview{{ $leastInterviews['interviewCount'] !== 1 ? 's' : '' }} completed. 
                            Practice sessions build confidence and boost scores.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if($dormantTracks->count() > 0)
            <div class="p-4 rounded-lg bg-red-600/10 border border-red-500/20">
                <div class="flex items-start gap-3">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <p class="text-sm font-medium text-red-300">Dormant Track{{ $dormantTracks->count() > 1 ? 's' : '' }}</p>
                        <p class="text-xs text-dark-400 mt-1">
                            {{ $dormantTracks->map(fn($s) => $s['track']?->name)->implode(', ') }} 
                            {{ $dormantTracks->count() > 1 ? 'have' : 'has' }} been inactive 15+ days. 
                            Even a small activity keeps momentum alive.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if($strongest && $strongest['fellowTrack']->score >= 41)
            <div class="p-4 rounded-lg bg-emerald-600/10 border border-emerald-500/20">
                <div class="flex items-start gap-3">
                    <span class="text-xl">🏆</span>
                    <div>
                        <p class="text-sm font-medium text-emerald-300">Leading Track: {{ $strongest['track']?->name }}</p>
                        <p class="text-xs text-dark-400 mt-1">
                            At {{ number_format($strongest['fellowTrack']->score, 1) }}%. Great work! 
                            Consider mentoring peers in this area to earn collaboration points.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            @if(empty($meta['achievements'] ?? []))
            <div class="p-4 rounded-lg bg-violet-600/10 border border-violet-500/20">
                <div class="flex items-start gap-3">
                    <span class="text-xl">🚀</span>
                    <div>
                        <p class="text-sm font-medium text-violet-300">Unlock Your First Badge</p>
                        <p class="text-xs text-dark-400 mt-1">
                            Reach Intern tier (21+ score) in 3 tracks to earn the "Renaissance Fellow" badge, 
                            or hit Professional tier in any track for "Specialist" status.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
