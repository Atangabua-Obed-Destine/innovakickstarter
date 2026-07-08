@extends('layouts.app')

@section('title', 'Interview Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.interviews.index') }}" class="hover:text-white">Interviews</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">Analytics</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">Interview Analytics</h1>
            <p class="text-dark-400 mt-1">Performance insights and trends for mock interviews</p>
        </div>
        <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Interviews
        </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.interviews.analytics') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-dark-400 text-sm">From:</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="input-field">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-dark-400 text-sm">To:</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="input-field">
            </div>
            <button type="submit" class="btn-primary">Apply</button>
            <div class="flex gap-2 ml-auto">
                <a href="{{ route('admin.interviews.analytics', ['date_from' => now()->subDays(7)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
                   class="btn btn-outline text-sm">Last 7 Days</a>
                <a href="{{ route('admin.interviews.analytics', ['date_from' => now()->subDays(30)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
                   class="btn btn-outline text-sm">Last 30 Days</a>
                <a href="{{ route('admin.interviews.analytics', ['date_from' => now()->subDays(90)->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
                   class="btn btn-outline text-sm">Last 90 Days</a>
            </div>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary-600/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($overallStats['total_interviews']) }}</p>
            <p class="text-dark-400 text-sm">Total Interviews</p>
        </div>

        <div class="card p-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($overallStats['completed']) }}</p>
            <p class="text-dark-400 text-sm">Completed</p>
        </div>

        <div class="card p-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($overallStats['completion_rate'], 1) }}%</p>
            <p class="text-dark-400 text-sm">Completion Rate</p>
        </div>

        <div class="card p-6 text-center">
            <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($overallStats['avg_score'] ?? 0, 1) }}%</p>
            <p class="text-dark-400 text-sm">Average Score</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Interviews by Type -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Performance by Interview Type
            </h3>
            @if($byType->count() > 0)
                <div class="space-y-4">
                    @foreach($byType as $item)
                        <div class="flex items-center gap-4">
                            <div class="w-32 text-dark-300 text-sm truncate">{{ $item['type'] }}</div>
                            <div class="flex-1">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-dark-500">{{ $item['count'] }} interviews</span>
                                    <span class="text-dark-300">{{ $item['avg_score'] }}% avg</span>
                                </div>
                                <div class="h-3 bg-dark-700 rounded-full overflow-hidden">
                                    @php
                                        $color = $item['avg_score'] >= 80 ? 'bg-green-500' : 
                                                ($item['avg_score'] >= 60 ? 'bg-amber-500' : 'bg-red-500');
                                    @endphp
                                    <div class="h-full {{ $color }} rounded-full transition-all duration-500" 
                                         style="width: {{ $item['avg_score'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-dark-500">
                    <p>No completed interviews in this period</p>
                </div>
            @endif
        </div>

        <!-- Interviews by Mode (AI vs Human) -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                AI vs Human Interviews
            </h3>
            @if($byMode->count() > 0)
                <div class="grid grid-cols-2 gap-4 mb-6">
                    @foreach($byMode as $item)
                        <div class="bg-dark-800 rounded-xl p-4 text-center {{ $item['mode_value'] === 'ai' ? 'border-l-4 border-purple-500' : 'border-l-4 border-cyan-500' }}">
                            <div class="text-3xl mb-2">{{ $item['mode_value'] === 'ai' ? '🤖' : '👤' }}</div>
                            <p class="text-2xl font-bold text-white">{{ $item['count'] }}</p>
                            <p class="text-dark-400 text-sm">{{ $item['mode'] }}</p>
                            <p class="text-dark-500 text-xs mt-1">Avg: {{ $item['avg_score'] }}%</p>
                        </div>
                    @endforeach
                </div>
                
                @php
                    $total = $byMode->sum('count');
                    $aiPercentage = $total > 0 ? round(($byMode->firstWhere('mode_value', 'ai')['count'] ?? 0) / $total * 100) : 0;
                @endphp
                <div class="h-4 bg-dark-700 rounded-full overflow-hidden flex">
                    <div class="h-full bg-purple-500" style="width: {{ $aiPercentage }}%"></div>
                    <div class="h-full bg-cyan-500" style="width: {{ 100 - $aiPercentage }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-dark-500 mt-2">
                    <span>AI: {{ $aiPercentage }}%</span>
                    <span>Human: {{ 100 - $aiPercentage }}%</span>
                </div>
            @else
                <div class="text-center py-8 text-dark-500">
                    <p>No completed interviews in this period</p>
                </div>
            @endif
        </div>

        <!-- Score Distribution -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Score Distribution
            </h3>
            @php
                $maxCount = max($scoreRanges) ?: 1;
            @endphp
            <div class="flex items-end justify-between gap-2 h-40">
                @foreach($scoreRanges as $range => $count)
                    @php
                        $height = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        $color = match($range) {
                            '0-50' => 'bg-red-500',
                            '51-70' => 'bg-amber-500',
                            '71-85' => 'bg-blue-500',
                            '86-100' => 'bg-green-500',
                            default => 'bg-gray-500'
                        };
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full flex flex-col items-center justify-end h-32">
                            <span class="text-dark-300 text-sm font-medium mb-1">{{ $count }}</span>
                            <div class="{{ $color }} w-full rounded-t-lg transition-all duration-500" 
                                 style="height: {{ max($height, 4) }}%"></div>
                        </div>
                        <span class="text-dark-500 text-xs mt-2">{{ $range }}%</span>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-center gap-6 mt-4 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-500"></span> Needs Work</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-500"></span> Fair</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> Good</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500"></span> Excellent</span>
            </div>
        </div>

        <!-- By Track -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                Performance by Track
            </h3>
            @if($byTrack->count() > 0)
                <div class="space-y-3">
                    @foreach($byTrack as $item)
                        <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                            <div>
                                <p class="text-dark-200 font-medium">{{ $item['track'] }}</p>
                                <p class="text-dark-500 text-xs">{{ $item['count'] }} interviews</p>
                            </div>
                            <div class="text-right">
                                @php
                                    $scoreColor = $item['avg_score'] >= 80 ? 'text-green-400' : 
                                                 ($item['avg_score'] >= 60 ? 'text-amber-400' : 'text-red-400');
                                @endphp
                                <p class="text-lg font-bold {{ $scoreColor }}">{{ $item['avg_score'] }}%</p>
                                <p class="text-dark-500 text-xs">avg score</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-dark-500">
                    <p>No completed interviews in this period</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Interview Volume Trend
        </h3>
        @if($dailyTrend->count() > 0)
            @php
                $maxDaily = $dailyTrend->max('count') ?: 1;
            @endphp
            <div class="h-48 flex items-end gap-1">
                @foreach($dailyTrend as $day)
                    @php
                        $height = ($day->count / $maxDaily) * 100;
                    @endphp
                    <div class="flex-1 group relative">
                        <div class="bg-primary-500 hover:bg-primary-400 rounded-t transition-all duration-200 cursor-pointer" 
                             style="height: {{ max($height, 4) }}%"
                             title="{{ $day->date }}: {{ $day->count }} interviews"></div>
                        <div class="hidden group-hover:block absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-dark-700 rounded text-xs text-white whitespace-nowrap z-10">
                            {{ \Carbon\Carbon::parse($day->date)->format('M j') }}: {{ $day->count }}
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-xs text-dark-500 mt-2">
                <span>{{ \Carbon\Carbon::parse($dailyTrend->first()->date)->format('M j') }}</span>
                <span>{{ \Carbon\Carbon::parse($dailyTrend->last()->date)->format('M j') }}</span>
            </div>
        @else
            <div class="text-center py-8 text-dark-500">
                <p>No interviews in this period</p>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performers -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                Top Performing Fellows
            </h3>
            @if($topPerformers->count() > 0)
                <div class="space-y-3">
                    @foreach($topPerformers as $index => $performer)
                        <div class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                {{ $index === 0 ? 'bg-yellow-500 text-dark-900' : ($index === 1 ? 'bg-gray-400 text-dark-900' : ($index === 2 ? 'bg-amber-600 text-white' : 'bg-dark-600 text-dark-300')) }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('admin.fellows.show', $performer->fellow_id) }}" 
                                   class="text-dark-200 font-medium hover:text-primary-400">
                                    {{ $performer->fellow?->name ?? 'Unknown' }}
                                </a>
                                <p class="text-dark-500 text-xs">{{ $performer->interview_count }} interviews</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-400">{{ number_format($performer->avg_score, 1) }}%</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-dark-500">
                    <p>No fellows with 2+ interviews yet</p>
                </div>
            @endif
        </div>

        <!-- Mentor Leaderboard -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Most Active Mentors
            </h3>
            @if($mentorStats->count() > 0)
                <div class="space-y-3">
                    @foreach($mentorStats as $index => $mentor)
                        <div class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-sm font-bold text-white">
                                {{ strtoupper(substr($mentor->interviewer?->name ?? '?', 0, 2)) }}
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('admin.mentors.show', $mentor->interviewer_id) }}" 
                                   class="text-dark-200 font-medium hover:text-cyan-400">
                                    {{ $mentor->interviewer?->name ?? 'Unknown' }}
                                </a>
                                <p class="text-dark-500 text-xs">{{ $mentor->count }} interviews conducted</p>
                            </div>
                            <div class="text-right">
                                @if($mentor->avg_rating)
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="text-dark-200 font-medium">{{ number_format($mentor->avg_rating, 1) }}</span>
                                    </div>
                                @else
                                    <span class="text-dark-500 text-sm">No ratings</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-dark-500">
                    <p>No mentor interviews in this period</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
