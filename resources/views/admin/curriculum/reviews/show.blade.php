@extends('layouts.app')

@section('title', 'Review Submission')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('admin.curriculum.reviews') }}" class="hover:text-white transition">Review Queue</a>
            <span>/</span>
            <span class="text-primary-400">Review Submission</span>
        </div>
        <h1 class="text-2xl font-bold text-white">{{ $progress->curriculumActivity->title }}</h1>
    </div>

    @if(session('error'))
    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Submission Details (Left) -->
        <div class="lg:col-span-2 space-y-6">
            {{-- Fellow Info --}}
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 text-lg font-bold">
                        {{ strtoupper(substr($progress->fellow->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">{{ $progress->fellow->name }}</h3>
                        <p class="text-dark-400 text-sm">{{ $progress->fellow->email }}</p>
                    </div>
                    <div class="ml-auto text-right">
                        @php
                            $statusColor = match($progress->status?->value ?? $progress->status ?? '') {
                                'submitted' => 'text-blue-400 bg-blue-500/10 border-blue-500/30',
                                'peer_review' => 'text-amber-400 bg-amber-500/10 border-amber-500/30',
                                'under_review' => 'text-purple-400 bg-purple-500/10 border-purple-500/30',
                                default => 'text-dark-400 bg-dark-700 border-dark-600',
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs border {{ $statusColor }}">
                            {{ $progress->status?->label() ?? ucfirst($progress->status ?? 'N/A') }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-dark-500">Track</p>
                        <p class="text-white">{{ $progress->curriculumActivity->track->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-dark-500">Milestone</p>
                        <p class="text-white">{{ $progress->curriculumActivity->milestone->title ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-dark-500">Difficulty</p>
                        <p class="text-white">{{ $progress->curriculumActivity->difficulty_level?->label() ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-dark-500">Type</p>
                        <p class="text-white">{{ $progress->curriculumActivity->type?->label() ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Activity Instructions --}}
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-3">Activity Description</h3>
                <div class="text-dark-300 text-sm prose prose-invert max-w-none">
                    {!! nl2br(e($progress->curriculumActivity->description)) !!}
                </div>
                @if($progress->curriculumActivity->instructions)
                <div class="mt-4 pt-4 border-t border-dark-700">
                    <h4 class="text-dark-400 text-sm font-medium mb-2">Instructions</h4>
                    <div class="text-dark-300 text-sm prose prose-invert max-w-none">
                        {!! nl2br(e($progress->curriculumActivity->instructions)) !!}
                    </div>
                </div>
                @endif
            </div>

            {{-- Submission Evidence --}}
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-4">Submission Evidence</h3>

                @if($progress->evidence_url)
                <div class="mb-4">
                    <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">URL</p>
                    <a href="{{ $progress->evidence_url }}" target="_blank" rel="noopener"
                       class="text-primary-400 hover:text-primary-300 text-sm break-all">
                        {{ $progress->evidence_url }}
                    </a>
                </div>
                @endif

                @if($progress->evidence_text)
                <div class="mb-4">
                    <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">Written Evidence</p>
                    <div class="bg-dark-800 rounded-lg p-4 text-dark-300 text-sm">
                        {!! nl2br(e($progress->evidence_text)) !!}
                    </div>
                </div>
                @endif

                @if($progress->evidence_files && count($progress->evidence_files) > 0)
                <div class="mb-4">
                    <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-2">Attached Files</p>
                    <div class="space-y-2">
                        @foreach($progress->evidence_files as $file)
                        <a href="{{ asset('storage/' . $file) }}" target="_blank"
                           class="flex items-center gap-2 text-primary-400 hover:text-primary-300 text-sm bg-dark-800 rounded-lg px-4 py-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ basename($file) }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($progress->reflection)
                <div>
                    <p class="text-dark-500 text-xs font-medium uppercase tracking-wider mb-1">Reflection</p>
                    <div class="bg-dark-800 rounded-lg p-4 text-dark-300 text-sm italic">
                        {!! nl2br(e($progress->reflection)) !!}
                    </div>
                </div>
                @endif

                @if(!$progress->evidence_url && !$progress->evidence_text && empty($progress->evidence_files))
                <p class="text-dark-500 text-sm italic">No evidence submitted.</p>
                @endif
            </div>

            {{-- Peer Review --}}
            @if($progress->peer_rating || $progress->peer_feedback)
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-3">Peer Review</h3>
                <div class="flex items-center gap-4 mb-3">
                    <div class="flex items-center gap-1">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="text-lg {{ $i <= ($progress->peer_rating ?? 0) ? 'text-amber-400' : 'text-dark-600' }}">★</span>
                        @endfor
                    </div>
                    <span class="text-dark-400 text-sm">by {{ $progress->peerReviewer->name ?? 'Unknown' }}</span>
                    @if($progress->peer_reviewed_at)
                        <span class="text-dark-500 text-xs">{{ $progress->peer_reviewed_at->diffForHumans() }}</span>
                    @endif
                </div>
                @if($progress->peer_feedback)
                <div class="bg-dark-800 rounded-lg p-4 text-dark-300 text-sm">
                    {!! nl2br(e($progress->peer_feedback)) !!}
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Review Panel (Right) -->
        <div class="space-y-6">
            {{-- Timeline --}}
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-4">Timeline</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-dark-400">Started</span>
                        <span class="text-white">{{ $progress->started_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Submitted</span>
                        <span class="text-white">{{ $progress->submitted_at?->format('M d, Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Deadline</span>
                        <span class="{{ $progress->isPastDeadline ? 'text-red-400' : 'text-white' }}">
                            {{ $progress->deadline_at?->format('M d, Y') ?? '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dark-400">Base Points</span>
                        <span class="text-white">{{ $progress->curriculumActivity->points ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Review Form --}}
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-4">Your Review</h3>

                <form action="{{ route('admin.curriculum.reviews.process', $progress->id) }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Rubric Scores --}}
                    @if($progress->curriculumActivity->evaluation_rubric && count($progress->curriculumActivity->evaluation_rubric) > 0)
                    <div class="space-y-3">
                        <label class="text-dark-300 text-sm font-medium">Rubric Scores</label>
                        @foreach($progress->curriculumActivity->evaluation_rubric as $i => $criteria)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-dark-400 text-xs">{{ $criteria['criterion'] ?? "Criterion " . ($i+1) }}</span>
                                <span class="text-dark-500 text-xs">{{ $criteria['weight'] ?? 25 }}%</span>
                            </div>
                            <input type="range" name="rubric_scores[{{ $i }}]" min="0" max="100" value="{{ old('rubric_scores.' . $i, 70) }}"
                                   class="w-full accent-primary-500" oninput="this.nextElementSibling.textContent = this.value + '/100'">
                            <span class="text-dark-400 text-xs">70/100</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Points Override --}}
                    <div>
                        <label for="points" class="block text-sm font-medium text-dark-300 mb-2">Points to Award</label>
                        <input type="number" name="points" id="points" min="0" max="1000"
                               value="{{ old('points', $progress->curriculumActivity->points ?? 100) }}"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        <p class="text-dark-500 text-xs mt-1">Default: {{ $progress->curriculumActivity->points ?? 100 }} base pts</p>
                    </div>

                    {{-- Feedback --}}
                    <div>
                        <label for="feedback" class="block text-sm font-medium text-dark-300 mb-2">Feedback</label>
                        <textarea name="feedback" id="feedback" rows="4" placeholder="Provide constructive feedback..."
                                  class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('feedback') }}</textarea>
                    </div>

                    {{-- Decision Buttons --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="submit" name="decision" value="reject"
                                class="w-full px-4 py-2.5 bg-red-500/20 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/30 font-medium text-sm transition">
                            ✕ Reject
                        </button>
                        <button type="submit" name="decision" value="approve"
                                class="w-full px-4 py-2.5 bg-green-500/20 text-green-400 border border-green-500/30 rounded-lg hover:bg-green-500/30 font-medium text-sm transition">
                            ✓ Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
