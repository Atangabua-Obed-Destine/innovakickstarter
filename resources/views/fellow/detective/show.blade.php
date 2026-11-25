@extends('layouts.app')

@section('title', $case->title . ' · Data Detective')

@section('content')
@include('partials.track.styles')
@php
    $config = [
        'tables' => $practiceConfig['sql_tables'] ?? [],
        'files' => $practiceConfig['python_files'] ?? [],
        'canonUrl' => asset('course-files/practice/practice_canon.py'),
        'answerUrl' => route('detective.answer', $case),
        'revealUrl' => route('detective.reveal', $case),
        'csrf' => csrf_token(),
        'correct' => $correctKeys->values(),
        'solved' => $solved,
    ];
@endphp
<style>
    .dd-story { border-radius: 18px; padding: 22px; background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 28%, #0f141d), #0f141d);
        border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent); }
    .dd-story blockquote { border-left: 3px solid var(--color-primary-400); padding-left: 12px; color: rgb(203 213 225); font-style: italic; }
    .pl-editor { width: 100%; min-height: 150px; resize: vertical; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; line-height: 1.5;
        background: #0b0f16; color: #e2e8f0; border: 1px solid rgba(148, 163, 184, 0.25); border-radius: 10px; padding: 10px 12px; }
    .pl-out { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; white-space: pre-wrap; background: #0b0f16; border-radius: 10px; padding: 10px; color: #cbd5e1; max-height: 240px; overflow: auto; }
    .pl-table { width: 100%; border-collapse: collapse; font-size: 12px; } .pl-table th, .pl-table td { border-bottom: 1px solid rgba(148,163,184,.15); padding: 4px 8px; text-align: left; }
    .dd-q { padding: 16px; border-radius: 14px; background: rgba(15, 20, 29, 0.6); border: 1px solid rgba(148, 163, 184, 0.15); }
    .dd-q.is-done { border-color: rgba(16, 185, 129, .5); }
    .dd-opt { display: flex; gap: 8px; align-items: flex-start; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.2); cursor: pointer; }
    .dd-opt.is-picked { border-color: var(--color-primary-500); background: color-mix(in srgb, var(--color-primary-500) 12%, transparent); }
</style>
<div class="max-w-5xl mx-auto space-y-5" x-data="detectiveCase(@js($config))">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('practice.index') }}" class="text-dark-400 hover:text-white">← Practice Lab</a>
        <div class="flex flex-wrap items-center gap-2">
            <span class="tx-chip">{{ $isWeekly ? "🕵️ This week's case" : '🗂️ Case archive' }}</span>
            <span class="text-dark-400 text-sm">Difficulty {{ str_repeat('●', min(4, $case->difficulty)) }} · {{ $xpNow }} XP{{ $isWeekly ? '' : ' (half XP outside its week)' }}</span>
        </div>
    </div>

    <div class="dd-story">
        <h1 class="text-2xl font-bold text-white mb-3">{{ $case->title }}</h1>
        <div class="lesson-md">{{ \App\Support\LessonMarkdown::render($case->story) }}</div>
        @if($case->data_note)
            <div class="lesson-md text-sm mt-3"><strong>Evidence available:</strong> {{ \App\Support\LessonMarkdown::render($case->data_note) }}</div>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Sandbox --}}
        <div class="card p-5 space-y-3 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-white font-semibold">🔍 Investigate</h3>
                <div class="flex gap-2">
                    <button type="button" class="btn-secondary text-xs px-3 py-1.5" :class="lang === 'sql' ? 'is-picked' : ''" @click="switchTo('sql')">SQL</button>
                    <button type="button" class="btn-secondary text-xs px-3 py-1.5" @click="switchTo('python')">Python (pandas)</button>
                </div>
            </div>
            <p class="text-dark-500 text-xs" x-show="lang === 'sql'">Tables: <code>sales</code> (Jan-Jun 2026, four branches) and <code>branches</code>. About 1 MB to load, once.</p>
            <p class="text-dark-500 text-xs" x-show="lang === 'python'" x-cloak>Files in <code>/data/</code>: nkwen_traders_ch2_sales.csv, nkwen_traders_branches.csv, nkwen_traders_sales.csv. First run downloads about 22 MB, once.</p>
            <textarea class="pl-editor" x-model="code" spellcheck="false" @keydown.ctrl.enter.prevent="run()" @keydown.tab.prevent="code += '    '"></textarea>
            <div class="flex items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm" :disabled="busy" @click="run()">▶ Run</button>
                <span class="text-dark-400 text-sm" x-text="status"></span>
            </div>
            <template x-if="error"><div class="pl-out text-amber-300" x-text="error"></div></template>
            <template x-if="output"><div class="pl-out" x-text="output"></div></template>
            <template x-if="result && result.columns && result.columns.length">
                <div class="overflow-x-auto">
                    <table class="pl-table">
                        <thead><tr><template x-for="c in result.columns" :key="c"><th class="text-dark-400" x-text="c"></th></template></tr></thead>
                        <tbody><template x-for="(r, i) in result.rows.slice(0, 40)" :key="i"><tr><template x-for="(v, j) in r" :key="j"><td class="text-dark-200" x-text="v === null ? 'NULL' : v"></td></template></tr></template></tbody>
                    </table>
                </div>
            </template>
            @if(!empty($case->hints))
                <div x-data="{ shown: 0 }" class="pt-2">
                    <template x-for="(h, i) in @js($case->hints)" :key="i"><p class="text-dark-300 text-sm" x-show="i < shown">💡 <span x-text="h"></span></p></template>
                    <button type="button" class="text-primary-400 hover:text-primary-300 text-sm underline" x-show="shown < {{ count($case->hints) }}" @click="shown++">Need a clue?</button>
                </div>
            @endif
        </div>

        {{-- Questions --}}
        <div class="space-y-3">
            <h3 class="text-white font-semibold">📝 Your findings</h3>
            @foreach($case->questions as $q)
                <div class="dd-q" :class="correct.includes('{{ $q['key'] }}') ? 'is-done' : ''" x-data="{ value: '', state: null }">
                    <div class="lesson-md text-sm">{{ \App\Support\LessonMarkdown::render($q['prompt']) }}</div>
                    <template x-if="correct.includes('{{ $q['key'] }}')"><p class="text-green-400 text-sm font-semibold mt-2">✓ Solved</p></template>
                    <div x-show="!correct.includes('{{ $q['key'] }}')" class="mt-2 space-y-2">
                        @if($q['kind'] === 'number')
                            <div class="flex items-center gap-2">
                                <input type="text" inputmode="decimal" x-model="value" placeholder="Your number" class="flex-1 bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500">
                                @if(!empty($q['unit']))<span class="text-dark-400 text-sm">{{ $q['unit'] }}</span>@endif
                            </div>
                        @else
                            @foreach($q['options'] as $i => $option)
                                <label class="dd-opt" :class="value === '{{ $i }}' ? 'is-picked' : ''">
                                    <input type="radio" value="{{ $i }}" x-model="value" class="accent-primary-500 mt-1">
                                    <span class="text-dark-200 text-sm">{{ $option }}</span>
                                </label>
                            @endforeach
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" :disabled="value === '' || busy" @click="state = await submit('{{ $q['key'] }}', value)">Check</button>
                        <p class="text-amber-300 text-sm" x-show="state === false">Not quite. Look at the evidence again.</p>
                    </div>
                </div>
            @endforeach
            <template x-if="xpGained > 0"><div class="dd-q is-done text-center"><p class="text-2xl">🕵️</p><p class="text-green-300 font-semibold" x-text="'Case closed! +' + xpGained + ' XP'"></p></div></template>
        </div>
    </div>

    <template x-if="solutionHtml">
        <div class="card p-6"><h3 class="text-white font-semibold mb-2">The solution</h3><div class="lesson-md" x-html="solutionHtml"></div></div>
    </template>
    @if(!$isWeekly && !$solutionHtml)
        <div class="text-right"><button type="button" class="text-primary-400 hover:text-primary-300 text-sm underline" x-show="!solutionHtml" @click="reveal()">Show the solution (no XP afterwards; try an answer first)</button></div>
    @endif

    <div class="grid md:grid-cols-2 gap-5">
        <div class="card p-5">
            <h3 class="text-white font-semibold mb-2">Who cracked this week's case</h3>
            @forelse($solvers as $i => $s)
                <p class="text-dark-200 text-sm py-1">{{ ['🥇', '🥈', '🥉'][$i] ?? ($i + 1) . '.' }} {{ $s['name'] }} <span class="text-dark-500">· {{ $s['at']->diffForHumans() }}</span></p>
            @empty
                <p class="text-dark-400 text-sm">Nobody yet. Be the first detective!</p>
            @endforelse
        </div>
        <div class="card p-5">
            <h3 class="text-white font-semibold mb-2">Other cases</h3>
            @foreach($others as $o)
                <a href="{{ route('detective.show', $o['case']) }}" class="flex items-center justify-between py-1.5 text-sm hover:text-white">
                    <span class="text-dark-200">{{ $o['solved'] ? '✅' : ($o['weekly'] ? '🕵️' : '🗂️') }} {{ $o['case']->title }}</span>
                    <span class="text-dark-500">{{ $o['weekly'] ? 'this week' : 'archive' }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('course-files/practice/practice-lab.js') }}?v=1"></script>
<script>
function detectiveCase(cfg) {
    const starters = { sql: "-- Investigate here. Example:\nSELECT Branch, COUNT(*) AS sales, SUM(TotalSale_FCFA) AS revenue\nFROM sales\nGROUP BY Branch;", python: "import pandas as pd\nsales = pd.read_csv('/data/nkwen_traders_ch2_sales.csv')\nprint(sales.head())" };
    return {
        lang: 'sql', code: starters.sql, busy: false, status: '', error: '', output: '', result: null,
        correct: cfg.correct, xpGained: 0, solutionHtml: @js($solutionHtml),
        switchTo(l) { this.lang = l; this.code = starters[l]; this.error = ''; this.output = ''; this.result = null; },
        async run() {
            this.busy = true; this.error = ''; this.output = ''; this.result = null;
            try {
                if (this.lang === 'sql') {
                    this.result = await window.PracticeLab.runSql(this.code, { tables: cfg.tables, ordered: true, onStatus: s => this.status = s });
                } else {
                    const r = await window.PracticeLab.runPython(this.code + '\nanswer = None', { setup: '', needsPandas: true, files: cfg.files, canonUrl: cfg.canonUrl, onStatus: s => this.status = s });
                    this.output = r.output; if (r.error) this.error = r.error;
                }
            } catch (e) { this.error = String(e.message || e).split('\n').slice(-3).join('\n'); }
            this.busy = false; this.status = '';
        },
        async submit(key, value) {
            this.busy = true;
            try {
                const res = await fetch(cfg.answerUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ key, value }) });
                const d = await res.json();
                if (d.correct && !this.correct.includes(key)) this.correct.push(key);
                if (d.xp) this.xpGained = d.xp;
                if (d.solution_html) this.solutionHtml = d.solution_html;
                return d.correct;
            } finally { this.busy = false; }
        },
        async reveal() {
            const res = await fetch(cfg.revealUrl, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf } });
            if (res.ok) this.solutionHtml = (await res.json()).solution_html; else alert('Try at least one answer first.');
        },
    };
}
</script>
@endpush
@include('partials.mentor.chat', ['track' => $case->track, 'contextType' => 'general', 'contextId' => null])
@endsection
