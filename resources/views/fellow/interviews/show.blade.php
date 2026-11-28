@extends('layouts.app')

@section('title', 'Mock Interview')

@section('content')
@php
    $status = $interview->status?->value ?? 'pending';
    $statusClass = match($status) {
        'scheduled', 'pending' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        'completed' => 'bg-green-600/20 text-green-400 border-green-500/30',
        'cancelled', 'no_show' => 'bg-red-600/20 text-red-400 border-red-500/30',
        'in_progress' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
        default => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
    };
    $isAi = $interview->mode === \App\Enums\InterviewMode::AI;
    $statusLabel = $status === 'cancelled' && $interview->cancellation_reason === 'abandoned' ? 'Abandoned (does not count)' : ($interview->status?->label() ?? 'Pending');
    // Older sessions (before the interview engine): one answer per question.
    $legacyAnswers = collect($interview->responses ?? [])->filter(fn ($r) => isset($r['question_index']))->keyBy(fn ($r) => (int) $r['question_index'])->sortKeys();
@endphp
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ $interview->curriculum_activity_id ? route('curriculum.activity.show', $interview->curriculum_activity_id) : route('interviews.index') }}"
       class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        ← {{ $interview->curriculum_activity_id ? 'Back to the activity' : 'Back to Interviews' }}
    </a>

    @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $key => $color)
        @if (session($key))
            <div class="p-4 rounded-lg border bg-{{ $color }}-600/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="card p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    <span class="badge bg-dark-600/20 text-dark-300 border-dark-500/30">{{ $isAi ? 'AI interviewer' : 'Mentor interview' }}</span>
                    @if($interview->is_practice)<span class="badge bg-amber-600/20 text-amber-400 border-amber-500/30">Practice: does not count</span>@endif
                </div>
                <h1 class="text-2xl font-bold text-white mb-1">{{ $interview->title ?? 'Mock Interview' }}</h1>
                <p class="text-dark-400 text-sm">
                    {{ $interview->track?->name }}
                    @if($interview->completed_at) · {{ $interview->completed_at->format('D j M Y, g:i A') }} @endif
                    @if($interview->duration_minutes) · {{ $interview->duration_minutes }} min @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($isAi && $status === 'in_progress' && $isEngine)
                    <a href="{{ route('interviews.room', $interview) }}" class="btn btn-primary">{{ empty($interview->plan['begun_at']) ? 'Open the briefing' : 'Continue the interview' }}</a>
                @elseif($isAi && in_array($status, ['pending', 'scheduled']))
                    <form method="POST" action="{{ route('interviews.start', $interview) }}">@csrf<button type="submit" class="btn btn-primary">Start</button></form>
                @elseif(!$isAi && $status === 'scheduled' && $interview->meeting_link)
                    <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener" class="btn btn-primary">Join the meeting</a>
                @endif
                @if($canCancel)
                    <form method="POST" action="{{ route('interviews.cancel', $interview) }}" onsubmit="return confirm('Cancel this interview? It will not count.');">
                        @csrf
                        <button type="submit" class="btn btn-secondary text-red-400 border-red-500/30 hover:bg-red-600/20">Cancel</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if(!$isAi && in_array($status, ['pending', 'scheduled']))
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-2">Your interview with a mentor</h3>
            @if($status === 'scheduled' && $interview->scheduled_at)
                <p class="text-dark-200">📅 {{ $interview->scheduled_at->format('l j F Y, g:i A') }} with <span class="text-white font-medium">{{ $interview->interviewer?->name ?? 'a mentor' }}</span>.</p>
                @if($interview->meeting_link)
                    <p class="text-dark-300 text-sm mt-2">Meeting link: <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener" class="text-primary-400 hover:text-primary-300 break-all">{{ $interview->meeting_link }}</a></p>
                @else
                    <p class="text-dark-400 text-sm mt-2">The meeting link will be shared before the interview.</p>
                @endif
                <p class="text-dark-400 text-sm mt-2">Be ready to show your best project, explain your decisions and answer follow-up questions. Your mentor scores the interview afterwards.</p>
            @else
                <p class="text-dark-300">⏳ The team will assign a mentor, a time and a meeting link. You will get a notification.</p>
            @endif
        </div>
    @endif

    @if($status === 'no_show')
        <div class="card p-6"><p class="text-dark-300">You were marked absent for this interview. It does not count. You can request a new one from the activity page.</p></div>
    @endif

    @if(!empty($standing) && $status === 'completed' && $interview->score !== null && !$standing['passed'])
        <div class="card p-6 space-y-2">
            @if($standing['retake_at'])
                <p class="text-amber-400">You can retake this interview from {{ $standing['retake_at']->format('D j M, g:i A') }}, with new questions.</p>
            @endif
            @if($standing['needs_mentor'])
                <p class="text-dark-200">You have tried this interview {{ $standing['failed_count'] }} times. <a href="{{ route('help.index') }}" class="text-primary-400 hover:text-primary-300 font-medium">Book a session with a mentor</a> before the next attempt.</p>
            @endif
        </div>
    @endif

    @if($status === 'completed' && $interview->scoring_status === 'queued')
        <div x-data="{ tries: 0 }" x-init="const t = setInterval(async () => { tries++; try { const r = await fetch('{{ route('interviews.score-status', $interview) }}', { headers: { Accept: 'application/json' } }); const d = await r.json(); if (d.ready || tries > 120) { clearInterval(t); location.reload(); } } catch (e) {} }, 4000)"></div>
    @endif

    @if($status === 'completed' && ($isEngine || !empty($interview->scorecard)))
        @include('partials.interview.report', ['interview' => $interview, 'audience' => 'fellow'])
    @elseif($status === 'completed')
        {{-- Sessions from the earlier interview system --}}
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-2">Result</h3>
            @if($interview->score !== null)
                <p class="text-3xl font-bold text-green-400">{{ rtrim(rtrim(number_format((float) $interview->score, 1), '0'), '.') }}/100</p>
            @else
                <p class="text-amber-400 font-semibold">Not scored</p>
                <p class="text-dark-400 text-sm mt-1">This interview could not be scored, so it doesn't count toward your Career Capital.</p>
            @endif
            @if($interview->feedback)
                <p class="text-dark-200 mt-3" style="white-space: pre-line;">{{ $interview->feedback }}</p>
            @endif
        </div>
        @if($legacyAnswers->isNotEmpty())
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Your answers</h3>
                <div class="space-y-4">
                    @foreach($legacyAnswers as $index => $answer)
                        @php $ev = $answer['evaluation'] ?? []; @endphp
                        <div class="p-4 bg-dark-800 rounded-lg">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <p class="text-white font-medium">Q{{ $index + 1 }}. {{ $answer['question'] ?? '' }}</p>
                                <span class="badge {{ is_numeric($ev['score'] ?? null) ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-amber-600/20 text-amber-400 border-amber-500/30' }}">
                                    {{ is_numeric($ev['score'] ?? null) ? $ev['score'] . '/100' : 'Not scored' }}
                                </span>
                            </div>
                            <p class="text-dark-300 text-sm" style="white-space: pre-line;">{{ $answer['response'] ?? '' }}</p>
                            @if(!empty($ev['feedback']))
                                <p class="text-dark-400 text-sm mt-2"><span class="text-dark-300 font-medium">Feedback:</span> {{ $ev['feedback'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
