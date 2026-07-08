@extends('layouts.app')

@section('title', 'Activities')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Activities</h1>
            <p class="text-dark-400 mt-1">Complete activities to build your Career Capital score.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" placeholder="Search activities..." 
                       class="form-input pl-10 w-64"
                       x-data x-model="search">
                <svg class="w-5 h-5 text-dark-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-4" x-data="{ activeFilter: 'all', activeType: 'all' }">
            <div class="flex items-center gap-2">
                <span class="text-sm text-dark-400">Status:</span>
                <div class="flex rounded-lg bg-dark-800 p-1">
                    <button @click="activeFilter = 'all'" 
                            :class="activeFilter === 'all' ? 'bg-dark-700 text-white' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        All
                    </button>
                    <button @click="activeFilter = 'available'" 
                            :class="activeFilter === 'available' ? 'bg-dark-700 text-white' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        Available
                    </button>
                    <button @click="activeFilter = 'in_progress'" 
                            :class="activeFilter === 'in_progress' ? 'bg-dark-700 text-white' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        In Progress
                    </button>
                    <button @click="activeFilter = 'completed'" 
                            :class="activeFilter === 'completed' ? 'bg-dark-700 text-white' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        Completed
                    </button>
                </div>
            </div>
            
            <div class="h-6 w-px bg-dark-700"></div>
            
            <div class="flex items-center gap-2">
                <span class="text-sm text-dark-400">Type:</span>
                <div class="flex rounded-lg bg-dark-800 p-1">
                    <button @click="activeType = 'all'" 
                            :class="activeType === 'all' ? 'bg-dark-700 text-white' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        All
                    </button>
                    <button @click="activeType = 'lesson'" 
                            :class="activeType === 'lesson' ? 'bg-purple-600/20 text-purple-400' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        📚 Lessons
                    </button>
                    <button @click="activeType = 'challenge'" 
                            :class="activeType === 'challenge' ? 'bg-blue-600/20 text-blue-400' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        ⚡ Challenges
                    </button>
                    <button @click="activeType = 'project'" 
                            :class="activeType === 'project' ? 'bg-teal-600/20 text-teal-400' : 'text-dark-400 hover:text-white'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors">
                        🚀 Projects
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activities ?? [] as $activity)
            <div class="card card-hover overflow-hidden group">
                <!-- Activity Header with Type Badge -->
                <div class="p-5 pb-0">
                    <div class="flex items-start justify-between mb-3">
                        <span class="badge 
                            @switch($activity->type ?? 'lesson')
                                @case('lesson') badge-primary @break
                                @case('challenge') bg-blue-600/20 text-blue-400 border-blue-500/30 @break
                                @case('project') bg-teal-600/20 text-teal-400 border-teal-500/30 @break
                                @default badge-primary
                            @endswitch">
                            @switch($activity->type ?? 'lesson')
                                @case('lesson') 📚 Lesson @break
                                @case('challenge') ⚡ Challenge @break
                                @case('project') 🚀 Project @break
                                @default 📋 Task
                            @endswitch
                        </span>
                        <span class="text-sm text-dark-500">{{ $activity->duration ?? '30' }} min</span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-white mb-2 group-hover:text-primary-400 transition-colors">
                        {{ $activity->title ?? 'Activity Title' }}
                    </h3>
                    
                    <p class="text-dark-400 text-sm line-clamp-2 mb-4">
                        {{ $activity->description ?? 'Complete this activity to earn points and boost your Career Capital score.' }}
                    </p>
                </div>
                
                <!-- Activity Meta -->
                <div class="px-5 pb-4">
                    <div class="flex items-center gap-4 text-sm text-dark-500 mb-4">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>{{ $activity->track->name ?? 'General' }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span>{{ $activity->points ?? 25 }} pts</span>
                        </div>
                    </div>
                    
                    <!-- Difficulty -->
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xs text-dark-500">Difficulty:</span>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="w-2 h-2 rounded-full {{ $i <= ($activity->difficulty ?? 2) ? 'bg-primary-500' : 'bg-dark-700' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                <!-- Action Button -->
                <div class="px-5 pb-5">
                    @if(($activity->status ?? 'available') === 'completed')
                        <button disabled class="w-full btn bg-green-600/20 text-green-400 cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Completed
                        </button>
                    @elseif(($activity->status ?? 'available') === 'in_progress')
                        <a href="{{ route('activities.show', $activity->id ?? 1) }}" class="w-full btn btn-primary">
                            Continue Activity
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('activities.show', $activity->id ?? 1) }}" class="w-full btn btn-secondary group-hover:btn-primary transition-all">
                            Start Activity
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="card p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No activities available</h3>
                    <p class="text-dark-400 max-w-md mx-auto mb-6">
                        Activities will appear here once your cohort admin adds them. Check back soon!
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($activities) && $activities->hasPages())
        <div class="flex justify-center">
            {{ $activities->links() }}
        </div>
    @endif
</div>
@endsection
