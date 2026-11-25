@extends('layouts.app')

@section('title', 'Module test: ' . $area->title)

@section('content')
<style>
    .bt-opt { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.2); cursor: pointer; color: rgb(226 232 240); font-size: 14px; }
    .bt-opt.picked { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 14%, transparent); }
    .bt-passage { white-space: pre-line; padding: 14px 16px; border-radius: 12px; background: #fdfcf7; color: #1f2937; line-height: 1.6; }
</style>
<div class="max-w-3xl mx-auto space-y-5" x-data="basecampTest(@js(['questions' => $questions, 'url' => route('base-camp.submit', [$area->key, 'test']), 'csrf' => csrf_token()]))">
    <a href="{{ route('base-camp.index') }}" class="text-dark-400 hover:text-white">← Base Camp</a>
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-white">{{ $area->icon }} Module test: {{ $area->title }}</h1>
        <p class="text-dark-400 text-sm mt-1">{{ count($questions) }} questions. Pass with {{ max(80, $targets['pass']) }}% to earn the badge. You can retake it as often as you need.</p>
        @if($passage)<div class="bt-passage mt-4">{{ $passage }}</div>@endif

        <div class="space-y-5 mt-5" x-show="!result">
            <template x-for="(q, qi) in questions" :key="q.key">
                <div>
                    <p class="text-white font-medium" x-text="(qi + 1) + '. ' + q.question"></p>
                    <div class="space-y-2 mt-2">
                        <template x-for="(o, oi) in q.options" :key="oi">
                            <label class="bt-opt" :class="answers[q.key] === oi && 'picked'">
                                <input type="radio" class="mt-1 accent-primary-500" :name="q.key" @change="answers[q.key] = oi">
                                <span x-text="o"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
            <p class="text-amber-300 text-sm" x-show="error" x-text="error"></p>
            <button type="button" class="btn-primary" :disabled="busy || questions.some(q => answers[q.key] === undefined)" @click="submit()" x-text="busy ? 'Checking...' : 'Submit the test'"></button>
        </div>

        <template x-if="result">
            <div class="mt-6 text-center">
                <p class="text-6xl" x-text="result.passed ? '🏅' : '📚'"></p>
                <p class="text-3xl font-bold text-white mt-2" x-text="result.score + ' / ' + result.total + ' (' + Math.round(result.percent) + '%)'"></p>
                <p class="mt-2" :class="result.passed ? 'text-green-400' : 'text-amber-300'" x-text="result.passed ? 'Passed! Badge earned: this area is ready.' : 'Not yet. Review the lessons, then try again: you are closer than you think.'"></p>
                <div class="text-left max-w-md mx-auto mt-4" x-show="!result.passed">
                    <p class="text-dark-300 text-sm mb-1">Questions to review:</p>
                    <ul class="text-dark-400 text-sm list-disc list-inside">
                        <template x-for="(q, qi) in questions" :key="q.key"><li x-show="!result.results[q.key]?.correct" x-text="'Question ' + (qi + 1)"></li></template>
                    </ul>
                </div>
                <div class="flex justify-center gap-3 mt-5">
                    <a href="{{ route('base-camp.index') }}" class="btn-primary">Back to Base Camp</a>
                    <button type="button" class="btn-secondary" x-show="!result.passed" @click="result = null; answers = {}; window.scrollTo(0, 0)">Try again</button>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function basecampTest(cfg) {
    return {
        questions: cfg.questions, answers: {}, result: null, busy: false, error: '',
        async submit() {
            this.busy = true; this.error = '';
            try {
                const res = await fetch(cfg.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ answers: this.answers }) });
                const d = await res.json();
                if (!res.ok) throw new Error();
                this.result = d; window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) { this.error = 'Could not submit right now. Try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endpush
