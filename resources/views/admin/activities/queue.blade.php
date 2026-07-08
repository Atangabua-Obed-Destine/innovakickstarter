@extends('layouts.app')

@section('title', 'Activity Review Queue')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Activity Review Queue</h1>
            <p class="text-dark-400 mt-1">Review and approve fellow activity submissions</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-amber-500/20 text-amber-400">
                {{ $activities->total() }} pending
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.activities.queue') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-48">
                <label for="track_id" class="block text-sm font-medium text-dark-400 mb-1">Track</label>
                <select name="track_id" id="track_id" class="input-field w-full">
                    <option value="">All Tracks</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>
                            {{ $track->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex-1 min-w-48">
                <label for="type" class="block text-sm font-medium text-dark-400 mb-1">Activity Type</label>
                <select name="type" id="type" class="input-field w-full">
                    <option value="">All Types</option>
                    <option value="course" {{ ($filters['type'] ?? '') == 'course' ? 'selected' : '' }}>Course</option>
                    <option value="project" {{ ($filters['type'] ?? '') == 'project' ? 'selected' : '' }}>Project</option>
                    <option value="certification" {{ ($filters['type'] ?? '') == 'certification' ? 'selected' : '' }}>Certification</option>
                    <option value="article" {{ ($filters['type'] ?? '') == 'article' ? 'selected' : '' }}>Article</option>
                    <option value="contribution" {{ ($filters['type'] ?? '') == 'contribution' ? 'selected' : '' }}>Open Source</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                </button>
                @if(!empty($filters['track_id']) || !empty($filters['type']))
                    <a href="{{ route('admin.activities.queue') }}" class="btn-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Activities List -->
    @if($activities->count() > 0)
        <div class="space-y-4">
            @foreach($activities as $activity)
                <div class="card p-6 hover:border-dark-600 transition-colors">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                        <!-- Fellow Info -->
                        <div class="flex items-center gap-4 lg:w-64 flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg">
                                {{ $activity->fellow ? strtoupper(substr($activity->fellow->name, 0, 2)) : 'NA' }}
                            </div>
                            <div>
                                <p class="font-medium text-white">
                                    {{ $activity->fellow?->name ?? 'Unknown Fellow' }}
                                </p>
                                <p class="text-sm text-dark-400">{{ $activity->track?->name ?? 'No Track' }}</p>
                            </div>
                        </div>

                        <!-- Activity Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-white text-lg">{{ $activity->title }}</h3>
                                    <div class="flex flex-wrap items-center gap-3 mt-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @switch($activity->type->value ?? $activity->type)
                                                @case('course') bg-blue-500/20 text-blue-400 @break
                                                @case('project') bg-purple-500/20 text-purple-400 @break
                                                @case('certification') bg-green-500/20 text-green-400 @break
                                                @case('article') bg-amber-500/20 text-amber-400 @break
                                                @case('contribution') bg-teal-500/20 text-teal-400 @break
                                                @default bg-dark-600 text-dark-300
                                            @endswitch
                                        ">
                                            {{ ucfirst($activity->type->value ?? $activity->type) }}
                                        </span>
                                        <span class="text-dark-500 text-sm">
                                            Submitted {{ $activity->created_at->diffForHumans() }}
                                        </span>
                                        @if($activity->points)
                                            <span class="text-primary-400 text-sm font-medium">
                                                +{{ $activity->points }} pts
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($activity->description)
                                <p class="text-dark-300 mt-3 line-clamp-2">{{ $activity->description }}</p>
                            @endif

                            @if($activity->url)
                                <a href="{{ $activity->url }}" target="_blank" rel="noopener noreferrer" 
                                   class="inline-flex items-center gap-1 text-primary-400 hover:text-primary-300 text-sm mt-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    View Submission
                                </a>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-row lg:flex-col gap-2 lg:w-32 flex-shrink-0">
                            <a href="{{ route('admin.activities.review', $activity) }}" 
                               class="btn-primary text-center flex-1 lg:flex-initial">
                                Review
                            </a>
                            <div class="flex gap-2 flex-1 lg:flex-initial">
                                <form action="{{ route('admin.activities.approve', $activity) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn-success w-full text-xs py-2" 
                                            onclick="return confirm('Approve this activity?')">
                                        ✓ Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.activities.reject', $activity) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="btn-danger w-full text-xs py-2"
                                            onclick="return confirm('Reject this activity?')">
                                        ✕ Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $activities->appends($filters)->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="card p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-green-500/20 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">All Caught Up!</h3>
            <p class="text-dark-400 max-w-md mx-auto">
                @if(!empty($filters['track_id']) || !empty($filters['type']))
                    No pending activities match your current filters.
                    <a href="{{ route('admin.activities.queue') }}" class="text-primary-400 hover:underline">Clear filters</a>
                @else
                    There are no activities waiting for review. Check back later!
                @endif
            </p>
        </div>
    @endif
</div>
@endsection
