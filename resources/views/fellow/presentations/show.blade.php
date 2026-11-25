@extends('layouts.app')

@section('title', $presentation->title)

@section('content')
@php
    $linkLabels = ['slides' => 'Slides', 'recording' => 'Recording', 'repository' => 'Repository', 'other' => 'Link'];
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <a href="{{ route('presentations.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">← My presentations</a>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400">{{ session('error') }}</div>
    @endif

    <div class="card p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="badge {{ $presentation->statusBadgeClass() }}">{{ $presentation->statusLabel() }}</span>
                    <span class="badge badge-primary">{{ $presentation->kindLabel() }}</span>
                </div>
                <h1 class="text-2xl font-bold text-white">{{ $presentation->title }}</h1>
                <p class="text-dark-400 mt-1">{{ $presentation->event_name }} · {{ $presentation->presented_at?->format('M j, Y') }} · {{ $presentation->track?->name }}</p>
            </div>
            @if($presentation->isEditableByFellow())
                <a href="{{ route('presentations.edit', $presentation) }}" class="btn btn-primary whitespace-nowrap">
                    {{ $presentation->status === \App\Models\Presentation::STATUS_CHANGES_REQUESTED ? 'Make changes' : 'Edit' }}
                </a>
            @endif
        </div>
    </div>

    @if($presentation->reviewed_at || $presentation->status === \App\Models\Presentation::STATUS_CHANGES_REQUESTED)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">Review</h3>
            @if($presentation->status === \App\Models\Presentation::STATUS_APPROVED && $presentation->category_boosts)
                <p class="text-dark-300 text-sm mb-3">Added to your Career Capital ({{ $presentation->track?->name }}):</p>
                <div class="grid sm:grid-cols-2 gap-2 mb-3">
                    @foreach($presentation->category_boosts as $category => $amount)
                        <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                            <span class="text-dark-300">{{ \App\Models\Presentation::CATEGORIES[$category] ?? $category }}</span>
                            <span class="text-green-400 font-semibold">+{{ rtrim(rtrim(number_format($amount, 1), '0'), '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            @if($presentation->review_feedback)
                <p class="text-dark-200 whitespace-pre-line">{{ $presentation->review_feedback }}</p>
            @endif
            @if($presentation->reviewer && $presentation->reviewed_at)
                <p class="text-dark-500 text-sm mt-3">Reviewed by {{ $presentation->reviewer->name }} on {{ $presentation->reviewed_at->format('M j, Y') }}</p>
            @endif
        </div>
    @endif

    <div class="card p-6 space-y-4">
        <div>
            <h3 class="text-white font-semibold mb-1">What was presented</h3>
            <p class="text-dark-200 whitespace-pre-line">{{ $presentation->summary }}</p>
        </div>
        @if($presentation->learnings)
            <div>
                <h3 class="text-white font-semibold mb-1">What I learned</h3>
                <p class="text-dark-200 whitespace-pre-line">{{ $presentation->learnings }}</p>
            </div>
        @endif
        <div class="grid sm:grid-cols-2 gap-3 text-sm">
            @if($presentation->audience)
                <div class="p-3 bg-dark-800 rounded-lg"><span class="text-dark-400">Audience:</span> <span class="text-white">{{ $presentation->audience }}</span></div>
            @endif
            @if($presentation->co_presenters)
                <div class="p-3 bg-dark-800 rounded-lg"><span class="text-dark-400">Co-presenters:</span> <span class="text-white">{{ $presentation->co_presenters }}</span></div>
            @endif
        </div>
    </div>

    @if(!empty($presentation->links) || !empty($presentation->files))
        <div class="card p-6">
            <h3 class="text-white font-semibold mb-3">Links and files</h3>
            <div class="space-y-2">
                @foreach($presentation->links ?? [] as $key => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center justify-between bg-dark-800 rounded-lg px-4 py-2 text-sm hover:bg-dark-700 transition">
                        <span class="text-dark-300">{{ $linkLabels[$key] ?? 'Link' }}</span>
                        <span class="text-primary-400 truncate ml-3">{{ $url }}</span>
                    </a>
                @endforeach
                @foreach($presentation->files ?? [] as $i => $path)
                    <a href="{{ route('presentations.file', [$presentation, $i]) }}" class="flex items-center justify-between bg-dark-800 rounded-lg px-4 py-2 text-sm hover:bg-dark-700 transition">
                        <span class="text-dark-300">File</span>
                        <span class="text-primary-400 truncate ml-3">{{ basename($path) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
