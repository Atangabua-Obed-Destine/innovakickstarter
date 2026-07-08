@extends('layouts.app')

@section('title', 'Mock Interview Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('interviews.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Interviews
    </a>

    <!-- Interview Header -->
    <div class="card p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    @php
                        $statusClass = match($interview->status ?? 'scheduled') {
                            'scheduled' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                            'completed' => 'bg-green-600/20 text-green-400 border-green-500/30',
                            'cancelled' => 'bg-red-600/20 text-red-400 border-red-500/30',
                            'in_progress' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
                            default => 'bg-dark-600/20 text-dark-400 border-dark-500/30'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">
                        {{ ucfirst($interview->status ?? 'Scheduled') }}
                    </span>
                    <span class="badge badge-primary">
                        {{ ucfirst($interview->type ?? 'Technical') }} Interview
                    </span>
                </div>
                
                <h1 class="text-2xl font-bold text-white mb-2">
                    {{ $interview->title ?? 'Senior Developer Mock Interview' }}
                </h1>
                
                <p class="text-dark-300">
                    {{ $interview->description ?? 'Practice your interview skills with an experienced interviewer. Get real-time feedback and improve your performance.' }}
                </p>
            </div>
            
            @if(($interview->status ?? 'scheduled') === 'scheduled')
            <div class="flex gap-2">
                <button class="btn btn-outline text-red-400 border-red-500/30 hover:bg-red-600/20">
                    Cancel
                </button>
                <a href="{{ $interview->meeting_link ?? '#' }}" target="_blank" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Join Meeting
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Interview Details Grid -->
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Date & Time -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Date & Time
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                    <span class="text-dark-400">Date</span>
                    <span class="text-white font-medium">{{ $interview->scheduled_at?->format('l, F j, Y') ?? 'December 20, 2024' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                    <span class="text-dark-400">Time</span>
                    <span class="text-white font-medium">{{ $interview->scheduled_at?->format('g:i A') ?? '2:00 PM' }} (WAT)</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                    <span class="text-dark-400">Duration</span>
                    <span class="text-white font-medium">{{ $interview->duration ?? 45 }} minutes</span>
                </div>
            </div>
            
            @if(($interview->status ?? 'scheduled') === 'scheduled')
            <div class="mt-4 p-3 bg-blue-600/10 border border-blue-500/30 rounded-lg">
                <div class="flex items-center gap-2 text-blue-400 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Starts in <span class="font-semibold" x-data x-text="'2 days 4 hours'">2 days 4 hours</span></span>
                </div>
            </div>
            @endif
        </div>

        <!-- Interviewer -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Interviewer
            </h3>
            
            <div class="flex items-center gap-4 p-4 bg-dark-800 rounded-lg">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr($interview->interviewer->name ?? 'John Doe', 0, 2)) }}
                </div>
                <div class="flex-1">
                    <p class="text-white font-semibold">{{ $interview->interviewer->name ?? 'John Doe' }}</p>
                    <p class="text-dark-400 text-sm">{{ $interview->interviewer->title ?? 'Senior Software Engineer @ Google' }}</p>
                    <div class="flex items-center gap-1 mt-1">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="text-dark-500 text-sm ml-1">(4.9)</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-2 gap-3 text-center">
                <div class="p-3 bg-dark-800 rounded-lg">
                    <p class="text-xl font-bold text-primary-400">150+</p>
                    <p class="text-dark-500 text-sm">Interviews</p>
                </div>
                <div class="p-3 bg-dark-800 rounded-lg">
                    <p class="text-xl font-bold text-teal-400">8+ yrs</p>
                    <p class="text-dark-500 text-sm">Experience</p>
                </div>
            </div>
        </div>
    </div>

    <!-- What to Prepare -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            What to Prepare
        </h3>
        
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Review your resume</p>
                        <p class="text-dark-500 text-sm">Be ready to discuss your experience</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Practice coding problems</p>
                        <p class="text-dark-500 text-sm">Data structures & algorithms</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Prepare questions</p>
                        <p class="text-dark-500 text-sm">Have questions ready for feedback</p>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Test your setup</p>
                        <p class="text-dark-500 text-sm">Camera, microphone & internet</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Quiet environment</p>
                        <p class="text-dark-500 text-sm">Find a distraction-free space</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 bg-dark-800 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-white font-medium">Join 5 mins early</p>
                        <p class="text-dark-500 text-sm">Be ready before start time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview Topics -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            Topics to be Covered
        </h3>
        
        <div class="flex flex-wrap gap-2">
            @foreach($interview->topics ?? ['Problem Solving', 'Data Structures', 'System Design', 'Behavioral Questions', 'Code Review', 'Communication Skills'] as $topic)
                <span class="px-3 py-1.5 bg-dark-700 text-dark-200 rounded-full text-sm">{{ $topic }}</span>
            @endforeach
        </div>
    </div>

    @if(($interview->status ?? 'scheduled') === 'completed')
    <!-- Interview Feedback -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Interview Feedback
        </h3>
        
        <!-- Overall Score -->
        <div class="flex items-center gap-6 p-4 bg-dark-800 rounded-lg mb-6">
            <div class="text-center">
                <div class="w-20 h-20 rounded-full border-4 border-green-500 flex items-center justify-center">
                    <span class="text-2xl font-bold text-green-400">{{ $interview->score ?? 85 }}%</span>
                </div>
                <p class="text-dark-400 text-sm mt-2">Overall Score</p>
            </div>
            <div class="flex-1">
                <p class="text-dark-200 mb-2">{{ $interview->summary ?? 'Great interview performance! You demonstrated strong problem-solving skills and good communication. A few areas could be improved, particularly in system design discussions.' }}</p>
                <p class="text-dark-500 text-sm">Feedback from {{ $interview->interviewer->name ?? 'John Doe' }}</p>
            </div>
        </div>
        
        <!-- Detailed Scores -->
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
            @foreach($interview->detailed_scores ?? [
                ['name' => 'Technical Skills', 'score' => 90],
                ['name' => 'Problem Solving', 'score' => 85],
                ['name' => 'Communication', 'score' => 88],
                ['name' => 'Code Quality', 'score' => 82],
            ] as $detail)
                <div class="p-4 bg-dark-800 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-dark-300">{{ $detail['name'] }}</span>
                        <span class="text-white font-medium">{{ $detail['score'] }}%</span>
                    </div>
                    <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $detail['score'] >= 80 ? 'bg-green-500' : ($detail['score'] >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" 
                             style="width: {{ $detail['score'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Strengths & Improvements -->
        <div class="grid md:grid-cols-2 gap-4">
            <div class="p-4 bg-green-600/10 border border-green-500/30 rounded-lg">
                <h4 class="text-green-400 font-semibold mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                    Strengths
                </h4>
                <ul class="space-y-2 text-dark-200 text-sm">
                    @foreach($interview->strengths ?? ['Clear explanation of technical concepts', 'Good problem decomposition', 'Excellent time management'] as $strength)
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $strength }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 bg-amber-600/10 border border-amber-500/30 rounded-lg">
                <h4 class="text-amber-400 font-semibold mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Areas to Improve
                </h4>
                <ul class="space-y-2 text-dark-200 text-sm">
                    @foreach($interview->improvements ?? ['Consider edge cases more thoroughly', 'Practice system design patterns', 'Ask more clarifying questions'] as $improvement)
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $improvement }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Career Capital Points -->
    <div class="card p-6 bg-gradient-to-r from-primary-600/20 to-blue-600/20 border-primary-500/30">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary-600/30 flex items-center justify-center">
                <svg class="w-7 h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div>
                <p class="text-dark-200">You earned</p>
                <p class="text-2xl font-bold text-white">+50 Career Capital Points</p>
                <p class="text-dark-400 text-sm">for completing this mock interview</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-3">
        @if(($interview->status ?? 'scheduled') === 'completed')
            <a href="{{ route('interviews.index') }}?schedule=1" class="btn btn-primary">
                Schedule Another Interview
            </a>
            <button class="btn btn-outline">
                Download Feedback Report
            </button>
        @elseif(($interview->status ?? 'scheduled') === 'scheduled')
            <a href="{{ $interview->meeting_link ?? '#' }}" target="_blank" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Join Meeting
            </a>
            <button class="btn btn-outline">
                Add to Calendar
            </button>
            <button class="btn btn-outline text-amber-400 border-amber-500/30 hover:bg-amber-600/20">
                Reschedule
            </button>
        @endif
    </div>
</div>
@endsection
