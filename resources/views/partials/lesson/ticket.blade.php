{{--
    Work ticket: projects and case studies are handed out the way teams do at work, with a
    stakeholder the fellow can ask clarifying questions (AI in character, never the solution).
    @param \App\Models\TrackCurriculumActivity $activity
    @param \App\Models\FellowCurriculumProgress|null $progress
    @param array $stakeholder  name, title, initials, company
--}}
@php
    $priority = $activity->type?->value === 'project' ? 'High' : 'Medium';
    $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) ($stakeholder['company'] ?? 'IKS')), 0, 3)) . '-' . (100 + (int) ($activity->milestone?->sequence_order ?? 0) * 10 + (int) $activity->sequence_order);
@endphp
<style>
    .tk { border-radius: 16px; background: #0b1220; border: 1px solid rgba(148,163,184,.2); overflow: hidden; }
    .tk-head { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; padding: 12px 18px; background: rgba(148,163,184,.07); border-bottom: 1px solid rgba(148,163,184,.15); font-size: 13px; color: rgb(203 213 225); }
    .tk-chip { padding: 2px 8px; border-radius: 6px; font-size: 12px; font-weight: 700; }
    .tk-msg { max-width: 88%; padding: 8px 12px; border-radius: 12px; font-size: 14px; }
    .tk-me { align-self: flex-end; background: var(--color-primary-600); color: #fff; }
    .tk-them { align-self: flex-start; background: #1a212d; color: rgb(226 232 240); }
</style>
<div class="tk" x-data="stakeholderChat(@js(['url' => route('curriculum.activity.stakeholder', $activity), 'csrf' => csrf_token()]))">
    <div class="tk-head">
        <span class="font-mono text-white font-bold">🎫 {{ $code }}</span>
        <span>From: <strong class="text-white">{{ $stakeholder['name'] }}</strong>, {{ $stakeholder['title'] }}</span>
        <span class="tk-chip {{ $priority === 'High' ? 'bg-red-500/20 text-red-300' : 'bg-amber-500/20 text-amber-300' }}">{{ $priority }} priority</span>
        @if($progress?->deadline_at)<span>Due {{ $progress->deadline_at->format('D j M') }}</span>@endif
        <span class="text-dark-400">This is how real teams hand out work.</span>
    </div>
    <div class="p-5">
        <p class="text-dark-200 text-sm">At work, tasks are rarely 100% clear. Good analysts ask the stakeholder short, specific questions <em>before</em> building: who will use this, what decision it supports, what "done" looks like. Practise it here.</p>
        <div class="flex flex-col gap-2 mt-3" x-show="messages.length">
            <template x-for="(m, i) in messages" :key="i">
                <div class="tk-msg" :class="m.role === 'user' ? 'tk-me' : 'tk-them'">
                    <template x-if="m.role === 'user'"><span x-text="m.text"></span></template>
                    <template x-if="m.role !== 'user'"><div><p class="text-xs text-primary-300 font-semibold">{{ $stakeholder['name'] }}</p><div class="lesson-md" x-html="m.html"></div></div></template>
                </div>
            </template>
        </div>
        <div class="flex flex-wrap gap-2 mt-3" x-show="!messages.length">
            @foreach(['Who will use the result, and what decision will they make with it?', 'What does a great result look like for you?', 'Is there anything I should leave out or not change?'] as $q)
                <button type="button" class="text-xs px-3 py-1.5 rounded-full border border-dark-600 text-dark-300 hover:text-white hover:border-primary-400" @click="draft = @js($q); send()">{{ $q }}</button>
            @endforeach
        </div>
        <form class="flex gap-2 mt-3" @submit.prevent="send()">
            <input x-model="draft" maxlength="800" class="flex-1 bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm placeholder-dark-500 focus:border-primary-500" placeholder="Ask {{ $stakeholder['name'] }} a clarifying question...">
            <button class="btn-primary text-sm px-4" :disabled="busy || draft.trim().length < 8" x-text="busy ? '...' : 'Ask'"></button>
        </form>
        <p class="text-amber-300 text-xs mt-2" x-show="error" x-text="error"></p>
    </div>
</div>
@once
@push('scripts')
<script>
function stakeholderChat(cfg) {
    return {
        messages: [], draft: '', busy: false, error: '',
        async send() {
            const q = this.draft.trim();
            if (q.length < 8 || this.busy) return;
            const history = this.messages.map(m => ({ role: m.role, text: m.text || m.plain || '' }));
            this.messages.push({ role: 'user', text: q }); this.draft = ''; this.busy = true; this.error = '';
            try {
                const res = await fetch(cfg.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ question: q, history }) });
                const d = await res.json().catch(() => ({}));
                if (res.ok && d.ok) this.messages.push({ role: 'assistant', html: d.html, plain: d.reply });
                else this.error = d.error || (res.status === 429 ? 'Too many questions in a minute.' : 'No answer this time. Try again.');
            } catch (e) { this.error = 'No connection. Try again.'; }
            this.busy = false;
        },
    };
}
</script>
@endpush
@endonce
