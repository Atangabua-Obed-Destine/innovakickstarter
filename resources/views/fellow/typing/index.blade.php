@extends('layouts.app')

@section('title', 'Typing trainer')

@section('content')
@include('partials.typing.styles')
@php
    $league = $leagues[$stats['league']];
    $best = $stats['best'];
    $chart = $stats['chart'];
    $maxWpm = max(array_merge([$targets['wpm'] + 10], array_column($chart, 'wpm')));
@endphp
<style>
    .ty-hero { border-radius: 18px; padding: 22px; background: radial-gradient(120% 140% at 0% 0%, color-mix(in srgb, var(--color-primary-600) 30%, #0f141d), #0f141d 60%); border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent); }
    .ty-lesson { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; background: rgba(15,20,29,.55); border: 1px solid rgba(148,163,184,.14); }
    .ty-lesson:hover { border-color: color-mix(in srgb, var(--color-primary-400) 60%, transparent); }
    .ty-num { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; background: rgba(148,163,184,.12); color: #fff; flex-shrink: 0; }
    .ty-num.ok { background: var(--color-primary-600); }
    .ty-bars { display: flex; align-items: flex-end; gap: 4px; height: 120px; position: relative; }
    .ty-bars div { flex: 1; border-radius: 4px 4px 0 0; background: linear-gradient(180deg, var(--color-primary-400), var(--color-primary-700)); min-height: 3px; }
    .ty-target { position: absolute; left: 0; right: 0; border-top: 2px dashed #fbbf24; }
</style>
<div class="max-w-5xl mx-auto space-y-5">
    <div class="ty-hero">
        <div class="flex flex-col md:flex-row md:items-center gap-5">
            <div class="flex-1">
                <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">⌨️ Typing trainer</p>
                <h1 class="text-2xl font-bold text-white mt-1">Type faster, think faster</h1>
                <p class="text-dark-300 mt-1">Every analyst, developer and engineer types all day. Learn to type with ten fingers without looking, then practise on real code from your track.</p>
                <div class="flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('typing.practice', 'drill') }}" class="btn-primary">{{ $stats['drill_today'] ? '✓ Drill done today: again?' : '▶ Today\'s 5-minute drill' }}</a>
                    <a href="{{ route('typing.practice', 'test') }}" class="btn-secondary">⏱ 1-minute speed test</a>
                    <a href="{{ route('typing.arena') }}" class="btn-secondary">🏁 Typing Arena</a>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="te-stat"><b>{{ $best ? round($best->wpm) : '–' }}</b><span>best words/min</span></div>
                <div class="te-stat"><b>{{ $stats['recent'] !== null ? round($stats['recent']) : '–' }}</b><span>recent average</span></div>
                <div class="te-stat"><b>{{ $league['icon'] }}</b><span>{{ $league['label'] }} league</span></div>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
        <div class="card p-6 md:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-white font-semibold">Your speed over time</h2>
                <span class="text-dark-400 text-xs">Target: {{ $targets['wpm'] }} words/min at {{ $targets['accuracy'] }}% · {{ $stats['days'] }} {{ Str::plural('day', $stats['days']) }} of practice</span>
            </div>
            @if(count($chart))
                <div class="ty-bars mt-4" role="img" aria-label="Typing speed chart">
                    <span class="ty-target" style="bottom: {{ $targets['wpm'] / $maxWpm * 100 }}%" title="Target {{ $targets['wpm'] }} wpm"></span>
                    @foreach($chart as $c)<div style="height: {{ max(2, $c['wpm'] / $maxWpm * 100) }}%" title="{{ $c['d'] }}: {{ round($c['wpm']) }} wpm, {{ round($c['acc']) }}%"></div>@endforeach
                </div>
                @if($stats['first'] && $best)
                    <p class="text-dark-300 text-sm mt-3">First test: <strong class="text-white">{{ round($stats['first']->wpm) }}</strong> words/min → best now: <strong class="text-white">{{ round($best->wpm) }}</strong>{{ $best->wpm > $stats['first']->wpm ? ' (+' . round($best->wpm - $stats['first']->wpm) . ') 📈' : '' }}</p>
                @endif
            @else
                <p class="text-dark-400 mt-4">No results yet. Take the 1-minute speed test to see where you start: everyone starts somewhere.</p>
            @endif
        </div>
        <div class="card p-6">
            <h2 class="text-white font-semibold">How to get fast</h2>
            <ul class="text-dark-300 text-sm space-y-2 mt-3">
                <li>👀 Eyes on the screen, not on your hands.</li>
                <li>🎯 Accuracy first. Speed comes by itself.</li>
                <li>🖐 Fingers rest on <code>asdf</code> and <code>jkl;</code>: feel the bumps on F and J.</li>
                <li>📅 5 minutes every day beats an hour once a week.</li>
            </ul>
        </div>
    </div>

    <div class="card p-6">
        <h2 class="text-white font-semibold">Lessons <span class="text-dark-400 text-sm font-normal">· pass each with {{ $targets['accuracy'] }}% accuracy</span></h2>
        <div class="grid md:grid-cols-3 gap-3 mt-4">
            @foreach($lessons as $i => $l)
                @php $ok = in_array($l['key'], $stats['lessons_passed'], true); @endphp
                <a href="{{ route('typing.practice', ['lesson', $l['key']]) }}" class="ty-lesson">
                    <span class="ty-num {{ $ok ? 'ok' : '' }}">{{ $ok ? '✓' : $i + 1 }}</span>
                    <span class="min-w-0"><span class="block text-white font-medium">{{ $l['title'] }}</span><span class="block text-dark-500 text-xs truncate">{{ $l['keys'] }}</span></span>
                </a>
            @endforeach
        </div>
    </div>

    @include('fellow.typing.partials.boards', ['boards' => $boards, 'leagues' => $leagues])
</div>
@endsection
