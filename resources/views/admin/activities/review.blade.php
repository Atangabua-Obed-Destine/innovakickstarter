@extends('layouts.app')

@section('title', 'Review Activity')

@section('content')
<div class="space-y-6" x-data="activityReviewer()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Dashboard</a>
                <span class="mx-2">/</span>
                <a href="{{ route('admin.activities.queue') }}" class="hover:text-white">Activity Queue</a>
                <span class="mx-2">/</span>
                <span class="text-dark-300">Review Activity</span>
            </nav>
            <h1 class="text-2xl font-semibold text-white">Review Activity Submission</h1>
            <p class="mt-1 text-dark-400">Review and approve or reject this activity submission</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.activities.queue') }}" 
               class="px-4 py-2 border border-dark-600 rounded-lg text-dark-300 hover:bg-dark-700 hover:text-white transition-colors">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Queue
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content - Activity Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Activity Overview Card -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">Activity Details</h2>
                    @php $statusVal = $activity->status->value ?? $activity->status ?? 'pending'; @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $statusVal === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                        {{ $statusVal === 'approved' ? 'bg-green-500/20 text-green-400' : '' }}
                        {{ $statusVal === 'rejected' ? 'bg-red-500/20 text-red-400' : '' }}">
                        {{ ucfirst($statusVal) }}
                    </span>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Activity Type & Title -->
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            @php $typeVal = $activity->type->value ?? $activity->type ?? 'project'; @endphp
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center
                                {{ $typeVal === 'project' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                {{ $typeVal === 'learning' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                {{ $typeVal === 'networking' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $typeVal === 'content' ? 'bg-orange-500/20 text-orange-400' : '' }}
                                {{ $typeVal === 'contribution' ? 'bg-pink-500/20 text-pink-400' : '' }}">
                                @switch($typeVal)
                                    @case('project')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                        @break
                                    @case('learning')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        @break
                                    @case('networking')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        @break
                                    @case('content')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        @break
                                    @default
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                @endswitch
                            </div>
                            <div>
                                <span class="text-sm text-dark-400 uppercase tracking-wide">{{ ucfirst($typeVal) }} Activity</span>
                                <h3 class="text-xl font-semibold text-white">{{ $activity->title ?? 'Untitled Activity' }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h4 class="text-sm font-medium text-dark-300 mb-2">Description</h4>
                        <div class="prose prose-sm prose-invert max-w-none text-dark-300 bg-dark-800 rounded-lg p-4">
                            {!! nl2br(e($activity->description ?? 'No description provided.')) !!}
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-dark-800 rounded-lg p-3">
                            <span class="text-xs text-dark-500 block">Track</span>
                            <span class="text-sm font-medium text-white">{{ $activity->track->name ?? 'Not assigned' }}</span>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <span class="text-xs text-dark-500 block">Time Spent</span>
                            <span class="text-sm font-medium text-white">{{ $activity->duration ?? 0 }} hours</span>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <span class="text-xs text-dark-500 block">Submitted</span>
                            <span class="text-sm font-medium text-white">{{ $activity->created_at?->diffForHumans() ?? 'Unknown' }}</span>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3">
                            <span class="text-xs text-dark-500 block">Career Capital</span>
                            <span class="text-sm font-medium text-white">{{ $activity->career_capital_category ?? 'Mixed' }}</span>
                        </div>
                    </div>

                    <!-- Evidence/Links -->
                    @if($activity->evidence_url || $activity->github_url || $activity->portfolio_url)
                    <div>
                        <h4 class="text-sm font-medium text-dark-300 mb-3">Evidence & Links</h4>
                        <div class="flex flex-wrap gap-3">
                            @if($activity->evidence_url)
                            <a href="{{ $activity->evidence_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center px-4 py-2 bg-dark-700 rounded-lg text-sm text-dark-300 hover:bg-dark-600 hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Evidence Link
                            </a>
                            @endif
                            @if($activity->github_url)
                            <a href="{{ $activity->github_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center px-4 py-2 bg-dark-700 rounded-lg text-sm text-white hover:bg-dark-600 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                GitHub Repository
                            </a>
                            @endif
                            @if($activity->portfolio_url)
                            <a href="{{ $activity->portfolio_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center px-4 py-2 bg-indigo-500/20 rounded-lg text-sm text-indigo-400 hover:bg-indigo-500/30 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                Portfolio Link
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Attachments -->
                    @if($activity->attachments && count($activity->attachments) > 0)
                    <div>
                        <h4 class="text-sm font-medium text-dark-300 mb-3">Attachments</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($activity->attachments as $attachment)
                            <div class="border border-dark-600 rounded-lg p-3 hover:bg-dark-700 transition-colors">
                                <div class="flex items-center gap-2">
                                    <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">{{ $attachment['name'] ?? 'File' }}</p>
                                        <p class="text-xs text-dark-500">{{ $attachment['size'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Previous Review History -->
            @if($activity->reviews && count($activity->reviews) > 0)
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-lg font-semibold text-white">Review History</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($activity->reviews as $review)
                        <div class="flex gap-4 p-4 bg-dark-800 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-dark-600 flex items-center justify-center text-sm font-medium text-dark-300">
                                {{ substr($review['admin_name'] ?? 'A', 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-white">{{ $review['admin_name'] ?? 'Admin' }}</span>
                                    <span class="text-xs text-dark-500">{{ $review['created_at'] ?? '' }}</span>
                                </div>
                                <p class="text-sm text-dark-400">{{ $review['notes'] ?? '' }}</p>
                                <span class="inline-block mt-2 px-2 py-1 text-xs rounded 
                                    {{ $review['decision'] === 'approved' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ ucfirst($review['decision'] ?? 'Reviewed') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Fellow Info & Review Actions -->
        <div class="space-y-6">
            <!-- Fellow Card -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-lg font-semibold text-white">Fellow Information</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        @if($activity->user->avatar ?? false)
                            <img src="{{ $activity->user->avatar }}" alt="{{ $activity->user->name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($activity->user->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-white">{{ $activity->user->name ?? 'Unknown User' }}</h3>
                            <p class="text-sm text-dark-400">{{ $activity->user->email ?? '' }}</p>
                            @if($activity->user->tier ?? false)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                                {{ $activity->user->tier === 'rookie' ? 'bg-dark-600 text-dark-300' : '' }}
                                {{ $activity->user->tier === 'intern' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                {{ $activity->user->tier === 'professional' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                {{ $activity->user->tier === 'elite' ? 'bg-yellow-500/20 text-yellow-400' : '' }}">
                                {{ ucfirst($activity->user->tier) }} Tier
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Fellow Stats -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-dark-800 rounded-lg p-3 text-center">
                            <span class="block text-2xl font-bold text-indigo-400">{{ $activity->user->primary_score ?? 0 }}</span>
                            <span class="text-xs text-dark-500">Career Score</span>
                        </div>
                        <div class="bg-dark-800 rounded-lg p-3 text-center">
                            <span class="block text-2xl font-bold text-green-400">{{ $activity->user->activities_count ?? 0 }}</span>
                            <span class="text-xs text-dark-500">Activities</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.fellows.show', $activity->user_id ?? 1) }}" 
                       class="block w-full text-center px-4 py-2 border border-dark-600 rounded-lg text-sm text-dark-300 hover:bg-dark-700 hover:text-white transition-colors">
                        View Full Profile
                    </a>
                </div>
            </div>

            <!-- Scoring Preview -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-lg font-semibold text-white">Scoring Preview</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @php
                            $rubric = $activity->track->scoring_rubric ?? [
                                'TECHNICAL' => 30,
                                'INTERVIEW' => 25,
                                'PORTFOLIO' => 20,
                                'COLLABORATION' => 15,
                                'LEARNING' => 10,
                            ];
                            $basePoints = $activity->estimated_points ?? 50;
                        @endphp
                        @foreach($rubric as $category => $weight)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-dark-400">{{ ucfirst(strtolower($category)) }}</span>
                                <span class="font-medium text-white">+{{ round($basePoints * ($weight / 100)) }} pts</span>
                            </div>
                            <div class="w-full bg-dark-700 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $weight }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-dark-700">
                        <div class="flex justify-between">
                            <span class="font-medium text-white">Total Points</span>
                            <span class="text-lg font-bold text-indigo-400">{{ $basePoints }} pts</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Actions -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-lg font-semibold text-white">Review Decision</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.activities.update', $activity->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <!-- Points Adjustment -->
                        <div class="mb-4">
                            <label for="points" class="form-label">Award Points</label>
                            <input type="number" name="points" id="points" 
                                   value="{{ $activity->estimated_points ?? 50 }}" 
                                   min="0" max="500"
                                   class="form-input">
                            <p class="text-xs text-dark-500 mt-1">Adjust points if needed (0-500)</p>
                        </div>

                        <!-- Review Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Review Notes</label>
                            <textarea name="notes" id="notes" rows="4" 
                                      placeholder="Add notes about this activity (visible to fellow)..."
                                      class="form-input resize-none"></textarea>
                        </div>

                        <!-- Internal Notes -->
                        <div class="mb-6">
                            <label for="internal_notes" class="form-label">Internal Notes</label>
                            <textarea name="internal_notes" id="internal_notes" rows="2" 
                                      placeholder="Private admin notes (not visible to fellow)..."
                                      class="form-input resize-none"></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button type="submit" name="decision" value="approved"
                                    class="w-full px-4 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve Activity
                            </button>
                            <button type="submit" name="decision" value="rejected"
                                    class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject Activity
                            </button>
                            <button type="submit" name="decision" value="needs_revision"
                                    class="w-full px-4 py-3 border border-yellow-500/50 text-yellow-400 rounded-lg font-medium hover:bg-yellow-500/10 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Request Revision
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-lg font-semibold text-white">Quick Actions</h2>
                </div>
                <div class="p-6 space-y-2">
                    <button @click="flagForReview = true"
                            class="w-full text-left px-4 py-2 rounded-lg hover:bg-dark-700 transition-colors flex items-center gap-3">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                        <span class="text-sm text-dark-300">Flag for Senior Review</span>
                    </button>
                    <button @click="assignMentor = true"
                            class="w-full text-left px-4 py-2 rounded-lg hover:bg-dark-700 transition-colors flex items-center gap-3">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm text-dark-300">Assign Mentor Review</span>
                    </button>
                    <button @click="sendMessage = true"
                            class="w-full text-left px-4 py-2 rounded-lg hover:bg-dark-700 transition-colors flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="text-sm text-dark-300">Message Fellow</span>
                    </button>
                    <button @click="viewSimilar = true"
                            class="w-full text-left px-4 py-2 rounded-lg hover:bg-dark-700 transition-colors flex items-center gap-3">
                        <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                        </svg>
                        <span class="text-sm text-dark-300">View Similar Activities</span>
                    </button>
                </div>
            </div>

            <!-- Navigation Between Activities -->
            <div class="flex gap-3">
                @if($previousActivity ?? false)
                <a href="{{ route('admin.activities.review', $previousActivity->id) }}"
                   class="flex-1 px-4 py-3 border border-dark-600 rounded-lg text-center text-sm text-dark-300 hover:bg-dark-700 hover:text-white transition-colors">
                    ← Previous
                </a>
                @else
                <div class="flex-1"></div>
                @endif
                @if($nextActivity ?? false)
                <a href="{{ route('admin.activities.review', $nextActivity->id) }}"
                   class="flex-1 px-4 py-3 border border-dark-600 rounded-lg text-center text-sm text-dark-300 hover:bg-dark-700 hover:text-white transition-colors">
                    Next →
                </a>
                @else
                <div class="flex-1"></div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function activityReviewer() {
    return {
        flagForReview: false,
        assignMentor: false,
        sendMessage: false,
        viewSimilar: false
    }
}
</script>
@endsection
