{{-- A Show & Tell video. Expects $video (FellowCurriculumProgress::show_tell_video). Optional $reviewer (bool) shows the Public check note.
     The player only loads when pressed (saves data, and YouTube sets no cookies before that). --}}
@if($video)
<div class="st-video" x-data="{ play: false }">
    <div class="st-frame">
        <template x-if="play">
            <iframe :src="@js($video['embed']) + '&autoplay=1'" title="{{ $video['title'] ?? 'Show & Tell video' }}" loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </template>
        <button type="button" x-show="!play" @click="play = true" class="st-poster" aria-label="Play the video">
            <img src="{{ $video['thumb'] }}" alt="" loading="lazy">
            <span class="st-play">▶</span>
        </button>
    </div>
    <div class="st-meta">
        <p class="text-white text-sm font-medium truncate">🎬 {{ $video['title'] ?? 'Show & Tell video' }}</p>
        <p class="text-dark-400 text-xs">
            @if(!empty($video['channel']))
                @if(!empty($video['channel_url']))<a href="{{ $video['channel_url'] }}" target="_blank" rel="noopener" class="hover:text-white">{{ $video['channel'] }}</a>@else{{ $video['channel'] }}@endif ·
            @endif
            <a href="{{ $video['watch'] }}" target="_blank" rel="noopener" class="text-primary-400 hover:underline">Open on YouTube</a>
        </p>
        @if(!empty($reviewer))
            <p class="text-xs mt-1 {{ ($video['verified'] ?? false) ? 'text-dark-400' : 'text-amber-300' }}">
                {{ ($video['verified'] ?? false) ? 'YouTube confirmed the video exists and can be embedded.' : 'YouTube could not be reached when this was submitted: open the link to confirm the video exists.' }}
                YouTube cannot tell Public from Unlisted to us: open it on YouTube and check it shows on the fellow's channel (the fellow confirmed it is Public).
            </p>
        @endif
    </div>
</div>
@once
<style>
    .st-video { border-radius: .85rem; overflow: hidden; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); }
    .st-frame { position: relative; aspect-ratio: 16 / 9; background: #000; }
    .st-frame iframe, .st-poster, .st-poster img { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
    .st-poster { cursor: pointer; padding: 0; background: #000; }
    .st-poster img { object-fit: cover; opacity: .85; }
    .st-play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 64px; height: 44px; border-radius: 12px; background: #dc2626; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
    .st-poster:hover .st-play { background: #ef4444; }
    .st-meta { padding: .65rem .85rem; }
</style>
@endonce
@endif
