@extends('layouts.app')

@section('title', 'Your Mentor')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-white">Ask {{ $persona['name'] }}</h1>
        <p class="text-dark-400 mt-1">{{ $persona['title'] }}. Your AI mentor for the {{ $track->name }} track: ask about concepts, error messages, tools or your career. It gives hints and good questions, not answers to graded work. Tip: open the chat from a lesson or Practice Lab challenge so it knows what you're working on.</p>
    </div>
    @include('partials.mentor.chat', ['track' => $track, 'contextType' => 'general', 'contextId' => null, 'inline' => true])
    <p class="text-dark-500 text-xs">The mentor is an AI and can make mistakes: check important facts in the lesson resources. Conversations are saved so it remembers the thread, and may be reviewed by the I-NNOVA team to improve it.</p>
</div>
@endsection
