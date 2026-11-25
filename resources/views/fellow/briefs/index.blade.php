@extends('layouts.app')

@section('title', 'Employer Briefs')

@section('content')
@include('partials.track.styles')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">💼 Employer briefs</h1>
        <p class="text-dark-400 mt-1">Short, real challenges from employers. Submit your solution before the deadline: the I-NNOVA team shortlists the best ones, and shortlisted work goes on your portfolio and to the employer.</p>
    </div>

    @forelse($briefs as $brief)
        @php $mineHere = $mine->get($brief->id); @endphp
        <a href="{{ route('briefs.show', $brief) }}" class="card p-5 block hover:bg-dark-800/50 transition">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-dark-400 text-xs uppercase tracking-wider">{{ $brief->company_name }} · {{ $brief->track?->name ?? 'All tracks' }}</p>
                    <p class="text-white font-semibold text-lg mt-1">{{ $brief->title }}</p>
                    <p class="text-dark-300 text-sm mt-1">{{ \App\Support\LessonMarkdown::plain($brief->summary, 180) }}</p>
                </div>
                <div class="flex flex-col items-start sm:items-end gap-2 flex-shrink-0">
                    @if($mineHere)
                        <span class="badge {{ $mineHere->badgeClass() }}">{{ \App\Models\BriefSubmission::STATUSES[$mineHere->status] }}</span>
                    @endif
                    @if($brief->acceptsSubmissions())
                        <span class="text-sm text-green-400">Open · due {{ $brief->deadline_on->format('j M') }}</span>
                    @else
                        <span class="text-sm text-dark-500">Closed</span>
                    @endif
                    @if($brief->reward)<span class="tx-chip">🏆 {{ $brief->reward }}</span>@endif
                </div>
            </div>
        </a>
    @empty
        <div class="card p-10 text-center">
            <p class="text-white font-semibold">No briefs right now</p>
            <p class="text-dark-400 text-sm mt-2">When an employer shares a challenge for your track, it will appear here and you'll get a notification.</p>
        </div>
    @endforelse
</div>
@endsection
