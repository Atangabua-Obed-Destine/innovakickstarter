@extends('layouts.app')

@section('title', 'Track Community')

@section('content')
@include('partials.track.styles')
<style>
    .cf-item { display: flex; gap: 14px; padding: 16px; border-radius: 14px; background: rgba(15, 20, 29, 0.55); border: 1px solid rgba(148, 163, 184, 0.12); }
    .cf-icon { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 20px;
        background: color-mix(in srgb, var(--color-primary-500) 18%, transparent); }
    .cf-kudos { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 13px; border: 1px solid rgba(148, 163, 184, 0.25); color: rgb(203 213 225); }
    .cf-kudos.is-given { border-color: var(--color-primary-500); color: var(--color-primary-300); background: color-mix(in srgb, var(--color-primary-500) 14%, transparent); }
    @keyframes cf-pop { 50% { transform: scale(1.25); } }
    .cf-kudos.is-given .cf-clap { display: inline-block; animation: cf-pop .3s ease-out; }
</style>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $experience?->emblem() }} {{ $track->name }} community</h1>
            <p class="text-dark-400 mt-1">What the {{ $fellowCount }} fellows in your track are achieving. Cheer each other on: give kudos, and comment on their Brand posts.</p>
        </div>
        <form method="POST" action="{{ route('community.visibility') }}" class="flex items-center gap-2 text-sm">
            @csrf
            <input type="hidden" name="show_in_track_feed" value="{{ auth()->user()->show_in_track_feed ? 0 : 1 }}">
            <span class="text-dark-400">{{ auth()->user()->show_in_track_feed ? 'You appear in this feed.' : 'You are hidden from this feed.' }}</span>
            <button type="submit" class="text-primary-400 hover:text-primary-300 underline">{{ auth()->user()->show_in_track_feed ? 'Hide me' : 'Show me' }}</button>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>
    @endif

    <div class="space-y-3">
        @forelse($events as $event)
            <div class="cf-item" x-data="{ given: @js((bool) $event->given_kudos), count: {{ (int) $event->kudos_count }}, busy: false }">
                <div class="cf-icon" aria-hidden="true">{{ $event->icon() }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-white">{{ $event->title }}</p>
                    @if($event->body)<p class="text-dark-400 text-sm mt-0.5">{{ $event->body }}</p>@endif
                    @php $ytId = app(\App\Services\YouTubeVideoService::class)->videoId($event->url); @endphp
                    @if($ytId)
                        <div class="mt-2" style="max-width: 420px;">
                            @include('partials.lesson.show-tell-video', ['video' => ['id' => $ytId, 'title' => null,
                                'watch' => "https://www.youtube.com/watch?v={$ytId}", 'embed' => "https://www.youtube-nocookie.com/embed/{$ytId}?rel=0", 'thumb' => "https://i.ytimg.com/vi/{$ytId}/hqdefault.jpg"]])
                        </div>
                    @endif
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="text-dark-500 text-xs">{{ $event->created_at->diffForHumans() }}</span>
                        @if($event->url)
                            <a href="{{ $event->url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-primary-400 hover:text-primary-300 text-sm">View →</a>
                        @endif
                        @if($event->fellow_id === auth()->id())
                            <span class="text-dark-400 text-sm" x-show="count > 0" x-text="'👏 ' + count + ' kudos'"></span>
                        @else
                            <button type="button" class="cf-kudos" :class="given ? 'is-given' : ''" :disabled="busy"
                                    @click="busy = true; fetch('{{ route('community.kudos', $event) }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                                        .then(r => r.json()).then(d => { given = d.given; count = d.count; }).finally(() => busy = false)">
                                <span class="cf-clap">👏</span> <span x-text="given ? 'Kudos given' : 'Kudos'"></span><span x-show="count > 0" x-text="' · ' + count"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-10 text-center">
                <p class="text-white font-semibold">Nothing here yet</p>
                <p class="text-dark-400 text-sm mt-2">When fellows finish chapters, projects, Brand posts or presentations, they show up here.</p>
            </div>
        @endforelse
    </div>
    {{ $events->links() }}
</div>
@endsection
