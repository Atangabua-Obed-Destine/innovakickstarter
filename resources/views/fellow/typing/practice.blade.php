@extends('layouts.app')

@php
    $titles = ['test' => '1-minute speed test', 'drill' => 'Daily drill', 'lesson' => 'Lesson: ' . ($lesson['title'] ?? '')];
@endphp
@section('title', $titles[$mode])

@section('content')
@include('partials.typing.styles')
<div class="max-w-4xl mx-auto space-y-5" x-data="typingPractice(@js([
        'mode' => $mode, 'lesson' => $lesson['key'] ?? null, 'parts' => $parts, 'limit' => $mode === 'test' ? 60 : null,
        'saveUrl' => route('typing.save'), 'csrf' => csrf_token(), 'targetWpm' => $targets['wpm'], 'targetAcc' => $targets['accuracy'],
        'keyboard' => $mode !== 'test',
    ]))" x-init="init()">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('typing.index') }}" class="text-dark-400 hover:text-white">← Typing trainer</a>
        <span class="text-dark-400 text-sm">Target: <strong class="text-white">{{ $targets['wpm'] }} words/min</strong> at <strong class="text-white">{{ $targets['accuracy'] }}%</strong> accuracy</span>
    </div>

    <div class="card p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $titles[$mode] }}</h1>
                <p class="text-dark-400 text-sm mt-1">
                    @if($mode === 'test') Type for one minute. Speed and accuracy both count. The timer starts with your first key.
                    @elseif($mode === 'drill') Five minutes a day builds real speed. Today's drill mixes sentences with code from your track.
                    @else {{ $lesson['tip'] ?? '' }} @if(!empty($lesson['keys']))<span class="text-dark-300">Keys: <code class="text-primary-300">{{ $lesson['keys'] }}</code></span>@endif
                    @endif
                </p>
            </div>
            <div class="flex gap-6">
                <div class="te-stat"><b x-text="Math.round(stats.wpm)">0</b><span>words / min</span></div>
                <div class="te-stat"><b x-text="Math.round(stats.acc) + '%'">100%</b><span>accuracy</span></div>
                <div class="te-stat"><b x-text="limit ? Math.max(0, Math.ceil(limit - stats.secs)) + 's' : (part + 1) + '/' + parts.length">-</b><span x-text="limit ? 'left' : 'part'"></span></div>
            </div>
        </div>

        <template x-if="!finished">
            <div class="mt-5">
                <p class="text-dark-500 text-xs mb-2" x-show="parts[part] && parts[part].kind === 'code'" x-text="'Real ' + (parts[part]?.lang || 'code') + ' from your track: type it exactly, Enter for a new line.'"></p>
                <div class="te-box" tabindex="0" x-ref="box" @keydown="key($event)" @focus="focused = true" @blur="focused = false" :class="!focused && 'te-blur'" x-html="html" @paste.prevent></div>
                <p class="text-dark-400 text-sm mt-2" x-show="!focused">👆 Click the text (or press Tab) to start typing.</p>
                <template x-if="keyboard">
                    <div class="mt-5">
                        <div class="kb" x-html="kb.html"></div>
                        <p class="text-center text-primary-200 text-sm mt-3" x-text="kb.hint"></p>
                        <div class="kb-legend text-center mt-2">
                            <span><i style="background:#4c1d95"></i>little</span><span><i style="background:#1e3a8a"></i>ring</span><span><i style="background:#065f46"></i>middle</span><span><i style="background:#7c2d12"></i>index</span><span><i style="background:#334155"></i>thumb</span>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="finished">
            <div class="mt-6 text-center or-result">
                <p class="text-6xl" x-text="passed ? '🎉' : '💪'"></p>
                <p class="text-3xl font-bold text-white mt-2"><span x-text="Math.round(stats.wpm)"></span> words/min · <span x-text="Math.round(stats.acc)"></span>%</p>
                <p class="mt-2" :class="passed ? 'text-green-400' : 'text-amber-300'"
                   x-text="mode === 'lesson' ? (stats.acc >= targetAcc ? 'Lesson passed: accuracy ' + Math.round(stats.acc) + '% (target ' + targetAcc + '%).' : 'Accuracy below ' + targetAcc + '%. Slow down a little: accuracy first, speed follows.')
                        : (passed ? 'You reached the Base Camp target!' : (stats.acc < targetAcc ? 'Focus on accuracy first: slow down until you reach ' + targetAcc + '%.' : 'Good accuracy! Keep practising daily to reach ' + targetWpm + ' words/min.'))"></p>
                <p class="text-dark-400 text-sm mt-1" x-show="saved" x-text="best ? 'Your best: ' + Math.round(best) + ' words/min · League: ' + league : ''"></p>
                <p class="text-amber-300 text-sm mt-1" x-show="error" x-text="error"></p>
                <div class="flex flex-wrap justify-center gap-3 mt-6">
                    <button type="button" class="btn-secondary" @click="restart()">↺ Try again</button>
                    @if($nextLesson)<a href="{{ route('typing.practice', ['lesson', $nextLesson['key']]) }}" class="btn-primary">Next lesson: {{ $nextLesson['title'] }} →</a>@endif
                    <a href="{{ route('typing.arena') }}" class="btn-secondary">🏁 Race in the Arena</a>
                    <a href="{{ route('typing.index') }}" class="btn-secondary">Back to the trainer</a>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function typingPractice(cfg) {
    return {
        ...cfg, part: 0, engine: null, html: '', kb: { html: '', hint: '' }, focused: false, finished: false, saved: false, error: '',
        stats: { wpm: 0, acc: 100, secs: 0 }, totals: { chars: 0, keys: 0, errors: 0, secs: 0 }, timer: null, best: null, league: '', passed: false,
        init() { this.load(); this.$nextTick(() => this.$refs.box && this.$refs.box.focus()); },
        load() { this.engine = window.TypingEngine(this.parts[this.part].text); this.render(); },
        render() {
            this.html = this.engine.html();
            if (this.keyboard) this.kb = window.TypingKeyboard.html(this.engine.next());
        },
        tick() {
            const e = this.engine, secs = this.totals.secs + e.seconds();
            const chars = this.totals.chars + e.pos, keys = this.totals.keys + e.keystrokes, errors = this.totals.errors + e.errors;
            this.stats = { secs, wpm: secs > 1 ? (chars / 5) / (secs / 60) : 0, acc: keys ? (keys - errors) / keys * 100 : 100 };
            if (this.limit && secs >= this.limit) this.finish();
        },
        key(ev) {
            if (this.finished) return;
            const k = window.typingKeyOf(ev);
            if (!k) return;
            ev.preventDefault();
            if (!this.timer) this.timer = setInterval(() => this.tick(), 200);
            this.engine.press(k);
            this.render(); this.tick();
            if (this.engine.done && !this.finished) {
                const e = this.engine;
                this.totals = { chars: this.totals.chars + e.pos, keys: this.totals.keys + e.keystrokes, errors: this.totals.errors + e.errors, secs: this.totals.secs + e.seconds() };
                if (this.part + 1 < this.parts.length) { this.part++; this.load(); }
                else if (this.limit) { this.parts.push({ kind: 'text', text: this.parts[0].text }); this.part++; this.load(); }
                else this.finish(true);
            }
        },
        async finish(alreadyCounted) {
            if (this.finished) return;
            clearInterval(this.timer); this.timer = null;
            if (!alreadyCounted) {
                const e = this.engine;
                this.totals = { chars: this.totals.chars + e.pos, keys: this.totals.keys + e.keystrokes, errors: this.totals.errors + e.errors, secs: this.totals.secs + e.seconds() };
            }
            const t = this.totals, secs = this.limit ? Math.min(this.limit, t.secs) : t.secs;
            this.stats = { secs, wpm: secs > 1 ? (t.chars / 5) / (secs / 60) : 0, acc: t.keys ? (t.keys - t.errors) / t.keys * 100 : 100 };
            this.passed = this.mode === 'lesson' ? this.stats.acc >= this.targetAcc : (this.stats.wpm >= this.targetWpm && this.stats.acc >= this.targetAcc);
            this.finished = true;
            if (t.chars < 5 || secs < 3) return;
            try {
                const res = await fetch(cfg.saveUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                    body: JSON.stringify({ mode: this.mode, lesson_key: this.lesson, wpm: Math.round(this.stats.wpm * 10) / 10, accuracy: Math.round(this.stats.acc * 10) / 10, chars: t.chars, seconds: Math.max(3, Math.round(secs)) }) });
                const d = await res.json();
                if (res.ok) { this.saved = true; this.best = d.best; this.league = d.league; } else this.error = 'Could not save this result.';
            } catch (e) { this.error = 'No connection: this result was not saved.'; }
        },
        restart() { window.location.reload(); },
    };
}
</script>
@endpush
