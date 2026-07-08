@extends('layouts.app')

@section('title', 'Mock Interviews')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Mock Interviews</h1>
            <p class="text-dark-400 mt-1">Practice interviews to boost your confidence and Career Capital.</p>
        </div>
        <div class="flex gap-3">
            <!-- Live AI Interview Button (NEW!) -->
            <a href="{{ route('interviews.live.lobby') }}" 
               class="btn bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white border-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
                🔴 Live AI Interview
            </a>
            <!-- Practice Mode Button -->
            <button onclick="document.getElementById('practiceModal').classList.remove('hidden')" 
                    class="btn btn-outline border-amber-500/50 text-amber-400 hover:bg-amber-500/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Practice Mode
            </button>
            <button onclick="document.getElementById('scheduleModal').classList.remove('hidden')" 
                    class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Schedule Interview
            </button>
        </div>
    </div>

    <!-- Live AI Interview Banner (NEW!) -->
    <div class="card bg-gradient-to-r from-purple-600/20 via-pink-600/20 to-orange-600/20 border border-purple-500/30 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                    <span class="text-2xl">🎤</span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-white font-semibold text-lg">NEW: Live AI Interview</p>
                        <span class="px-2 py-0.5 rounded-full bg-red-500 text-white text-xs font-bold animate-pulse">LIVE</span>
                    </div>
                    <p class="text-dark-400 text-sm">Have a real-time conversation with your AI interviewer. Voice-enabled with dynamic follow-ups!</p>
                </div>
            </div>
            <a href="{{ route('interviews.live.lobby') }}" class="btn bg-white/10 hover:bg-white/20 text-white backdrop-blur">
                Try Now →
            </a>
        </div>
    </div>

    <!-- Enhanced Features Banner -->
    <div class="card bg-gradient-to-r from-primary-600/10 to-teal-600/10 border border-primary-500/20 p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✨</span>
                <div>
                    <p class="text-white font-medium">Enhanced Interview Experience</p>
                    <p class="text-dark-400 text-sm">Voice recording, code editor, whiteboard & video recording now available!</p>
                </div>
            </div>
            <div class="flex gap-2">
                <span class="px-2 py-1 bg-primary-600/20 text-primary-400 text-xs rounded">🎤 Voice</span>
                <span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded">💻 Code</span>
                <span class="px-2 py-1 bg-blue-600/20 text-blue-400 text-xs rounded">📊 Whiteboard</span>
                <span class="px-2 py-1 bg-red-600/20 text-red-400 text-xs rounded">🎬 Record</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-dark-400 text-sm">Total Interviews</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-dark-400 text-sm">Completed</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['completed'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-dark-400 text-sm">Average Score</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['avgScore'] ?? 0 }}%</p>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-dark-400 text-sm">Upcoming</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['upcoming'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'upcoming' }">
        <div class="border-b border-dark-700 mb-6">
            <nav class="flex gap-6">
                <button @click="activeTab = 'upcoming'"
                        :class="activeTab === 'upcoming' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-white'"
                        class="pb-3 text-sm font-medium border-b-2 transition-colors">
                    Upcoming Interviews
                </button>
                <button @click="activeTab = 'completed'"
                        :class="activeTab === 'completed' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-white'"
                        class="pb-3 text-sm font-medium border-b-2 transition-colors">
                    Completed
                </button>
                <button @click="activeTab = 'feedback'"
                        :class="activeTab === 'feedback' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-white'"
                        class="pb-3 text-sm font-medium border-b-2 transition-colors">
                    Feedback & Scores
                </button>
            </nav>
        </div>

        <!-- Upcoming Tab -->
        <div x-show="activeTab === 'upcoming'" x-cloak>
            <div class="space-y-4">
                @forelse($upcomingInterviews ?? [] as $interview)
                    <div class="card p-5 card-hover">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-white">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $interview->type ?? 'Technical Interview' }}</h3>
                                    <p class="text-dark-400 text-sm">{{ $interview->track->name ?? 'Software Development' }} Track</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <p class="text-dark-500 text-xs uppercase tracking-wide">Date</p>
                                    <p class="text-white font-medium">{{ $interview->scheduled_at?->format('M d, Y') ?? 'TBD' }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-dark-500 text-xs uppercase tracking-wide">Time</p>
                                    <p class="text-white font-medium">{{ $interview->scheduled_at?->format('g:i A') ?? 'TBD' }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-dark-500 text-xs uppercase tracking-wide">Duration</p>
                                    <p class="text-white font-medium">{{ $interview->duration ?? 45 }} min</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a href="{{ route('interviews.show', $interview->id ?? 1) }}" class="btn btn-primary">
                                    Join Interview
                                </a>
                                <button class="btn btn-ghost text-dark-400 hover:text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card p-12 text-center">
                        <div class="w-20 h-20 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">No upcoming interviews</h3>
                        <p class="text-dark-400 max-w-md mx-auto mb-6">
                            Schedule a mock interview to practice and improve your interview skills.
                        </p>
                        <button onclick="document.getElementById('scheduleModal').classList.remove('hidden')" 
                                class="btn btn-primary">
                            Schedule Your First Interview
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Completed Tab -->
        <div x-show="activeTab === 'completed'" x-cloak>
            <div class="space-y-4">
                @forelse($completedInterviews ?? [] as $interview)
                    <div class="card p-5">
                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-14 h-14 rounded-xl bg-green-600/20 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $interview->type ?? 'Technical Interview' }}</h3>
                                    <p class="text-dark-400 text-sm">Completed {{ $interview->completed_at?->diffForHumans() ?? 'recently' }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-6">
                                <div class="text-center">
                                    <p class="text-dark-500 text-xs uppercase tracking-wide">Overall Score</p>
                                    <p class="text-2xl font-bold text-white">{{ $interview->score ?? 85 }}%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-dark-500 text-xs uppercase tracking-wide">Points Earned</p>
                                    <p class="text-green-400 font-semibold">+{{ $interview->points ?? 50 }} pts</p>
                                </div>
                            </div>
                            
                            <a href="{{ route('interviews.show', $interview->id ?? 1) }}" class="btn btn-secondary">
                                View Feedback
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="card p-12 text-center">
                        <div class="w-20 h-20 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">No completed interviews yet</h3>
                        <p class="text-dark-400 max-w-md mx-auto">
                            Complete your scheduled interviews to see your results here.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Feedback Tab -->
        <div x-show="activeTab === 'feedback'" x-cloak>
            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Score Breakdown -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-6">Score Breakdown</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-dark-300">Technical Knowledge</span>
                                <span class="text-white font-medium">{{ $scoreBreakdown['technical'] ?? 78 }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill bg-purple-600" style="width: {{ $scoreBreakdown['technical'] ?? 78 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-dark-300">Communication</span>
                                <span class="text-white font-medium">{{ $scoreBreakdown['communication'] ?? 85 }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill bg-blue-600" style="width: {{ $scoreBreakdown['communication'] ?? 85 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-dark-300">Problem Solving</span>
                                <span class="text-white font-medium">{{ $scoreBreakdown['problemSolving'] ?? 72 }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill bg-teal-600" style="width: {{ $scoreBreakdown['problemSolving'] ?? 72 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-dark-300">Confidence</span>
                                <span class="text-white font-medium">{{ $scoreBreakdown['confidence'] ?? 80 }}%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-bar-fill bg-amber-600" style="width: {{ $scoreBreakdown['confidence'] ?? 80 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Feedback -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-6">Recent Feedback</h3>
                    <div class="space-y-4">
                        @forelse($recentFeedback ?? [] as $feedback)
                            <div class="p-4 bg-dark-800 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-600/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-dark-200 text-sm">{{ $feedback->comment ?? 'Great job explaining your thought process. Work on being more concise with your answers.' }}</p>
                                        <p class="text-dark-500 text-xs mt-2">{{ $feedback->created_at?->diffForHumans() ?? '2 days ago' }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-dark-400">No feedback received yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data="{ interviewType: 'technical' }">
    <div class="modal-backdrop" onclick="document.getElementById('scheduleModal').classList.add('hidden')"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content relative w-full max-w-lg p-6 animate-slide-up">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">Schedule Mock Interview</h3>
                <button onclick="document.getElementById('scheduleModal').classList.add('hidden')" 
                        class="text-dark-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('interviews.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="form-label">Interview Type</label>
                    <select name="type" class="form-input" x-model="interviewType">
                        <option value="technical">Technical Interview</option>
                        <option value="behavioral">Behavioral Interview</option>
                        <option value="case_study">Case Study</option>
                        <option value="system_design">System Design</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Preferred Date</label>
                    <input type="date" name="date" class="form-input" required min="{{ date('Y-m-d') }}">
                </div>
                
                <div>
                    <label class="form-label">Preferred Time</label>
                    <select name="time" class="form-input">
                        <option value="09:00">9:00 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                    </select>
                </div>
                
                <div>
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" rows="3" class="form-input" placeholder="Any specific topics you'd like to focus on..."></textarea>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('scheduleModal').classList.add('hidden')" 
                            class="flex-1 btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn btn-primary">
                        Schedule Interview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Practice Mode Modal -->
<div id="practiceModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data="{ practiceType: 'behavioral', difficulty: 'intermediate' }">
    <div class="modal-backdrop" onclick="document.getElementById('practiceModal').classList.add('hidden')"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="modal-content relative w-full max-w-lg p-6 animate-slide-up">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                        <span class="text-amber-400">🎯</span>
                        Practice Mode
                    </h3>
                    <p class="text-dark-400 text-sm mt-1">Unlimited practice - doesn't affect your Career Capital</p>
                </div>
                <button onclick="document.getElementById('practiceModal').classList.add('hidden')" 
                        class="text-dark-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Practice Benefits -->
            <div class="bg-amber-600/10 border border-amber-500/30 rounded-lg p-4 mb-6">
                <h4 class="text-amber-400 font-medium mb-2">Practice Mode Benefits:</h4>
                <ul class="text-dark-300 text-sm space-y-1">
                    <li class="flex items-center gap-2">
                        <span class="text-green-400">✓</span>
                        <span>Unlimited practice sessions</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-green-400">✓</span>
                        <span>No impact on your Career Capital score</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-green-400">✓</span>
                        <span>Real AI evaluation & feedback</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-green-400">✓</span>
                        <span>Try all interview types freely</span>
                    </li>
                </ul>
            </div>
            
            <form action="{{ route('interviews.practice') }}" method="GET" class="space-y-5">
                <div>
                    <label class="form-label">Interview Type</label>
                    <select name="type" class="form-input" x-model="practiceType">
                        <option value="behavioral">Behavioral Interview</option>
                        <option value="technical_coding">Technical Coding</option>
                        <option value="system_design">System Design</option>
                        <option value="product_case">Product Case Study</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Difficulty Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative">
                            <input type="radio" name="difficulty" value="beginner" x-model="difficulty" class="sr-only peer">
                            <div class="p-3 border border-dark-600 rounded-lg text-center cursor-pointer transition-all peer-checked:border-green-500 peer-checked:bg-green-500/10">
                                <span class="text-xl">🌱</span>
                                <p class="text-sm font-medium" :class="difficulty === 'beginner' ? 'text-green-400' : 'text-dark-300'">Beginner</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="difficulty" value="intermediate" x-model="difficulty" class="sr-only peer">
                            <div class="p-3 border border-dark-600 rounded-lg text-center cursor-pointer transition-all peer-checked:border-amber-500 peer-checked:bg-amber-500/10">
                                <span class="text-xl">🚀</span>
                                <p class="text-sm font-medium" :class="difficulty === 'intermediate' ? 'text-amber-400' : 'text-dark-300'">Intermediate</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="difficulty" value="advanced" x-model="difficulty" class="sr-only peer">
                            <div class="p-3 border border-dark-600 rounded-lg text-center cursor-pointer transition-all peer-checked:border-red-500 peer-checked:bg-red-500/10">
                                <span class="text-xl">🔥</span>
                                <p class="text-sm font-medium" :class="difficulty === 'advanced' ? 'text-red-400' : 'text-dark-300'">Advanced</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Features Available -->
                <div class="bg-dark-800 rounded-lg p-4">
                    <p class="text-dark-400 text-sm mb-3">Available Features:</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2 py-1 bg-primary-600/20 text-primary-400 text-xs rounded flex items-center gap-1">
                            <span>🎤</span> Voice Input
                        </span>
                        <span x-show="practiceType === 'technical_coding'" class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded flex items-center gap-1">
                            <span>💻</span> Code Editor
                        </span>
                        <span x-show="practiceType === 'system_design'" class="px-2 py-1 bg-blue-600/20 text-blue-400 text-xs rounded flex items-center gap-1">
                            <span>📊</span> Whiteboard
                        </span>
                        <span class="px-2 py-1 bg-red-600/20 text-red-400 text-xs rounded flex items-center gap-1">
                            <span>🎬</span> Recording
                        </span>
                        <span class="px-2 py-1 bg-teal-600/20 text-teal-400 text-xs rounded flex items-center gap-1">
                            <span>🤖</span> AI Feedback
                        </span>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('practiceModal').classList.add('hidden')" 
                            class="flex-1 btn btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 btn btn-primary bg-amber-600 hover:bg-amber-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        </svg>
                        Start Practice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
