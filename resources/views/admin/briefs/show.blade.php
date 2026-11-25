@extends('layouts.app')

@section('title', $brief->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-5">
    <a href="{{ route('admin.briefs.index') }}" class="text-dark-400 hover:text-white">← Employer briefs</a>
    @if(session('success'))<div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>@endif
    <div class="card p-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <p class="text-dark-400 text-sm">{{ $brief->company_name }} · {{ $brief->track?->name ?? 'All tracks' }} · due {{ $brief->deadline_on->format('j M Y') }} · {{ \App\Models\EmployerBrief::STATUSES[$brief->status] }}@if($brief->recruiter) · shortlist shared with {{ $brief->recruiter->name }}@endif</p>
                <h1 class="text-2xl font-bold text-white mt-1">{{ $brief->title }}</h1>
            </div>
            <a href="{{ route('admin.briefs.edit', $brief) }}" class="btn btn-secondary whitespace-nowrap">Edit</a>
        </div>
        <div class="lesson-md mt-3">{{ \App\Support\LessonMarkdown::render($brief->summary) }}</div>
    </div>

    <h2 class="text-white font-semibold text-lg">Submissions ({{ $brief->submissions->count() }})</h2>
    @forelse($brief->submissions->sortByDesc('updated_at') as $s)
        <div class="card p-5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <p class="text-white font-medium">{{ $s->fellow->name }} <span class="badge {{ $s->badgeClass() }} ml-2">{{ \App\Models\BriefSubmission::STATUSES[$s->status] }}</span></p>
                    <a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" class="text-primary-400 text-sm">{{ Str::limit($s->url, 80) }}</a>
                </div>
                <span class="text-dark-500 text-xs">{{ $s->updated_at->diffForHumans() }}</span>
            </div>
            <p class="text-dark-200 text-sm whitespace-pre-line">{{ $s->notes }}</p>
            <form method="POST" action="{{ route('admin.briefs.review', [$brief, $s]) }}" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input name="feedback" maxlength="2000" value="{{ $s->feedback }}" placeholder="Feedback for the fellow (optional)"
                       class="flex-1 bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500">
                <button name="status" value="winner" class="btn btn-primary btn-sm">🏆 Winner</button>
                <button name="status" value="shortlisted" class="btn btn-secondary btn-sm">Shortlist</button>
                <button name="status" value="not_selected" class="btn btn-secondary btn-sm">Not selected</button>
            </form>
        </div>
    @empty
        <div class="card p-8 text-center text-dark-400">No submissions yet.</div>
    @endforelse
</div>
@endsection
