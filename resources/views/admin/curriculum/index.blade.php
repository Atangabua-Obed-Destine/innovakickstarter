@extends('layouts.app')

@section('title', "Curriculum — {$track->name}")

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
                <a href="{{ route('admin.tracks.index') }}" class="hover:text-white transition">Tracks</a>
                <span>/</span>
                <span class="text-white">{{ $track->name }}</span>
                <span>/</span>
                <span class="text-primary-400">Curriculum</span>
            </div>
            <h1 class="text-2xl font-bold text-white">{{ $track->name }} Curriculum</h1>
            <p class="text-dark-400 mt-1">Manage milestones, activities, and progression</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.curriculum.analytics', $track) }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Analytics
            </a>
            <a href="{{ route('admin.curriculum.pairs', $track) }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pairs
            </a>
            <a href="{{ route('admin.curriculum.milestones.create', $track) }}" class="btn-primary text-sm">
                <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Milestone
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center text-xl">🏁</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $milestones->count() }}</p>
                    <p class="text-dark-400 text-sm">Milestones</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center text-xl">📋</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $milestones->sum(fn($m) => $m->curriculumActivities->count()) }}</p>
                    <p class="text-dark-400 text-sm">Total Activities</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center text-xl">📈</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ number_format($analytics['completion_rate'] ?? 0, 1) }}%</p>
                    <p class="text-dark-400 text-sm">Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center text-xl">⚠️</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $analytics['overdue_count'] ?? 0 }}</p>
                    <p class="text-dark-400 text-sm">Overdue Items</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <!-- Milestones List -->
    @forelse($milestones as $milestone)
    <div class="card overflow-hidden" id="milestone-{{ $milestone->id }}">
        <!-- Milestone Header -->
        <div class="p-6 border-b border-dark-700 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center text-2xl">
                    {{ $milestone->badge_icon ?? '🏁' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-dark-400 text-sm font-mono">M{{ $milestone->sequence_order }}</span>
                        <h3 class="text-lg font-semibold text-white">{{ $milestone->title }}</h3>
                        @if($milestone->is_required)
                            <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full">Required</span>
                        @endif
                    </div>
                    <p class="text-dark-400 text-sm mt-0.5">
                        {{ Str::limit($milestone->description, 100) }}
                        · {{ $milestone->curriculumActivities->count() }} activities
                        @if($milestone->estimated_duration_days)
                            · ~{{ $milestone->estimated_duration_days }} days
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.curriculum.activities.create', [$track, $milestone]) }}" 
                   class="btn-secondary text-xs px-3 py-1.5">
                    + Activity
                </a>
                <a href="{{ route('admin.curriculum.milestones.edit', [$track, $milestone]) }}" 
                   class="btn-secondary text-xs px-3 py-1.5">
                    Edit
                </a>
                <form action="{{ route('admin.curriculum.milestones.destroy', [$track, $milestone]) }}" 
                      method="POST" class="inline"
                      onsubmit="return confirm('Delete this milestone?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-300 p-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Activities Table -->
        @if($milestone->curriculumActivities->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-sm">#</th>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium text-sm">Activity</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium text-sm">Type</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium text-sm">Difficulty</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium text-sm">Points</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium text-sm">Deadline</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium text-sm">Required</th>
                        <th class="text-right py-3 px-6 text-dark-400 font-medium text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @foreach($milestone->curriculumActivities->sortBy('order') as $activity)
                    <tr class="hover:bg-dark-800/50 transition">
                        <td class="py-3 px-6 text-dark-400 text-sm font-mono">{{ $activity->sequence_order }}</td>
                        <td class="py-3 px-6">
                            <div class="text-white font-medium">{{ $activity->title }}</div>
                            <div class="text-dark-400 text-xs mt-0.5">{{ Str::limit($activity->description, 60) }}</div>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-dark-300">
                                {{ $activity->type?->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $activity->difficulty_level?->badgeClass() ?? 'bg-dark-700 text-dark-300' }}">
                                {{ $activity->difficulty_level?->label() ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center text-white font-medium">{{ $activity->points }}</td>
                        <td class="py-3 px-6 text-center text-dark-400 text-sm">
                            {{ $activity->deadline_days ? $activity->deadline_days . 'd' : '—' }}
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($activity->is_required)
                                <span class="text-green-400">●</span>
                            @else
                                <span class="text-dark-600">○</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.curriculum.activities.edit', [$track, $activity]) }}" class="text-dark-400 hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('admin.curriculum.activities.destroy', [$track, $activity]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this activity?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-dark-400 hover:text-red-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-dark-400">
            <p class="text-lg mb-2">No activities yet</p>
            <a href="{{ route('admin.curriculum.activities.create', [$track, $milestone]) }}" class="btn-primary text-sm">Add First Activity</a>
        </div>
        @endif
    </div>
    @empty
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">🎯</div>
        <h3 class="text-xl font-semibold text-white mb-2">No Curriculum Yet</h3>
        <p class="text-dark-400 mb-6">Start building the curriculum by creating the first milestone.</p>
        <a href="{{ route('admin.curriculum.milestones.create', $track) }}" class="btn-primary">
            Create First Milestone
        </a>
    </div>
    @endforelse
</div>
@endsection
