@extends('layouts.app')

@section('title', 'Mock Interviews')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Mock Interviews</h1>
            <p class="text-dark-400 mt-1">Practice your interview skills with AI-powered mock interviews.</p>
        </div>
        @if($canSchedule)
            <a href="{{ route('interviews.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Schedule Interview
            </a>
        @else
            <button disabled class="btn bg-dark-700 text-dark-400 cursor-not-allowed">
                Weekly Limit Reached
            </button>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Completed</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_completed'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Average Score</p>
                    <p class="text-2xl font-bold text-primary-400">
                        {{ $stats['average_score'] ? number_format($stats['average_score'], 3) : 'N/A' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">This Week</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $stats['this_week'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Interviews -->
    <div class="card">
        <div class="p-5 border-b border-dark-700">
            <h2 class="text-lg font-semibold text-white">Upcoming Interviews</h2>
        </div>
        
        @if($upcoming->isEmpty())
            <div class="p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-dark-800 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Upcoming Interviews</h3>
                <p class="text-dark-400 mb-6 max-w-md mx-auto">
                    Schedule a mock interview to practice your skills and boost your Career Capital score.
                </p>
                @if($canSchedule)
                    <a href="{{ route('interviews.create') }}" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Schedule Your First Interview
                    </a>
                @endif
            </div>
        @else
            <div class="divide-y divide-dark-700">
                @foreach($upcoming as $interview)
                    <div class="p-5 hover:bg-dark-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Interview Type Icon -->
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0
                                @if($interview->mode->value === 'ai') bg-purple-600/20 text-purple-400
                                @else bg-blue-600/20 text-blue-400 @endif">
                                @if($interview->mode->value === 'ai')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Interview Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-white mb-1">
                                    {{ ucfirst(str_replace('_', ' ', $interview->type->value ?? $interview->type)) }} Interview
                                </h3>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-dark-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $interview->scheduled_at->format('M d, Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $interview->scheduled_at->format('g:i A') }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($interview->mode->value === 'ai') bg-purple-600/20 text-purple-400
                                        @else bg-blue-600/20 text-blue-400 @endif">
                                        {{ $interview->mode->label() }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                @if($interview->scheduled_at->diffInMinutes(now()) <= 15 && $interview->scheduled_at->isFuture())
                                    <a href="{{ route('interviews.ai-room', $interview) }}" class="btn btn-primary btn-sm">
                                        Join Now
                                    </a>
                                @else
                                    <span class="text-dark-500 text-sm">
                                        Starts {{ $interview->scheduled_at->diffForHumans() }}
                                    </span>
                                @endif
                                
                                <form action="{{ route('interviews.cancel', $interview) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to cancel this interview?')">
                                    @csrf
                                    <button type="submit" class="p-2 text-dark-400 hover:text-red-400 hover:bg-red-600/10 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Past Interviews -->
    <div class="card">
        <div class="p-5 border-b border-dark-700">
            <h2 class="text-lg font-semibold text-white">Past Interviews</h2>
        </div>
        
        @if($past->isEmpty())
            <div class="p-8 text-center">
                <p class="text-dark-400">No completed interviews yet.</p>
            </div>
        @else
            <div class="divide-y divide-dark-700">
                @foreach($past as $interview)
                    <div class="p-5 hover:bg-dark-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Status Icon -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                @if($interview->status->value === 'completed') bg-green-600/20 text-green-400
                                @elseif($interview->status->value === 'cancelled') bg-red-600/20 text-red-400
                                @else bg-amber-600/20 text-amber-400 @endif">
                                @if($interview->status->value === 'completed')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif($interview->status->value === 'cancelled')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Interview Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-base font-medium text-white">
                                        {{ ucfirst(str_replace('_', ' ', $interview->type->value ?? $interview->type)) }} Interview
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($interview->status->value === 'completed') bg-green-600/20 text-green-400
                                        @elseif($interview->status->value === 'cancelled') bg-red-600/20 text-red-400
                                        @else bg-amber-600/20 text-amber-400 @endif">
                                        {{ ucfirst($interview->status->value) }}
                                    </span>
                                </div>
                                <p class="text-dark-500 text-sm">
                                    {{ $interview->scheduled_at->format('M d, Y - g:i A') }}
                                </p>
                            </div>
                            
                            <!-- Score -->
                            @if($interview->status->value === 'completed' && $interview->score)
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-primary-400">{{ number_format($interview->score, 3) }}</p>
                                    <p class="text-dark-500 text-xs">Score</p>
                                </div>
                            @endif
                            
                            <!-- View Details -->
                            @if($interview->status->value === 'completed')
                                <a href="{{ route('interviews.show', $interview) }}" class="btn btn-secondary btn-sm">
                                    View Details
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($past->hasPages())
                <div class="p-4 border-t border-dark-700">
                    {{ $past->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Interview Tips -->
    <div class="card p-6 bg-gradient-to-r from-primary-900/20 to-blue-900/20 border-primary-800/30">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary-600/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-2">Tips for Success</h3>
                <ul class="text-dark-300 text-sm space-y-1">
                    <li>• Practice the STAR method (Situation, Task, Action, Result) for behavioral questions</li>
                    <li>• For technical interviews, think out loud and explain your reasoning</li>
                    <li>• Prepare questions to ask at the end of the interview</li>
                    <li>• Review your past interview feedback to identify areas for improvement</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
