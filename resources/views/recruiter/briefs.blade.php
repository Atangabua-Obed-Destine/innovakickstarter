@extends('layouts.app')

@section('title', 'Your Briefs')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Your briefs</h1>
        <p class="text-dark-400 mt-1">Challenges you shared with I-NNOVA fellows, and the fellows the I-NNOVA team shortlisted.</p>
    </div>
    @forelse($briefs as $brief)
        <div class="card p-6 space-y-3">
            <div>
                <p class="text-dark-400 text-sm">{{ $brief->track?->name ?? 'All tracks' }} · due {{ $brief->deadline_on->format('j M Y') }} · {{ \App\Models\EmployerBrief::STATUSES[$brief->status] }}</p>
                <h2 class="text-white font-semibold text-lg">{{ $brief->title }}</h2>
            </div>
            @forelse($brief->submissions->sortBy(fn ($s) => $s->status === 'winner' ? 0 : 1) as $s)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 bg-dark-800 rounded-lg">
                    <div>
                        <p class="text-white">{{ $s->status === 'winner' ? '🏆' : '⭐' }} {{ $s->fellow->name }}</p>
                        <p class="text-dark-400 text-sm">{{ Str::limit($s->notes, 140) }}</p>
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ $s->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">Their solution</a>
                        <a href="{{ route('recruiter.talent.show', $s->fellow) }}" class="btn btn-primary btn-sm">Profile</a>
                    </div>
                </div>
            @empty
                <p class="text-dark-400 text-sm">The shortlist appears here once the I-NNOVA team has reviewed the submissions.</p>
            @endforelse
        </div>
    @empty
        <div class="card p-10 text-center text-dark-400">No briefs yet. Tell the I-NNOVA team about a real problem you'd like fellows to solve.</div>
    @endforelse
</div>
@endsection
