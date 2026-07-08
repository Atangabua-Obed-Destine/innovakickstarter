@extends('layouts.app')

@section('title', 'Select Your Track')

@section('content')
@php
    $statusMeta = [
        'pending'        => ['label' => 'Pending admin review',      'class' => 'bg-amber-500/10 border-amber-500/30 text-amber-300'],
        'needs_revision' => ['label' => 'Needs revision — see notes','class' => 'bg-orange-500/10 border-orange-500/30 text-orange-300'],
        'approved'       => ['label' => 'You\'re enrolled',          'class' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'],
        'rejected'       => ['label' => 'Enrollment rejected',       'class' => 'bg-red-500/10 border-red-500/30 text-red-300'],
    ];
@endphp

<div class="max-w-5xl mx-auto space-y-8">
    <div class="text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">Choose Your Career Track</h1>
        <p class="text-dark-400 mt-2 max-w-2xl mx-auto">
            @if($hasAnyTrack)
                Additional tracks go through admin review. Tell us why you want to add this track and an admin will approve or ask for revisions.
            @else
                Pick the track that matches your goals. Your first track is enabled right away so you can start earning Career Capital.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-4 text-emerald-300 text-center">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-blue-300 text-center">{{ session('info') }}</div>
    @endif

    <div class="grid md:grid-cols-2 gap-6" x-data="{ selectedId: null }">
        @forelse($tracks as $track)
            @php
                $existing = $existingByTrack[$track->id] ?? null;
                $meta = $existing ? ($statusMeta[$existing->status] ?? null) : null;
                $canRequest = !$existing || $existing->status === \App\Models\FellowTrack::STATUS_REJECTED;
            @endphp
            <div class="card p-6 transition-all">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center"
                         style="background: {{ $track->color ?? '#1e293b' }}20; color: {{ $track->color ?? '#6366f1' }}">
                        <x-track-icon :icon="$track->icon" class="w-7 h-7" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white">{{ $track->name }}</h3>
                        <p class="text-dark-500 text-sm">{{ $track->fellows_count ?? 0 }} fellows enrolled</p>
                    </div>
                </div>

                @if($track->description)
                    <p class="text-dark-400 text-sm mb-5">{{ $track->description }}</p>
                @endif

                @if(is_array($track->skills) && count($track->skills))
                    <div class="flex flex-wrap gap-1.5 mb-5">
                        @foreach(array_slice($track->skills, 0, 8) as $skill)
                            <span class="px-2 py-1 text-xs bg-dark-700 text-dark-300 rounded-lg">{{ $skill }}</span>
                        @endforeach
                    </div>
                @endif

                @if($meta)
                    <div class="mb-4 rounded-lg border p-3 text-sm {{ $meta['class'] }}">
                        <p class="font-medium">{{ $meta['label'] }}</p>
                        @if($existing->review_notes && in_array($existing->status, ['needs_revision', 'rejected']))
                            <p class="mt-1 text-xs opacity-90 whitespace-pre-line">{{ $existing->review_notes }}</p>
                        @endif
                    </div>
                @endif

                @if($canRequest)
                    <form method="POST" action="{{ route('tracks.enroll') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="track_id" value="{{ $track->id }}">
                        @if($hasAnyTrack)
                            <button type="button" @click="selectedId = selectedId === '{{ $track->id }}' ? null : '{{ $track->id }}'"
                                    class="btn btn-outline w-full text-sm"
                                    x-text="selectedId === '{{ $track->id }}' ? 'Cancel' : 'Request enrollment'"></button>
                            <div x-show="selectedId === '{{ $track->id }}'" x-cloak x-transition class="space-y-2">
                                <label class="block">
                                    <span class="text-dark-300 text-xs">Why do you want to add {{ $track->name }}? (optional but helps approval)</span>
                                    <textarea name="motivation" rows="3" maxlength="1000"
                                              class="form-input w-full mt-1 text-sm"
                                              placeholder="e.g. Complements my Full-Stack skills and aligns with roles I'm targeting."></textarea>
                                </label>
                                <button type="submit" class="btn btn-primary w-full">Send request to admin</button>
                            </div>
                        @else
                            <button type="submit" class="btn btn-primary w-full">
                                Select {{ $track->name }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        @endif
                    </form>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-dark-500">
                <p class="text-lg">No tracks available right now.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
