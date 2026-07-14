@extends('layouts.app')

@section('title', 'Review Submission')

@section('content')
<div x-data="{ previewOpen: false, previewUrl: '', previewType: '' }" class="max-w-4xl mx-auto space-y-6">
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
                            @php
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isPdf = $ext === 'pdf';
                                $url = asset('storage/' . $file);
                            @endphp

                            @if($isImage || $isPdf)
                                <button type="button" @click="previewUrl = '{{ $url }}'; previewType = '{{ $ext }}'; previewOpen = true;"
                                   class="w-full flex items-center gap-2 text-primary-400 hover:text-primary-300 text-sm bg-dark-800 rounded-lg px-4 py-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    {{ basename($file) }} <span class="text-dark-500 text-xs ml-auto border border-dark-600 rounded px-1.5 py-0.5">Preview</span>
                                </button>
                            @else
                                <a href="{{ $url }}" target="_blank"
                                   class="flex items-center gap-2 text-primary-400 hover:text-primary-300 text-sm bg-dark-800 rounded-lg px-4 py-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    {{ basename($file) }} <span class="text-dark-500 text-xs ml-auto border border-dark-600 rounded px-1.5 py-0.5">Download</span>
                                </a>
                            @endif
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

            {{-- Peer Reviews --}}
            @if($progress->peerReviews && $progress->peerReviews->count() > 0)
            <div class="card p-6">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <span class="text-purple-400">👥</span> Peer Reviews ({{ $progress->peerReviews->where('status', \App\Enums\ActivityStatus::COMPLETED)->count() }}/{{ $progress->peerReviews->count() }})
                </h3>
                
                <div class="space-y-4">
                @foreach($progress->peerReviews as $review)
                    <div class="p-4 bg-dark-800/50 rounded-lg border border-dark-700">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-white font-medium">{{ $review->reviewer->name ?? 'Unknown' }}</span>
                                @if($review->status === \App\Enums\ActivityStatus::COMPLETED)
                                    <span class="px-2 py-0.5 bg-green-500/10 text-green-400 text-xs rounded border border-green-500/20">Completed</span>
                                @elseif($review->status === \App\Enums\ActivityStatus::BYPASSED)
                                    <span class="px-2 py-0.5 bg-dark-600 text-dark-300 text-xs rounded border border-dark-500">Bypassed</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 text-xs rounded border border-amber-500/20">Pending</span>
                                @endif
                            </div>
                            @if($review->completed_at)
                                <span class="text-dark-500 text-xs">{{ $review->completed_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        
                        @if($review->status === \App\Enums\ActivityStatus::COMPLETED)
                            <div class="flex items-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= ($review->rating ?? 0) ? 'text-amber-400' : 'text-dark-600' }}">★</span>
                                @endfor
                            </div>
                            @if($review->feedback)
                            <div class="bg-dark-900 rounded p-3 text-dark-300 text-sm">
                                {!! nl2br(e($review->feedback)) !!}
                            </div>
                            @endif
                        @elseif($review->status === \App\Enums\ActivityStatus::BYPASSED)
                            <p class="text-dark-400 text-sm italic">This review was bypassed.</p>
                        @endif
                    </div>
                @endforeach
                </div>
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
                        <label for="points" class="block text-sm font-medium text-dark-300 mb-2">Points to Award *</label>
                        <input type="number" name="points" id="points" min="0" max="1000" required
                               value="{{ old('points') }}" placeholder="e.g. {{ $progress->curriculumActivity->points ?? 100 }}"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 @error('points') border-red-500 @enderror">
                        @error('points')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-dark-500 text-xs mt-1 flex items-center justify-between">
                            <span>Default: {{ $progress->curriculumActivity->points ?? 100 }} base pts</span>
                            <span class="text-amber-500/70 italic">Type a higher number to award bonus points!</span>
                        </p>
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

    <!-- File Preview Modal -->
    <div x-show="previewOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak 
         style="display: none;">
        
        <div @click.away="previewOpen = false" 
             class="bg-dark-800 rounded-xl w-full max-w-5xl h-[85vh] flex flex-col relative shadow-2xl ring-1 ring-white/10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">
            
            <div class="flex justify-between items-center px-6 py-4 border-b border-dark-700 bg-dark-800/80 backdrop-blur rounded-t-xl">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    File Preview
                </h3>
                <div class="flex items-center gap-4">
                    <a :href="previewUrl" target="_blank" class="text-primary-400 hover:text-primary-300 text-sm font-medium flex items-center gap-1">
                        Open in New Tab
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <button @click="previewOpen = false" class="text-dark-400 hover:text-white bg-dark-700 hover:bg-dark-600 rounded-full p-1.5 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="flex-1 p-0 overflow-auto flex items-center justify-center bg-dark-900 rounded-b-xl relative">
                <!-- PDF iframe -->
                <template x-if="previewType === 'pdf'">
                    <iframe :src="previewUrl" class="w-full h-full border-0"></iframe>
                </template>
                
                <!-- Image tag -->
                <template x-if="['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(previewType)">
                    <img :src="previewUrl" class="max-w-full max-h-full object-contain p-4">
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
