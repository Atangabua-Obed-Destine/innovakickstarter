@extends('layouts.app')

@section('title', $activity->title)

@section('content')
@php
    $statusValue = $progress?->status?->value ?? $progress?->status ?? null;
    $isStarted = in_array($statusValue, ['in_progress', 'submitted', 'peer_review', 'under_review', 'completed', 'rejected', 'overdue']);
    $canSubmit = in_array($statusValue, ['in_progress', 'rejected', 'overdue']);
    $isCompleted = $statusValue === 'completed';
    $isLocked = $statusValue === 'locked';
    $isWaiting = in_array($statusValue, ['submitted', 'peer_review', 'under_review']);

    // Interview activity detection
    $isInterviewActivity = $activity->requiresInterviewSession();
    $interviewConfig = $activity->interview_config ?? [];
    $linkedInterviews = $linkedInterviews ?? collect();

    // Check availability for start button
    // "available" status means progress record exists but activity hasn't been started yet
    $canStart = false;
    $lockReason = null;
    if (!$progress || $statusValue === 'available') {
        try {
            $canStart = $activity->isAvailableFor(auth()->user());
            if (!$canStart) {
                if (!$activity->milestone->isUnlockedFor(auth()->user())) {
                    $lockReason = 'Complete the previous milestone first to unlock this activity.';
                } elseif (!$activity->prerequisitesMet(auth()->user())) {
                    $lockReason = 'Complete prerequisite activities first.';
                }
            }
        } catch (\Exception $e) {
            $lockReason = 'Unable to determine availability.';
        }
    }

    // Interview-specific computed values
    $interviewSessions = $linkedInterviews;
    $completedSessions = $interviewSessions->filter(fn($s) => ($s->status->value ?? $s->status) === 'completed');
    $bestScore = $completedSessions->max(fn($s) => $s->overall_score ?? $s->score ?? 0) ?? 0;
    $requiredCount = $interviewConfig['count'] ?? 1;
    $minScore = $interviewConfig['min_score'] ?? 70;
    $passed = $bestScore >= $minScore && $completedSessions->count() >= $requiredCount;
    $activeSession = $interviewSessions->filter(fn($s) => in_array($s->status->value ?? $s->status, ['in_progress', 'scheduled']))->first();
@endphp

<div x-data="{ submitOpen: {{ $errors->any() ? 'true' : 'false' }} }" class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-dark-400 text-sm">
        <a href="{{ route('curriculum.index') }}" class="hover:text-white transition">📚 Curriculum</a>
        <span class="text-dark-600">/</span>
        <span>{{ $activity->milestone->title ?? 'Milestone' }}</span>
        <span class="text-dark-600">/</span>
        <span class="text-primary-400">{{ Str::limit($activity->title, 40) }}</span>
    </div>

    {{-- ============================================= --}}
    {{-- ACTIVITY HEADER --}}
    {{-- ============================================= --}}
    <div class="card p-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-xl bg-primary-500/20 flex items-center justify-center text-3xl flex-shrink-0">
                {{ $activity->type?->icon() ?? '📋' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-white">{{ $activity->title }}</h1>
                    @if($activity->is_required)
                        <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full font-medium">Required</span>
                    @else
                        <span class="px-2 py-0.5 bg-dark-700 text-dark-400 text-xs rounded-full">Optional</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-dark-800 rounded-lg text-sm">
                        <span>{{ $activity->type?->icon() ?? '' }}</span>
                        <span class="text-dark-300">{{ $activity->type?->label() ?? 'Activity' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-dark-800 rounded-lg text-sm">
                        <span>{{ $activity->difficulty_level?->icon() ?? '' }}</span>
                        <span class="text-dark-300">{{ $activity->difficulty_level?->label() ?? '' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-primary-500/10 rounded-lg text-sm text-primary-400 font-medium">
                        🏆 {{ $activity->points }} pts
                    </span>
                    @if($activity->is_collaborative)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500/10 rounded-lg text-sm text-blue-400">
                            👥 Collaborative
                        </span>
                    @endif
                    @if($activity->requires_peer_review)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-500/10 rounded-lg text-sm text-purple-400">
                            🔍 Peer Review
                        </span>
                    @endif
                    @if($activity->requires_cross_track)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500/10 rounded-lg text-sm text-amber-400">
                            🔀 Cross-Track
                        </span>
                    @endif
                    @if($progress && $progress->attempt_number > 1)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500/10 rounded-lg text-sm text-amber-400 font-medium">
                            🔄 Attempt {{ $progress->attempt_number }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- LEARNING RESOURCES --}}
    {{-- ============================================= --}}
    @if(!empty($activity->resources) && is_array($activity->resources))
    <div class="card p-6 space-y-4">
        <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-2">📚 Learning Resources</h2>
        <div class="grid gap-3">
            @foreach($activity->resources as $resource)
                @php
                    $res = is_array($resource) ? $resource : ['type' => 'link', 'title' => 'Resource', 'content' => $resource];
                    $type = $res['type'] ?? 'link';
                    $title = $res['title'] ?? 'Resource';
                    $content = $res['content'] ?? '';
                    
                    // Fallback to youtube type if it's a link but contains youtube URL
                    if ($type === 'link' && (str_contains($content, 'youtube.com') || str_contains($content, 'youtu.be'))) {
                        $type = 'youtube';
                    }
                @endphp

                @if($type === 'youtube')
                    @php
                        // Extract video ID for embed
                        $videoId = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $content, $match)) {
                            $videoId = $match[1];
                        }
                    @endphp
                    @if($videoId)
                        <style>
                            @keyframes watermarkFloat {
                                0% { top: 10%; left: 5%; transform: translate(0, 0) rotate(-10deg); }
                                25% { top: 80%; left: 70%; transform: translate(-100%, -100%) rotate(-10deg); }
                                50% { top: 15%; left: 80%; transform: translate(-100%, 0) rotate(-10deg); }
                                75% { top: 75%; left: 10%; transform: translate(0, -100%) rotate(-10deg); }
                                100% { top: 10%; left: 5%; transform: translate(0, 0) rotate(-10deg); }
                            }
                            .video-watermark {
                                position: absolute;
                                pointer-events: none;
                                z-index: 10;
                                color: rgba(255, 255, 255, 0.12); /* Barely visible */
                                font-size: clamp(1rem, 3vw, 2.5rem);
                                font-weight: 900;
                                text-transform: uppercase;
                                text-align: center;
                                line-height: 1.1;
                                white-space: nowrap;
                                animation: watermarkFloat 35s linear infinite;
                                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
                                mix-blend-mode: overlay;
                            }
                        </style>
                        <div class="rounded-lg overflow-hidden border border-dark-700 bg-black w-full relative group" style="padding-top: 56.25%;">
                            <iframe src="https://www.youtube.com/embed/{{ $videoId }}?rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="absolute top-0 left-0 w-full h-full z-0"></iframe>
                            
                            {{-- Watermark Overlay --}}
                            <div class="video-watermark pointer-events-none">
                                I-NNOVA KICKSTARTER<br>ACCELERATOR
                            </div>
                        </div>
                    @else
                        <a href="{{ $content }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-primary-500/50 hover:bg-dark-800 transition group">
                            <span class="w-10 h-10 rounded bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0">▶️</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                                <p class="text-dark-500 text-xs truncate">{{ $content }}</p>
                            </div>
                        </a>
                    @endif
                @elseif($type === 'file')
                    @php
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $content);
                        $isPdf = preg_match('/\.pdf$/i', $content);
                    @endphp
                    
                    @if($isImage)
                        <div class="rounded-lg overflow-hidden border border-dark-700 bg-dark-800 p-2">
                            <p class="text-white text-sm font-medium mb-2 px-2">{{ $title }}</p>
                            <img src="{{ asset(ltrim($content, '/')) }}" alt="{{ $title }}" class="max-w-full h-auto rounded">
                            <div class="mt-2 text-right">
                                <a href="{{ asset(ltrim($content, '/')) }}" download class="text-xs text-primary-400 hover:text-primary-300">Download Image</a>
                            </div>
                        </div>
                    @elseif($isPdf)
                        <div class="rounded-lg overflow-hidden border border-dark-700 bg-dark-800 p-2">
                            <div class="flex items-center justify-between px-2 mb-2">
                                <p class="text-white text-sm font-medium">{{ $title }}</p>
                                <a href="{{ asset(ltrim($content, '/')) }}" target="_blank" class="text-xs text-primary-400 hover:text-primary-300 flex items-center gap-1">
                                    <span>Open in new tab</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                            <iframe src="{{ asset(ltrim($content, '/')) }}" class="w-full h-[500px] rounded border border-dark-700"></iframe>
                        </div>
                    @else
                        <a href="{{ asset(ltrim($content, '/')) }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-primary-500/50 hover:bg-dark-800 transition group">
                            <span class="w-10 h-10 rounded bg-green-500/10 text-green-400 flex items-center justify-center flex-shrink-0">📄</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                                <p class="text-dark-500 text-xs">Download File</p>
                            </div>
                        </a>
                    @endif
                @else
                    <a href="{{ $content }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-dark-700 bg-dark-800/50 hover:border-primary-500/50 hover:bg-dark-800 transition group">
                        <span class="w-10 h-10 rounded bg-blue-500/10 text-blue-400 flex items-center justify-center flex-shrink-0">🔗</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-primary-400 group-hover:text-primary-300 text-sm font-medium truncate">{{ $title }}</p>
                            <p class="text-dark-500 text-xs truncate">{{ $content }}</p>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- STATUS & ACTION CARD (always visible) --}}
    {{-- ============================================= --}}
    <div class="card overflow-hidden">
        {{-- Status Colored Top Bar --}}
        <div class="h-1.5
            @if($isCompleted) bg-green-500
            @elseif($canSubmit) bg-blue-500
            @elseif($isWaiting) bg-purple-500
            @elseif($statusValue === 'rejected') bg-red-500
            @elseif($canStart) bg-primary-500
            @else bg-dark-600
            @endif"></div>

        <div class="p-6">
            {{-- COMPLETED STATE --}}
            @if($isCompleted)
            <div class="text-center space-y-3">
                <div class="text-5xl">🎉</div>
                <h3 class="text-xl font-bold text-green-400">Activity Completed!</h3>
                <div class="flex items-center justify-center gap-6 text-sm">
                    @if($progress->score_awarded)
                    <span class="text-dark-300">Score: <span class="text-white font-bold">{{ $progress->score_awarded }}/100</span></span>
                    @endif
                    @if($progress->points_awarded)
                        @if($progress->points_awarded > ($activity->points ?? 0))
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full font-bold text-sm shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                                ⭐ {{ $progress->points_awarded }} Points Earned (Bonus Included!)
                            </span>
                        @else
                            <span class="text-green-400 font-medium">+{{ $progress->points_awarded }} points earned</span>
                        @endif
                    @endif
                    @if($progress->reviewed_at)
                    <span class="text-dark-500">Reviewed {{ $progress->reviewed_at->diffForHumans() }}</span>
                    @endif
                </div>
                @if($progress->review_notes)
                <div class="mt-4 p-4 bg-green-500/5 border border-green-500/20 rounded-lg text-left">
                    <p class="text-green-400 text-xs font-medium mb-1">Reviewer Feedback</p>
                    <p class="text-dark-300 text-sm">{!! nl2br(e($progress->review_notes)) !!}</p>
                </div>
                @endif

                @if($progress->submission_notes || !empty($progress->evidence))
                <div class="mt-6 p-4 bg-dark-800/50 border border-dark-700 rounded-lg text-left">
                    <h4 class="text-white text-sm font-medium mb-4">Your Submission</h4>
                    
                    @if($progress->submission_notes)
                    <div class="mb-4">
                        <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">Reflection / Notes</p>
                        <p class="text-dark-200 text-sm whitespace-pre-wrap">{{ $progress->submission_notes }}</p>
                    </div>
                    @endif

                    @if(!empty($progress->evidence) && is_array($progress->evidence))
                    <div class="space-y-3">
                        @if(!empty($progress->evidence['evidence_url']))
                        <div>
                            <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">URL Link</p>
                            <a href="{{ $progress->evidence['evidence_url'] }}" target="_blank" class="text-primary-400 hover:text-primary-300 text-sm break-all">
                                {{ $progress->evidence['evidence_url'] }}
                            </a>
                        </div>
                        @endif

                        @if(!empty($progress->evidence['evidence_text']))
                        <div>
                            <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">Text Submission</p>
                            <div class="p-3 bg-dark-900 rounded text-sm text-dark-300 whitespace-pre-wrap">{{ $progress->evidence['evidence_text'] }}</div>
                        </div>
                        @endif

                        @if(!empty($progress->evidence['evidence_files']))
                        <div>
                            <p class="text-dark-400 text-xs mb-2 uppercase tracking-wider font-semibold">Uploaded Files</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($progress->evidence['evidence_files'] as $file)
                                <a href="{{ Storage::url($file) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-dark-700 hover:bg-dark-600 rounded text-xs text-dark-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Attachment {{ $loop->iteration }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- SUBMITTED / WAITING FOR REVIEW --}}
            @elseif($isWaiting)
            <div x-data="{ bypassOpen: false }" class="text-center space-y-3">
                <div class="text-5xl">⏳</div>
                <h3 class="text-xl font-bold text-purple-400">
                    {{ $statusValue === 'peer_review' ? 'Awaiting Peer Review' : ($statusValue === 'under_review' ? 'Under Admin Review' : 'Submitted for Review') }}
                </h3>
                <p class="text-dark-400 text-sm">Your submission is being reviewed. You'll be notified once feedback is available.</p>
                @if($progress->submitted_at)
                <p class="text-dark-500 text-xs">Submitted {{ $progress->submitted_at->diffForHumans() }}</p>
                @endif
                @if($statusValue === 'peer_review')
                <div class="mt-4 flex justify-center">
                    <button type="button" @click="bypassOpen = true" class="px-4 py-2 text-sm bg-dark-800 border border-dark-600 hover:border-purple-500 hover:text-purple-400 text-dark-300 rounded-lg transition-colors">
                        Skip Peer Review
                    </button>
                </div>

                <!-- Bypass Modal -->
                <div x-show="bypassOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 text-left" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                    <div x-show="bypassOpen" x-transition.opacity class="fixed inset-0 bg-dark-900/80 backdrop-blur-sm" @click="bypassOpen = false"></div>
                    <div x-show="bypassOpen" x-transition class="relative bg-dark-800 border border-dark-600 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                        <h3 class="text-xl font-bold text-white mb-2" id="modal-title">Bypass Peer Review</h3>
                        <p class="text-dark-300 text-sm mb-6">Are you sure you want to bypass peer review? Please provide a reason for the admin.</p>
                        <form action="{{ route('peer-review.bypass', $progress) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-dark-300 mb-1">Reason for Bypassing</label>
                                    <textarea name="reason" id="reason" rows="3" required class="w-full bg-dark-900 border border-dark-600 rounded-lg px-4 py-2 text-white placeholder-dark-500 focus:border-purple-500 focus:ring-1 focus:ring-purple-500" placeholder="e.g. My peers are unavailable..."></textarea>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="bypassOpen = false" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">Submit Reason</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($progress->submission_notes || !empty($progress->evidence))
                <div class="mt-6 p-4 bg-dark-800/50 border border-dark-700 rounded-lg text-left">
                    <h4 class="text-white text-sm font-medium mb-4">Your Submission</h4>
                    
                    @if($progress->submission_notes)
                    <div class="mb-4">
                        <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">Reflection / Notes</p>
                        <p class="text-dark-200 text-sm whitespace-pre-wrap">{{ $progress->submission_notes }}</p>
                    </div>
                    @endif

                    @if(!empty($progress->evidence) && is_array($progress->evidence))
                    <div class="space-y-3">
                        @if(!empty($progress->evidence['evidence_url']))
                        <div>
                            <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">URL Link</p>
                            <a href="{{ $progress->evidence['evidence_url'] }}" target="_blank" class="text-primary-400 hover:text-primary-300 text-sm break-all">
                                {{ $progress->evidence['evidence_url'] }}
                            </a>
                        </div>
                        @endif

                        @if(!empty($progress->evidence['evidence_text']))
                        <div>
                            <p class="text-dark-400 text-xs mb-1 uppercase tracking-wider font-semibold">Text Submission</p>
                            <div class="p-3 bg-dark-900 rounded text-sm text-dark-300 whitespace-pre-wrap">{{ $progress->evidence['evidence_text'] }}</div>
                        </div>
                        @endif

                        @if(!empty($progress->evidence['evidence_files']))
                        <div>
                            <p class="text-dark-400 text-xs mb-2 uppercase tracking-wider font-semibold">Uploaded Files</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($progress->evidence['evidence_files'] as $file)
                                <a href="{{ Storage::url($file) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-dark-700 hover:bg-dark-600 rounded text-xs text-dark-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Attachment {{ $loop->iteration }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- IN PROGRESS: INTERVIEW ACTIVITY --}}
            @elseif($isInterviewActivity && $canSubmit)
            <div class="space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2.5 h-2.5 bg-purple-500 rounded-full animate-pulse"></span>
                            <h3 class="text-lg font-semibold text-purple-400">🎤 Interview In Progress</h3>
                        </div>
                        <p class="text-dark-400 text-sm">
                            Complete {{ $requiredCount }} interview session{{ $requiredCount > 1 ? 's' : '' }} 
                            with a minimum score of {{ $minScore }}% to pass.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($activeSession)
                            @if($activeSession->mode === \App\Enums\InterviewMode::AI)
                            <a href="{{ route('interviews.ai-room', $activeSession) }}" class="btn-primary text-lg px-6 py-3 flex-shrink-0">
                                🎤 Continue Interview
                            </a>
                            @else
                            <a href="{{ route('interviews.show', $activeSession) }}" class="btn-primary text-lg px-6 py-3 flex-shrink-0">
                                📋 View Session
                            </a>
                            @endif
                        @else
                            <form action="{{ route('curriculum.activity.interview', $activity) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary text-lg px-6 py-3">
                                    🎤 {{ $completedSessions->count() > 0 ? 'Retry Interview' : 'Launch Interview' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Interview Progress Tracker --}}
                <div class="p-4 bg-dark-800/50 rounded-xl border border-dark-700 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-dark-400">Sessions: <span class="text-white font-medium">{{ $completedSessions->count() }}/{{ $requiredCount }}</span></span>
                        <span class="text-dark-400">Best Score: 
                            <span class="{{ $bestScore >= $minScore ? 'text-green-400' : 'text-amber-400' }} font-bold">{{ round($bestScore) }}%</span>
                            <span class="text-dark-500">/ {{ $minScore }}% required</span>
                        </span>
                    </div>
                    <div class="w-full bg-dark-700 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full transition-all {{ $bestScore >= $minScore ? 'bg-green-500' : 'bg-amber-500' }}"
                             style="width: {{ min(100, ($bestScore / max($minScore, 1)) * 100) }}%"></div>
                    </div>
                    @if($passed)
                    <p class="text-green-400 text-sm font-medium">✅ You've met the requirements! Your submission is being processed.</p>
                    @elseif($completedSessions->count() > 0)
                    <p class="text-amber-400 text-sm">{{ $bestScore < $minScore ? "Score needs improvement. Try again, you've got this!" : "Complete more sessions to meet the requirement." }}</p>
                    @endif
                </div>
            </div>

            {{-- IN PROGRESS / CAN SUBMIT (non-interview) --}}
            @elseif($canSubmit)
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></span>
                        <h3 class="text-lg font-semibold text-blue-400">
                            {{ $statusValue === 'rejected' ? '🔄 Revision Needed' : ($statusValue === 'overdue' ? '⚠️ Overdue: Submit Now' : '🔨 In Progress') }}
                        </h3>
                    </div>
                    @if($progress->deadline_at)
                    <p class="text-dark-400 text-sm">
                        ⏰ Due {{ $progress->deadline_at->format('M d, Y') }}
                        @if($progress->deadline_at->isPast())
                            <span class="text-red-400 font-medium ml-1">
                                ({{ $progress->deadline_at->diffForHumans() }}, {{ $activity->late_penalty_percent ?? 20 }}% late penalty)
                            </span>
                        @else
                            <span class="text-dark-500 ml-1">({{ $progress->deadline_at->diffForHumans() }})</span>
                        @endif
                    </p>
                    @endif
                    @if($statusValue === 'rejected' && $progress->review_notes)
                    <div class="mt-2 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                        <p class="text-red-400 text-xs font-medium mb-1">Review Feedback</p>
                        <p class="text-dark-300 text-sm">{!! nl2br(e($progress->review_notes)) !!}</p>
                        <p class="text-dark-500 text-xs mt-1">by {{ $progress->reviewer->name ?? 'Reviewer' }} · {{ $progress->reviewed_at?->diffForHumans() ?? '' }}</p>
                    </div>
                    @endif
                </div>
                <button type="button" @click="submitOpen = true; window.AudioManager?.play('pop')" class="btn-primary text-lg px-8 py-3 flex-shrink-0">
                    {{ $statusValue === 'rejected' ? '📝 Resubmit' : '📤 Submit Work' }}
                </button>
            </div>

            {{-- NOT STARTED YET (can start) --}}
            @elseif($canStart)
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white mb-1">Ready to Begin!</h3>
                    @if($isInterviewActivity)
                    <p class="text-dark-400 text-sm">
                        🎤 This activity requires completing a <span class="text-purple-400 font-medium">{{ \App\Enums\InterviewType::tryFrom($interviewConfig['type'] ?? 'behavioral')?->label() ?? 'Mock' }} Interview</span>.
                        Clicking start will launch your interview session.
                    </p>
                    <p class="text-dark-500 text-xs mt-1">
                        Minimum score: {{ $minScore }}% · Sessions required: {{ $requiredCount }} · 
                        Mode: {{ ucfirst($interviewConfig['mode'] ?? 'AI') }}
                    </p>
                    @else
                    <p class="text-dark-400 text-sm">Read the instructions below, then start this activity when you're ready.</p>
                    @if($activity->deadline_days)
                    <p class="text-dark-500 text-xs mt-1">⏰ You'll have {{ $activity->deadline_days }} days to complete it once started
                        @if($activity->grace_period_days) (+ {{ $activity->grace_period_days }} day grace period) @endif
                    </p>
                    @endif
                    @endif
                </div>
                <form action="{{ route('curriculum.activity.start', $activity) }}" method="POST" x-on:submit="window.AudioManager?.play('start')">
                    @csrf
                    <button type="submit" class="btn-primary text-lg px-8 py-3">
                        {{ $isInterviewActivity ? '🎤 Start Interview' : '🚀 Start Activity' }}
                    </button>
                </form>
            </div>

            {{-- LOCKED --}}
            @else
            <div class="text-center space-y-3">
                <div class="text-5xl">🔒</div>
                <h3 class="text-xl font-bold text-dark-400">Activity Locked</h3>
                <p class="text-dark-500 text-sm max-w-md mx-auto">
                    {{ $lockReason ?? 'Complete previous activities or milestones to unlock this one.' }}
                </p>

                {{-- Show what needs to be done --}}
                @if($activity->chain_parent_id)
                <div class="mt-3 p-3 bg-dark-800 rounded-lg inline-flex items-center gap-2 text-sm">
                    <span class="text-dark-400">Requires:</span>
                    <a href="{{ route('curriculum.activity.show', $activity->chain_parent_id) }}"
                       class="text-primary-400 hover:text-primary-300 transition">
                        {{ $activity->chainParent->title ?? 'Previous activity' }} →
                    </a>
                </div>
                @endif

                @if(!empty($activity->prerequisites))
                <div class="mt-3 space-y-1">
                    <p class="text-dark-500 text-xs font-medium">Prerequisites:</p>
                    @foreach($activity->prerequisites as $prereqId)
                    @php $prereq = \App\Models\TrackCurriculumActivity::find($prereqId); @endphp
                    @if($prereq)
                    <a href="{{ route('curriculum.activity.show', $prereq) }}"
                       class="block text-primary-400 hover:text-primary-300 text-sm transition">
                        → {{ $prereq->title }}
                    </a>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- INTERVIEW DETAILS (for mock_interview activities) --}}
    {{-- ============================================= --}}
    @if($isInterviewActivity)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-purple-400">🎤</span> Interview Details
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-dark-400 text-xs mb-1">Interview Type</p>
                <p class="text-white font-semibold text-sm">{{ \App\Enums\InterviewType::tryFrom($interviewConfig['type'] ?? 'behavioral')?->label() ?? 'Behavioral' }}</p>
            </div>
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-dark-400 text-xs mb-1">Mode</p>
                <p class="text-white font-semibold text-sm">
                    {{ ($interviewConfig['mode'] ?? 'ai') === 'ai' ? '🤖 AI-Powered' : (($interviewConfig['mode'] ?? '') === 'human' ? '👤 Human' : '👥 Peer') }}
                </p>
            </div>
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-dark-400 text-xs mb-1">Min. Score</p>
                <p class="text-amber-400 font-bold text-lg">{{ $minScore }}%</p>
            </div>
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-dark-400 text-xs mb-1">Required Sessions</p>
                <p class="text-primary-400 font-bold text-lg">{{ $requiredCount }}</p>
            </div>
        </div>

        @if(!empty($interviewConfig['difficulty']))
        <div class="flex items-center gap-2 text-sm text-dark-400">
            <span>Difficulty:</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                {{ $interviewConfig['difficulty'] === 'beginner' ? 'bg-green-500/10 text-green-400' : 
                  ($interviewConfig['difficulty'] === 'advanced' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400') }}">
                {{ ucfirst($interviewConfig['difficulty']) }}
            </span>
        </div>
        @endif
    </div>

    {{-- Interview Session History --}}
    @if($interviewSessions->count() > 0)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-indigo-400">📊</span> Your Interview Sessions
        </h3>
        <div class="space-y-3">
            @foreach($interviewSessions as $session)
            @php
                $sessionStatus = $session->status->value ?? $session->status ?? 'unknown';
                $sessionScore = $session->overall_score ?? $session->score ?? 0;
                $sessionPassed = $sessionScore >= $minScore;
            @endphp
            <div class="flex items-center gap-4 p-4 bg-dark-800 rounded-lg border border-dark-700 hover:border-dark-600 transition">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg flex-shrink-0
                    {{ $sessionStatus === 'completed' ? ($sessionPassed ? 'bg-green-500/10 border border-green-500/30' : 'bg-red-500/10 border border-red-500/30') : 
                       ($sessionStatus === 'in_progress' ? 'bg-blue-500/10 border border-blue-500/30' : 'bg-dark-700') }}">
                    @if($sessionStatus === 'completed')
                        {{ $sessionPassed ? '✅' : '❌' }}
                    @elseif($sessionStatus === 'in_progress')
                        🎤
                    @elseif($sessionStatus === 'scheduled')
                        📅
                    @else
                        ⏸️
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-white text-sm font-medium">
                            {{ $session->type?->label() ?? 'Interview' }} Session
                        </p>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $sessionStatus === 'completed' ? ($sessionPassed ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400') :
                               ($sessionStatus === 'in_progress' ? 'bg-blue-500/10 text-blue-400' : 'bg-dark-700 text-dark-400') }}">
                            {{ ucfirst(str_replace('_', ' ', $sessionStatus)) }}
                        </span>
                    </div>
                    <p class="text-dark-400 text-xs mt-0.5">
                        {{ $session->created_at?->format('M d, Y \a\t g:i A') ?? 'Pending' }}
                        @if($session->duration_minutes)
                            · {{ $session->duration_minutes }} min
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($sessionStatus === 'completed')
                    <div class="text-right">
                        <p class="text-lg font-bold {{ $sessionPassed ? 'text-green-400' : 'text-red-400' }}">{{ round($sessionScore) }}%</p>
                        <p class="text-dark-500 text-xs">{{ $sessionPassed ? 'Passed' : 'Below ' . $minScore . '%' }}</p>
                    </div>
                    @endif
                    @if(in_array($sessionStatus, ['completed', 'in_progress']))
                    @php
                        $sessionLink = $sessionStatus === 'in_progress'
                            ? ($session->mode === \App\Enums\InterviewMode::AI
                                ? route('interviews.ai-room', $session)
                                : route('interviews.show', $session))
                            : route('interviews.show', $session);
                    @endphp
                    <a href="{{ $sessionLink }}"
                       class="text-primary-400 hover:text-primary-300 text-sm font-medium transition">
                        {{ $sessionStatus === 'in_progress' ? 'Continue →' : 'Review →' }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- ============================================= --}}
    {{-- DESCRIPTION --}}
    {{-- ============================================= --}}
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-primary-400">📖</span> Description
        </h3>
        <div class="text-dark-300 text-sm leading-relaxed">
            {!! nl2br(e($activity->description)) !!}
        </div>
    </div>

    {{-- ============================================= --}}
    {{-- INSTRUCTIONS --}}
    {{-- ============================================= --}}
    @if($activity->instructions)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-blue-400">📋</span> Step-by-Step Instructions
        </h3>
        <div class="text-dark-300 text-sm leading-relaxed prose prose-invert max-w-none">
            {!! nl2br(e($activity->instructions)) !!}
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- WHAT TO SUBMIT (Evidence Requirements) --}}
    {{-- ============================================= --}}
    @if($activity->evidence_requirements && count($activity->evidence_requirements) > 0)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-green-400">📎</span> What to Submit
        </h3>
        <p class="text-dark-400 text-sm mb-4">You'll need to provide the following evidence when submitting:</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($activity->evidence_requirements as $evType)
            @php
                $evEnum = \App\Enums\EvidenceType::tryFrom($evType);
            @endphp
            <div class="flex items-center gap-3 text-dark-300 text-sm bg-dark-800 rounded-lg px-4 py-3 border border-dark-700">
                <span class="text-lg">{{ $evEnum?->icon() ?? '📎' }}</span>
                <span class="font-medium">{{ $evEnum?->label() ?? $evType }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- EVALUATION CRITERIA (Rubric) --}}
    {{-- ============================================= --}}
    @if($activity->evaluation_rubric && count($activity->evaluation_rubric) > 0)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-amber-400">⚖️</span> How You'll Be Graded
        </h3>
        <p class="text-dark-400 text-sm mb-4">Your submission will be evaluated on these criteria:</p>
        <div class="space-y-3">
            @foreach($activity->evaluation_rubric as $criteria)
            <div class="flex items-start gap-3 bg-dark-800 rounded-lg p-4 border border-dark-700">
                <div class="w-10 h-10 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-400 text-xs font-bold flex-shrink-0 border border-amber-500/30">
                    {{ $criteria['weight'] ?? 25 }}%
                </div>
                <div>
                    <p class="text-white text-sm font-medium">{{ $criteria['criterion'] ?? 'Criterion' }}</p>
                    @if(!empty($criteria['description']))
                    <p class="text-dark-400 text-sm mt-1">{{ $criteria['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- DEADLINE & POLICIES --}}
    {{-- ============================================= --}}
    @if($activity->deadline_days || $activity->late_penalty_percent || $activity->grace_period_days)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-orange-400">⏰</span> Deadline & Policies
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if($activity->deadline_days)
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-2xl font-bold text-white">{{ $activity->deadline_days }}</p>
                <p class="text-dark-400 text-xs mt-1">days to complete</p>
            </div>
            @endif
            @if($activity->grace_period_days)
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-2xl font-bold text-amber-400">+{{ $activity->grace_period_days }}</p>
                <p class="text-dark-400 text-xs mt-1">grace period (days)</p>
            </div>
            @endif
            @if($activity->late_penalty_percent)
            <div class="bg-dark-800 rounded-lg p-4 border border-dark-700 text-center">
                <p class="text-2xl font-bold text-red-400">-{{ $activity->late_penalty_percent }}%</p>
                <p class="text-dark-400 text-xs mt-1">late penalty</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- ACTIVITY CHAIN (if part of a sequence) --}}
    {{-- ============================================= --}}
    @if($activity->is_chained && ($activity->chainParent || $activity->chainChildren->count() > 0))
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-indigo-400">🔗</span> Activity Chain
        </h3>
        <p class="text-dark-400 text-sm mb-4">This activity is part of a sequence. Complete them in order:</p>
        <div class="space-y-2">
            @foreach($activity->chain as $chainAct)
            @php
                $chainProgress = \App\Models\FellowCurriculumProgress::where('fellow_id', auth()->id())
                    ->where('curriculum_activity_id', $chainAct->id)->first();
                $chainStatus = $chainProgress?->status?->value ?? 'not_started';
                $isCurrent = $chainAct->id === $activity->id;
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg border transition
                {{ $isCurrent ? 'bg-primary-500/10 border-primary-500/30' : 'bg-dark-800 border-dark-700' }}">
                <span class="text-lg flex-shrink-0">
                    @if($chainStatus === 'completed') ✅
                    @elseif($isCurrent) 👉
                    @elseif(in_array($chainStatus, ['in_progress', 'submitted'])) 🔄
                    @else 🔒
                    @endif
                </span>
                @if($chainAct->id !== $activity->id)
                <a href="{{ route('curriculum.activity.show', $chainAct) }}"
                   class="text-sm {{ $isCurrent ? 'text-primary-400 font-medium' : 'text-dark-300 hover:text-white' }} transition truncate">
                    {{ $chainAct->title }}
                </a>
                @else
                <span class="text-sm text-primary-400 font-medium truncate">{{ $chainAct->title }} (current)</span>
                @endif
                <span class="text-dark-500 text-xs ml-auto flex-shrink-0">{{ $chainAct->points }} pts</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================= --}}
    {{-- BOTTOM ACTION BAR --}}
    {{-- ============================================= --}}
    <div class="flex items-center justify-between gap-3 py-2">
        <a href="{{ route('curriculum.index') }}" class="btn-secondary">← Back to Curriculum</a>

        <div class="flex items-center gap-3">
            @if($isInterviewActivity && $canSubmit)
                @if($activeSession)
                    @if($activeSession->mode === \App\Enums\InterviewMode::AI)
                    <a href="{{ route('interviews.ai-room', $activeSession) }}" class="btn-primary">
                        🎤 Continue Interview
                    </a>
                    @else
                    <a href="{{ route('interviews.show', $activeSession) }}" class="btn-primary">
                        📋 View Session
                    </a>
                    @endif
                @else
                    <form action="{{ route('curriculum.activity.interview', $activity) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary">
                            🎤 {{ $completedSessions->count() > 0 ? 'Retry Interview' : 'Launch Interview' }}
                        </button>
                    </form>
                @endif
            @elseif($canSubmit && $progress)
                <button type="button" @click="submitOpen = true; window.AudioManager?.play('pop')" class="btn-primary">
                    {{ $statusValue === 'rejected' ? '📝 Resubmit Work' : '📤 Submit Work' }}
                </button>
            @elseif($canStart)
                <form action="{{ route('curriculum.activity.start', $activity) }}" method="POST" x-on:submit="window.AudioManager?.play('start')">
                    @csrf
                    <button type="submit" class="btn-primary">
                        {{ $isInterviewActivity ? '🎤 Start Interview' : '🚀 Start Activity' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
<!-- Slide-over Panel for Submission -->
@if($progress && $canSubmit)
<div x-show="submitOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
    <div class="absolute inset-0 overflow-hidden">
        <!-- Backdrop -->
        <div x-show="submitOpen" x-transition.opacity class="absolute inset-0 bg-dark-900/80 backdrop-blur-sm transition-opacity" @click="submitOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex">
            <!-- Slide-over panel -->
            <div x-show="submitOpen" 
                 x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-xl relative">
                 
                <!-- Panel Content -->
                <div class="h-full flex flex-col bg-dark-900 shadow-2xl overflow-y-auto border-l border-dark-700">
                    <div class="px-6 py-5 border-b border-dark-700 bg-dark-800/50 flex items-center justify-between sticky top-0 z-10">
                        <div>
                            <h2 class="text-xl font-bold text-white" id="slide-over-title">Submit Work</h2>
                            <p class="text-dark-400 text-sm mt-0.5">{{ Str::limit($activity->title, 40) }}</p>
                        </div>
                        <button type="button" @click="submitOpen = false" class="text-dark-400 hover:text-white transition bg-dark-800 p-2 rounded-lg hover:bg-dark-700">
                            <span class="sr-only">Close panel</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="flex-1 px-6 py-6">
                        @if($errors->any())
                        <div class="p-4 mb-6 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Deadline Warning -->
                        @if($progress->deadline_at)
                        <div class="p-4 mb-6 rounded-lg border
                            {{ $progress->isPastDeadline ? 'bg-red-500/10 border-red-500/30' : 'bg-dark-800 border-dark-700' }}">
                            <div class="flex items-center gap-2">
                                <span class="{{ $progress->isPastDeadline ? 'text-red-400' : 'text-dark-400' }}">
                                    {{ $progress->isPastDeadline ? '⚠️' : '⏰' }}
                                    Deadline: {{ $progress->deadline_at->format('M d, Y g:i A') }}
                                </span>
                                @if($progress->isPastDeadline)
                                    <span class="text-red-400 text-sm font-medium ml-auto">
                                        -{{ $activity->late_penalty_percent ?? 20 }}% points
                                    </span>
                                @else
                                    <span class="text-dark-400 text-sm ml-auto">{{ $progress->daysRemaining ?? '' }} days left</span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <form action="{{ route('curriculum.submit', $progress) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-on:submit="window.AudioManager?.play('success')">
                            @csrf
                            @php $evidenceTypes = $activity->evidence_requirements ?? []; @endphp

                            <!-- Evidence Sections -->
                            {{-- URL --}}
                            @if(empty($evidenceTypes) || in_array('url', $evidenceTypes) || in_array('github_repo', $evidenceTypes) || in_array('github_commit', $evidenceTypes))
                            <div class="card p-5 bg-dark-800/50">
                                <h3 class="text-white font-semibold mb-3">🔗 Link / URL</h3>
                                <input type="url" name="evidence_url" value="{{ old('evidence_url') }}"
                                       placeholder="https://github.com/your-repo or project URL"
                                       class="w-full bg-dark-900 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <p class="text-dark-500 text-xs mt-2">GitHub repo, deployed project, or relevant link</p>
                            </div>
                            @endif

                            {{-- Text Evidence --}}
                            @if(empty($evidenceTypes) || in_array('text', $evidenceTypes))
                            <div class="card p-5 bg-dark-800/50">
                                <h3 class="text-white font-semibold mb-3">📝 Written Evidence</h3>
                                <textarea name="evidence_text" rows="5" placeholder="Describe your work, approach, and what you learned..."
                                          class="w-full bg-dark-900 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('evidence_text') }}</textarea>
                                <p class="text-dark-500 text-xs mt-2">Explain your approach, challenges faced, and key learnings (max 5000 chars)</p>
                            </div>
                            @endif

                            {{-- File Upload --}}
                            @if(empty($evidenceTypes) || in_array('file_upload', $evidenceTypes) || in_array('screenshot', $evidenceTypes) || in_array('video', $evidenceTypes))
                            <div class="card p-5 bg-dark-800/50">
                                <h3 class="text-white font-semibold mb-3">📎 File Uploads</h3>
                                <div class="border-2 border-dashed border-dark-600 rounded-lg p-6 text-center hover:border-dark-500 transition bg-dark-900">
                                    <input type="file" name="evidence_files[]" multiple id="evidence_files" class="hidden"
                                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.zip,.mp4,.mov">
                                    <label for="evidence_files" class="cursor-pointer">
                                        <div class="text-3xl mb-2">📁</div>
                                        <p class="text-white text-sm font-medium">Click to upload files</p>
                                        <p class="text-dark-500 text-xs mt-1">JPG, PNG, PDF, ZIP, MP4. Max 10MB each (up to 5)</p>
                                    </label>
                                </div>
                                <div id="file-list" class="mt-3 space-y-1"></div>
                            </div>
                            @endif

                            {{-- Reflection --}}
                            <div class="card p-5 bg-dark-800/50">
                                <h3 class="text-white font-semibold mb-3">💭 Reflection</h3>
                                <textarea name="reflection" rows="3"
                                          placeholder="What did you learn? What would you do differently?"
                                          class="w-full bg-dark-900 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('reflection') }}</textarea>
                            </div>

                            <!-- Submit -->
                            <div class="sticky bottom-0 bg-dark-900 pt-4 pb-2 border-t border-dark-700 mt-6 flex items-center justify-end gap-3 z-20">
                                <button type="button" @click="submitOpen = false" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">🚀 Submit for Review</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('evidence_files')?.addEventListener('change', function(e) {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    Array.from(e.target.files).forEach(file => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 text-sm text-dark-300 bg-dark-800 rounded-lg px-3 py-2 mt-2';
        div.innerHTML = `<span>📄</span> <span class="truncate max-w-[200px]">${file.name}</span> <span class="text-dark-500 ml-auto">${(file.size / 1024 / 1024).toFixed(1)} MB</span>`;
        list.appendChild(div);
    });
});

// Confetti micro-animation for dopamine hits
@if(session('success') && ($canSubmit || $isCompleted))
    document.addEventListener('DOMContentLoaded', function() {
        var duration = 3 * 1000;
        var end = Date.now() + duration;

        (function frame() {
            confetti({
                particleCount: 5,
                angle: 60,
                spread: 55,
                origin: { x: 0 },
                colors: ['#3B82F6', '#8B5CF6', '#10B981']
            });
            confetti({
                particleCount: 5,
                angle: 120,
                spread: 55,
                origin: { x: 1 },
                colors: ['#3B82F6', '#8B5CF6', '#10B981']
            });

            if (Date.now() < end) {
                requestAnimationFrame(frame);
            }
        }());
    });
@endif
</script>
@endif
    {{-- ============================================= --}}
    {{-- TRACK-WIDE DISCUSSION FORUM --}}
    {{-- ============================================= --}}
    <div class="card p-6 mt-8 border-t-[3px] border-primary-500">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-white font-bold text-xl flex items-center gap-2">
                    <span class="text-primary-400">💬</span> Track Discussion
                </h3>
                <p class="text-dark-400 text-sm mt-1">Ask questions, share tips, and help your peers on this activity.</p>
            </div>
            <span class="px-3 py-1 bg-dark-800 border border-dark-700 rounded-full text-xs font-medium text-dark-300">
                {{ $activity->comments->count() }} Comments
            </span>
        </div>

        {{-- Comment Form --}}
        <form action="{{ route('curriculum.activities.comments.store', $activity) }}" method="POST" class="mb-8 relative">
            @csrf
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 font-bold flex-shrink-0 border border-primary-500/30">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <textarea name="content" rows="3" required placeholder="Start a discussion or ask a question..." class="w-full bg-dark-900 border border-dark-700 rounded-xl px-4 py-3 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition shadow-inner resize-none"></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="btn-primary py-1.5 px-4 text-sm shadow-[0_0_15px_rgba(59,130,246,0.2)] hover:shadow-[0_0_20px_rgba(59,130,246,0.4)]">
                            Post Comment
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Comments List --}}
        <div class="space-y-6">
            @forelse($activity->comments as $comment)
            <div class="flex items-start gap-3 group">
                <div class="w-10 h-10 rounded-full bg-dark-700 flex items-center justify-center text-dark-300 font-bold flex-shrink-0">
                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="bg-dark-800/80 border border-dark-700 rounded-2xl rounded-tl-sm p-4 hover:border-dark-600 transition shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-white font-medium text-sm">{{ $comment->user->name }}</span>
                                @if($comment->user->id === auth()->id())
                                    <span class="px-1.5 py-0.5 bg-dark-700 rounded text-[10px] text-dark-400 font-medium">You</span>
                                @endif
                            </div>
                            <span class="text-dark-500 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-dark-300 text-sm leading-relaxed whitespace-pre-wrap">{{ $comment->content }}</p>
                    </div>
                    
                    {{-- Actions (Reply / Delete) --}}
                    <div class="flex items-center gap-4 mt-1.5 ml-2">
                        <button onclick="document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('hidden')" class="text-xs text-dark-500 hover:text-primary-400 transition font-medium">
                            Reply
                        </button>
                        @if($comment->user_id === auth()->id())
                        <form action="{{ route('curriculum.comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-dark-500 hover:text-red-400 transition font-medium">Delete</button>
                        </form>
                        @endif
                    </div>

                    {{-- Reply Form (Hidden by default) --}}
                    <form id="reply-form-{{ $comment->id }}" action="{{ route('curriculum.activities.comments.store', $activity) }}" method="POST" class="hidden mt-3 mb-4">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <div class="flex items-start gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary-500/10 flex items-center justify-center text-primary-400 font-bold flex-shrink-0 text-xs">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 flex gap-2">
                                <input type="text" name="content" required placeholder="Write a reply..." class="w-full bg-dark-900 border border-dark-700 rounded-lg px-3 py-1.5 text-sm text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                <button type="submit" class="btn-primary py-1.5 px-3 text-xs whitespace-nowrap">Reply</button>
                            </div>
                        </div>
                    </form>

                    {{-- Replies --}}
                    @if($comment->replies->count() > 0)
                    <div class="mt-4 space-y-4 relative before:absolute before:inset-y-0 before:left-[-23px] before:w-px before:bg-dark-700">
                        @foreach($comment->replies as $reply)
                        <div class="flex items-start gap-2 relative">
                            <div class="absolute left-[-23px] top-[14px] w-[18px] border-t border-dark-700"></div>
                            <div class="w-7 h-7 rounded-full bg-dark-700 flex items-center justify-center text-dark-300 font-bold flex-shrink-0 text-xs">
                                {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-dark-900/80 border border-dark-700 rounded-2xl rounded-tl-sm p-3 hover:border-dark-600 transition">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-white font-medium text-xs">{{ $reply->user->name }}</span>
                                            @if($reply->user->id === auth()->id())
                                                <span class="px-1.5 py-0.5 bg-dark-800 rounded text-[10px] text-dark-400 font-medium">You</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-dark-500 text-[10px]">{{ $reply->created_at->diffForHumans() }}</span>
                                            @if($reply->user_id === auth()->id())
                                            <form action="{{ route('curriculum.comments.destroy', $reply) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reply?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-dark-600 hover:text-red-400 transition" title="Delete reply">×</button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-dark-300 text-xs leading-relaxed whitespace-pre-wrap">{{ $reply->content }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <div class="text-4xl mb-3">💬</div>
                <p class="text-dark-300 font-medium">No discussions yet</p>
                <p class="text-dark-500 text-sm mt-1">Be the first to start a conversation about this activity!</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

<!-- AI Mentor FAB -->
<div x-data="{ aiOpen: false, messages: [{role: 'ai', text: 'Hi! I see you are working on {{ addslashes($activity->title) }}. Need a hint without giving away the answer?'}], input: '' }" class="fixed bottom-6 right-6 z-40">
    <!-- Chat Window -->
    <div x-show="aiOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="absolute bottom-16 right-0 w-80 sm:w-96 bg-dark-800 border border-dark-600 rounded-2xl shadow-2xl overflow-hidden flex flex-col"
         style="height: 500px; display: none;">
         
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-4 py-3 flex items-center justify-between shadow-md z-10">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🤖</span>
                <div>
                    <h3 class="text-white font-bold text-sm">AI Mentor</h3>
                    <p class="text-purple-200 text-xs opacity-80">Context-aware assistant</p>
                </div>
            </div>
            <button @click="aiOpen = false" class="text-white/70 hover:text-white transition p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-dark-900/50" id="ai-chat-messages">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'ai' ? 'flex items-start gap-2' : 'flex items-end justify-end gap-2'">
                    <template x-if="msg.role === 'ai'">
                        <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center text-sm flex-shrink-0 border border-purple-500/30">🤖</div>
                    </template>
                    <div :class="msg.role === 'ai' ? 'bg-dark-800 border border-dark-700 rounded-2xl rounded-tl-sm text-dark-300' : 'bg-primary-600 text-white rounded-2xl rounded-tr-sm'" class="px-4 py-2 text-sm max-w-[85%] shadow-sm">
                        <span x-text="msg.text"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-dark-800 border-t border-dark-700">
            <form @submit.prevent="
                if(input.trim() !== '') {
                    messages.push({role: 'user', text: input});
                    let userMsg = input;
                    input = '';
                    setTimeout(() => {
                        let chat = document.getElementById('ai-chat-messages');
                        chat.scrollTop = chat.scrollHeight;
                        setTimeout(() => {
                            messages.push({role: 'ai', text: 'I am a UI mockup for now! Integrate me with your AI service in Phase 2.'});
                            chat.scrollTop = chat.scrollHeight;
                        }, 1000);
                    }, 50);
                }
            " class="flex items-center gap-2">
                <input type="text" x-model="input" placeholder="Ask for a hint..." class="flex-1 bg-dark-900 border border-dark-600 rounded-full px-4 py-2 text-sm text-white placeholder-dark-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                <button type="submit" class="w-9 h-9 rounded-full bg-purple-500 hover:bg-purple-600 text-white flex items-center justify-center transition shadow-lg flex-shrink-0">
                    <svg class="w-4 h-4 translate-x-[1px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- FAB Button -->
    <button @click="aiOpen = !aiOpen" 
            class="w-14 h-14 rounded-full bg-gradient-to-r from-purple-600 to-blue-600 text-white shadow-[0_0_20px_rgba(139,92,246,0.4)] flex items-center justify-center text-2xl hover:scale-105 active:scale-95 transition-all focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:ring-offset-dark-900 relative group">
        <span x-show="!aiOpen" class="transition-opacity">🤖</span>
        <span x-show="aiOpen" class="transition-opacity" style="display:none;">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </span>
        <!-- Tooltip -->
        <span class="absolute right-full mr-4 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-dark-800 text-dark-300 text-sm rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none border border-dark-700 shadow-xl">
            Ask AI Mentor
        </span>
    </button>
</div>

</div>

@endsection
