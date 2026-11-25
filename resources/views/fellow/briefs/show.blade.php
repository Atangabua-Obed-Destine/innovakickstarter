@extends('layouts.app')

@section('title', $brief->title)

@section('content')
@include('partials.track.styles')
@php $input = 'w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500'; @endphp
<div class="max-w-3xl mx-auto space-y-5">
    <a href="{{ route('briefs.index') }}" class="text-dark-400 hover:text-white">← Employer briefs</a>

    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))<div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>@endif
    @endforeach
    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="tx-hero">
        <div class="relative" style="z-index: 1;">
            <p class="text-dark-300 text-sm">{{ $brief->company_name }} · {{ $brief->track?->name ?? 'All tracks' }}</p>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $brief->title }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <span class="tx-chip">{{ $brief->acceptsSubmissions() ? 'Open until ' . $brief->deadline_on->format('j M Y') : 'Closed' }}</span>
                @if($brief->reward)<span class="tx-chip">🏆 {{ $brief->reward }}</span>@endif
                <span class="text-dark-400 text-sm">{{ $count }} {{ Str::plural('submission', $count) }} so far</span>
            </div>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div><h3 class="text-white font-semibold mb-1">The challenge</h3><div class="lesson-md">{{ \App\Support\LessonMarkdown::render($brief->summary) }}</div></div>
        <div><h3 class="text-white font-semibold mb-1">What to submit</h3><div class="lesson-md">{{ \App\Support\LessonMarkdown::render($brief->deliverable) }}</div></div>
        @if($brief->resources_url)
            <a href="{{ $brief->resources_url }}" target="_blank" rel="noopener noreferrer" class="text-primary-400 hover:text-primary-300 text-sm">Data and resources →</a>
        @endif
    </div>

    @if($submission && $submission->status !== 'submitted')
        <div class="card p-6">
            <span class="badge {{ $submission->badgeClass() }}">{{ \App\Models\BriefSubmission::STATUSES[$submission->status] }}</span>
            @if($submission->feedback)<p class="text-dark-200 mt-3 whitespace-pre-line">{{ $submission->feedback }}</p>@endif
            <p class="text-dark-500 text-sm mt-2">Your submission: <a href="{{ $submission->url }}" target="_blank" rel="noopener noreferrer" class="text-primary-400">{{ Str::limit($submission->url, 60) }}</a></p>
        </div>
    @elseif($brief->acceptsSubmissions())
        <form method="POST" action="{{ route('briefs.submit', $brief) }}" class="card p-6 space-y-4">
            @csrf
            <h3 class="text-white font-semibold">{{ $submission ? 'Update your submission' : 'Submit your solution' }}</h3>
            <div>
                <label class="block text-dark-300 text-sm mb-1">Link to your work (GitHub, Colab, Looker Studio, Google Doc...)</label>
                <input type="url" name="url" required maxlength="500" value="{{ old('url', $submission?->url) }}" class="{{ $input }}" placeholder="https://github.com/...">
            </div>
            <div>
                <label class="block text-dark-300 text-sm mb-1">Your approach and main finding</label>
                <textarea name="notes" rows="5" required minlength="80" maxlength="3000" class="{{ $input }}" placeholder="What you did, what you found, what you recommend.">{{ old('notes', $submission?->notes) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">{{ $submission ? 'Save changes' : 'Submit' }}</button>
            <p class="text-dark-500 text-xs">You can update your submission until the deadline. Make sure the link is viewable by anyone with it.</p>
        </form>
    @elseif($submission)
        <div class="card p-6 text-dark-300">Your submission is in. The team is reviewing submissions now.</div>
    @else
        <div class="card p-6 text-dark-400">This brief is closed.</div>
    @endif
</div>
@php $chatTrack = $brief->track ?? auth()->user()->primaryTrack?->track; @endphp
@if($chatTrack)
    @include('partials.mentor.chat', ['track' => $chatTrack, 'contextType' => 'general', 'contextId' => null])
@endif
@endsection
