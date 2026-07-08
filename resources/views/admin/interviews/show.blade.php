@extends('layouts.app')

@section('title', 'Interview Details')

@section('content')
<div class="space-y-6" x-data="{ 
    showCancelModal: false, 
    showRescheduleModal: false,
    showAssignModal: false
}">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.interviews.index') }}" class="hover:text-white">Interviews</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $interview->type->shortLabel() }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">Interview Details</h1>
        </div>
        <div class="flex gap-2">
            @if($interview->mode->value === 'human' && in_array($interview->status->value, ['scheduled', 'pending']))
                <button @click="showAssignModal = true" class="btn btn-outline text-cyan-400 border-cyan-400 hover:bg-cyan-400/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $interview->interviewer ? 'Change' : 'Assign' }} Mentor
                </button>
            @endif

            @if(in_array($interview->status->value, ['scheduled', 'pending']))
                <button @click="showRescheduleModal = true" class="btn btn-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Reschedule
                </button>
                <button @click="showCancelModal = true" class="btn btn-outline text-red-400 border-red-400 hover:bg-red-400/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
            @endif

            <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Interview Overview Card -->
            <div class="card p-6">
                <div class="flex items-start gap-6">
                    <!-- Mode Icon -->
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shadow-lg
                        {{ $interview->mode->value === 'ai' ? 'bg-gradient-to-br from-purple-500 to-indigo-600' : 'bg-gradient-to-br from-cyan-500 to-blue-600' }}">
                        {{ $interview->mode->value === 'ai' ? '🤖' : '👤' }}
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-bold text-white">{{ $interview->type->label() }}</h2>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
                                    'scheduled' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                                    'in_progress' => 'bg-yellow-600/20 text-yellow-400 border-yellow-500/30',
                                    'completed' => 'bg-green-600/20 text-green-400 border-green-500/30',
                                    'cancelled' => 'bg-red-600/20 text-red-400 border-red-500/30',
                                    'no_show' => 'bg-orange-600/20 text-orange-400 border-orange-500/30',
                                ];
                                $statusClass = $statusColors[$interview->status->value] ?? 'bg-gray-600/20 text-gray-400';
                            @endphp
                            <span class="badge {{ $statusClass }}">
                                {{ $interview->status->label() }}
                            </span>
                        </div>
                        <p class="text-dark-400 mb-4">{{ $interview->mode->description() }}</p>
                        
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2 text-dark-300">
                                <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @if($interview->scheduled_at)
                                    {{ $interview->scheduled_at->format('M j, Y \a\t g:i A') }}
                                @else
                                    Not scheduled
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-dark-300">
                                <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $interview->formatted_duration }}
                            </div>
                            @if($interview->track)
                                <div class="flex items-center gap-2 text-dark-300">
                                    <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    {{ $interview->track->name }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Score Display -->
                    @if($interview->score !== null)
                        <div class="text-center">
                            @php
                                $scoreColor = $interview->score >= 80 ? 'text-green-400' : 
                                             ($interview->score >= 60 ? 'text-amber-400' : 'text-red-400');
                                $scoreBg = $interview->score >= 80 ? 'from-green-500/20 to-green-600/10' : 
                                          ($interview->score >= 60 ? 'from-amber-500/20 to-amber-600/10' : 'from-red-500/20 to-red-600/10');
                            @endphp
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $scoreBg }} flex items-center justify-center">
                                <span class="text-2xl font-bold {{ $scoreColor }}">{{ number_format($interview->score, 0) }}%</span>
                            </div>
                            <p class="text-dark-500 text-xs mt-1">Score</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rubric Scores (if completed) -->
            @if($interview->status->value === 'completed' && !empty($interview->rubric_scores))
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Performance Breakdown
                    </h3>
                    <div class="grid gap-4">
                        @foreach($interview->formatted_rubric_scores as $rubric)
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-dark-300 text-sm">{{ $rubric['label'] }}</span>
                                    <span class="text-dark-200 font-medium">{{ $rubric['score'] }}%</span>
                                </div>
                                <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                    @php
                                        $barColor = $rubric['score'] >= 80 ? 'bg-green-500' : 
                                                   ($rubric['score'] >= 60 ? 'bg-amber-500' : 'bg-red-500');
                                    @endphp
                                    <div class="h-full {{ $barColor }} rounded-full transition-all duration-500" 
                                         style="width: {{ $rubric['score'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- AI Feedback (if available) -->
            @if($interview->ai_feedback && count($interview->ai_feedback) > 0)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <span class="text-xl">🤖</span>
                        AI Feedback
                    </h3>
                    <div class="space-y-4">
                        @if(isset($interview->ai_feedback['summary']))
                            <div class="bg-dark-800 rounded-lg p-4">
                                <p class="text-dark-500 text-xs uppercase tracking-wider mb-2">Summary</p>
                                <p class="text-dark-200">{{ $interview->ai_feedback['summary'] }}</p>
                            </div>
                        @endif

                        @if(isset($interview->ai_feedback['strengths']))
                            <div class="bg-green-600/10 border border-green-500/20 rounded-lg p-4">
                                <p class="text-green-400 text-xs uppercase tracking-wider mb-2">Strengths</p>
                                <ul class="space-y-1">
                                    @foreach((array)$interview->ai_feedback['strengths'] as $strength)
                                        <li class="text-dark-200 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-green-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $strength }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(isset($interview->ai_feedback['improvements']))
                            <div class="bg-amber-600/10 border border-amber-500/20 rounded-lg p-4">
                                <p class="text-amber-400 text-xs uppercase tracking-wider mb-2">Areas for Improvement</p>
                                <ul class="space-y-1">
                                    @foreach((array)$interview->ai_feedback['improvements'] as $improvement)
                                        <li class="text-dark-200 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $improvement }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Interviewer Notes (if human interview) -->
            @if($interview->mode->value === 'human' && $interview->interviewer_notes)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Mentor Notes
                    </h3>
                    <div class="bg-dark-800 rounded-lg p-4">
                        <p class="text-dark-200 whitespace-pre-wrap">{{ $interview->interviewer_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Communication Metrics -->
            @if($interview->communication_insights && count($interview->communication_insights) > 0)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Communication Metrics
                    </h3>
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach($interview->communication_insights as $key => $metric)
                            <div class="bg-dark-800 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold {{ $metric['good'] ? 'text-green-400' : 'text-amber-400' }}">
                                    {{ $metric['value'] }}
                                </p>
                                <p class="text-dark-400 text-sm">{{ $metric['label'] }}</p>
                                <p class="text-dark-500 text-xs mt-1">Benchmark: {{ $metric['benchmark'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Fellow's Other Interviews -->
            @if($fellowInterviews->isNotEmpty())
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Other Interviews by This Fellow
                    </h3>
                    <div class="space-y-3">
                        @foreach($fellowInterviews as $otherInterview)
                            <a href="{{ route('admin.interviews.show', $otherInterview) }}" 
                               class="flex items-center justify-between p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="{{ $otherInterview->mode->value === 'ai' ? 'text-purple-400' : 'text-cyan-400' }}">
                                        {{ $otherInterview->mode->value === 'ai' ? '🤖' : '👤' }}
                                    </span>
                                    <div>
                                        <p class="text-dark-200 font-medium">{{ $otherInterview->type->shortLabel() }}</p>
                                        <p class="text-dark-500 text-xs">{{ $otherInterview->scheduled_at?->format('M j, Y') ?? 'Not scheduled' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($otherInterview->score !== null)
                                        <span class="font-bold {{ $otherInterview->score >= 70 ? 'text-green-400' : 'text-amber-400' }}">
                                            {{ number_format($otherInterview->score, 0) }}%
                                        </span>
                                    @endif
                                    <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Fellow Info -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Fellow Information</h3>
                @if($interview->fellow)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-lg font-bold text-white">
                            {{ strtoupper(substr($interview->fellow->name, 0, 2)) }}
                        </div>
                        <div>
                            <a href="{{ route('admin.fellows.show', $interview->fellow) }}" 
                               class="text-dark-100 font-medium hover:text-primary-400">
                                {{ $interview->fellow->name }}
                            </a>
                            <p class="text-dark-500 text-sm">{{ $interview->fellow->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.fellows.show', $interview->fellow) }}" class="btn btn-outline w-full text-sm">
                        View Fellow Profile
                    </a>
                @else
                    <p class="text-dark-500">Fellow not found</p>
                @endif
            </div>

            <!-- Mentor Info (for human interviews) -->
            @if($interview->mode->value === 'human')
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Assigned Mentor</h3>
                    @if($interview->interviewer)
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-lg font-bold text-white">
                                {{ strtoupper(substr($interview->interviewer->name, 0, 2)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.mentors.show', $interview->interviewer) }}" 
                                   class="text-dark-100 font-medium hover:text-cyan-400">
                                    {{ $interview->interviewer->name }}
                                </a>
                                <p class="text-dark-500 text-sm">{{ $interview->interviewer->email }}</p>
                            </div>
                        </div>
                        @if($interview->interviewer_rating)
                            <div class="flex items-center gap-1 mb-3">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $interview->interviewer_rating ? 'text-amber-400' : 'text-dark-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="text-dark-400 text-sm ml-2">Mentor Rating</span>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <div class="w-12 h-12 rounded-full bg-amber-600/20 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <p class="text-amber-400 font-medium mb-1">No Mentor Assigned</p>
                            <p class="text-dark-500 text-sm mb-3">This interview needs a mentor</p>
                            <button @click="showAssignModal = true" class="btn-primary w-full text-sm">
                                Assign Mentor
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Interview Timeline -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Timeline</h3>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-dark-500 mt-2"></div>
                        <div>
                            <p class="text-dark-300 text-sm">Created</p>
                            <p class="text-dark-500 text-xs">{{ $interview->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    @if($interview->scheduled_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-400 mt-2"></div>
                            <div>
                                <p class="text-dark-300 text-sm">Scheduled</p>
                                <p class="text-dark-500 text-xs">{{ $interview->scheduled_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    @endif
                    @if($interview->started_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-yellow-400 mt-2"></div>
                            <div>
                                <p class="text-dark-300 text-sm">Started</p>
                                <p class="text-dark-500 text-xs">{{ $interview->started_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    @endif
                    @if($interview->completed_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-400 mt-2"></div>
                            <div>
                                <p class="text-dark-300 text-sm">Completed</p>
                                <p class="text-dark-500 text-xs">{{ $interview->completed_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    @endif
                    @if($interview->status->value === 'cancelled')
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-red-400 mt-2"></div>
                            <div>
                                <p class="text-dark-300 text-sm">Cancelled</p>
                                @if($interview->cancellation_reason)
                                    <p class="text-dark-500 text-xs">{{ $interview->cancellation_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Technical Details -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-dark-500">Interview ID</dt>
                        <dd class="text-dark-300 font-mono text-xs">{{ Str::limit($interview->id, 8) }}...</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-dark-500">Difficulty</dt>
                        <dd class="text-dark-300 capitalize">{{ $interview->difficulty ?? 'Medium' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-dark-500">Target Duration</dt>
                        <dd class="text-dark-300">{{ $interview->target_duration ?? 30 }} min</dd>
                    </div>
                    @if($interview->duration_minutes)
                        <div class="flex justify-between">
                            <dt class="text-dark-500">Actual Duration</dt>
                            <dd class="text-dark-300">{{ $interview->duration_minutes }} min</dd>
                        </div>
                    @endif
                    @if($interview->points_earned)
                        <div class="flex justify-between">
                            <dt class="text-dark-500">Points Earned</dt>
                            <dd class="text-green-400 font-bold">+{{ $interview->points_earned }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div x-show="showCancelModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showCancelModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" @click="showCancelModal = false"></div>
            <div class="relative bg-dark-800 rounded-xl max-w-md w-full p-6 border border-dark-700">
                <h3 class="text-lg font-semibold text-white mb-4">Cancel Interview</h3>
                <form action="{{ route('admin.interviews.cancel', $interview) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-300 mb-2">Cancellation Reason</label>
                        <textarea name="reason" rows="3" required
                                  class="input-field w-full"
                                  placeholder="Enter the reason for cancellation..."></textarea>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showCancelModal = false" class="btn btn-outline">
                            Keep Interview
                        </button>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">
                            Cancel Interview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div x-show="showRescheduleModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showRescheduleModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" @click="showRescheduleModal = false"></div>
            <div class="relative bg-dark-800 rounded-xl max-w-md w-full p-6 border border-dark-700">
                <h3 class="text-lg font-semibold text-white mb-4">Reschedule Interview</h3>
                <form action="{{ route('admin.interviews.reschedule', $interview) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-300 mb-2">New Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" required
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="input-field w-full">
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showRescheduleModal = false" class="btn btn-outline">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Mentor Modal -->
    <div x-show="showAssignModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAssignModal = false">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/60" @click="showAssignModal = false"></div>
            <div class="relative bg-dark-800 rounded-xl max-w-md w-full p-6 border border-dark-700">
                <h3 class="text-lg font-semibold text-white mb-4">Assign Mentor</h3>
                <form action="{{ route('admin.interviews.assign-mentor', $interview) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-dark-300 mb-2">Select Mentor</label>
                        <select name="mentor_id" required class="input-field w-full">
                            <option value="">Choose a mentor...</option>
                            @foreach($availableMentors as $mentor)
                                <option value="{{ $mentor->id }}" 
                                        {{ $interview->interviewer_id == $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->name }} ({{ $mentor->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" @click="showAssignModal = false" class="btn btn-outline">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            Assign Mentor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
