@extends('layouts.app')

@section('title', 'Daily Review')

@section('content')
@include('partials.track.styles')
<style>
    .fr-card { border-radius: 18px; padding: 24px; background: #0f141d; border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent); }
    .fr-opt { display: flex; gap: 10px; align-items: flex-start; width: 100%; text-align: left; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.22); background: rgba(15, 20, 29, 0.6); }
    .fr-opt:hover:not(:disabled) { border-color: color-mix(in srgb, var(--color-primary-500) 55%, transparent); }
    .fr-opt.is-right { border-color: rgba(16, 185, 129, .7); background: rgba(16, 185, 129, .12); }
    .fr-opt.is-wrong { border-color: rgba(239, 68, 68, .7); background: rgba(239, 68, 68, .1); }
    .fr-progress { display: flex; gap: 6px; } .fr-progress span { flex: 1; height: 5px; border-radius: 999px; background: rgba(148, 163, 184, .2); }
    .fr-progress span.is-done { background: var(--color-primary-500); }
</style>
<div class="max-w-2xl mx-auto space-y-5" x-data="flashReview(@js($cards), {{ $answeredToday }})">
    <div>
        <h1 class="text-2xl font-bold text-white">Daily review</h1>
        <p class="text-dark-400 mt-1">Three quick questions from chapters you've already reached. Cards you get right come back later and later; cards you miss come back tomorrow. That spacing is what makes learning last.</p>
    </div>

    <template x-if="cards.length && index < cards.length">
        <div class="space-y-4">
            <div class="fr-progress"><template x-for="(c, i) in cards" :key="i"><span :class="i < index || (i === index && result) ? 'is-done' : ''"></span></template></div>
            <div class="fr-card">
                <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider" x-text="'Chapter ' + card().chapter + ' · question ' + (index + 1) + ' of ' + cards.length"></p>
                <div class="lesson-md text-white mt-2" style="font-size: 1rem;" x-html="card().question_html"></div>
                <div class="space-y-2 mt-4">
                    <template x-for="(opt, i) in card().options_html" :key="i">
                        <button type="button" class="fr-opt" :disabled="!!result || busy" @click="answer(i)"
                                :class="result ? (i === result.answer_index ? 'is-right' : (i === picked ? 'is-wrong' : '')) : ''">
                            <span class="text-dark-400 text-sm" x-text="String.fromCharCode(65 + i) + '.'"></span>
                            <span class="lesson-md text-sm" x-html="opt"></span>
                        </button>
                    </template>
                </div>
                <template x-if="result">
                    <div class="mt-4">
                        <p class="font-semibold" :class="result.correct ? 'text-green-400' : 'text-amber-300'"
                           x-text="result.correct ? ('Right!' + (result.xp ? ' +' + result.xp + ' XP' : '') + ' See you again in ' + days(result.next_due) + '.') : 'Not this time. It will come back tomorrow.'"></p>
                        <div class="lesson-md text-sm mt-2" x-show="result.explanation_html" x-html="result.explanation_html"></div>
                        <button type="button" class="btn btn-primary mt-4" @click="next()" x-text="index + 1 < cards.length ? 'Next question' : 'Finish'"></button>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <template x-if="!cards.length || index >= cards.length">
        <div class="fr-card text-center">
            <p class="text-4xl">✅</p>
            <p class="text-white text-xl font-semibold mt-2">Review done for today</p>
            <p class="text-dark-400 mt-1" x-show="score !== null" x-text="score + ' of ' + cards.length + ' right this session.'"></p>
            <p class="text-dark-400 mt-1" x-show="!cards.length && done === 0">No cards to review yet: they unlock as you reach new chapters.</p>
            <p class="text-dark-400 mt-1">🔥 Practice streak: {{ $stats['streak'] }} {{ Str::plural('day', $stats['streak']) }}. Come back tomorrow for the next three.</p>
            <div class="flex flex-wrap justify-center gap-2 mt-4">
                <a href="{{ route('practice.index') }}" class="btn btn-primary">Go to the Practice Lab</a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to dashboard</a>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function flashReview(cards, done) {
    return {
        cards, done, index: 0, result: null, picked: null, busy: false, score: null, right: 0,
        card() { return this.cards[this.index]; },
        days(date) { const d = Math.round((new Date(date) - new Date(new Date().toDateString())) / 86400000); return d === 1 ? '1 day' : d + ' days'; },
        async answer(i) {
            this.busy = true; this.picked = i;
            try {
                const res = await fetch(this.card().answer_url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ option: i }) });
                if (res.ok) { this.result = await res.json(); if (this.result.correct) this.right++; }
            } finally { this.busy = false; }
        },
        next() {
            this.index++; this.result = null; this.picked = null;
            if (this.index >= this.cards.length) this.score = this.right;
        },
    };
}
</script>
@endpush
@endsection
