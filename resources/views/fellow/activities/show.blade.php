@extends('layouts.app')

@section('title', $activity->title ?? 'Activity')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Activities
    </a>

    <!-- Activity Header -->
    <div class="card p-6">
        <div class="flex flex-col lg:flex-row lg:items-start gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
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
                    <span class="text-dark-500">{{ $activity->track->name ?? 'General' }}</span>
                </div>
                
                <h1 class="text-2xl font-bold text-white mb-3">{{ $activity->title ?? 'Activity Title' }}</h1>
                
                <p class="text-dark-300 mb-4">
                    {{ $activity->description ?? 'Complete this activity to earn points and boost your Career Capital score.' }}
                </p>
                
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-2 text-dark-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $activity->duration ?? '30' }} minutes</span>
                    </div>
                    <div class="flex items-center gap-2 text-dark-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span class="text-primary-400 font-medium">{{ $activity->points ?? 25 }} points</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-dark-500">Difficulty:</span>
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="w-2 h-2 rounded-full {{ $i <= ($activity->difficulty ?? 2) ? 'bg-primary-500' : 'bg-dark-700' }}"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Completion Status -->
            <div class="lg:text-right">
                @if($completion ?? false)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600/20 text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Completed
                    </div>
                    <p class="text-dark-500 text-sm mt-2">{{ $completion->completed_at?->format('M d, Y') }}</p>
                @else
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-dark-700 text-dark-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Not Started
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Learning Objectives -->
    @if($activity->objectives ?? false)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Learning Objectives
        </h2>
        <ul class="space-y-2">
            @foreach($activity->objectives as $objective)
                <li class="flex items-start gap-3 text-dark-300">
                    <svg class="w-5 h-5 text-teal-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $objective }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Activity Content -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Activity Content
        </h2>
        
        <div class="prose prose-invert max-w-none">
            {!! $activity->content ?? '<p class="text-dark-400">Activity content will appear here. This may include instructions, reading materials, video embeds, or interactive elements.</p>' !!}
        </div>
    </div>

    <!-- Resources -->
    @if($activity->resources ?? false)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Resources
        </h2>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($activity->resources as $resource)
                <a href="{{ $resource['url'] }}" target="_blank" 
                   class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center text-dark-400">
                        @if($resource['type'] === 'pdf')
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                        @elseif($resource['type'] === 'video')
                            <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-dark-200 font-medium truncate">{{ $resource['title'] }}</p>
                        <p class="text-dark-500 text-sm">{{ $resource['type'] }}</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Submission Form -->
    @if(!($completion ?? false))
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Mark as Complete
        </h2>
        
        <form action="{{ route('activities.complete', $activity->id ?? 1) }}" method="POST" class="space-y-4">
            @csrf
            
            @if(($activity->type ?? 'lesson') !== 'lesson')
            <div>
                <label class="form-label">Submission URL (Optional)</label>
                <input type="url" name="submission_url" class="form-input" 
                       placeholder="https://github.com/yourproject or link to your work">
                <p class="text-dark-500 text-xs mt-1">If applicable, provide a link to your completed work</p>
            </div>
            @endif
            
            <div>
                <label class="form-label">Reflection (Optional)</label>
                <textarea name="reflection" rows="3" class="form-input" 
                          placeholder="What did you learn? What challenges did you face?"></textarea>
            </div>
            
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Complete Activity (+{{ $activity->points ?? 25 }} pts)
                </button>
                <span class="text-dark-500 text-sm">You can always edit your submission later</span>
            </div>
        </form>
    </div>
    @else
    <!-- Completion Details -->
    <div class="card p-6 bg-green-600/5 border-green-500/30">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-green-600/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-green-400 mb-1">Activity Completed!</h3>
                <p class="text-dark-300 mb-3">You earned <span class="text-green-400 font-semibold">+{{ $activity->points ?? 25 }} points</span> for completing this activity.</p>
                
                @if($completion->reflection ?? false)
                <div class="p-3 bg-dark-800 rounded-lg">
                    <p class="text-dark-400 text-sm mb-1">Your reflection:</p>
                    <p class="text-dark-200">{{ $completion->reflection }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Related Activities -->
    @if(count($relatedActivities ?? []) > 0)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Related Activities</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($relatedActivities as $related)
                <a href="{{ route('activities.show', $related->id) }}" 
                   class="p-4 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <h4 class="text-dark-200 font-medium mb-1">{{ $related->title }}</h4>
                    <p class="text-dark-500 text-sm">{{ $related->points }} points</p>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
