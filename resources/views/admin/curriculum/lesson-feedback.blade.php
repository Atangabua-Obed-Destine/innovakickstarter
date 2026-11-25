@extends('layouts.app')

@section('title', 'Lesson Feedback')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Lesson Feedback</h1>
            <p class="text-dark-400 mt-1">What fellows answered to "Was this lesson clear?". Lessons with the most "not really" answers come first.</p>
        </div>
        <form method="GET" action="{{ route('admin.curriculum.lesson-feedback') }}">
            <select name="track_id" onchange="this.form.submit()"
                    class="bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <option value="">All tracks</option>
                @foreach($tracks as $t)
                    <option value="{{ $t->id }}" @selected($trackId === $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if($rows->isEmpty())
        <div class="card p-10 text-center text-dark-400">No feedback yet.</div>
    @else
        <div class="space-y-3">
            @foreach($rows as $row)
                <div class="card p-5" x-data="{ open: false }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-white font-medium">{{ $row->title }}</p>
                            <p class="text-dark-500 text-xs mt-1">{{ $row->track }} · M{{ $row->milestone_order }} {{ $row->milestone }}</p>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <span class="text-dark-300">{{ $row->responses }} {{ Str::plural('answer', $row->responses) }}</span>
                            <span class="{{ $row->unclear_pct >= 30 ? 'text-red-400' : ($row->unclear > 0 ? 'text-amber-400' : 'text-green-400') }} font-semibold">
                                {{ $row->unclear }} unclear ({{ $row->unclear_pct }}%)
                            </span>
                            @if(isset($comments[$row->id]))
                                <button type="button" @click="open = !open" class="text-primary-400 hover:text-primary-300" x-text="open ? 'Hide comments' : '{{ $comments[$row->id]->count() }} {{ Str::plural('comment', $comments[$row->id]->count()) }}'"></button>
                            @endif
                        </div>
                    </div>
                    @if(isset($comments[$row->id]))
                        <div x-show="open" x-cloak class="mt-4 space-y-2">
                            @foreach($comments[$row->id] as $feedback)
                                <div class="p-3 bg-dark-800 rounded-lg text-sm">
                                    <p class="text-dark-200 whitespace-pre-line">{{ $feedback->comment }}</p>
                                    <p class="text-dark-500 text-xs mt-1">{{ $feedback->fellow->name ?? 'Fellow' }} · {{ $feedback->is_clear ? 'found it clear' : 'found it unclear' }} · {{ $feedback->updated_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
