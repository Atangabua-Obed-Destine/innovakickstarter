@extends('layouts.app')

@section('title', 'Mock Interviews')

@section('content')
@php
    $label = function ($s) {
        if ($s->status === \App\Enums\InterviewStatus::CANCELLED && $s->cancellation_reason === 'abandoned') return 'Abandoned';
        if ($s->status === \App\Enums\InterviewStatus::COMPLETED && $s->score === null) return $s->scoring_status === 'queued' ? 'Scoring…' : 'Not scored';
        return $s->status?->label() ?? 'Pending';
    };
@endphp
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Mock Interviews</h1>
        <p class="text-dark-400 mt-1">Real interview loops at your level: the interviewer writes, you answer out loud, code and SQL in an editor.</p>
    </div>

    @foreach (['success' => 'green', 'error' => 'red', 'info' => 'blue'] as $key => $color)
        @if (session($key))
            <div class="p-4 rounded-lg border bg-{{ $color }}-600/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="grid sm:grid-cols-3 gap-4">
        <div class="card p-5"><p class="text-dark-400 text-sm">Scored interviews</p><p class="text-2xl font-bold text-white">{{ $stats['completed'] }}</p></div>
        <div class="card p-5"><p class="text-dark-400 text-sm">Average score</p><p class="text-2xl font-bold text-white">{{ $stats['average'] !== null ? $stats['average'] . '/100' : '—' }}</p></div>
        <div class="card p-5"><p class="text-dark-400 text-sm">Practice interviews done</p><p class="text-2xl font-bold text-white">{{ $stats['practice'] }}</p></div>
    </div>

    <div class="card p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-white">Practice interview</h2>
                <p class="text-dark-400 text-sm mt-1">
                    @if($track)
                        {{ $track->name }}{{ $practiceMilestone ? ' · ' . $practiceMilestone->title : '' }}. Same room and scorecard as a chapter interview, but it never counts toward Career Capital or your curriculum.
                    @else
                        Choose your track first: practice follows your current chapter.
                    @endif
                </p>
                <p class="text-dark-500 text-xs mt-1">{{ $practiceLeft }} of {{ \App\Services\Interview\InterviewEngine::PRACTICE_PER_DAY }} practice interviews left today. You need a microphone; full screen is required.</p>
            </div>
            @if($track && $practiceLeft > 0)
                <form method="POST" action="{{ route('interviews.practice') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">🎤 Start a practice interview</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-3">How chapter interviews work</h2>
        <ul class="space-y-1 text-dark-300 text-sm">
            <li>• Each chapter ends with a mock interview activity. Start it from the activity page.</li>
            <li>• One question at a time, each timed. The interviewer may ask follow-up questions, and about your own project.</li>
            <li>• Explain out loud: your answer is transcribed and you can fix transcription mistakes before sending.</li>
            <li>• Full screen, no camera. Leaving full screen, switching tabs and pasting are logged for your reviewer.</li>
            <li>• Passed? A reviewer checks the interview. Not passed? Retake after 24 hours with new questions.</li>
        </ul>
    </div>

    @if($active->isNotEmpty())
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-3">Open interviews</h2>
        <div class="space-y-3">
            @foreach($active as $s)
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-dark-800 rounded-lg">
                    <div>
                        <p class="text-white font-medium">{{ $s->title ?? 'Mock interview' }} @if($s->is_practice)<span class="text-amber-400 text-xs">(practice)</span>@endif</p>
                        <p class="text-dark-400 text-xs">
                            @if($s->mode === \App\Enums\InterviewMode::AI)
                                AI interviewer · {{ empty($s->plan['begun_at']) ? 'not started yet' : 'in progress' }}
                            @elseif($s->status === \App\Enums\InterviewStatus::SCHEDULED && $s->scheduled_at)
                                With {{ $s->interviewer?->name ?? 'a mentor' }} · {{ $s->scheduled_at->format('D j M, g:i A') }}
                            @else
                                With a mentor · waiting for the team to schedule it
                            @endif
                        </p>
                    </div>
                    @if($s->mode === \App\Enums\InterviewMode::AI && $s->status === \App\Enums\InterviewStatus::IN_PROGRESS && $s->engine_version)
                        <a href="{{ route('interviews.room', $s) }}" class="btn btn-primary">Open</a>
                    @else
                        <a href="{{ route('interviews.show', $s) }}" class="btn btn-secondary">Details</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-3">Past interviews</h2>
        @forelse($past as $s)
            <a href="{{ route('interviews.show', $s) }}" class="flex flex-wrap items-center justify-between gap-3 p-4 bg-dark-800 rounded-lg mb-2 hover:bg-dark-700 transition">
                <div>
                    <p class="text-white font-medium">{{ $s->title ?? 'Mock interview' }} @if($s->is_practice)<span class="text-amber-400 text-xs">(practice)</span>@endif</p>
                    <p class="text-dark-400 text-xs">{{ ($s->completed_at ?? $s->created_at)?->format('D j M Y') }} · {{ $label($s) }}</p>
                </div>
                <div class="text-right">
                    @if($s->score !== null)
                        <p class="text-white font-bold">{{ round((float) $s->score) }}/100</p>
                        @if($s->hire_signal)<p class="text-dark-400 text-xs">{{ \App\Services\Interview\BlueprintService::signalLabel($s->hire_signal) }}</p>@endif
                    @endif
                </div>
            </a>
        @empty
            <p class="text-dark-400 text-sm">No interviews yet.</p>
        @endforelse
    </div>
</div>
@endsection
