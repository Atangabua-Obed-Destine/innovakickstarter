@extends('layouts.app')

@section('title', 'Skills check')

@section('content')
@include('partials.typing.styles')
<style>
    .ck-opt { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.2); cursor: pointer; color: rgb(226 232 240); font-size: 14px; }
    .ck-opt.picked { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 14%, transparent); }
    .ck-steps { display: flex; gap: 6px; flex-wrap: wrap; }
    .ck-steps span { padding: 5px 10px; border-radius: 99px; font-size: 12px; background: rgba(148,163,184,.1); color: rgb(148 163 184); }
    .ck-steps span.on { background: var(--color-primary-600); color: #fff; }
    .ck-steps span.done { background: rgba(34,197,94,.18); color: #86efac; }
    .ck-passage { white-space: pre-line; padding: 14px 16px; border-radius: 12px; background: #fdfcf7; color: #1f2937; line-height: 1.6; }
</style>
<div class="max-w-3xl mx-auto space-y-5" x-data="skillsCheck(@js([
        'areas' => $areas, 'text' => $typingText, 'saveTyping' => route('typing.save'),
        'submitBase' => url('/base-camp'), 'done' => route('base-camp.index'), 'csrf' => csrf_token(),
    ]))">
    <a href="{{ route('base-camp.index') }}" class="text-dark-400 hover:text-white">← Base Camp</a>

    <div class="card p-6">
        <div class="ck-steps">
            <span :class="{ on: step === 'intro' }">Start</span>
            <span :class="{ on: step === 'typing', done: typingDone }">⌨️ Typing</span>
            <template x-for="(a, i) in areas" :key="a.key">
                <span :class="{ on: step === i, done: results[a.key] }" x-text="a.icon + ' ' + a.title"></span>
            </template>
        </div>

        {{-- Intro --}}
        <div x-show="step === 'intro'" class="mt-6">
            <h1 class="text-2xl font-bold text-white">Skills check</h1>
            <p class="text-dark-300 mt-2">About 15 minutes. It is not an exam and it doesn't affect your score: it shows where Base Camp can help you, so no chapter feels too steep later.</p>
            <ul class="text-dark-300 text-sm mt-3 space-y-1">
                <li>⌨️ A 1-minute typing test (use a laptop or desktop keyboard)</li>
                @foreach($areas as $a)<li>{{ $a['icon'] }} {{ $a['title'] }}: {{ count($a['questions']) }} short questions</li>@endforeach
            </ul>
            <p class="text-dark-400 text-sm mt-3">Answer honestly, without searching: "I don't know" is useful information.</p>
            <button type="button" class="btn-primary px-6 py-3 mt-5" @click="step = 'typing'; $nextTick(() => $refs.box.focus())">Begin with typing →</button>
        </div>

        {{-- Typing --}}
        <div x-show="step === 'typing'" x-cloak class="mt-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">⌨️ 1-minute typing test</h2>
                <div class="flex gap-5">
                    <div class="te-stat"><b x-text="Math.round(wpm)">0</b><span>words / min</span></div>
                    <div class="te-stat"><b x-text="Math.round(acc) + '%'">100%</b><span>accuracy</span></div>
                    <div class="te-stat"><b x-text="Math.max(0, Math.ceil(60 - secs)) + 's'">60s</b><span>left</span></div>
                </div>
            </div>
            <p class="text-dark-400 text-sm mt-1">Type the text below. The timer starts with your first key.</p>
            <div class="te-box mt-3" tabindex="0" x-ref="box" @keydown="key($event)" @paste.prevent x-html="html" x-show="!typingDone"></div>
            <div x-show="typingDone" class="mt-4 p-4 rounded-lg bg-dark-800 text-center">
                <p class="text-white text-xl font-bold" x-text="Math.round(wpm) + ' words/min · ' + Math.round(acc) + '% accuracy'"></p>
                <p class="text-dark-400 text-sm mt-1">Saved. The typing trainer will help you improve, whatever your speed today.</p>
                <button type="button" class="btn-primary mt-4" @click="step = 0">Next: {{ $areas[0]['title'] ?? '' }} →</button>
            </div>
        </div>

        {{-- Areas --}}
        <template x-for="(a, i) in areas" :key="a.key">
            <div x-show="step === i" x-cloak class="mt-6">
                <h2 class="text-xl font-bold text-white" x-text="a.icon + ' ' + a.title"></h2>
                <p class="text-dark-400 text-sm" x-text="a.summary"></p>
                <template x-if="a.passage"><div class="ck-passage mt-4" x-text="a.passage"></div></template>
                <div class="space-y-5 mt-5">
                    <template x-for="(q, qi) in a.questions" :key="q.key">
                        <div>
                            <p class="text-white font-medium" x-text="(qi + 1) + '. ' + q.question"></p>
                            <div class="space-y-2 mt-2">
                                <template x-for="(o, oi) in q.options" :key="oi">
                                    <label class="ck-opt" :class="answers[q.key] === oi && 'picked'">
                                        <input type="radio" class="mt-1 accent-primary-500" :name="q.key" @change="answers[q.key] = oi" :disabled="!!results[a.key]">
                                        <span x-text="o"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <p class="text-amber-300 text-sm mt-3" x-show="error" x-text="error"></p>
                <div class="flex justify-end mt-5">
                    <button type="button" class="btn-primary" :disabled="busy || a.questions.some(q => answers[q.key] === undefined)" @click="submitArea(a, i)"
                            x-text="busy ? 'Saving...' : (i + 1 < areas.length ? 'Next section →' : 'See my results →')"></button>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function skillsCheck(cfg) {
    return {
        areas: cfg.areas, step: 'intro', answers: {}, results: {}, busy: false, error: '',
        engine: window.TypingEngine(cfg.text), html: '', wpm: 0, acc: 100, secs: 0, timer: null, typingDone: false,
        init() { this.html = this.engine.html(); },
        key(ev) {
            if (this.typingDone) return;
            const k = window.typingKeyOf(ev);
            if (!k) return;
            ev.preventDefault();
            if (!this.timer) this.timer = setInterval(() => this.tick(), 200);
            this.engine.press(k); this.html = this.engine.html(); this.tick();
            if (this.engine.done) this.finishTyping();
        },
        tick() {
            this.secs = this.engine.seconds(); this.wpm = this.engine.wpm(); this.acc = this.engine.accuracy();
            if (this.secs >= 60) this.finishTyping();
        },
        async finishTyping() {
            if (this.typingDone) return;
            clearInterval(this.timer); this.typingDone = true;
            const secs = Math.min(60, this.engine.seconds());
            try {
                await fetch(cfg.saveTyping, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                    body: JSON.stringify({ mode: 'test', wpm: Math.round((this.engine.pos / 5) / (secs / 60) * 10) / 10, accuracy: Math.round(this.engine.accuracy() * 10) / 10, chars: Math.max(1, this.engine.pos), seconds: Math.max(3, Math.round(secs)) }) });
            } catch (e) { /* the rest of the check still works */ }
        },
        async submitArea(a, i) {
            this.busy = true; this.error = '';
            const answers = {}; a.questions.forEach(q => answers[q.key] = this.answers[q.key]);
            try {
                const res = await fetch(cfg.submitBase + '/' + a.key + '/check', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ answers }) });
                if (!res.ok) throw new Error();
                this.results[a.key] = await res.json();
                if (i + 1 < this.areas.length) { this.step = i + 1; window.scrollTo({ top: 0, behavior: 'smooth' }); }
                else window.location.href = cfg.done;
            } catch (e) { this.error = 'Could not save your answers. Check your connection and try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endpush
