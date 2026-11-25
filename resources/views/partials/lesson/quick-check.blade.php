{{--
    One lesson quick check (a flash card linked to the lesson). Answer checked on the server.
    @param array $check  from FlashReviewService::quickChecks()
--}}
@once
<style>
    .qc { padding: 14px 16px; border-radius: 12px; border: 1px dashed color-mix(in srgb, var(--color-primary-400) 55%, transparent);
        background: color-mix(in srgb, var(--color-primary-600) 8%, #0f141d); }
    .qc-opt { display: block; width: 100%; text-align: left; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.22); color: rgb(226 232 240); font-size: 14px; background: rgba(11,15,22,.6); transition: all .15s; }
    .qc-opt:hover:not(:disabled) { border-color: var(--color-primary-400); }
    .qc-opt.right { border-color: #22c55e; background: rgba(34,197,94,.12); }
    .qc-opt.wrong { border-color: #f87171; background: rgba(248,113,113,.10); }
    .qc-opt:disabled { cursor: default; }
</style>
@endonce
<div class="qc" x-data="quickCheck(@js($check + ['csrf' => csrf_token()]))">
    <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider">⚡ Quick check</p>
    <div class="lesson-md text-white font-medium mt-1">{{ \App\Support\LessonMarkdown::render($check['question']) }}</div>
    <div class="space-y-2 mt-3">
        <template x-for="(opt, i) in options" :key="i">
            <button type="button" class="qc-opt" :disabled="answerIndex !== null || busy" @click="pick(i)"
                    :class="{ right: answerIndex !== null && i === answerIndex, wrong: answerIndex !== null && i === chosen && i !== answerIndex }">
                <span class="text-dark-400 mr-2" x-text="String.fromCharCode(65 + i) + '.'"></span><span x-text="opt"></span>
            </button>
        </template>
    </div>
    <template x-if="answerIndex !== null">
        <div class="mt-3 text-sm">
            <p :class="correct ? 'text-green-400' : 'text-amber-300'" class="font-semibold"
               x-text="correct ? (xp ? '✅ Right! +' + xp + ' XP' : '✅ Right!') : (firstTime ? '❌ Not quite. Here\'s why:' : 'You answered this before. The right answer is highlighted.')"></p>
            <p class="text-dark-200 mt-1" x-text="explanation"></p>
            <p class="text-dark-500 text-xs mt-1">This question will come back in your daily review, so it sticks.</p>
        </div>
    </template>
    <p class="text-amber-300 text-sm mt-2" x-show="error" x-text="error" x-cloak></p>
</div>
@once
@push('scripts')
<script>
function quickCheck(c) {
    return {
        options: c.options, busy: false, error: '', xp: 0, chosen: null, firstTime: !c.answered,
        answerIndex: c.answered ? c.answer_index : null, correct: c.answered ? c.first_try_correct : false, explanation: c.explanation || '',
        async pick(i) {
            this.busy = true; this.error = ''; this.chosen = i;
            try {
                const res = await fetch(c.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': c.csrf }, body: JSON.stringify({ option: i }) });
                const d = await res.json();
                if (!res.ok) throw new Error(d.message || 'error');
                this.correct = d.correct; this.answerIndex = d.answer_index; this.explanation = d.explanation || ''; this.xp = d.xp || 0;
            } catch (e) { this.error = 'Could not check that answer. Try again.'; this.chosen = null; }
            this.busy = false;
        },
    };
}
</script>
@endpush
@endonce
