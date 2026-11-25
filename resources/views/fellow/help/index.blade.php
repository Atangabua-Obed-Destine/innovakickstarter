@extends('layouts.app')

@section('title', 'Ask a human')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div class="card p-6">
        <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">🙋 Ask a human</p>
        <h1 class="text-2xl font-bold text-white mt-1">The I-NNOVA team is here to help</h1>
        <p class="text-dark-300 mt-1">When the AI hints are not enough, ask a person. Say what you tried and where you are stuck: the more specific, the faster the help.</p>
        @include('partials.help.ask', ['inline' => true])
    </div>

    <div class="card p-6">
        <h2 class="text-white font-semibold text-lg">My questions</h2>
        <div class="space-y-3 mt-3">
            @forelse($requests as $r)
                <div class="p-4 rounded-xl bg-dark-800/70 border border-dark-700">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-dark-400 text-xs">{{ $r->created_at->format('j M, H:i') }}{{ $r->activity ? ' · ' . $r->activity->title : '' }}</span>
                        <span class="text-xs font-semibold {{ $r->status === 'answered' ? 'text-green-400' : 'text-amber-300' }}">{{ $r->status === 'answered' ? 'Answered' : 'Waiting for the team' }}</span>
                    </div>
                    <p class="text-dark-100 mt-2 whitespace-pre-line">{{ $r->question }}</p>
                    @if($r->answer)
                        <div class="mt-3 p-3 rounded-lg bg-primary-500/10 border border-primary-500/20">
                            <p class="text-primary-300 text-xs font-semibold">{{ $r->answerer?->name ?? 'The team' }} · {{ $r->answered_at?->diffForHumans() }}</p>
                            <div class="lesson-md mt-1">{{ \App\Support\LessonMarkdown::render($r->answer) }}</div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-dark-500">No questions yet. Asking for help is a professional skill: don't wait until you are frustrated.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
