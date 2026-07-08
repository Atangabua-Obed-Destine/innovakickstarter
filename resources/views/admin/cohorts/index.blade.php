@extends('layouts.app')

@section('title', 'Manage Cohorts')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manage Cohorts</h1>
            <p class="text-dark-400">Create and manage fellow cohorts</p>
        </div>
        <a href="{{ route('admin.cohorts.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Cohort
        </a>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-600/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-600/20 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-lg">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-600/20 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    <!-- Filters & Search -->
    <form method="GET" action="{{ route('admin.cohorts.index') }}" class="card p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search cohorts..." class="form-input pl-10">
            </div>
            <select name="track_id" class="form-input w-full sm:w-48">
                <option value="">All Tracks</option>
                @foreach($tracks as $track)
                    <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>
                        {{ $track->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-input w-full sm:w-40">
                <option value="">All Status</option>
                <option value="draft" {{ ($filters['status'] ?? '') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="upcoming" {{ ($filters['status'] ?? '') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="archived" {{ ($filters['status'] ?? '') == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
            <button type="submit" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            @if(!empty($filters['search']) || !empty($filters['track_id']) || !empty($filters['status']))
                <a href="{{ route('admin.cohorts.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-dark-400 text-sm">Total Cohorts</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-green-400">{{ $stats['active'] }}</p>
            <p class="text-dark-400 text-sm">Active</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-blue-400">{{ $stats['upcoming'] }}</p>
            <p class="text-dark-400 text-sm">Upcoming</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-dark-400">{{ $stats['completed'] }}</p>
            <p class="text-dark-400 text-sm">Completed</p>
        </div>
    </div>

    <!-- Cohorts Grid -->
    @if($cohorts->count() > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cohorts as $cohort)
            @php
                $trackColors = [
                    'software-engineering' => 'bg-primary-500',
                    'data-science' => 'bg-teal-500',
                    'product-management' => 'bg-blue-500',
                    'digital-marketing' => 'bg-amber-500',
                    'ui-ux-design' => 'bg-pink-500',
                ];
                $trackColor = $trackColors[$cohort->track?->slug ?? ''] ?? 'bg-gray-500';
                
                $statusClasses = match($cohort->status) {
                    'active' => 'bg-green-600/20 text-green-400 border-green-500/30',
                    'upcoming' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                    'completed' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
                    'draft' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
                    'archived' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
                    'cancelled' => 'bg-red-600/20 text-red-400 border-red-500/30',
                    default => 'bg-dark-600/20 text-dark-400 border-dark-500/30'
                };
            @endphp
            <div class="card overflow-hidden hover:border-primary-500/50 transition-colors">
                <!-- Header -->
                <div class="p-4 border-b border-dark-700">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-3 h-3 rounded-full {{ $trackColor }}"></span>
                                <span class="text-dark-400 text-sm">{{ $cohort->track?->name ?? 'No Track' }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-white">{{ $cohort->name }}</h3>
                        </div>
                        <span class="badge {{ $statusClasses }}">{{ $cohort->status_label }}</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="p-4 space-y-4">
                    <!-- Fellows Count -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-dark-400 text-sm">Fellows</span>
                            <span class="text-dark-200 font-medium">{{ $cohort->fellows_count }}/{{ $cohort->max_fellows }}</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $cohort->fill_percentage >= 90 ? 'bg-amber-500' : 'bg-primary-500' }} rounded-full" 
                                 style="width: {{ $cohort->fill_percentage }}%"></div>
                        </div>
                        @if($cohort->canEnroll())
                            <p class="text-xs text-dark-500 mt-1">{{ $cohort->spots_remaining }} spots available</p>
                        @elseif($cohort->status !== 'completed' && $cohort->status !== 'archived')
                            <p class="text-xs text-amber-400 mt-1">Enrollment closed</p>
                        @endif
                    </div>

                    <!-- Dates -->
                    <div class="flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-2 text-dark-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $cohort->start_date->format('M j, Y') }}</span>
                        </div>
                        <span class="text-dark-600">→</span>
                        <span class="text-dark-400">{{ $cohort->end_date->format('M j, Y') }}</span>
                    </div>

                    <!-- Progress (for active cohorts) -->
                    @if($cohort->isActive())
                    <div class="pt-2 border-t border-dark-700">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-dark-400 text-sm">Week {{ $cohort->current_week }} of {{ $cohort->duration_weeks }}</span>
                            <span class="text-dark-200 text-sm">{{ $cohort->days_remaining }} days left</span>
                        </div>
                        <div class="h-1.5 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $cohort->progress_percentage }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if($cohort->status !== 'draft' && $cohort->status !== 'upcoming')
                    <!-- Score Metrics -->
                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-dark-700">
                        <div class="text-center">
                            <p class="text-xl font-bold {{ $cohort->avg_score >= 80 ? 'text-green-400' : ($cohort->avg_score >= 60 ? 'text-amber-400' : 'text-dark-400') }}">
                                {{ number_format($cohort->avg_score, 0) }}%
                            </p>
                            <p class="text-dark-500 text-xs">Avg Score</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-bold text-primary-400">{{ $cohort->completion_rate }}%</p>
                            <p class="text-dark-500 text-xs">Completion</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="p-4 border-t border-dark-700 flex items-center justify-between">
                    <a href="{{ route('admin.cohorts.show', $cohort) }}" class="text-primary-400 text-sm hover:underline">View Details</a>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.cohorts.edit', $cohort) }}" 
                           class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" 
                           title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @if($cohort->status === 'draft' || ($cohort->fellows_count === 0))
                        <form action="{{ route('admin.cohorts.destroy', $cohort) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this cohort?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors" 
                                    title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-dark-500 text-sm">
            Showing {{ $cohorts->firstItem() ?? 0 }} to {{ $cohorts->lastItem() ?? 0 }} of {{ $cohorts->total() }} cohorts
        </p>
        {{ $cohorts->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-dark-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <h3 class="text-xl font-semibold text-white mb-2">No Cohorts Found</h3>
        <p class="text-dark-400 mb-6">
            @if(!empty($filters['search']) || !empty($filters['track_id']) || !empty($filters['status']))
                No cohorts match your filters. Try adjusting your search criteria.
            @else
                Get started by creating your first cohort.
            @endif
        </p>
        @if(empty($filters['search']) && empty($filters['track_id']) && empty($filters['status']))
            <a href="{{ route('admin.cohorts.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create First Cohort
            </a>
        @else
            <a href="{{ route('admin.cohorts.index') }}" class="btn btn-secondary">Clear Filters</a>
        @endif
    </div>
    @endif
</div>
@endsection
