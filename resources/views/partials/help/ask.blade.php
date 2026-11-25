{{--
    "Ask a human". Inline form, or a modal opened from anywhere with:
    $dispatch('ask-human', { question: '...', activity_id: '...', step_key: '...' })
    @param bool $inline
--}}
@php $inline = $inline ?? false; @endphp
@once
@push('scripts')
<script>
function askHuman(cfg) {
    return {
        open: cfg.inline, question: '', activityId: null, stepKey: null, busy: false, sent: '', error: '',
        show(d) { this.question = (d && d.question) || ''; this.activityId = (d && d.activity_id) || null; this.stepKey = (d && d.step_key) || null; this.sent = ''; this.error = ''; this.open = true;
            this.$nextTick(() => this.$refs.q && this.$refs.q.focus()); },
        async send() {
            this.busy = true; this.error = '';
            try {
                const res = await fetch(cfg.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                    body: JSON.stringify({ question: this.question, activity_id: this.activityId, step_key: this.stepKey }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.sent = d.message; this.question = ''; if (cfg.inline) setTimeout(() => window.location.reload(), 900); }
                else this.error = d.error || (d.errors ? Object.values(d.errors).flat()[0] : 'Could not send. Try again.');
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endpush
@endonce
<div x-data="askHuman(@js(['url' => route('help.store'), 'csrf' => csrf_token(), 'inline' => $inline]))" @ask-human.window="show($event.detail)">
    <div x-show="open" x-cloak @keydown.escape.window="if (!{{ $inline ? 'true' : 'false' }}) open = false"
         class="{{ $inline ? 'mt-4' : 'fixed inset-0 flex items-center justify-center p-4' }}" style="{{ $inline ? '' : 'z-index: 85; background: rgba(3,7,18,.75);' }}">
        <div class="{{ $inline ? '' : 'card p-6 w-full max-w-lg' }}" @click.stop>
            @unless($inline)
                <div class="flex items-center justify-between">
                    <h3 class="text-white font-semibold text-lg">🙋 Ask a human</h3>
                    <button type="button" class="text-dark-400 hover:text-white text-xl" @click="open = false" aria-label="Close">×</button>
                </div>
                <p class="text-dark-400 text-sm mt-1">A person from the I-NNOVA team will answer, usually within a day. You'll get a notification.</p>
            @endunless
            <textarea x-ref="q" x-model="question" rows="5" maxlength="2000" class="w-full mt-3 bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500"
                      placeholder="What are you trying to do? What did you try? What happened (copy any error message)?"></textarea>
            <p class="text-amber-300 text-sm mt-2" x-show="error" x-text="error"></p>
            <p class="text-green-400 text-sm mt-2" x-show="sent" x-text="sent"></p>
            <div class="flex items-center justify-between gap-3 mt-3">
                <a href="{{ route('help.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">My questions and answers →</a>
                <button type="button" class="btn-primary" :disabled="busy || question.trim().length < 15" @click="send()" x-text="busy ? 'Sending...' : 'Send to the team'"></button>
            </div>
        </div>
    </div>
</div>
