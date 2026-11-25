{{--
    Track identity banner.
    @param \App\Support\TrackExperience $experience
    @param \App\Models\FellowTrack|null $fellowTrack
    @param \App\Models\User $user
    @param bool $compact
--}}
@include('partials.track.styles')
@php
    $compact = $compact ?? false;
    $levelTitle = $experience->levelTitle($fellowTrack?->tier);
    $firstName = explode(' ', $user->name)[0];
@endphp
<div class="tx-hero">
    <div class="relative flex flex-col sm:flex-row sm:items-center gap-4" style="z-index: 1;">
        <div class="tx-emblem" aria-hidden="true">{{ $experience->emblem() }}</div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="tx-chip">{{ $levelTitle }}</span>
                @if($experience->team())
                    <span class="text-dark-400 text-sm">{{ $experience->team() }}@if($experience->company()) · {{ $experience->company() }}@endif</span>
                @endif
            </div>
            @if($compact)
                <h1 class="text-2xl font-bold text-white">{{ $experience->track->name }}</h1>
            @else
                <h1 class="text-2xl font-bold text-white">Welcome back, {{ $firstName }}</h1>
            @endif
            <p class="text-dark-300 mt-1">{{ $compact ? ($experience->tagline() ?? $experience->greeting()) : $experience->greeting() }}</p>
        </div>
        @if($experience->manager() && !$compact)
            @php $manager = $experience->manager(); @endphp
            <div class="flex items-center gap-3">
                <div class="tx-avatar" aria-hidden="true">{{ $manager['initials'] }}</div>
                <div>
                    <p class="text-dark-400 text-xs">Your manager</p>
                    <p class="text-white text-sm font-semibold">{{ $manager['name'] }}</p>
                    <p class="text-dark-400 text-xs">{{ $manager['title'] }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
