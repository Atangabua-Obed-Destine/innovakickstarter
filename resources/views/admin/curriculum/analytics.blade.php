@extends('layouts.app')

@section('title', "Curriculum Analytics — {$track->name}")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('admin.curriculum.index', $track) }}" class="hover:text-white transition">{{ $track->name }} Curriculum</a>
            <span>/</span>
            <span class="text-primary-400">Analytics</span>
        </div>
        <h1 class="text-2xl font-bold text-white">{{ $track->name }} | Curriculum Analytics</h1>
        <p class="text-dark-400 mt-1">Track performance, completion rates, and engagement metrics</p>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center text-2xl">📈</div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ number_format($analytics['completion_rate'] ?? 0, 1) }}%</p>
                    <p class="text-dark-400 text-sm">Overall Completion</p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-2xl">👥</div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ $analytics['active_fellows'] ?? 0 }}</p>
                    <p class="text-dark-400 text-sm">Active Fellows</p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-2xl">⏱️</div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ number_format($analytics['avg_completion_time'] ?? 0, 1) }}d</p>
                    <p class="text-dark-400 text-sm">Avg Completion Time</p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center text-2xl">⚠️</div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ $analytics['overdue_count'] ?? 0 }}</p>
                    <p class="text-dark-400 text-sm">Overdue Activities</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Status Breakdown --}}
        <div class="card p-6">
            <h3 class="text-white font-semibold text-lg mb-4">Status Breakdown</h3>
            @php
                $statuses = [
                    ['label' => 'Completed', 'count' => $analytics['completed_count'] ?? 0, 'color' => 'bg-green-500', 'text' => 'text-green-400'],
                    ['label' => 'In Progress', 'count' => $analytics['in_progress_count'] ?? 0, 'color' => 'bg-blue-500', 'text' => 'text-blue-400'],
                    ['label' => 'Submitted/Review', 'count' => $analytics['pending_review_count'] ?? 0, 'color' => 'bg-purple-500', 'text' => 'text-purple-400'],
                    ['label' => 'Available', 'count' => $analytics['available_count'] ?? 0, 'color' => 'bg-dark-500', 'text' => 'text-dark-300'],
                    ['label' => 'Locked', 'count' => $analytics['locked_count'] ?? 0, 'color' => 'bg-dark-700', 'text' => 'text-dark-500'],
                    ['label' => 'Overdue', 'count' => $analytics['overdue_count'] ?? 0, 'color' => 'bg-red-500', 'text' => 'text-red-400'],
                ];
                $totalProgress = max(array_sum(array_column($statuses, 'count')), 1);
            @endphp
            <div class="space-y-3">
                @foreach($statuses as $s)
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="{{ $s['text'] }}">{{ $s['label'] }}</span>
                        <span class="text-dark-400">{{ $s['count'] }} ({{ number_format(($s['count'] / $totalProgress) * 100, 0) }}%)</span>
                    </div>
                    <div class="w-full bg-dark-800 rounded-full h-2">
                        <div class="{{ $s['color'] }} rounded-full h-2 transition-all"
                             style="width: {{ ($s['count'] / $totalProgress) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Milestone Progress --}}
        <div class="card p-6">
            <h3 class="text-white font-semibold text-lg mb-4">Milestone Progress</h3>
            @if(!empty($analytics['milestone_stats']))
            <div class="space-y-4">
                @foreach($analytics['milestone_stats'] as $ms)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-white text-sm">{{ $ms['icon'] ?? '🏁' }} {{ $ms['title'] ?? 'Milestone' }}</span>
                        <span class="text-dark-400 text-sm">{{ $ms['completed'] ?? 0 }}/{{ $ms['total'] ?? 0 }}</span>
                    </div>
                    @php $pct = ($ms['total'] ?? 0) > 0 ? (($ms['completed'] ?? 0) / $ms['total']) * 100 : 0; @endphp
                    <div class="w-full bg-dark-800 rounded-full h-2">
                        <div class="bg-primary-500 rounded-full h-2 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-dark-500 text-sm">No milestone data available yet.</p>
            @endif
        </div>
    </div>

    <!-- Top Performers & Recent Completions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Rankings --}}
        <div class="card p-6">
            <h3 class="text-white font-semibold text-lg mb-4">🏆 Top Performers</h3>
            @if(!empty($analytics['rankings']))
            <div class="space-y-3">
                @foreach(array_slice($analytics['rankings'], 0, 10) as $i => $rank)
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $i === 0 ? 'bg-amber-500/20 text-amber-400' : ($i === 1 ? 'bg-gray-400/20 text-gray-300' : ($i === 2 ? 'bg-orange-500/20 text-orange-400' : 'bg-dark-700 text-dark-400')) }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm truncate">{{ $rank['name'] ?? 'Fellow' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-primary-400 text-sm font-medium">{{ $rank['completed'] ?? 0 }}</span>
                        <span class="text-dark-500 text-xs">done</span>
                    </div>
                    <div class="text-right">
                        <span class="text-white text-sm font-medium">{{ number_format($rank['points'] ?? 0) }}</span>
                        <span class="text-dark-500 text-xs">pts</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-dark-500 text-sm">No data yet.</p>
            @endif
        </div>

        {{-- Recent Completions --}}
        <div class="card p-6">
            <h3 class="text-white font-semibold text-lg mb-4">🎯 Recent Completions</h3>
            @if(!empty($analytics['recent_completions']))
            <div class="space-y-3">
                @foreach(array_slice($analytics['recent_completions'], 0, 8) as $completion)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 text-xs">✓</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm truncate">{{ $completion['activity'] ?? 'Activity' }}</p>
                        <p class="text-dark-500 text-xs">{{ $completion['fellow'] ?? '' }} · {{ $completion['completed_at'] ?? '' }}</p>
                    </div>
                    <span class="text-primary-400 text-sm">+{{ $completion['points'] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-dark-500 text-sm">No completions yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
