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

<div class="max-w-4xl mx-auto space-y-6">
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
                </div>
            </div>
        </div>
    </div>

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
                    <span class="text-green-400 font-medium">+{{ $progress->points_awarded }} points earned</span>
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
            </div>

            {{-- SUBMITTED / WAITING FOR REVIEW --}}
            @elseif($isWaiting)
            <div class="text-center space-y-3">
                <div class="text-5xl">⏳</div>
                <h3 class="text-xl font-bold text-purple-400">
                    {{ $statusValue === 'peer_review' ? 'Awaiting Peer Review' : ($statusValue === 'under_review' ? 'Under Admin Review' : 'Submitted for Review') }}
                </h3>
                <p class="text-dark-400 text-sm">Your submission is being reviewed. You'll be notified once feedback is available.</p>
                @if($progress->submitted_at)
                <p class="text-dark-500 text-xs">Submitted {{ $progress->submitted_at->diffForHumans() }}</p>
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
                <a href="{{ route('curriculum.submit.form', $progress) }}" class="btn-primary text-lg px-8 py-3 flex-shrink-0">
                    {{ $statusValue === 'rejected' ? '📝 Resubmit' : '📤 Submit Work' }}
                </a>
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
                <form action="{{ route('curriculum.activity.start', $activity) }}" method="POST">
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
    {{-- RESOURCES --}}
    {{-- ============================================= --}}
    @if($activity->resources && count($activity->resources) > 0)
    <div class="card p-6">
        <h3 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
            <span class="text-cyan-400">📚</span> Helpful Resources
        </h3>
        <p class="text-dark-400 text-sm mb-4">These resources will help you complete this activity:</p>
        <div class="space-y-2">
            @foreach($activity->resources as $resource)
            <a href="{{ $resource }}" target="_blank" rel="noopener"
               class="flex items-center gap-3 text-primary-400 hover:text-primary-300 text-sm bg-dark-800 rounded-lg px-4 py-3 border border-dark-700 hover:border-primary-500/30 transition group">
                <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span class="truncate">{{ $resource }}</span>
            </a>
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
                <a href="{{ route('curriculum.submit.form', $progress) }}" class="btn-primary">
                    {{ $statusValue === 'rejected' ? '📝 Resubmit Work' : '📤 Submit Work' }}
                </a>
            @elseif($canStart)
                <form action="{{ route('curriculum.activity.start', $activity) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">
                        {{ $isInterviewActivity ? '🎤 Start Interview' : '🚀 Start Activity' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
