@extends('layouts.app')

@section('title', $lesson['title'])

@section('content')
<style>
    .bl-opt { display: block; width: 100%; text-align: left; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.22); color: rgb(226 232 240); font-size: 14px; background: rgba(11,15,22,.6); }
    .bl-opt:hover:not(:disabled) { border-color: var(--color-primary-400); }
    .bl-opt.right { border-color: #22c55e; background: rgba(34,197,94,.12); }
    .bl-opt.wrong { border-color: #f87171; background: rgba(248,113,113,.10); }
</style>
<div class="max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('base-camp.index') }}" class="text-dark-400 hover:text-white">← Base Camp</a>
        <span class="text-dark-400 text-sm">{{ $area->icon }} {{ $area->title }} · lesson {{ $position }} of {{ $count }} · {{ $lesson['minutes'] ?? 8 }} min</span>
    </div>
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-white">{{ $lesson['title'] }}</h1>
        <div class="lesson-md mt-4">{{ \App\Support\LessonMarkdown::render($lesson['body'] ?? '') }}</div>
    </div>

    @if($practice)
    <div class="card p-6" x-data="basecampPractice(@js(['questions' => $practice, 'url' => route('base-camp.submit', [$area->key, 'lesson', $lesson['key']]), 'csrf' => csrf_token()]))">
        <h2 class="text-white font-semibold text-lg">✏️ Practice</h2>
        <p class="text-dark-400 text-sm">Answer all, then check. You'll see why each answer is right.</p>
        <div class="space-y-5 mt-4">
            <template x-for="(q, qi) in questions" :key="q.key">
                <div>
                    <p class="text-white font-medium" x-text="(qi + 1) + '. ' + q.question"></p>
                    <div class="space-y-2 mt-2">
                        <template x-for="(o, oi) in q.options" :key="oi">
                            <button type="button" class="bl-opt" :disabled="checked" @click="answers[q.key] = oi"
                                    :class="{ right: checked && results[q.key]?.answer === oi, wrong: checked && answers[q.key] === oi && results[q.key]?.answer !== oi, 'border-primary-400': !checked && answers[q.key] === oi }">
                                <span class="text-dark-400 mr-2" x-text="String.fromCharCode(65 + oi) + '.'"></span><span x-text="o"></span>
                            </button>
                        </template>
                    </div>
                    <p class="text-sm mt-2" x-show="checked" :class="results[q.key]?.correct ? 'text-green-400' : 'text-amber-300'" x-text="(results[q.key]?.correct ? '✅ ' : '❌ ') + (results[q.key]?.explain || '')"></p>
                </div>
            </template>
        </div>
        <p class="text-amber-300 text-sm mt-3" x-show="error" x-text="error"></p>
        <div class="flex items-center gap-3 mt-5">
            <button type="button" class="btn-primary" x-show="!checked" :disabled="busy || questions.some(q => answers[q.key] === undefined)" @click="check()">Check my answers</button>
            <p class="text-white font-semibold" x-show="checked" x-text="score + ' of ' + questions.length + ' right'"></p>
            <button type="button" class="btn-secondary text-sm" x-show="checked" @click="checked = false; answers = {}; results = {}">Try again</button>
        </div>
    </div>
    @endif

    <div class="flex justify-between">
        <a href="{{ route('base-camp.index') }}" class="btn-secondary">Back to Base Camp</a>
        @if($next)
            <a href="{{ route('base-camp.lesson', [$area->key, $next['key']]) }}" class="btn-primary">Next: {{ $next['title'] }} →</a>
        @else
            <a href="{{ route('base-camp.test', $area->key) }}" class="btn-primary">Take the module test →</a>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function basecampPractice(cfg) {
    return {
        questions: cfg.questions, answers: {}, results: {}, checked: false, busy: false, error: '', score: 0,
        async check() {
            this.busy = true; this.error = '';
            try {
                const res = await fetch(cfg.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ answers: this.answers }) });
                const d = await res.json();
                if (!res.ok) throw new Error();
                this.results = d.results; this.score = d.score; this.checked = true;
            } catch (e) { this.error = 'Could not check right now. Try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endpush
