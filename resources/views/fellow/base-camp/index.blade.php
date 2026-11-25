@extends('layouts.app')

@section('title', 'Base Camp')

@section('content')
@php
    $ready = collect($status)->where('ready', true)->count();
    $total = count($status);
@endphp
<style>
    .bc-hero { border-radius: 18px; padding: 24px; background: radial-gradient(120% 140% at 100% 0%, color-mix(in srgb, var(--color-primary-600) 30%, #0f141d), #0f141d 60%); border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent); }
    .bc-area { border-radius: 16px; padding: 18px; background: rgba(15,20,29,.6); border: 1px solid rgba(148,163,184,.14); }
    .bc-area.ready { border-color: rgba(34,197,94,.45); }
    .bc-area.boost { border-color: rgba(251,191,36,.45); }
    .bc-pill { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; white-space: nowrap; flex-shrink: 0; }
    .bc-lesson { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 10px; font-size: 14px; color: rgb(203 213 225); background: rgba(11,15,22,.5); }
    .bc-lesson:hover { color: #fff; background: rgba(148,163,184,.1); }
</style>
<div class="max-w-5xl mx-auto space-y-5">
    <div class="bc-hero">
        <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">⛺ Base Camp</p>
        <h1 class="text-2xl font-bold text-white mt-1">Strong foundations, so no chapter feels too steep</h1>
        <p class="text-dark-300 mt-2 max-w-3xl">Every track assumes a few basics: typing, using a computer, everyday maths, reading instructions and spreadsheets. Base Camp checks them in about 15 minutes, then gives you short lessons for anything that needs a boost. It runs alongside Chapters 1-2: you never have to wait to start.</p>
        <div class="flex flex-wrap items-center gap-3 mt-4">
            @if(!$checkTaken)
                <a href="{{ route('base-camp.check') }}" class="btn-primary px-6 py-3">Take the skills check (15 min) →</a>
                <span class="text-dark-400 text-sm">No pressure: it only tells us where to help you.</span>
            @else
                <span class="text-white font-semibold">{{ $ready }} of {{ $total }} areas ready</span>
                <a href="{{ route('base-camp.check') }}" class="btn-secondary text-sm">Retake the check</a>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @foreach($status as $a)
            @php $cls = !$a['checked'] ? '' : ($a['ready'] ? 'ready' : 'boost'); @endphp
            <div class="bc-area {{ $cls }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-white font-semibold text-lg">{{ $a['icon'] }} {{ $a['title'] }}</p>
                        <p class="text-dark-400 text-sm">{{ $a['summary'] }}</p>
                    </div>
                    @if(!$a['checked'])
                        <span class="bc-pill bg-dark-700 text-dark-300">Not checked yet</span>
                    @elseif($a['ready'])
                        <span class="bc-pill bg-green-500/20 text-green-400">Ready ✓</span>
                    @else
                        <span class="bc-pill bg-amber-500/20 text-amber-300">Needs a boost</span>
                    @endif
                </div>
                <p class="text-dark-300 text-sm mt-3">
                    @if($a['best'])Your best: <strong class="text-white">{{ $a['best'] }}</strong> · @endif
                    Target: {{ $a['target'] }}
                </p>
                @if($a['key'] === 'typing')
                    <div class="flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('typing.index') }}" class="btn-primary text-sm">Typing trainer →</a>
                        <a href="{{ route('typing.practice', 'test') }}" class="btn-secondary text-sm">1-minute test</a>
                    </div>
                @else
                    <div class="space-y-1 mt-3">
                        @foreach($a['lessons'] as $i => $l)
                            <a href="{{ route('base-camp.lesson', [$a['key'], $l['key']]) }}" class="bc-lesson">
                                <span>{{ in_array($l['key'], $a['lessons_done'], true) ? '✅' : '📘' }}</span>
                                <span class="flex-1">{{ $l['title'] }}</span>
                                <span class="text-dark-500 text-xs">{{ $l['minutes'] }} min</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-3 mt-3">
                        <a href="{{ route('base-camp.test', $a['key']) }}" class="{{ $a['ready'] ? 'btn-secondary' : 'btn-primary' }} text-sm">{{ $a['test_passed'] ? 'Retake the module test' : 'Module test' }}</a>
                        @if($a['test_passed'])<span class="text-green-400 text-sm">🏅 Badge earned</span>@endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <p class="text-dark-500 text-sm text-center">Stuck on something basic? That's exactly what the team is for: use 🙋 <a class="text-primary-400" href="{{ route('help.index') }}">Ask a human</a>.</p>
</div>
@endsection
