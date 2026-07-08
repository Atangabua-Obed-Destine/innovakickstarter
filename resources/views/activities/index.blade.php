@extends('layouts.app')

@section('title', 'My Activities')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">My Activities</h1>
            <p class="text-dark-400 mt-1">Track your submissions and Career Capital progress.</p>
        </div>
        <a href="{{ route('activities.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Log New Activity
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="flex lg:grid lg:grid-cols-5 gap-4 overflow-x-auto pb-4 snap-x snap-mandatory hide-scrollbar">
        <div class="card p-4 flex-none w-48 lg:w-auto snap-center">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-dark-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-4 flex-none w-48 lg:w-auto snap-center">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ $stats['approved'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-4 flex-none w-48 lg:w-auto snap-center">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Pending</p>
                    <p class="text-2xl font-bold text-amber-400">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-4 flex-none w-48 lg:w-auto snap-center">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Rejected</p>
                    <p class="text-2xl font-bold text-red-400">{{ $stats['rejected'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-red-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-4 flex-none w-48 lg:w-auto snap-center">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Points</p>
                    <p class="text-2xl font-bold text-primary-400">{{ $stats['total_points'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('activities.index') }}" class="flex items-center gap-4 overflow-x-auto pb-2 hide-scrollbar">
            <div class="flex items-center gap-2 flex-shrink-0">
                <label class="text-sm font-medium text-dark-400">Status:</label>
                <select name="status" class="form-input py-2 w-32 rounded-xl bg-dark-800/80 border-dark-700/50 text-sm focus:ring-primary-500" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <div class="flex items-center gap-2 flex-shrink-0">
                <label class="text-sm font-medium text-dark-400">Track:</label>
                <select name="track_id" class="form-input py-2 w-40 rounded-xl bg-dark-800/80 border-dark-700/50 text-sm focus:ring-primary-500" onchange="this.form.submit()">
                    <option value="">All Tracks</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>
                            {{ $track->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-2 flex-shrink-0">
                <label class="text-sm font-medium text-dark-400">Type:</label>
                <select name="type" class="form-input py-2 w-36 rounded-xl bg-dark-800/80 border-dark-700/50 text-sm focus:ring-primary-500" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="project" {{ ($filters['type'] ?? '') === 'project' ? 'selected' : '' }}>Project</option>
                    <option value="certification" {{ ($filters['type'] ?? '') === 'certification' ? 'selected' : '' }}>Certification</option>
                    <option value="course" {{ ($filters['type'] ?? '') === 'course' ? 'selected' : '' }}>Course</option>
                    <option value="workshop" {{ ($filters['type'] ?? '') === 'workshop' ? 'selected' : '' }}>Workshop</option>
                    <option value="blog_post" {{ ($filters['type'] ?? '') === 'blog_post' ? 'selected' : '' }}>Blog Post</option>
                    <option value="open_source" {{ ($filters['type'] ?? '') === 'open_source' ? 'selected' : '' }}>Open Source</option>
                    <option value="mentoring" {{ ($filters['type'] ?? '') === 'mentoring' ? 'selected' : '' }}>Mentoring</option>
                    <option value="peer_review" {{ ($filters['type'] ?? '') === 'peer_review' ? 'selected' : '' }}>Peer Review</option>
                </select>
            </div>
            
            @if(array_filter($filters ?? []))
                <a href="{{ route('activities.index') }}" class="text-sm font-medium text-primary-400 hover:text-primary-300 flex-shrink-0 px-2 active:scale-95 transition-transform">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- Activities List -->
    <div class="card overflow-hidden">
        @if($activities->isEmpty())
            <div class="p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-dark-800 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Activities Yet</h3>
                <p class="text-dark-400 mb-6 max-w-md mx-auto">
                    Start logging your learning activities, projects, and achievements to build your Career Capital score.
                </p>
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log Your First Activity
                </a>
            </div>
        @else
            <div class="divide-y divide-dark-700">
                @foreach($activities as $activity)
                    <div class="p-5 hover:bg-dark-800/50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Activity Type Icon -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                @switch($activity->type)
                                    @case('project') bg-teal-600/20 text-teal-400 @break
                                    @case('certification') bg-amber-600/20 text-amber-400 @break
                                    @case('course') bg-rose-600/20 text-rose-400 @break
                                    @case('workshop') bg-purple-600/20 text-purple-400 @break
                                    @case('blog_post') bg-blue-600/20 text-blue-400 @break
                                    @case('open_source') bg-green-600/20 text-green-400 @break
                                    @case('mentoring') bg-pink-600/20 text-pink-400 @break
                                    @case('peer_review') bg-indigo-600/20 text-indigo-400 @break
                                    @default bg-dark-700 text-dark-400
                                @endswitch">
                                @switch($activity->type)
                                    @case('project')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        @break
                                    @case('certification')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                        @break
                                    @case('blog_post')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                @endswitch
                            </div>
                            
                            <!-- Activity Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-medium text-white mb-1">
                                            <a href="{{ route('activities.show', $activity) }}" class="hover:text-primary-400 transition-colors">
                                                {{ $activity->title }}
                                            </a>
                                        </h3>
                                        <p class="text-dark-400 text-sm line-clamp-2 mb-3">
                                            {{ Str::limit($activity->description, 150) }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 text-sm">
                                            <span class="text-dark-500">
                                                {{ ucfirst(str_replace('_', ' ', $activity->type)) }}
                                            </span>
                                            @if($activity->track)
                                                <span class="text-dark-600">•</span>
                                                <span class="text-dark-500">{{ $activity->track->name }}</span>
                                            @endif
                                            <span class="text-dark-600">•</span>
                                            <span class="text-dark-500">{{ $activity->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                        <!-- Status Badge -->
                                        @switch($activity->status)
                                            @case('approved')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-600/20 text-green-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Approved
                                                </span>
                                                @break
                                            @case('pending')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-600/20 text-amber-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Pending Review
                                                </span>
                                                @break
                                            @case('rejected')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-600/20 text-red-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Rejected
                                                </span>
                                                @break
                                        @endswitch
                                        
                                        <!-- Points -->
                                        @if($activity->status === 'approved' && $activity->points_earned)
                                            <span class="text-primary-400 font-semibold">
                                                +{{ $activity->points_earned }} pts
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($activities->hasPages())
                <div class="p-4 border-t border-dark-700">
                    {{ $activities->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
