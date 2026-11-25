@extends('layouts.app')

@section('title', 'Choose your pathway')

@section('content')
@php
    $byPathway = collect($definitions)->mapWithKeys(fn ($d) => [$d['key'] => $chapters->filter(fn ($c) => in_array($d['key'], (array) $c->pathways, true))->values()]);
    $sharedAfter = $chapters->filter(fn ($c) => empty($c->pathways) && $c->sequence_order > ($chapters->whereNotNull('pathways')->min('sequence_order') ?? PHP_INT_MAX));
@endphp
<div class="max-w-5xl mx-auto space-y-6" x-data="pathwayChooser(@js($major), @js($minors), @js($quiz), @js(collect($definitions)->pluck('key')), @js($stacks))">
    <div>
        <a href="{{ route('curriculum.index') }}" class="text-dark-400 hover:text-white text-sm">← Curriculum</a>
        <h1 class="text-2xl font-bold text-white mt-2">🧭 Choose your pathway</h1>
        <p class="text-dark-300 mt-1 max-w-3xl">Software is a family of jobs. After the shared core, you go deep in <b class="text-white">one pathway</b>: that is what employers hire juniors for. You can add chapters from another pathway as a <b class="text-white">minor</b>, and you can change your mind later: finished work is always kept.</p>
        @unless($coreComplete)
            <p class="text-amber-300 text-sm mt-2">You can choose now. Your pathway chapters open when you finish the shared core.</p>
        @endunless
    </div>

    @if(count($quiz))
    <div class="card p-6" x-show="!quizDone || retake" x-cloak>
        <h2 class="text-white font-semibold text-lg">Not sure? Answer {{ count($quiz) }} quick questions</h2>
        <div class="space-y-4 mt-4">
            <template x-for="(q, qi) in quiz" :key="qi">
                <div>
                    <p class="text-dark-100 text-sm font-medium" x-text="(qi + 1) + '. ' + q.question"></p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <template x-for="(o, oi) in q.options" :key="oi">
                            <button type="button" class="pw-opt" :class="answers[qi] === oi && 'is-on'" @click="answers[qi] = oi" x-text="o.text"></button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <button type="button" class="btn-primary mt-5" :disabled="answers.filter(a => a !== null).length < quiz.length" @click="score()">See my suggestion</button>
    </div>
    <div class="card p-5" x-show="suggestion" x-cloak style="border-color: rgba(99,102,241,.5);">
        <p class="text-white">✨ Your answers point to <b x-text="titles[suggestion]"></b>. It is only a suggestion: read the pathways below and choose what excites you.</p>
        <button type="button" class="text-primary-400 text-sm mt-1" @click="retake = true; suggestion = null">Retake the quiz</button>
    </div>
    @endif

    <form method="POST" action="{{ route('curriculum.pathway.store') }}" class="space-y-6">
        @csrf
        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            @foreach($definitions as $d)
                <label class="pw-card" :class="{ 'is-chosen': choice === @js($d['key']), 'is-suggested': suggestion === @js($d['key']) }">
                    <input type="radio" name="pathway" value="{{ $d['key'] }}" x-model="choice" class="sr-only">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-white text-lg font-semibold">{{ $d['icon'] ?? '' }} {{ $d['title'] }}</p>
                        <span class="pw-pill" x-show="choice === @js($d['key'])">Chosen</span>
                        <span class="pw-pill" style="background: rgba(99,102,241,.2); color: #c7d2fe;" x-show="choice !== @js($d['key']) && suggestion === @js($d['key'])">Suggested</span>
                    </div>
                    <p class="text-dark-300 text-sm mt-1">{{ $d['summary'] ?? '' }}</p>
                    <p class="text-sm mt-3"><span class="text-dark-400">You build with:</span> <b class="text-white">{{ $d['stack'] ?? '' }}</b></p>
                    @if(!empty($d['alternatives']))<p class="text-dark-400 text-xs mt-1">Also allowed: {{ implode(', ', $d['alternatives']) }}</p>@endif
                    @if(!empty($d['demand']))<p class="text-emerald-300 text-xs mt-2">📈 {{ $d['demand'] }}</p>@endif
                    <ul class="mt-3 space-y-1 text-sm text-dark-200">
                        @foreach($byPathway[$d['key']] as $c)
                            <li>📘 {{ $c->title }}</li>
                        @endforeach
                    </ul>
                    @if(!empty($d['roles']))<p class="text-dark-400 text-xs mt-3">Junior roles: {{ implode(' · ', $d['roles']) }}</p>@endif
                </label>
            @endforeach
        </div>

        <div class="card p-6" x-show="choice" x-cloak>
            <h2 class="text-white font-semibold">Which stack will you build in?</h2>
            <p class="text-dark-400 text-sm mt-1">The recommended stack has starter files, reviewer guides and full mentor support. Another stack is allowed: your projects are checked on what they do, and lessons show the equivalent code.</p>
            @foreach($definitions as $d)
                <div class="flex flex-wrap gap-2 mt-3" x-show="choice === @js($d['key'])">
                    @foreach(array_merge([$d['stack'] ?? ''], (array) ($d['alternatives'] ?? [])) as $i => $s)
                        <label class="pw-opt" :class="stack === @js($s) && 'is-on'">
                            <input type="radio" class="sr-only" value="{{ $s }}" x-model="stack" :name="choice === @js($d['key']) ? 'stack' : ''">
                            {{ $s }}{{ $i === 0 ? ' (recommended)' : '' }}
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="card p-6" x-show="choice" x-cloak>
            <h2 class="text-white font-semibold">Add a minor (optional)</h2>
            <p class="text-dark-400 text-sm mt-1">Take chapters from another pathway alongside yours. They count for your Career Capital and job readiness, but never block your own pathway.</p>
            <div class="grid gap-2 mt-3" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                @foreach($chapters->whereNotNull('pathways') as $c)
                    <label class="pw-minor" x-show="choice && !@js((array) $c->pathways).includes(choice)">
                        <input type="checkbox" name="minors[]" value="{{ $c->id }}" x-model="minorIds" class="accent-primary-500">
                        <span class="text-dark-100 text-sm">{{ $c->title }}</span>
                        <span class="text-dark-500 text-xs">{{ collect($definitions)->whereIn('key', (array) $c->pathways)->pluck('title')->implode(', ') }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        @if($sharedAfter->isNotEmpty())
            <p class="text-dark-400 text-sm">After your pathway, everyone comes back together: {{ $sharedAfter->pluck('title')->implode(' → ') }}.</p>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary" :disabled="!choice" x-text="@js($major) ? 'Save changes' : 'Choose this pathway'"></button>
            <a href="{{ route('curriculum.index') }}" class="btn-secondary">Back</a>
        </div>
    </form>
</div>

<style>
    .pw-card { display: block; cursor: pointer; padding: 1.1rem 1.2rem; border-radius: 1rem; background: rgba(255,255,255,.03); border: 2px solid rgba(255,255,255,.08); transition: border-color .15s, background .15s; }
    .pw-card:hover { border-color: rgba(129,140,248,.45); }
    .pw-card.is-suggested { border-color: rgba(129,140,248,.6); }
    .pw-card.is-chosen { border-color: #10b981; background: rgba(16,185,129,.07); }
    .pw-pill { font-size: .72rem; padding: .15rem .55rem; border-radius: 999px; background: rgba(16,185,129,.2); color: #6ee7b7; white-space: nowrap; }
    .pw-opt { cursor: pointer; font-size: .85rem; padding: .4rem .8rem; border-radius: .6rem; color: #e4e4ea; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.12); }
    .pw-opt.is-on { background: rgba(99,102,241,.25); border-color: #818cf8; color: #fff; }
    .pw-minor { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; padding: .55rem .75rem; border-radius: .6rem; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); cursor: pointer; }
    .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
</style>
<script>
function pathwayChooser(major, minors, quiz, keys, stacks) {
    return {
        choice: major, minorIds: minors, quiz, answers: quiz.map(() => null), suggestion: null, quizDone: !!major, retake: false,
        stack: (major && stacks[major]) || '',
        titles: @js(collect($definitions)->pluck('title', 'key')),
        init() { this.$watch('choice', () => { this.stack = stacks[this.choice] || ''; }); },
        score() {
            const t = Object.fromEntries(keys.map(k => [k, 0]));
            this.answers.forEach((a, qi) => { const o = this.quiz[qi].options[a]; (o.pathways || [o.pathway]).forEach(p => { if (p in t) t[p]++; }); });
            this.suggestion = keys.reduce((best, k) => t[k] > t[best] ? k : best, keys[0]);
            if (!this.choice) this.choice = this.suggestion;
            this.retake = false; this.quizDone = true;
        },
    };
}
</script>
@endsection
