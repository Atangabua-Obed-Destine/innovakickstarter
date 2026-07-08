@extends('layouts.app')

@section('title', 'Review Interview')

@section('content')
<div class="space-y-6" x-data="interviewReview()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('mentor.dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('mentor.interviews') }}" class="hover:text-gray-700">Interviews</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Review Interview</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900">Interview Review</h1>
            <p class="mt-1 text-gray-600">
                {{ ucfirst(str_replace('_', ' ', $interview->type ?? 'Technical')) }} Interview with {{ $interview->fellow->name ?? 'Fellow' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($interview->meeting_link && $interview->status === 'scheduled')
            <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener noreferrer"
               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Join Meeting
            </a>
            @endif
            <a href="{{ route('mentor.interviews') }}" 
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - Interview Details & Feedback Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Interview Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Interview Details</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $interview->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $interview->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $interview->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ ucfirst($interview->status ?? 'scheduled') }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <span class="text-xs text-gray-500 block mb-1">Interview Type</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $interview->type ?? 'Technical')) }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <span class="text-xs text-gray-500 block mb-1">Mode</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($interview->mode ?? 'Video') }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <span class="text-xs text-gray-500 block mb-1">Scheduled</span>
                            <span class="text-sm font-medium text-gray-900">{{ $interview->scheduled_at?->format('M d, Y g:i A') ?? 'TBD' }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <span class="text-xs text-gray-500 block mb-1">Duration</span>
                            <span class="text-sm font-medium text-gray-900">{{ $interview->duration ?? 45 }} minutes</span>
                        </div>
                    </div>

                    @if($interview->notes)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Fellow's Notes</h4>
                        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                            {!! nl2br(e($interview->notes)) !!}
                        </div>
                    </div>
                    @endif

                    @if($interview->track)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Career Track</h4>
                        <div class="flex items-center gap-3 bg-indigo-50 rounded-lg p-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $interview->track->name ?? 'Track' }}</p>
                                <p class="text-sm text-gray-500">{{ $interview->track->category ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Feedback Form -->
            @if(!$interview->feedback || $interview->status !== 'completed')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Submit Feedback</h2>
                    <p class="text-sm text-gray-500 mt-1">Provide detailed feedback to help the fellow improve</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('mentor.interviews.complete', $interview->id) }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Scoring Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Technical Score -->
                            <div>
                                <label for="technical_score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Technical Skills
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="range" name="technical_score" id="technical_score" 
                                           min="1" max="10" value="5" x-model="scores.technical"
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                    <span class="w-12 text-center text-lg font-bold" 
                                          :class="scores.technical >= 7 ? 'text-green-600' : (scores.technical >= 5 ? 'text-yellow-600' : 'text-red-600')"
                                          x-text="scores.technical"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Coding ability, problem-solving approach</p>
                            </div>

                            <!-- Communication Score -->
                            <div>
                                <label for="communication_score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Communication
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="range" name="communication_score" id="communication_score" 
                                           min="1" max="10" value="5" x-model="scores.communication"
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                    <span class="w-12 text-center text-lg font-bold" 
                                          :class="scores.communication >= 7 ? 'text-green-600' : (scores.communication >= 5 ? 'text-yellow-600' : 'text-red-600')"
                                          x-text="scores.communication"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Clarity, articulation, active listening</p>
                            </div>

                            <!-- Problem Solving Score -->
                            <div>
                                <label for="problem_solving_score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Problem Solving
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="range" name="problem_solving_score" id="problem_solving_score" 
                                           min="1" max="10" value="5" x-model="scores.problemSolving"
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                    <span class="w-12 text-center text-lg font-bold" 
                                          :class="scores.problemSolving >= 7 ? 'text-green-600' : (scores.problemSolving >= 5 ? 'text-yellow-600' : 'text-red-600')"
                                          x-text="scores.problemSolving"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Analytical thinking, approach to problems</p>
                            </div>

                            <!-- Overall Score -->
                            <div>
                                <label for="overall_score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Overall Performance
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-4">
                                    <input type="range" name="overall_score" id="overall_score" 
                                           min="1" max="10" value="5" x-model="scores.overall"
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                    <span class="w-12 text-center text-lg font-bold" 
                                          :class="scores.overall >= 7 ? 'text-green-600' : (scores.overall >= 5 ? 'text-yellow-600' : 'text-red-600')"
                                          x-text="scores.overall"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Overall interview performance</p>
                            </div>
                        </div>

                        <!-- Average Score Display -->
                        <div class="bg-indigo-50 rounded-xl p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-900">Average Score</p>
                                <p class="text-xs text-indigo-700">Calculated from all categories</p>
                            </div>
                            <div class="text-3xl font-bold" 
                                 :class="averageScore >= 7 ? 'text-green-600' : (averageScore >= 5 ? 'text-yellow-600' : 'text-red-600')"
                                 x-text="averageScore.toFixed(1)">5.0</div>
                        </div>

                        <!-- Written Feedback -->
                        <div>
                            <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">
                                Detailed Feedback
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea name="feedback" id="feedback" rows="5" required minlength="50"
                                      placeholder="Provide detailed feedback about the interview. What went well? What could be improved? This will be shared with the fellow..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimum 50 characters. Be constructive and specific.</p>
                        </div>

                        <!-- Strengths -->
                        <div>
                            <label for="strengths" class="block text-sm font-medium text-gray-700 mb-2">
                                Key Strengths
                            </label>
                            <textarea name="strengths" id="strengths" rows="3"
                                      placeholder="What did the fellow do particularly well?"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                        </div>

                        <!-- Areas for Improvement -->
                        <div>
                            <label for="areas_for_improvement" class="block text-sm font-medium text-gray-700 mb-2">
                                Areas for Improvement
                            </label>
                            <textarea name="areas_for_improvement" id="areas_for_improvement" rows="3"
                                      placeholder="What should the fellow focus on improving?"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                        </div>

                        <!-- Recommendations -->
                        <div>
                            <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-2">
                                Recommendations
                            </label>
                            <textarea name="recommendations" id="recommendations" rows="3"
                                      placeholder="Suggest resources, practice areas, or next steps..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
                        </div>

                        <!-- Internal Notes -->
                        <div>
                            <label for="internal_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Internal Notes
                                <span class="text-gray-400 text-xs ml-2">(Not visible to fellow)</span>
                            </label>
                            <textarea name="internal_notes" id="internal_notes" rows="2"
                                      placeholder="Private notes for program administrators..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none bg-gray-50"></textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Submit Feedback
                            </button>
                            <button type="button" @click="showNoShowModal = true"
                                    class="px-6 py-3 border border-red-300 text-red-700 rounded-lg font-medium hover:bg-red-50 transition-colors">
                                Mark as No Show
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <!-- Submitted Feedback Display -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Submitted Feedback</h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Completed
                    </span>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Scores Display -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold {{ ($interview->technical_score ?? 0) >= 7 ? 'text-green-600' : (($interview->technical_score ?? 0) >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $interview->technical_score ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Technical</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold {{ ($interview->communication_score ?? 0) >= 7 ? 'text-green-600' : (($interview->communication_score ?? 0) >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $interview->communication_score ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Communication</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold {{ ($interview->problem_solving_score ?? 0) >= 7 ? 'text-green-600' : (($interview->problem_solving_score ?? 0) >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $interview->problem_solving_score ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Problem Solving</p>
                        </div>
                        <div class="text-center p-4 bg-indigo-50 rounded-lg">
                            <p class="text-2xl font-bold text-indigo-600">{{ number_format($interview->score ?? 0, 1) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Overall</p>
                        </div>
                    </div>

                    <!-- Feedback Text -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Feedback</h4>
                        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                            {!! nl2br(e($interview->feedback ?? 'No feedback provided.')) !!}
                        </div>
                    </div>

                    @if($interview->strengths)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Strengths</h4>
                        <div class="bg-green-50 rounded-lg p-4 text-sm text-gray-600">
                            {!! nl2br(e($interview->strengths)) !!}
                        </div>
                    </div>
                    @endif

                    @if($interview->areas_for_improvement)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Areas for Improvement</h4>
                        <div class="bg-yellow-50 rounded-lg p-4 text-sm text-gray-600">
                            {!! nl2br(e($interview->areas_for_improvement)) !!}
                        </div>
                    </div>
                    @endif

                    @if($interview->recommendations)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Recommendations</h4>
                        <div class="bg-blue-50 rounded-lg p-4 text-sm text-gray-600">
                            {!! nl2br(e($interview->recommendations)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Fellow Info & Context -->
        <div class="space-y-6">
            <!-- Fellow Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Fellow Profile</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        @if($interview->fellow->avatar ?? false)
                            <img src="{{ $interview->fellow->avatar }}" alt="{{ $interview->fellow->name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($interview->fellow->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $interview->fellow->name ?? 'Unknown Fellow' }}</h3>
                            <p class="text-sm text-gray-500">{{ $interview->fellow->email ?? '' }}</p>
                            @if($interview->fellow->tier ?? false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                                {{ $interview->fellow->tier === 'rookie' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $interview->fellow->tier === 'intern' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $interview->fellow->tier === 'professional' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $interview->fellow->tier === 'elite' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                {{ ucfirst($interview->fellow->tier) }} Tier
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Fellow Stats -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <span class="block text-xl font-bold text-indigo-600">{{ $interview->fellow->primary_score ?? 0 }}</span>
                            <span class="text-xs text-gray-500">Career Score</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <span class="block text-xl font-bold text-green-600">{{ $interview->fellow->interviews_count ?? 0 }}</span>
                            <span class="text-xs text-gray-500">Interviews</span>
                        </div>
                    </div>

                    @if($interview->fellow->bio)
                    <div class="text-sm text-gray-600 mb-4">
                        {{ Str::limit($interview->fellow->bio, 150) }}
                    </div>
                    @endif

                    @if($interview->fellow->linkedin_url || $interview->fellow->github_url || $interview->fellow->portfolio_url)
                    <div class="flex gap-2">
                        @if($interview->fellow->linkedin_url)
                        <a href="{{ $interview->fellow->linkedin_url }}" target="_blank" rel="noopener noreferrer"
                           class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        @endif
                        @if($interview->fellow->github_url)
                        <a href="{{ $interview->fellow->github_url }}" target="_blank" rel="noopener noreferrer"
                           class="p-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                        </a>
                        @endif
                        @if($interview->fellow->portfolio_url)
                        <a href="{{ $interview->fellow->portfolio_url }}" target="_blank" rel="noopener noreferrer"
                           class="p-2 bg-indigo-100 text-indigo-600 rounded-lg hover:bg-indigo-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Previous Interviews -->
            @if(count($previousInterviews ?? []) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Previous Interviews</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($previousInterviews as $prev)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $prev->type ?? 'Interview')) }}</span>
                            @if($prev->score)
                            <span class="text-sm font-bold {{ $prev->score >= 7 ? 'text-green-600' : ($prev->score >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ number_format($prev->score, 1) }}/10
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">{{ $prev->completed_at?->format('M d, Y') ?? 'N/A' }}</p>
                        @if($prev->feedback)
                        <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ Str::limit($prev->feedback, 100) }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Activities -->
            @if(count($recentActivities ?? []) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activities</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($recentActivities as $activity)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                {{ $activity->type === 'project' ? 'bg-blue-100 text-blue-600' : '' }}
                                {{ $activity->type === 'learning' ? 'bg-purple-100 text-purple-600' : '' }}
                                {{ $activity->type === 'content' ? 'bg-orange-100 text-orange-600' : '' }}
                                {{ !in_array($activity->type ?? '', ['project', 'learning', 'content']) ? 'bg-gray-100 text-gray-600' : '' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $activity->title ?? 'Activity' }}</p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at?->diffForHumans() ?? '' }}</p>
                            </div>
                            @if($activity->points_earned)
                            <span class="text-xs font-medium text-green-600">+{{ $activity->points_earned }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Interview Guidelines -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-sm p-6 text-white">
                <h3 class="font-semibold mb-3">Scoring Guidelines</h3>
                <div class="space-y-3 text-sm text-indigo-100">
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-green-300">8-10:</span>
                        <span>Excellent - Exceeds expectations</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-yellow-300">5-7:</span>
                        <span>Good - Meets expectations</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="font-bold text-red-300">1-4:</span>
                        <span>Needs improvement</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- No Show Modal -->
    <div x-show="showNoShowModal" x-cloak 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div @click.outside="showNoShowModal = false" 
             class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Mark as No Show?</h3>
                <p class="text-gray-600 mb-4">This will mark the interview as a no-show. The fellow will be notified.</p>
                <form action="{{ route('mentor.interviews.complete', $interview->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="no_show" value="true">
                    <textarea name="no_show_notes" rows="3"
                              placeholder="Add any notes about the no-show (optional)..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none mb-4"></textarea>
                    <div class="flex gap-3">
                        <button type="button" @click="showNoShowModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            Confirm No Show
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function interviewReview() {
    return {
        showNoShowModal: false,
        scores: {
            technical: 5,
            communication: 5,
            problemSolving: 5,
            overall: 5
        },
        get averageScore() {
            return (parseInt(this.scores.technical) + 
                    parseInt(this.scores.communication) + 
                    parseInt(this.scores.problemSolving) + 
                    parseInt(this.scores.overall)) / 4;
        }
    }
}
</script>
@endsection
