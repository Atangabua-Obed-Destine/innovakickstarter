@extends('layouts.app')

@section('title', 'My Presentations')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">My Presentations</h1>
            <p class="text-dark-400 mt-1">Project demos, workshops and talks you gave. The I-NNOVA team reviews each one, and approved presentations add to your Career Capital.</p>
        </div>
        <a href="{{ route('presentations.create') }}" class="btn btn-primary whitespace-nowrap">+ Submit a presentation</a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>
    @endif

    @forelse($presentations as $presentation)
        <a href="{{ route('presentations.show', $presentation) }}" class="card p-5 block hover:bg-dark-800/50 transition">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-white font-semibold">{{ $presentation->title }}</p>
                    <p class="text-dark-400 text-sm mt-1">
                        {{ $presentation->kindLabel() }} · {{ $presentation->event_name }} · {{ $presentation->presented_at?->format('M j, Y') }}
                        · {{ $presentation->track?->name }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if($presentation->status === \App\Models\Presentation::STATUS_APPROVED && $presentation->category_boosts)
                        <span class="text-green-400 text-sm">+{{ rtrim(rtrim(number_format(array_sum($presentation->category_boosts), 1), '0'), '.') }} across {{ count($presentation->category_boosts) }} {{ Str::plural('category', count($presentation->category_boosts)) }}</span>
                    @endif
                    <span class="badge {{ $presentation->statusBadgeClass() }}">{{ $presentation->statusLabel() }}</span>
                </div>
            </div>
        </a>
    @empty
        <div class="card p-10 text-center">
            <p class="text-white font-semibold">No presentations yet</p>
            <p class="text-dark-400 text-sm mt-2">Gave a project demo, workshop or talk? Submit it with your slides, recording or repository so the team can review it.</p>
            <a href="{{ route('presentations.create') }}" class="btn btn-primary mt-5 inline-flex">Submit your first presentation</a>
        </div>
    @endforelse
</div>
@endsection
