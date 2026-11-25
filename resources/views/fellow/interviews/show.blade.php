@extends('layouts.app')

@section('title', 'Mock Interview Details')

@section('content')
@php
    $status = $interview->status?->value ?? 'scheduled';
    $statusClass = match($status) {
        'scheduled', 'pending' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        'completed' => 'bg-green-600/20 text-green-400 border-green-500/30',
        'cancelled', 'no_show' => 'bg-red-600/20 text-red-400 border-red-500/30',
        'in_progress' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
        default => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
    };
    $isAi = $interview->mode === \App\Enums\InterviewMode::AI;
    $score = $interview->overall_score ?? $interview->score;
    // Latest answer per question (a fellow may retry an answer).
    $answers = collect($interview->responses ?? [])->keyBy(fn ($r) => (int) ($r['question_index'] ?? 0))->sortKeys();
@endphp
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ $interview->curriculum_activity_id ? route('curriculum.activity.show', $interview->curriculum_activity_id) : route('interviews.index') }}"
       class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        {{ $interview->curriculum_activity_id ? 'Back to Activity' : 'Back to Interviews' }}
    </a>

    @foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
        @if (session($key))
            <div class="p-4 rounded-lg border bg-{{ $color }}-600/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="card p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <span class="badge {{ $statusClass }}">{{ $interview->status?->label() ?? 'Scheduled' }}</span>
                    <span class="badge badge-primary">{{ $interview->type?->label() ?? 'Interview' }}</span>
                    <span class="badge bg-dark-600/20 text-dark-300 border-dark-500/30">{{ $interview->mode?->label() }}</span>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">{{ $interview->title ?? 'Mock Interview' }}</h1>
                @if ($interview->description)
                    <p class="text-dark-300">{{ $interview->description }}</p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if ($isAi && $status === 'in_progress')
                    <a href="{{ route('interviews.ai-room', $interview) }}" class="btn btn-primary">Continue Interview</a>
                @elseif (!$isAi && in_array($status, ['scheduled', 'pending']) && $interview->meeting_link)
                    <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener" class="btn btn-primary">Join Meeting</a>
                @endif
                @if ($canCancel)
                    <form method="POST" action="{{ route('interviews.cancel', $interview) }}"
                          onsubmit="return confirm('Cancel this interview? You can start a new one afterwards.');">
                        @csrf
                        <button type="submit" class="btn btn-secondary text-red-400 border-red-500/30 hover:bg-red-600/20">Cancel Interview</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Details</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                    <span class="text-dark-400">{{ $interview->started_at ? 'Started' : 'Scheduled' }}</span>
                    <span class="text-white font-medium">{{ ($interview->started_at ?? $interview->scheduled_at)?->format('M j, Y g:i A') ?? 'Not scheduled' }}</span>
                </div>
                @if ($interview->completed_at)
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <span class="text-dark-400">Completed</span>
                        <span class="text-white font-medium">{{ $interview->completed_at->format('M j, Y g:i A') }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                    <span class="text-dark-400">Duration</span>
                    <span class="text-white font-medium">{{ $interview->duration_minutes ?? 45 }} minutes</span>
                </div>
                @if ($interview->track)
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <span class="text-dark-400">Track</span>
                        <span class="text-white font-medium">{{ $interview->track->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">{{ $isAi ? 'Result' : 'Interviewer' }}</h3>
            @if (!$isAi)
                <div class="p-4 bg-dark-800 rounded-lg">
                    <p class="text-white font-semibold">{{ $interview->interviewer->name ?? 'Not assigned yet' }}</p>
                </div>
            @endif
            @if ($status === 'completed')
                <div class="p-4 bg-dark-800 rounded-lg {{ $isAi ? '' : 'mt-3' }}">
                    @if ($score !== null)
                        <p class="text-dark-400 text-sm">Overall score</p>
                        <p class="text-3xl font-bold text-green-400">{{ rtrim(rtrim(number_format((float) $score, 1), '0'), '.') }}%</p>
                    @else
                        <p class="text-amber-400 font-semibold">Not scored</p>
                        <p class="text-dark-400 text-sm mt-1">The AI could not score this interview, so it doesn't count toward your Career Capital. You can try again.</p>
                    @endif
                </div>
            @elseif ($isAi)
                <p class="text-dark-400">{{ $status === 'in_progress' ? 'This interview is still in progress.' : 'No result.' }}</p>
            @endif
        </div>
    </div>

    @if ($interview->feedback)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">Feedback</h3>
            <p class="text-dark-200 whitespace-pre-line">{{ $interview->feedback }}</p>
        </div>
    @endif

    @if ($answers->isNotEmpty())
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Your Answers</h3>
            <div class="space-y-4">
                @foreach ($answers as $index => $answer)
                    @php $ev = $answer['evaluation'] ?? []; @endphp
                    <div class="p-4 bg-dark-800 rounded-lg">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <p class="text-white font-medium">Q{{ $index + 1 }}. {{ $answer['question'] ?? '' }}</p>
                            <span class="badge {{ is_numeric($ev['score'] ?? null) ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-amber-600/20 text-amber-400 border-amber-500/30' }}">
                                {{ is_numeric($ev['score'] ?? null) ? $ev['score'] . '%' : 'Not scored' }}
                            </span>
                        </div>
                        <p class="text-dark-300 text-sm whitespace-pre-line">{{ $answer['response'] ?? '' }}</p>
                        @if (!empty($ev['feedback']))
                            <p class="text-dark-400 text-sm mt-2"><span class="text-dark-300 font-medium">Feedback:</span> {{ $ev['feedback'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
