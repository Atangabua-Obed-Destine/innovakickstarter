@extends('layouts.app')

@section('title', 'Career Capital Score Breakdown')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-sm">
                    <li><a href="{{ route('dashboard') }}" class="text-dark-400 hover:text-white">Dashboard</a></li>
                    <li class="flex items-center"><svg class="w-4 h-4 text-dark-600 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg><span class="text-dark-200">Score Breakdown</span></li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold text-white">Career Capital Score Breakdown</h1>
            <p class="text-dark-400 mt-1">Understand how your score is calculated across all four quadrants.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Overall Score Card -->
    <div class="card p-6 border-l-4 border-primary-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-dark-400 text-sm font-medium">Total Career Capital Score</p>
                <p class="text-4xl font-bold text-white mt-1">{{ number_format($breakdown['total'] ?? 0, 3) }}</p>
                <p class="text-dark-500 text-sm mt-1">{{ $primaryTrack?->track?->name ?? 'No track selected' }}</p>
            </div>
            <div class="w-20 h-20 rounded-full border-4 border-primary-500 flex items-center justify-center">
                <span class="text-2xl font-bold text-primary-400">{{ ucfirst($primaryTrack?->tier ?? 'N/A') }}</span>
            </div>
        </div>
    </div>

    <!-- Five Category Breakdown -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $categories = $breakdown['categories'] ?? [];
            $categoryMeta = [
                'technical' => ['icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'color' => 'purple', 'description' => 'Projects, code contributions, and technical skills'],
                'interview' => ['icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'color' => 'blue', 'description' => 'Mock interviews, practice sessions, and readiness'],
                'portfolio' => ['icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'teal', 'description' => 'Projects, blogs & open source contributions'],
                'collaboration' => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'amber', 'description' => 'Code reviews, mentoring, and peer collaboration'],
                'learning' => ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'rose', 'description' => 'Courses, certifications & workshops'],
            ];
        @endphp

        @foreach($categories as $key => $category)
        @php
            $meta = $categoryMeta[$key] ?? ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2', 'color' => 'gray', 'description' => ''];
            $maxScore = $category['weight'] ?? 25;
            $pct = $maxScore > 0 ? min(100, ($category['score'] / $maxScore) * 100) : 0;
        @endphp
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-3 rounded-xl bg-{{ $meta['color'] }}-600/20">
                    <svg class="w-6 h-6 text-{{ $meta['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ $category['label'] ?? ucfirst($key) }}</h3>
                    <p class="text-dark-500 text-xs">{{ $meta['description'] }}</p>
                </div>
            </div>
            <div class="flex items-end justify-between mb-3">
                <span class="text-3xl font-bold text-{{ $meta['color'] }}-400">{{ number_format($category['score'] ?? 0, 3) }}</span>
                <span class="text-dark-500 text-sm">/ {{ $maxScore }} pts</span>
            </div>
            <div class="w-full bg-dark-700 rounded-full h-2 mb-4">
                <div class="h-2 rounded-full bg-{{ $meta['color'] }}-500" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-dark-400 text-sm">Weight: {{ $category['weight'] ?? 0 }}%</p>
        </div>
        @endforeach
    </div>

    <!-- Score History -->
    @if(!empty($scoreHistory))
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Score History</h3>
        <div class="space-y-3">
            @foreach(array_slice(array_reverse($scoreHistory), 0, 10) as $entry)
            <div class="flex items-center justify-between py-2 border-b border-dark-700 last:border-0">
                <span class="text-dark-300 text-sm">{{ $entry['date'] ?? 'N/A' }}</span>
                <span class="text-white font-semibold">{{ number_format($entry['score'] ?? 0, 3) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Activities by Category -->
    @if(!empty($activitiesByCategory))
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Recent Activities by Category</h3>
        <div class="space-y-4">
            @foreach($activitiesByCategory as $category => $activities)
            <div>
                <h4 class="text-sm font-semibold text-dark-300 uppercase tracking-wider mb-2">{{ $category }}</h4>
                <div class="space-y-2">
                    @foreach($activities->take(5) as $activity)
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <div>
                            <p class="text-dark-200 text-sm font-medium">{{ $activity->title }}</p>
                            <p class="text-dark-500 text-xs">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-primary-400 font-semibold text-sm">+{{ $activity->points_earned ?? 0 }} pts</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
