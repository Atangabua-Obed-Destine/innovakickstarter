{{-- Base Camp link: foundations this lesson leans on that the fellow has not made ready yet. Expects $needs (FoundationLinkService::forFellow). --}}
@php $bysChecked = collect($needs)->contains('checked', true); @endphp
<div class="card p-6 bys" x-data="{ open: @js($bysChecked) }">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-white font-semibold text-lg">🧭 Before you start: basics this lesson uses</h3>
            <p class="text-dark-400 text-sm mt-1 max-w-2xl">You can start the lesson now. If any of these feel shaky, a short Base Camp refresher first will make the lesson much easier to understand. Nobody else sees this.</p>
        </div>
        <button type="button" class="text-dark-400 hover:text-white text-sm" @click="open = !open" x-text="open ? 'Hide' : 'Show refreshers'"></button>
    </div>
    <div class="flex flex-wrap items-center gap-2 mt-3" x-show="!open" x-cloak>
        @foreach($needs as $n)
            <span class="bys-pill" style="background: rgba(255,255,255,.06); color: #e4e4ea; font-size: .8rem; padding: .25rem .65rem;">{{ $n['icon'] }} {{ $n['title'] }}</span>
        @endforeach
        @unless($bysChecked)
            <a href="{{ route('base-camp.check') }}" class="text-primary-400 hover:text-primary-300 text-sm ml-1">Take the 15-minute Base Camp check to see which you need →</a>
        @endunless
    </div>
    <div class="grid gap-3 mt-4" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));" x-show="open" x-cloak>
        @foreach($needs as $n)
            <div class="bys-area">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-white font-medium">{{ $n['icon'] }} {{ $n['title'] }}</p>
                    @if(!$n['checked'])
                        <span class="bys-pill" style="background: rgba(255,255,255,.07); color: #c4c4d0;">Not checked yet</span>
                    @else
                        <span class="bys-pill" style="background: rgba(245,158,11,.15); color: #fcd34d;">{{ $n['best'] ? 'Your best: ' . $n['best'] : 'Needs a boost' }}</span>
                    @endif
                </div>
                @if($n['why'])
                    <p class="text-dark-400 text-xs mt-1">Why: {{ implode('; ', array_slice($n['why'], 0, 2)) }}.</p>
                @endif
                <div class="space-y-1 mt-2">
                    @foreach(array_slice($n['lessons'], 0, 3) as $l)
                        <a href="{{ $l['url'] }}" class="bys-lesson">
                            <span>{{ $l['done'] ? '✅' : '📘' }}</span>
                            <span class="flex-1">{{ $l['title'] }}</span>
                            <span class="text-dark-500 text-xs">{{ $l['minutes'] }} min</span>
                        </a>
                    @endforeach
                </div>
                @if(!$n['checked'] && $n['key'] !== 'typing')
                    <a href="{{ route('base-camp.check') }}" class="text-primary-400 hover:text-primary-300 text-xs inline-block mt-2">Not sure? Take the 15-minute Base Camp check →</a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@once
<style>
    .bys { border-color: rgba(245,158,11,.25); }
    .bys-area { padding: .85rem 1rem; border-radius: .8rem; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); }
    .bys-pill { font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; white-space: nowrap; }
    .bys-lesson { display: flex; align-items: center; gap: .5rem; padding: .4rem .55rem; border-radius: .5rem; font-size: .85rem; color: #d4d4dc; background: rgba(255,255,255,.03); }
    .bys-lesson:hover { background: rgba(255,255,255,.07); color: #fff; }
</style>
@endonce
