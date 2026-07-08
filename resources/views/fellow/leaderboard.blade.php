@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Leaderboard</h1>
            <p class="text-dark-400">See how you rank against other fellows in your track</p>
        </div>
        
        <!-- Track Selector -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="btn btn-outline flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-primary-500"></span>
                <span>{{ $currentTrack ?? 'Software Engineering' }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" 
                 x-transition
                 class="absolute right-0 mt-2 w-56 bg-dark-800 rounded-lg shadow-xl border border-dark-700 z-50">
                @foreach($tracks ?? [
                    ['name' => 'Software Engineering', 'color' => 'bg-primary-500'],
                    ['name' => 'Data Science', 'color' => 'bg-teal-500'],
                    ['name' => 'Product Management', 'color' => 'bg-blue-500'],
                    ['name' => 'Digital Marketing', 'color' => 'bg-amber-500'],
                    ['name' => 'UI/UX Design', 'color' => 'bg-pink-500'],
                ] as $track)
                    <a href="?track={{ Str::slug($track['name']) }}" 
                       class="flex items-center gap-3 px-4 py-2.5 hover:bg-dark-700 text-dark-200 hover:text-white transition-colors first:rounded-t-lg last:rounded-b-lg">
                        <span class="w-3 h-3 rounded-full {{ $track['color'] }}"></span>
                        {{ $track['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Time Period Filter -->
    <div class="flex gap-2 overflow-x-auto pb-2">
        @foreach(['This Week' => 'week', 'This Month' => 'month', 'All Time' => 'all'] as $label => $period)
            <a href="?period={{ $period }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-colors
                      {{ ($currentPeriod ?? 'week') === $period ? 'bg-primary-600 text-white' : 'bg-dark-800 text-dark-300 hover:bg-dark-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Top 3 Podium -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-6 text-center">Top Performers</h2>
        
        <div class="flex items-end justify-center gap-4 mb-8">
            <!-- 2nd Place -->
            <div class="text-center">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white font-bold text-2xl mx-auto ring-4 ring-slate-400/50">
                        {{ strtoupper(substr($topFellows[1]->name ?? 'Jane Smith', 0, 2)) }}
                    </div>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-slate-400 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                        2
                    </div>
                </div>
                <p class="text-white font-medium text-sm">{{ $topFellows[1]->name ?? 'Jane Smith' }}</p>
                <p class="text-slate-400 font-bold">{{ $topFellows[1]->score ?? 87 }}%</p>
                <div class="h-24 w-28 bg-gradient-to-t from-slate-600 to-slate-500 rounded-t-lg mt-3 flex items-end justify-center pb-3">
                    <span class="text-white/80 text-xs">+{{ $topFellows[1]->points ?? 450 }} pts</span>
                </div>
            </div>

            <!-- 1st Place -->
            <div class="text-center -mb-4">
                <div class="relative mb-3">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white font-bold text-3xl mx-auto ring-4 ring-amber-400/50 animate-pulse-slow">
                        {{ strtoupper(substr($topFellows[0]->name ?? 'Alex Johnson', 0, 2)) }}
                    </div>
                    <div class="absolute -top-2 left-1/2 -translate-x-1/2">
                        <svg class="w-8 h-8 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zm-2.207 4.207a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                        1
                    </div>
                </div>
                <p class="text-white font-semibold">{{ $topFellows[0]->name ?? 'Alex Johnson' }}</p>
                <p class="text-amber-400 font-bold text-lg">{{ $topFellows[0]->score ?? 92 }}%</p>
                <div class="h-32 w-32 bg-gradient-to-t from-amber-600 to-amber-500 rounded-t-lg mt-3 flex items-end justify-center pb-3">
                    <span class="text-white/80 text-sm">+{{ $topFellows[0]->points ?? 580 }} pts</span>
                </div>
            </div>

            <!-- 3rd Place -->
            <div class="text-center">
                <div class="relative mb-3">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-amber-700 to-amber-900 flex items-center justify-center text-white font-bold text-2xl mx-auto ring-4 ring-amber-700/50">
                        {{ strtoupper(substr($topFellows[2]->name ?? 'Mike Brown', 0, 2)) }}
                    </div>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-amber-700 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                        3
                    </div>
                </div>
                <p class="text-white font-medium text-sm">{{ $topFellows[2]->name ?? 'Mike Brown' }}</p>
                <p class="text-amber-700 font-bold">{{ $topFellows[2]->score ?? 85 }}%</p>
                <div class="h-16 w-28 bg-gradient-to-t from-amber-800 to-amber-700 rounded-t-lg mt-3 flex items-end justify-center pb-3">
                    <span class="text-white/80 text-xs">+{{ $topFellows[2]->points ?? 420 }} pts</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Your Ranking Card -->
    <div class="card p-6 bg-gradient-to-r from-primary-600/20 to-blue-600/20 border-primary-500/30">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr(auth()->user()->name ?? 'You', 0, 2)) }}
            </div>
            <div class="flex-1">
                <p class="text-dark-400 text-sm">Your Current Ranking</p>
                <div class="flex items-center gap-3">
                    <span class="text-3xl font-bold text-white">#{{ $yourRank ?? 12 }}</span>
                    <span class="flex items-center gap-1 text-green-400 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        +3 from last week
                    </span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-dark-400 text-sm">Career Capital Score</p>
                <p class="text-2xl font-bold text-primary-400">{{ $yourScore ?? 72 }}%</p>
            </div>
        </div>
    </div>

    <!-- Full Leaderboard Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-4 px-6 text-dark-400 font-medium">Rank</th>
                        <th class="text-left py-4 px-6 text-dark-400 font-medium">Fellow</th>
                        <th class="text-left py-4 px-6 text-dark-400 font-medium">Cohort</th>
                        <th class="text-center py-4 px-6 text-dark-400 font-medium">Score</th>
                        <th class="text-center py-4 px-6 text-dark-400 font-medium">Points</th>
                        <th class="text-center py-4 px-6 text-dark-400 font-medium">Activities</th>
                        <th class="text-center py-4 px-6 text-dark-400 font-medium">Streak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @foreach($leaderboard ?? [
                        ['rank' => 1, 'name' => 'Alex Johnson', 'cohort' => 'Cohort 7', 'score' => 92, 'points' => 580, 'activities' => 45, 'streak' => 21],
                        ['rank' => 2, 'name' => 'Jane Smith', 'cohort' => 'Cohort 7', 'score' => 87, 'points' => 450, 'activities' => 38, 'streak' => 14],
                        ['rank' => 3, 'name' => 'Mike Brown', 'cohort' => 'Cohort 6', 'score' => 85, 'points' => 420, 'activities' => 40, 'streak' => 18],
                        ['rank' => 4, 'name' => 'Sarah Wilson', 'cohort' => 'Cohort 7', 'score' => 82, 'points' => 390, 'activities' => 35, 'streak' => 10],
                        ['rank' => 5, 'name' => 'David Lee', 'cohort' => 'Cohort 6', 'score' => 80, 'points' => 375, 'activities' => 33, 'streak' => 8],
                        ['rank' => 6, 'name' => 'Emily Chen', 'cohort' => 'Cohort 7', 'score' => 78, 'points' => 360, 'activities' => 30, 'streak' => 12],
                        ['rank' => 7, 'name' => 'Chris Davis', 'cohort' => 'Cohort 7', 'score' => 76, 'points' => 340, 'activities' => 28, 'streak' => 5],
                        ['rank' => 8, 'name' => 'Lisa Taylor', 'cohort' => 'Cohort 6', 'score' => 75, 'points' => 325, 'activities' => 26, 'streak' => 7],
                        ['rank' => 9, 'name' => 'James Anderson', 'cohort' => 'Cohort 7', 'score' => 74, 'points' => 310, 'activities' => 25, 'streak' => 4],
                        ['rank' => 10, 'name' => 'Amy Martinez', 'cohort' => 'Cohort 6', 'score' => 73, 'points' => 300, 'activities' => 24, 'streak' => 6],
                        ['rank' => 11, 'name' => 'Kevin White', 'cohort' => 'Cohort 7', 'score' => 72, 'points' => 290, 'activities' => 22, 'streak' => 3],
                        ['rank' => 12, 'name' => 'You', 'cohort' => 'Cohort 7', 'score' => 72, 'points' => 285, 'activities' => 21, 'streak' => 5, 'isYou' => true],
                    ] as $fellow)
                        <tr class="{{ $fellow['isYou'] ?? false ? 'bg-primary-600/10' : 'hover:bg-dark-800/50' }} transition-colors">
                            <td class="py-4 px-6">
                                @if($fellow['rank'] === 1)
                                    <span class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 font-bold inline-flex items-center justify-center">🥇</span>
                                @elseif($fellow['rank'] === 2)
                                    <span class="w-8 h-8 rounded-full bg-slate-500/20 text-slate-400 font-bold inline-flex items-center justify-center">🥈</span>
                                @elseif($fellow['rank'] === 3)
                                    <span class="w-8 h-8 rounded-full bg-amber-700/20 text-amber-600 font-bold inline-flex items-center justify-center">🥉</span>
                                @else
                                    <span class="text-dark-400 font-medium ml-2">#{{ $fellow['rank'] }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-dark-700 flex items-center justify-center text-dark-300 font-medium {{ $fellow['isYou'] ?? false ? 'ring-2 ring-primary-500' : '' }}">
                                        {{ strtoupper(substr($fellow['name'], 0, 2)) }}
                                    </div>
                                    <span class="text-dark-200 font-medium {{ $fellow['isYou'] ?? false ? 'text-primary-400' : '' }}">
                                        {{ $fellow['name'] }}
                                        @if($fellow['isYou'] ?? false)
                                            <span class="text-xs text-primary-400 ml-1">(You)</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-dark-400 text-sm">{{ $fellow['cohort'] }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="font-bold {{ $fellow['score'] >= 80 ? 'text-green-400' : ($fellow['score'] >= 60 ? 'text-amber-400' : 'text-dark-300') }}">
                                    {{ $fellow['score'] }}%
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center text-dark-300">{{ $fellow['points'] }}</td>
                            <td class="py-4 px-6 text-center text-dark-400">{{ $fellow['activities'] }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center gap-1 text-amber-400">
                                    🔥 {{ $fellow['streak'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="p-4 border-t border-dark-700 flex items-center justify-between">
            <p class="text-dark-500 text-sm">Showing 1-12 of 156 fellows</p>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-500 text-sm cursor-not-allowed" disabled>Previous</button>
                <button class="px-3 py-1.5 rounded bg-dark-800 text-dark-200 text-sm hover:bg-dark-700 transition-colors">Next</button>
            </div>
        </div>
    </div>

    <!-- Leaderboard Stats -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-primary-400">156</p>
            <p class="text-dark-400 text-sm">Total Fellows</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-teal-400">72%</p>
            <p class="text-dark-400 text-sm">Average Score</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-amber-400">2,450</p>
            <p class="text-dark-400 text-sm">Activities Completed</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-blue-400">8</p>
            <p class="text-dark-400 text-sm">Average Streak</p>
        </div>
    </div>
</div>
@endsection
