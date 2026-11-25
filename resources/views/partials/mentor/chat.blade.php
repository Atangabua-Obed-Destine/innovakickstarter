{{--
    AI mentor chat.
    @param \App\Models\Track $track
    @param string $contextType  general | activity | challenge
    @param string|null $contextId
    @param bool $inline  render as a page section instead of a floating panel
--}}
@php
    $persona = app(\App\Services\MentorService::class)->persona($track);
    $inline = $inline ?? false;
    $mentorConfig = [
        'historyUrl' => route('ai-mentor.history', ['context_type' => $contextType, 'context_id' => $contextId]),
        'askUrl' => route('ai-mentor.ask'),
        'contextType' => $contextType,
        'contextId' => $contextId,
        'csrf' => csrf_token(),
        'inline' => $inline,
    ];
@endphp
@include('partials.track.styles')
<style>
    .mc-fab { position: fixed; right: 18px; bottom: 84px; z-index: 60; display: flex; align-items: center; gap: 8px; padding: 10px 16px 10px 10px; border-radius: 999px;
        background: var(--color-primary-600); color: #fff; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,.35); }
    @media (min-width: 1024px) { .mc-fab { bottom: 24px; } }
    .mc-panel { position: fixed; right: 16px; bottom: 16px; z-index: 70; width: min(420px, calc(100vw - 24px)); height: min(620px, calc(100vh - 90px));
        display: flex; flex-direction: column; border-radius: 16px; overflow: hidden; background: #0f141d; border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent);
        box-shadow: 0 24px 60px rgba(0,0,0,.5); }
    .mc-inline { position: static; width: 100%; height: 640px; box-shadow: none; }
    .mc-head { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-bottom: 1px solid rgba(148,163,184,.15);
        background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 35%, #0f141d), #0f141d); }
    .mc-body { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
    .mc-msg { max-width: 88%; padding: 9px 12px; border-radius: 14px; font-size: 14px; line-height: 1.5; }
    .mc-user { align-self: flex-end; background: var(--color-primary-600); color: #fff; border-bottom-right-radius: 4px; white-space: pre-wrap; }
    .mc-bot { align-self: flex-start; background: #1a212d; color: rgb(226 232 240); border-bottom-left-radius: 4px; }
    .mc-bot .lesson-md { font-size: 14px; color: rgb(226 232 240); }
    .mc-bot pre { background: #0b0f16; border-radius: 8px; padding: 8px 10px; overflow-x: auto; font-size: 12.5px; }
    .mc-foot { padding: 10px; border-top: 1px solid rgba(148,163,184,.15); }
    .mc-input { width: 100%; resize: none; background: #0b0f16; color: #e2e8f0; border: 1px solid rgba(148,163,184,.25); border-radius: 10px; padding: 9px 11px; font-size: 14px; }
    .mc-input:focus { outline: none; border-color: var(--color-primary-500); }
    .mc-dots span { display: inline-block; width: 6px; height: 6px; margin-right: 3px; border-radius: 50%; background: rgb(148 163 184); animation: mc-b 1s infinite ease-in-out; }
    .mc-dots span:nth-child(2) { animation-delay: .15s; } .mc-dots span:nth-child(3) { animation-delay: .3s; }
    @keyframes mc-b { 0%, 80%, 100% { opacity: .3; } 40% { opacity: 1; } }
</style>
<div x-data="mentorChat(@js($mentorConfig))" x-init="if (cfg.inline) openChat()" @mentor-ask.window="ask($event.detail.message)">
    @unless($inline)
        <button type="button" class="mc-fab" x-show="!open" @click="openChat()" aria-label="Ask {{ $persona['name'] }}">
            <span class="tx-avatar" style="width:32px;height:32px;font-size:12px;">{{ $persona['initials'] }}</span>
            Ask {{ $persona['short'] }}
        </button>
    @endunless
    <div class="mc-panel {{ $inline ? 'mc-inline' : '' }}" x-show="open" x-cloak @keydown.escape.window="if (!cfg.inline) open = false" role="dialog" aria-label="Mentor chat">
        <div class="mc-head">
            <div class="tx-avatar" aria-hidden="true">{{ $persona['initials'] }}</div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-sm">{{ $persona['name'] }}</p>
                <p class="text-dark-400 text-xs truncate">{{ $persona['title'] }} · AI mentor</p>
            </div>
            @unless($inline)
                <button type="button" class="text-dark-400 hover:text-white text-xl leading-none px-2" @click="open = false" aria-label="Close">×</button>
            @endunless
        </div>
        <div class="mc-body" x-ref="body">
            <div class="mc-msg mc-bot">
                <div class="lesson-md"><p>Hi! I'm {{ $persona['name'] }}. Ask me about {{ $contextType === 'activity' ? 'this activity' : ($contextType === 'challenge' ? 'this challenge' : 'anything you are learning') }}: concepts, error messages, where to start. I'll give you hints and good questions, not the answers.</p></div>
            </div>
            <template x-for="(m, i) in messages" :key="i">
                <div class="mc-msg" :class="m.role === 'user' ? 'mc-user' : 'mc-bot'">
                    <template x-if="m.role === 'user'"><span x-text="m.text"></span></template>
                    <template x-if="m.role !== 'user'"><div class="lesson-md" x-html="m.html"></div></template>
                </div>
            </template>
            <div class="mc-msg mc-bot" x-show="thinking"><span class="mc-dots"><span></span><span></span><span></span></span></div>
            <p class="text-amber-300 text-xs" x-show="error" x-text="error"></p>
        </div>
        <form class="mc-foot" @submit.prevent="send()">
            <textarea class="mc-input" rows="2" x-model="draft" maxlength="1500" placeholder="Ask a question... (Enter to send, Shift+Enter for a new line)"
                      @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); send(); }" :disabled="thinking"></textarea>
            <div class="flex items-center justify-between mt-1">
                <span class="text-dark-500" style="font-size:11px;"><span x-text="remaining !== null ? remaining + ' left today · ' : ''"></span><button type="button" class="text-primary-400 hover:text-primary-300" @click="$dispatch('ask-human', { question: draft, activity_id: cfg.contextType === 'activity' ? cfg.contextId : null })">🙋 Ask a human instead</button></span>
                <button type="submit" class="btn btn-primary btn-sm" :disabled="thinking || draft.trim().length < 2">Send</button>
            </div>
        </form>
    </div>
</div>
@include('partials.help.ask')
@once
@push('scripts')
<script>
function mentorChat(cfg) {
    return {
        cfg, open: false, loaded: false, messages: [], draft: '', thinking: false, error: '', remaining: null,
        async openChat() {
            this.open = true;
            if (!this.loaded) {
                this.loaded = true;
                try {
                    const res = await fetch(cfg.historyUrl, { headers: { 'Accept': 'application/json' } });
                    if (res.ok) { const d = await res.json(); this.messages = d.messages; this.remaining = d.remaining; }
                } catch (e) { /* offline: the chat still opens */ }
                this.scroll();
            }
        },
        scroll() { this.$nextTick(() => { const b = this.$refs.body; if (b) b.scrollTop = b.scrollHeight; }); },
        /** Ask a prepared question (e.g. from a lesson's "Stuck?" buttons). */
        async ask(text) {
            if (!text || this.thinking) return;
            await this.openChat();
            this.draft = String(text).slice(0, 1500);
            this.send();
        },
        async send() {
            const text = this.draft.trim();
            if (text.length < 2 || this.thinking) return;
            this.messages.push({ role: 'user', text }); this.draft = ''; this.error = ''; this.thinking = true; this.scroll();
            try {
                const res = await fetch(cfg.askUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
                    body: JSON.stringify({ message: text, context_type: cfg.contextType, context_id: cfg.contextId }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) { this.messages.push({ role: 'assistant', html: d.html }); }
                else { this.error = d.error || (res.status === 429 ? 'Too many questions in a minute. Take a breath and try again.' : 'Something went wrong. Try again.'); }
                if (typeof d.remaining === 'number') this.remaining = d.remaining;
            } catch (e) { this.error = 'No connection. Check your internet and try again.'; }
            this.thinking = false; this.scroll();
        },
    };
}
</script>
@endpush
@endonce
