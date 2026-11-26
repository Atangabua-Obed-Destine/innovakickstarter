@extends('layouts.app')

@section('title', $challenge->title . ' · Practice Lab')

@section('content')
@include('partials.track.styles')
@php
    $skillLabel = collect($experience?->skills() ?? [])->pluck('label', 'key')[$challenge->skill] ?? ucfirst((string) $challenge->skill);
    $config = [
        'kind' => $challenge->kind,
        'starter' => (string) $challenge->starter_code,
        'ordered' => (bool) $challenge->ordered,
        'needsPandas' => (bool) $challenge->needs_pandas,
        'setup' => (string) $challenge->setup,
        'tables' => $practiceConfig['sql_tables'] ?? [],
        'files' => $practiceConfig['python_files'] ?? [],
        'canonUrl' => asset('course-files/practice/practice_canon.py'),
        'jsCanonUrl' => asset('course-files/practice/practice_canon.js'),
        'checkUrl' => route('practice.check', $challenge),
        'revealUrl' => route('practice.reveal', $challenge),
        'csrf' => csrf_token(),
        'solved' => $solved,
        'xp' => (int) round($challenge->xp * ($isDaily ? 1.5 : 1)),
    ];
@endphp
<style>
    .pl-editor { width: 100%; min-height: 220px; resize: vertical; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13.5px; line-height: 1.55;
        background: #0b0f16; color: #e2e8f0; border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 10px; padding: 12px 14px; tab-size: 4; }
    .pl-editor:focus { outline: none; border-color: var(--color-primary-500); box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500) 25%, transparent); }
    .pl-out { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; white-space: pre-wrap; background: #0b0f16; border-radius: 10px; padding: 10px 12px; color: #cbd5e1; max-height: 260px; overflow: auto; }
    .pl-table { width: 100%; border-collapse: collapse; font-size: 12.5px; } .pl-table th, .pl-table td { border-bottom: 1px solid rgba(148, 163, 184, 0.15); padding: 5px 8px; text-align: left; }
    .pl-table th { color: rgb(148 163 184); font-weight: 600; }
    .pl-ok { border-radius: 12px; padding: 14px 16px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.4); }
    .pl-no { border-radius: 12px; padding: 14px 16px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.35); }
    .pl-option { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(148, 163, 184, 0.2); cursor: pointer; }
    .pl-option:hover { border-color: color-mix(in srgb, var(--color-primary-500) 50%, transparent); }
    .pl-option.is-picked { border-color: var(--color-primary-500); background: color-mix(in srgb, var(--color-primary-500) 12%, transparent); }
    @keyframes pl-pop { 0% { transform: scale(.9); opacity: 0; } 60% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(1); } }
    .pl-pop { animation: pl-pop .45s ease-out; }
    @media (prefers-reduced-motion: reduce) { .pl-pop { animation: none; } }
</style>
<div class="max-w-4xl mx-auto space-y-5" x-data="practiceRunner(@js($config))">
    <a href="{{ route('practice.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">← Practice Lab</a>

    <div class="card p-6">
        <div class="flex flex-wrap items-center gap-2 mb-2">
            @if($isDaily)<span class="tx-chip">Today's challenge · +50% XP</span>@endif
            <span class="badge badge-primary">{{ $challenge->kindLabel() }}</span>
            <span class="text-dark-400 text-sm">{{ $skillLabel }} · {{ str_repeat('●', $challenge->difficulty) }}{{ str_repeat('○', 3 - $challenge->difficulty) }} · {{ $config['xp'] }} XP</span>
            @if($solved)<span class="text-green-400 text-sm font-semibold">✓ Solved</span>@endif
        </div>
        <h1 class="text-2xl font-bold text-white mb-3">{{ $challenge->title }}</h1>
        <div class="lesson-md">{{ \App\Support\LessonMarkdown::render($challenge->prompt) }}</div>
    </div>

    {{-- Quiz --}}
    @if($challenge->kind === 'quiz')
        <div class="card p-6 space-y-3">
            @foreach($challenge->options ?? [] as $i => $option)
                <label class="pl-option" :class="option === {{ $i }} ? 'is-picked' : ''">
                    <input type="radio" name="option" value="{{ $i }}" x-model.number="option" class="accent-primary-500 mt-1">
                    <span class="text-dark-200 text-sm lesson-md">{{ \App\Support\LessonMarkdown::render($option) }}</span>
                </label>
            @endforeach
            <button type="button" class="btn btn-primary" :disabled="option === null || busy" @click="checkQuiz()">Check answer</button>
        </div>
    @else
        {{-- SQL / Python --}}
        <div class="card p-6 space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-white font-semibold">{{ ['sql' => 'Your SQL query', 'javascript' => 'Your JavaScript code'][$challenge->kind] ?? 'Your Python code' }}</h3>
                <div class="flex items-center gap-2">
                    @if($challenge->kind === 'sql')
                        <button type="button" class="btn-secondary text-xs px-3 py-1.5" @click="showSchema()">Tables &amp; columns</button>
                    @endif
                    <button type="button" class="btn-secondary text-xs px-3 py-1.5" @click="code = starter">Reset</button>
                </div>
            </div>
            <template x-if="schema">
                <div class="pl-out"><template x-for="t in schema" :key="t.name"><div><span class="text-white" x-text="t.name"></span>: <span x-text="t.columns.join(', ')"></span></div></template></div>
            </template>
            @if($challenge->kind === 'python')
                <p class="text-dark-400 text-xs">Put your final result in a variable named <code>answer</code>. Use <code>print()</code> to explore.</p>
            @elseif($challenge->kind === 'javascript')
                <p class="text-dark-400 text-xs">Put your final result in a variable named <code>answer</code> (for example <code>const answer = ...</code>). Use <code>console.log()</code> to explore; <code>await fetch(...)</code> works.</p>
            @endif
            <textarea class="pl-editor" x-model="code" spellcheck="false" autocapitalize="off" autocomplete="off"
                      @keydown.tab.prevent="insertTab($event)" @keydown.ctrl.enter.prevent="run(true)" @keydown.meta.enter.prevent="run(true)"
                      aria-label="Code editor"></textarea>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="btn btn-secondary" :disabled="busy" @click="run(false)">▶ Run</button>
                <button type="button" class="btn btn-primary" :disabled="busy" @click="run(true)">Check answer</button>
                <span class="text-dark-500 text-xs">Ctrl + Enter to check</span>
                <span class="text-dark-400 text-sm" x-show="status" x-text="status"></span>
            </div>
            <template x-if="error"><div class="pl-out text-amber-300" x-text="error"></div></template>
            <template x-if="output"><div class="pl-out" x-text="output"></div></template>
            <template x-if="result && result.columns && result.columns.length">
                <div class="overflow-x-auto">
                    <p class="text-dark-500 text-xs mb-1" x-text="result.rows.length + ' row(s)' + (result.rows.length > 50 ? ', showing the first 50' : '')"></p>
                    <table class="pl-table">
                        <thead><tr><template x-for="c in result.columns" :key="c"><th x-text="c"></th></template></tr></thead>
                        <tbody><template x-for="(r, i) in result.rows.slice(0, 50)" :key="i"><tr><template x-for="(v, j) in r" :key="j"><td class="text-dark-200" x-text="v === null ? 'NULL' : v"></td></template></tr></template></tbody>
                    </table>
                </div>
            </template>
            <template x-if="preview"><div class="pl-out"><span class="text-dark-500">answer = </span><span x-text="preview"></span></div></template>
        </div>
    @endif

    {{-- Feedback --}}
    <template x-if="verdict === 'correct'">
        <div class="pl-ok pl-pop">
            <p class="text-green-300 font-semibold text-lg">🎉 Correct!<span x-show="xpGained > 0" x-text="' +' + xpGained + ' XP'"></span></p>
            <p class="text-dark-300 text-sm" x-show="xpGained === 0">Already solved before, so no extra XP, but nice work.</p>
        </div>
    </template>
    <template x-if="verdict === 'wrong'">
        <div class="pl-no">
            <p class="text-amber-300 font-semibold">Not quite yet. Try again.</p>
            <p class="text-dark-300 text-sm mt-1" x-show="hint"><span class="text-white">Hint:</span> <span x-text="hint"></span></p>
            <button type="button" class="text-primary-400 hover:text-primary-300 text-sm mt-2 underline" x-show="canReveal && !explanationHtml" @click="reveal()">Show the explanation (no XP)</button>
        </div>
    </template>
    <template x-if="explanationHtml">
        <div class="card p-6"><h3 class="text-white font-semibold mb-2">Explanation</h3><div class="lesson-md" x-html="explanationHtml"></div></div>
    </template>
    @if($explanation)
        <div class="card p-6" x-show="!explanationHtml"><h3 class="text-white font-semibold mb-2">Explanation</h3><div class="lesson-md">{{ \App\Support\LessonMarkdown::render($explanation) }}</div></div>
    @endif

    @if($next)
        <div class="flex justify-end"><a href="{{ route('practice.show', $next) }}" class="btn btn-secondary">Next challenge: {{ Str::limit($next->title, 40) }} →</a></div>
    @endif
</div>

@push('scripts')
<script src="{{ asset('course-files/practice/practice-lab.js') }}?v=1"></script>
<script>
function practiceRunner(cfg) {
    return {
        kind: cfg.kind, starter: cfg.starter, code: cfg.starter, option: null,
        busy: false, status: '', error: '', output: '', preview: '', result: null, schema: null,
        verdict: null, hint: '', xpGained: 0, canReveal: false, explanationHtml: '',
        insertTab(e) {
            const el = e.target, s = el.selectionStart;
            this.code = this.code.slice(0, s) + '    ' + this.code.slice(el.selectionEnd);
            this.$nextTick(() => { el.selectionStart = el.selectionEnd = s + 4; });
        },
        setStatus(s) { this.status = s; },
        async showSchema() {
            try { this.schema = await window.PracticeLab.sqlSchema(cfg.tables, s => this.setStatus(s)); }
            catch (e) { this.error = e.message; }
        },
        async run(check) {
            this.busy = true; this.error = ''; this.output = ''; this.preview = ''; this.result = null;
            if (check) this.verdict = null;
            try {
                let canonical = null;
                if (this.kind === 'sql') {
                    this.result = await window.PracticeLab.runSql(this.code, { tables: cfg.tables, ordered: cfg.ordered, onStatus: s => this.setStatus(s) });
                    canonical = this.result.canonical;
                } else if (this.kind === 'javascript') {
                    const r = await window.PracticeLab.runJs(this.code, { setup: cfg.setup, canonUrl: cfg.jsCanonUrl });
                    this.output = r.output || ''; this.preview = r.preview || '';
                    if (r.error) { this.error = r.error; }
                    canonical = r.canonical;
                } else {
                    const r = await window.PracticeLab.runPython(this.code, { setup: cfg.setup, needsPandas: cfg.needsPandas, files: cfg.files, canonUrl: cfg.canonUrl, onStatus: s => this.setStatus(s) });
                    this.output = r.output; this.preview = r.preview || '';
                    if (r.error) { this.error = r.error; }
                    canonical = r.canonical;
                }
                if (check && canonical !== null && !this.error) await this.submit({ canonical });
            } catch (e) {
                this.error = String(e.message || e).split('\n').slice(-3).join('\n');
            } finally {
                this.busy = false; this.status = '';
            }
        },
        async checkQuiz() {
            this.busy = true;
            try { await this.submit({ option: this.option }); } finally { this.busy = false; }
        },
        async submit(payload) {
            const res = await fetch(cfg.checkUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify(payload) });
            if (!res.ok) { this.error = res.status === 429 ? 'Too many tries in a minute. Take a short break.' : 'Could not check your answer. Try again.'; return; }
            const data = await res.json();
            this.verdict = data.correct ? 'correct' : 'wrong';
            this.hint = data.hint || '';
            this.xpGained = data.xp || 0;
            this.canReveal = data.can_reveal;
            if (data.explanation_html) this.explanationHtml = data.explanation_html;
        },
        async reveal() {
            const res = await fetch(cfg.revealUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf } });
            if (res.ok) { this.explanationHtml = (await res.json()).explanation_html; }
        },
    };
}
</script>
@endpush
@include('partials.mentor.chat', ['track' => $challenge->track, 'contextType' => 'challenge', 'contextId' => $challenge->id])
@endsection
