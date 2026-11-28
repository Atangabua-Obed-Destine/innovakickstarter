@extends('layouts.app')

@section('title', 'Interview room')

@section('content')
@php
    $b = $briefing;
    $cfg = [
        'urls' => [
            'begin' => route('interviews.begin', $interview),
            'state' => route('interviews.state', $interview),
            'answer' => route('interviews.answer', $interview),
            'transcribe' => route('interviews.transcribe', $interview),
            'events' => route('interviews.events', $interview),
            'finish' => route('interviews.finish', $interview),
            'scoreStatus' => route('interviews.score-status', $interview),
            'results' => route('interviews.show', $interview),
        ],
        'csrf' => csrf_token(),
        'begun' => $b['begun'],
        'transcription' => $transcription,
        'sandbox' => $sandbox,
        'interviewerName' => $b['interviewer']['name'] ?? 'Interviewer',
    ];
    $modeIcon = ['voice' => '🎤 spoken', 'code' => '💻 code editor', 'sql' => '🗄️ SQL editor'];
@endphp
<style>
    #iks-room:fullscreen { background: #0b1220; overflow-y: auto; padding: 16px; }
    #iks-room:-webkit-full-screen { background: #0b1220; overflow-y: auto; padding: 16px; }
    .iks-noselect { user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; }
    .iks-bubble { padding: 12px 14px; border-radius: 14px; font-size: 15px; line-height: 1.55; white-space: normal; word-wrap: break-word; }
    .iks-editor { width: 100%; min-height: 220px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 14px; line-height: 1.5; background: #0f172a; color: #e2e8f0; border: 1px solid #334155; border-radius: 10px; padding: 12px; tab-size: 2; white-space: pre; overflow: auto; }
    .iks-text { width: 100%; min-height: 120px; background: #0f172a; color: #e2e8f0; border: 1px solid #334155; border-radius: 10px; padding: 12px; font-size: 15px; line-height: 1.5; }
    .iks-rec { width: 84px; height: 84px; border-radius: 999px; border: none; font-size: 30px; color: #fff; cursor: pointer; flex-shrink: 0; }
    .iks-pre { white-space: pre-wrap; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 13px; background: #0f172a; border-radius: 8px; padding: 10px; color: #cbd5e1; overflow-x: auto; }
</style>

<div id="iks-room" x-data="interviewRoom(@js($cfg))" class="max-w-3xl mx-auto" style="position: relative;">

    {{-- ======================= BRIEFING ======================= --}}
    <div x-show="phase === 'briefing' || phase === 'resume'" class="space-y-5">
        <div class="card p-6">
            <p class="text-dark-400 text-sm">{{ $b['practice'] ? 'Practice interview (does not count)' : 'Chapter mock interview' }}{{ $b['chapter'] ? ' · Chapter ' . ($b['chapter']['chapter'] ?? '') . ': ' . $b['chapter']['title'] : '' }}</p>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $b['role']['title'] ?? 'Interview' }}</h1>
            @if(!empty($b['role']['summary']))<p class="text-dark-300 mt-2">{{ $b['role']['summary'] }}</p>@endif
            <div class="mt-4 p-4 bg-dark-800 rounded-lg">
                <p class="text-white font-medium">Your interviewer: {{ $b['interviewer']['name'] ?? 'Interviewer' }}</p>
                <p class="text-dark-400 text-sm">{{ $b['interviewer']['title'] ?? '' }}</p>
                @if(!empty($b['interviewer']['style']))<p class="text-dark-300 text-sm mt-1">{{ $b['interviewer']['style'] }}</p>@endif
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-3">The interview</h2>
            <p class="text-dark-300 text-sm">About {{ $b['minutes'] }} minutes, {{ $b['questions'] }} main questions, one at a time. Each question has its own timer. The interviewer may ask follow-up questions.</p>
            <ul class="mt-3 space-y-2">
                @foreach($b['rounds'] as $round)
                    <li class="flex flex-wrap items-center justify-between gap-2 p-3 bg-dark-800 rounded-lg text-sm">
                        <span class="text-white">{{ $round['label'] }}</span>
                        <span class="text-dark-400">{{ $round['count'] }} {{ $round['count'] === 1 ? 'question' : 'questions' }} · {{ $modeIcon[$round['answer_mode']] ?? $round['answer_mode'] }}{{ $round['language'] && $round['answer_mode'] !== 'sql' ? ' (' . $round['language'] . ')' : '' }}</span>
                    </li>
                @endforeach
            </ul>
            <h3 class="text-white font-medium mt-5 mb-2">What is assessed</h3>
            <ul class="space-y-1">
                @foreach($b['rubric'] as $r)
                    <li class="text-dark-300 text-sm"><span class="text-white">{{ $r['label'] }}</span>{{ $r['what_it_means'] ? ': ' . $r['what_it_means'] : '' }}</li>
                @endforeach
            </ul>
            <p class="text-dark-400 text-sm mt-3">Pass mark: {{ $b['pass_mark'] }}/100. You get a scorecard with what a strong answer contains and what to practise next.</p>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-3">The rules</h2>
            <ul class="space-y-2 text-dark-300 text-sm">
                <li>🎤 <b class="text-white">Explain out loud.</b> Press Record, speak, press Stop. Your words appear as text; fix any transcription mistakes, then send. Code and SQL go in an editor (you can run them).</li>
                <li>🖥️ <b class="text-white">Full screen, no camera.</b> Stay in full screen and on this tab. Leaving full screen or switching tabs is logged with how long you were away.</li>
                <li>📋 <b class="text-white">No pasting.</b> Paste and drag-and-drop are blocked in every answer box, and copying the interviewer's text is blocked. Attempts are logged.</li>
                <li>⏱️ <b class="text-white">Each answer is timed</b> by the server. Questions are sent one at a time.</li>
                <li>🙋 Microphone broken? Use <b class="text-white">"My microphone isn't working"</b> to type instead. Your reviewer will see that you typed.</li>
                <li>✅ Logged events never fail you on their own: a reviewer looks at them with your answers.</li>
            </ul>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-2">Test your microphone</h2>
            <p class="text-dark-400 text-sm">Say a sentence for 3 seconds, for example: "My name is … and I am ready for my interview."</p>
            <div class="flex flex-wrap items-center gap-3 mt-3">
                <button type="button" class="btn btn-secondary" @click="micTest()" :disabled="mic.state === 'recording' || mic.state === 'transcribing'"
                        x-text="mic.state === 'recording' ? 'Listening… ' + mic.count : (mic.state === 'transcribing' ? 'Transcribing…' : 'Record 3 seconds')"></button>
                <div style="width: 140px; height: 8px; background: #1e293b; border-radius: 999px; overflow: hidden;"><div :style="'height:8px;background:#22c55e;width:' + rec.level + '%'"></div></div>
            </div>
            <p class="text-dark-200 text-sm mt-3" x-show="mic.text" x-text="'We heard: “' + mic.text + '”'"></p>
            <p class="text-sm mt-2" style="color:#fca5a5;" x-show="mic.error" x-text="mic.error"></p>
        </div>

        <div class="card p-6">
            <template x-if="phase === 'briefing'">
                <div>
                    <label class="flex items-start gap-3 text-dark-200 text-sm cursor-pointer">
                        <input type="checkbox" x-model="consent" style="margin-top: 3px; width: 18px; height: 18px;">
                        <span>I understand the rules. I will answer on my own, without help, notes from others or AI tools, and I agree that my answers and the logged events are shared with my reviewer.</span>
                    </label>
                    <button type="button" class="btn btn-primary mt-4 w-full" style="padding: 14px; font-size: 16px;" :disabled="!consent || busy" @click="start()">
                        <span x-text="busy ? 'Starting…' : 'Enter full screen and start'"></span>
                    </button>
                </div>
            </template>
            <template x-if="phase === 'resume'">
                <div>
                    <p class="text-dark-300 text-sm">Your interview is in progress. The question timer kept running.</p>
                    <button type="button" class="btn btn-primary mt-4 w-full" style="padding: 14px; font-size: 16px;" :disabled="busy" @click="resume()">Return to full screen and continue</button>
                </div>
            </template>
            <p class="text-sm mt-3" style="color:#fca5a5;" x-show="error" x-text="error"></p>
            <p class="text-dark-500 text-xs mt-3" x-show="!fsSupported">This browser cannot switch to full screen (for example on an iPhone). You can still take the interview: stay on this page.</p>
        </div>
    </div>

    {{-- ======================= INTERVIEW ======================= --}}
    <div x-show="phase === 'interview'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <p class="text-dark-400 text-sm" x-show="prompt" x-text="prompt ? ((prompt.kind === 'follow_up' ? 'Follow-up · ' : '') + 'Question ' + prompt.n + ' of ' + prompt.total + (prompt.round_label ? ' · ' + prompt.round_label : '')) : ''"></p>
            <div class="flex items-center gap-3 text-sm">
                <span class="text-dark-400" x-show="totalLeft !== null">Interview: <span class="text-white" x-text="clock(totalLeft)"></span></span>
                <button type="button" class="text-dark-400 hover:text-white" style="text-decoration: underline;" @click="finish('fellow_ended')">End interview</button>
            </div>
        </div>

        {{-- Conversation (interviewer text cannot be selected or copied) --}}
        <div x-ref="log" class="space-y-3" style="max-height: 46vh; overflow-y: auto; padding-right: 4px;" @copy="onCopy($event)" @cut="onCopy($event)">
            <template x-for="(m, i) in messages" :key="i">
                <div :style="m.role === 'fellow' ? 'display:flex;justify-content:flex-end' : 'display:flex;justify-content:flex-start'">
                    <div class="iks-bubble" :class="m.role === 'fellow' ? '' : 'iks-noselect'"
                         :style="m.role === 'fellow' ? 'background:#1e3a5f;color:#e2e8f0;max-width:85%' : 'background:#1e293b;color:#f1f5f9;max-width:92%;border:1px solid #334155'"
                         @contextmenu="m.role !== 'fellow' && $event.preventDefault()">
                        <p class="text-xs" style="opacity:.6;margin-bottom:4px" x-text="m.role === 'fellow' ? 'You' + (m.mode === 'typed' ? ' (typed)' : (m.mode === 'voice' ? ' (spoken)' : (m.mode ? ' (' + m.mode + ')' : ''))) : cfg.interviewerName"></p>
                        <template x-if="m.code"><pre class="iks-pre" style="margin-bottom:6px" x-text="m.code"></pre></template>
                        <template x-if="m.role === 'fellow'"><span style="white-space: pre-line;" x-text="m.text"></span></template>
                        {{-- Interviewer text: rendered and escaped on the server (code blocks, bold). --}}
                        <template x-if="m.role !== 'fellow'"><div style="white-space: normal;" x-html="m.html || ''"></div></template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Current question details --}}
        <template x-if="prompt">
            <div class="card p-4" style="border: 1px solid #334155;">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-dark-400 text-xs" x-text="prompt.answer_mode === 'voice' ? 'Answer out loud' : (prompt.answer_mode === 'sql' ? 'Write SQL (SQLite), then explain if you like' : 'Write code (' + (prompt.language || 'code') + '), then explain if you like')"></span>
                    <span class="text-sm font-semibold" :style="qLeft <= 0 ? 'color:#f87171' : (qLeft < 30 ? 'color:#fbbf24' : 'color:#e2e8f0')" x-text="'⏱ ' + clock(qLeft)"></span>
                </div>
                <p class="text-sm mt-2" style="color:#f87171" x-show="qLeft <= 0">Time is up for this question: send what you have.</p>
                <template x-if="prompt.setup_html"><div class="iks-noselect mt-3 text-dark-200 text-sm" x-html="prompt.setup_html" @copy="onCopy($event)"></div></template>
            </div>
        </template>

        {{-- Code / SQL editor --}}
        <div x-show="prompt && prompt.answer_mode !== 'voice'" class="space-y-2">
            <textarea class="iks-editor" x-model="code" spellcheck="false" autocapitalize="off" autocomplete="off"
                      @keydown="codeKey($event)" @paste="blockPaste($event)" @drop="blockDrop($event)" @dragover.prevent
                      @beforeinput="beforeInput($event)" @input="burst('code')" aria-label="Code editor"></textarea>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="btn btn-secondary" x-show="runnable()" @click="runCode()" :disabled="run.busy" x-text="run.busy ? 'Running…' : '▶ Run'"></button>
                <span class="text-dark-400 text-xs" x-text="run.status"></span>
            </div>
            <template x-if="run.table">
                <div style="overflow-x: auto;">
                    <table class="text-sm" style="border-collapse: collapse; min-width: 100%;">
                        <thead><tr><template x-for="c in run.table.columns"><th class="text-dark-300" style="text-align:left;padding:4px 8px;border-bottom:1px solid #334155" x-text="c"></th></template></tr></thead>
                        <tbody><template x-for="row in run.table.rows"><tr><template x-for="v in row"><td class="text-dark-200" style="padding:4px 8px;border-bottom:1px solid #1e293b" x-text="v === null ? 'NULL' : v"></td></template></tr></template></tbody>
                    </table>
                    <p class="text-dark-500 text-xs mt-1" x-show="run.table.more" x-text="run.table.more"></p>
                </div>
            </template>
            <pre class="iks-pre" x-show="run.output" x-text="run.output"></pre>
            <pre class="iks-pre" style="color:#fca5a5" x-show="run.error" x-text="run.error"></pre>
        </div>

        {{-- Spoken answer (or explanation of the code) --}}
        <div x-show="prompt" class="card p-4 space-y-3">
            <div class="flex items-center gap-4">
                <button type="button" class="iks-rec" :style="rec.on ? 'background:#dc2626' : 'background:#7c3aed'" x-show="!micOff"
                        @click="rec.on ? stopRec() : startRec()" :disabled="rec.busy" :aria-label="rec.on ? 'Stop recording' : 'Record'">
                    <span x-text="rec.busy ? '…' : (rec.on ? '■' : '●')"></span>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium" x-text="micOff ? 'Typing mode (logged for your reviewer)' : (rec.on ? 'Recording… ' + clock(rec.seconds) + ' (press Stop when you finish)' : (rec.busy ? 'Transcribing your answer…' : (prompt && prompt.answer_mode !== 'voice' ? 'Optional: explain your code out loud' : 'Press Record and answer out loud')))"></p>
                    <div x-show="!micOff" style="height: 8px; background: #1e293b; border-radius: 999px; overflow: hidden; margin-top: 6px;"><div :style="'height:8px;background:#22c55e;transition:width .1s;width:' + rec.level + '%'"></div></div>
                </div>
            </div>
            <textarea class="iks-text" x-model="answer" :readonly="!micOff && !answer"
                      :placeholder="micOff ? 'Type your answer here.' : 'Your transcribed answer appears here. You can correct transcription mistakes.'"
                      @keydown="key()" @paste="blockPaste($event)" @drop="blockDrop($event)" @dragover.prevent
                      @beforeinput="beforeInput($event)" @input="burst('answer')" aria-label="Your answer"></textarea>
            <label class="flex items-center gap-2 text-dark-400 text-sm cursor-pointer">
                <input type="checkbox" x-model="micOff" @change="micOff && log('mic_off')"> My microphone isn't working (type instead)
            </label>
            <p class="text-sm" style="color:#fca5a5" x-show="error" x-text="error"></p>
            <p class="text-sm text-amber-400" x-show="notice" x-text="notice"></p>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="btn btn-primary" style="padding: 12px 22px;" @click="send(false)" :disabled="!canSend()" x-text="busy ? 'Sending…' : 'Send answer'"></button>
                <button type="button" class="btn btn-secondary" @click="skip()" :disabled="busy || rec.on">I don't know: skip</button>
            </div>
        </div>
    </div>

    {{-- ======================= SCORING ======================= --}}
    <div x-show="phase === 'scoring'" x-cloak class="card p-8 text-center">
        <p class="text-3xl">⏳</p>
        <p class="text-white text-lg font-semibold mt-2">Scoring your interview…</p>
        <p class="text-dark-400 text-sm mt-1">This usually takes a minute. Your scorecard opens by itself.</p>
        <a :href="cfg.urls.results" class="text-primary-400 hover:text-primary-300 text-sm mt-4 inline-block">Open the results page</a>
    </div>

    {{-- Full screen overlay: moved under <body> so it covers the whole window (a transformed
         parent in the layout would otherwise pin position:fixed to that parent). It only shows
         when the fellow is out of full screen, so living outside the full-screen element is fine. --}}
    <template x-teleport="body">
        {{-- x-show toggles "display", so the flex centring lives on an inner wrapper. --}}
        <div x-show="overlay && phase === 'interview'" x-cloak style="position: fixed; inset: 0; z-index: 9999; background: rgba(2,6,23,.96);">
            <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 24px;">
                <div class="card p-6" style="max-width: 420px; width: 100%; text-align: center;">
                    <p class="text-2xl">🖥️</p>
                    <p class="text-white font-semibold mt-2">Please return to full screen</p>
                    <p class="text-dark-400 text-sm mt-2">The interview needs full screen. Time away is logged for your reviewer, and the question timer keeps running.</p>
                    <button type="button" class="btn btn-primary mt-4 w-full" @click="enterFullscreen()">Return to full screen</button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script src="{{ asset('course-files/practice/practice-lab.js') }}?v=1"></script>
<script>
function interviewRoom(cfg) {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    return {
        cfg,
        phase: cfg.begun ? 'resume' : 'briefing',
        consent: false, busy: false, error: '', notice: '', ending: false,
        fsSupported: !!(document.fullscreenEnabled || document.webkitFullscreenEnabled),
        overlay: false,
        mic: { state: 'idle', text: '', error: '', count: 3 },
        messages: [], prompt: null, turn: 0,
        answer: '', code: '', micOff: false, source: 'none', voiceSeconds: 0,
        rec: { on: false, busy: false, level: 0, seconds: 0 },
        run: { busy: false, output: '', error: '', table: null, status: '' },
        qLeft: 0, totalLeft: null,
        events: [], away: { fs: null, hidden: null, blur: null },
        stats: { keystrokes: 0, max_burst: 0 }, lens: { answer: 0, code: 0 },
        stream: null, recorder: null, chunks: [], sr: null, srText: '', recStart: 0, recTimer: null, raf: null, audioCtx: null,

        init() {
            const fsChange = () => this.onFullscreen();
            document.addEventListener('fullscreenchange', fsChange);
            document.addEventListener('webkitfullscreenchange', fsChange);
            document.addEventListener('visibilitychange', () => this.onVisibility());
            window.addEventListener('blur', () => { if (this.phase === 'interview' && !document.hidden) this.away.blur = Date.now(); });
            window.addEventListener('focus', () => {
                if (this.away.blur && this.phase === 'interview') {
                    const ms = Date.now() - this.away.blur;
                    if (ms > 1500) this.log('blur', { duration_ms: ms });
                }
                this.away.blur = null;
            });
            window.addEventListener('pagehide', () => this.beacon());
            setInterval(() => this.tick(), 1000);
            setInterval(() => { if (this.phase === 'interview' && !document.hidden) this.flush(); }, 60000);
        },

        // ------------------------------------------------ helpers
        clock(s) { s = Math.max(0, Math.floor(s || 0)); return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); },
        log(type, extra = {}) { if (this.events.length < 300) this.events.push(Object.assign({ type, turn: this.turn }, extra)); },
        takeEvents() { const e = this.events; this.events = []; return e; },
        async post(url, body) {
            const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify(body || {}) });
            let data = {}; try { data = await r.json(); } catch (e) {}
            return { ok: r.ok, status: r.status, data };
        },
        scroll() { this.$nextTick(() => { const el = this.$refs.log; if (el) el.scrollTop = el.scrollHeight; }); },
        tick() {
            if (this.phase !== 'interview') return;
            if (this.prompt) this.qLeft--;
            if (this.totalLeft !== null) {
                this.totalLeft--;
                if (this.totalLeft <= -5 && !this.busy && !this.ending) this.finish('time_up');
            }
        },

        // ------------------------------------------------ full screen & focus
        async enterFullscreen() {
            const el = document.getElementById('iks-room');
            try {
                if (el.requestFullscreen) await el.requestFullscreen();
                else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                else throw new Error('unsupported');
            } catch (e) {
                if (!this.fsSupported) this.log('fullscreen_unsupported');
            }
        },
        isFullscreen() { return !!(document.fullscreenElement || document.webkitFullscreenElement); },
        onFullscreen() {
            if (this.phase !== 'interview' || this.ending) return;
            if (!this.isFullscreen()) { this.away.fs = Date.now(); this.overlay = true; }
            else { if (this.away.fs) this.log('fullscreen_exit', { duration_ms: Date.now() - this.away.fs }); this.away.fs = null; this.overlay = false; }
        },
        onVisibility() {
            if (this.phase !== 'interview' || this.ending) return;
            if (document.hidden) { this.away.hidden = Date.now(); if (this.rec.on) this.stopRec(); }
            else if (this.away.hidden) {
                this.log('visibility_hidden', { duration_ms: Date.now() - this.away.hidden });
                this.away.hidden = null;
                if (this.fsSupported && !this.isFullscreen()) this.overlay = true;
            }
        },
        beacon() {
            if (this.phase !== 'interview' || !navigator.sendBeacon) return;
            const fd = new FormData();
            fd.append('_token', cfg.csrf);
            fd.append('events', JSON.stringify(this.takeEvents().concat([{ type: 'page_left', turn: this.turn }])));
            navigator.sendBeacon(cfg.urls.events, fd);
        },
        async flush() {
            const events = this.takeEvents();
            try { await this.post(cfg.urls.events, { events }); } catch (e) { this.events = events.concat(this.events); }
        },

        // ------------------------------------------------ blocked input
        blockPaste(e) { e.preventDefault(); const t = (e.clipboardData && e.clipboardData.getData('text')) || ''; this.log('paste_blocked', { chars: t.length }); this.notice = 'Pasting is turned off in the interview.'; },
        blockDrop(e) { e.preventDefault(); this.log('drop_blocked'); this.notice = 'Dropping text is turned off in the interview.'; },
        beforeInput(e) {
            if (e.inputType && /^insertFrom(Paste|Drop|Yank|PasteAsQuotation)/.test(e.inputType)) { e.preventDefault(); this.log(e.inputType.includes('Drop') ? 'drop_blocked' : 'paste_blocked'); this.notice = 'Pasting is turned off in the interview.'; }
        },
        onCopy(e) { e.preventDefault(); this.log('copy_blocked'); this.notice = 'Copying the interviewer\'s text is turned off.'; },
        key() { this.stats.keystrokes++; },
        burst(field) {
            const now = (field === 'code' ? this.code : this.answer).length;
            const delta = now - this.lens[field];
            if (delta > 60) { this.log('large_burst', { chars: delta }); this.stats.max_burst = Math.max(this.stats.max_burst, delta); }
            this.lens[field] = now;
        },
        codeKey(e) {
            this.stats.keystrokes++;
            if (e.key === 'Tab') {
                e.preventDefault();
                const t = e.target, s = t.selectionStart, end = t.selectionEnd;
                this.code = this.code.slice(0, s) + '  ' + this.code.slice(end);
                this.lens.code = this.code.length;
                this.$nextTick(() => { t.selectionStart = t.selectionEnd = s + 2; });
            }
        },

        // ------------------------------------------------ starting
        async start() {
            if (!this.consent) return;
            this.busy = true; this.error = '';
            await this.enterFullscreen();
            const r = await this.post(cfg.urls.begin, { consent: 1 });
            this.busy = false;
            if (!r.ok) { this.error = r.data.message || 'The interview could not start. Please reload the page.'; return; }
            this.phase = 'interview';
            this.apply(r.data);
            this.askFullscreen();
        },
        async resume() {
            this.busy = true; this.error = '';
            await this.enterFullscreen();
            const r = await fetch(cfg.urls.state, { headers: { Accept: 'application/json' } });
            const d = await r.json().catch(() => ({}));
            this.busy = false;
            if (!r.ok) { this.error = d.message || 'The interview could not be loaded.'; return; }
            this.log('resumed');
            this.phase = 'interview';
            this.apply(d);
            this.askFullscreen();
        },
        // Full screen was refused or failed: ask again (nothing is logged until they leave it).
        askFullscreen() { if (this.phase === 'interview' && this.fsSupported && !this.isFullscreen()) this.overlay = true; },
        apply(s) {
            this.messages = s.messages || [];
            this.totalLeft = s.interview_remaining_seconds;
            if (s.done || !s.prompt) { this.scoring(); return; }
            const changed = !this.prompt || this.prompt.turn !== s.prompt.turn;
            this.prompt = s.prompt; this.turn = s.turn;
            if (changed) {
                this.answer = ''; this.source = 'none'; this.voiceSeconds = 0; this.srText = '';
                this.code = s.prompt.answer_mode !== 'voice' ? (s.prompt.starter || '') : '';
                this.lens = { answer: 0, code: this.code.length };
                this.stats = { keystrokes: 0, max_burst: 0 };
                this.run = { busy: false, output: '', error: '', table: null, status: '' };
                this.qLeft = s.prompt.remaining_seconds; this.notice = ''; this.error = '';
            }
            this.scroll();
        },

        // ------------------------------------------------ recording
        async ensureStream() {
            if (this.stream) return this.stream;
            this.stream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
            return this.stream;
        },
        mime() {
            const types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4'];
            return window.MediaRecorder && MediaRecorder.isTypeSupported ? (types.find(t => MediaRecorder.isTypeSupported(t)) || '') : '';
        },
        meter(stream) {
            try {
                this.audioCtx = this.audioCtx || new (window.AudioContext || window.webkitAudioContext)();
                const src = this.audioCtx.createMediaStreamSource(stream), an = this.audioCtx.createAnalyser();
                an.fftSize = 512; src.connect(an);
                const data = new Uint8Array(an.fftSize);
                const loop = () => {
                    an.getByteTimeDomainData(data);
                    let sum = 0; for (const v of data) { const x = (v - 128) / 128; sum += x * x; }
                    this.rec.level = Math.min(100, Math.round(Math.sqrt(sum / data.length) * 300));
                    if (this.rec.on || this.mic.state === 'recording') this.raf = requestAnimationFrame(loop); else this.rec.level = 0;
                };
                loop();
            } catch (e) {}
        },
        async record(maxSeconds) {
            const stream = await this.ensureStream();
            if (!window.MediaRecorder) throw new Error('This browser cannot record audio.');
            const type = this.mime();
            this.recorder = new MediaRecorder(stream, type ? { mimeType: type } : undefined);
            this.chunks = [];
            this.recorder.ondataavailable = e => { if (e.data && e.data.size) this.chunks.push(e.data); };
            this.srText = '';
            if (SR) {
                try {
                    const r = new SR(); r.lang = 'en-US'; r.continuous = true; r.interimResults = false;
                    r.onresult = e => { for (let i = e.resultIndex; i < e.results.length; i++) if (e.results[i].isFinal) this.srText += e.results[i][0].transcript + ' '; };
                    r.onerror = () => {}; r.start(); this.sr = r;
                } catch (e) { this.sr = null; }
            }
            this.recStart = Date.now();
            this.recorder.start(1000);
            this.meter(stream);
            return new Promise(resolve => {
                this.recorder.onstop = () => {
                    const t = this.recorder.mimeType || type || 'audio/webm';
                    resolve({ blob: new Blob(this.chunks, { type: t.split(';')[0] }), seconds: (Date.now() - this.recStart) / 1000, ext: t.includes('mp4') ? 'mp4' : (t.includes('ogg') ? 'ogg' : 'webm') });
                };
                this.recTimer = setTimeout(() => this.stopRecorder(), maxSeconds * 1000);
            });
        },
        stopRecorder() {
            clearTimeout(this.recTimer);
            try { if (this.sr) this.sr.stop(); } catch (e) {}
            if (this.recorder && this.recorder.state !== 'inactive') this.recorder.stop();
        },
        async transcribe(rec, turn) {
            if (!cfg.transcription) return { fallback: true };
            const fd = new FormData();
            fd.append('audio', rec.blob, 'answer.' + rec.ext);
            fd.append('turn', turn);
            fd.append('duration_seconds', Math.round(rec.seconds * 10) / 10);
            try {
                const r = await fetch(cfg.urls.transcribe, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: fd });
                const d = await r.json().catch(() => ({}));
                if (r.ok) return d;
                if (r.status === 503 || d.fallback) return { fallback: true };
                return { error: d.message || 'The recording could not be transcribed.' };
            } catch (e) { return { fallback: true }; }
        },
        async micTest() {
            this.mic = { state: 'recording', text: '', error: '', count: 3 };
            const countdown = setInterval(() => { if (this.mic.count > 1) this.mic.count--; }, 1000);
            try {
                const rec = await this.record(3);
                clearInterval(countdown);
                this.mic.state = 'transcribing';
                const r = await this.transcribe(rec, 0);
                const text = r.text || (r.fallback ? this.srText.trim() : '');
                this.mic.state = 'done';
                if (text) this.mic.text = text;
                else this.mic.error = r.error || 'We could not hear any words. Check that the right microphone is selected and speak a little louder, or use "My microphone isn\'t working" during the interview.';
            } catch (e) {
                clearInterval(countdown);
                this.mic.state = 'idle';
                this.mic.error = 'The microphone is blocked or missing. Allow microphone access for this site in your browser settings, then try again.';
            }
        },
        async startRec() {
            if (this.rec.on || this.rec.busy) return;
            this.error = ''; this.notice = '';
            const turn = this.turn;
            this.rec.on = true; this.rec.seconds = 0;
            const counter = setInterval(() => { this.rec.seconds++; }, 1000);
            let rec;
            try { rec = await this.record(300); }
            catch (e) { clearInterval(counter); this.rec.on = false; this.error = 'The microphone is blocked or missing. Allow it in your browser settings, or tick "My microphone isn\'t working".'; return; }
            clearInterval(counter);
            this.rec.on = false; this.rec.busy = true;
            const r = await this.transcribe(rec, turn);
            this.rec.busy = false;
            if (turn !== this.turn) return;
            let text = r.text || '';
            if (!text && r.fallback) { text = this.srText.trim(); if (text) this.source = this.source === 'groq' ? 'groq' : 'browser'; }
            else if (text) this.source = 'groq';
            if (!text) { this.error = r.error || 'We could not hear an answer. Please record again, closer to the microphone.'; return; }
            this.voiceSeconds += rec.seconds;
            this.answer = (this.answer ? this.answer.trimEnd() + ' ' : '') + text;
            this.lens.answer = this.answer.length;
        },
        stopRec() { this.stopRecorder(); },

        // ------------------------------------------------ running code
        runnable() { return this.prompt && ((this.prompt.answer_mode === 'sql' && Object.keys(cfg.sandbox.sqlTables || {}).length) || ['javascript', 'python'].includes(this.prompt.language)); },
        async runCode() {
            if (!window.PracticeLab) { this.run.error = 'The code runner could not load.'; return; }
            this.run = { busy: true, output: '', error: '', table: null, status: '' };
            const status = s => { this.run.status = s; };
            try {
                if (this.prompt.answer_mode === 'sql') {
                    const r = await window.PracticeLab.runSql(this.code, { tables: cfg.sandbox.sqlTables, ordered: true, onStatus: status });
                    const rows = (r.rows || []).slice(0, 30);
                    this.run.table = { columns: r.columns || [], rows, more: (r.rows || []).length > 30 ? (r.rows.length + ' rows, first 30 shown') : '' };
                    this.run.output = (r.rows || []).length ? '' : 'The query ran and returned no rows.';
                } else if (this.prompt.language === 'javascript') {
                    const r = await window.PracticeLab.runJs(this.code, { setup: '', canonUrl: cfg.sandbox.jsCanonUrl });
                    this.run.output = [r.output, r.preview && r.preview !== 'null' ? 'answer = ' + r.preview : ''].filter(Boolean).join('\n') || '(no output: use console.log)';
                    if (r.error && !/variable named answer/.test(r.error)) this.run.error = r.error;
                } else if (this.prompt.language === 'python') {
                    const r = await window.PracticeLab.runPython(this.code + '\nanswer = None', { setup: '', needsPandas: true, files: cfg.sandbox.pythonFiles, canonUrl: cfg.sandbox.canonUrl, onStatus: status });
                    this.run.output = r.output || '(no output: use print)';
                    if (r.error) this.run.error = r.error;
                }
            } catch (e) { this.run.error = String(e && e.message ? e.message : e); }
            this.run.busy = false; this.run.status = '';
        },
        runSummary() {
            const parts = [];
            if (this.run.table) parts.push(this.run.table.columns.join(' | ') + '\n' + this.run.table.rows.slice(0, 10).map(r => r.join(' | ')).join('\n'));
            if (this.run.output) parts.push(this.run.output);
            if (this.run.error) parts.push('Error: ' + this.run.error);
            return parts.join('\n').slice(0, 3900) || null;
        },

        // ------------------------------------------------ sending
        canSend() {
            if (!this.prompt || this.busy || this.rec.on || this.rec.busy) return false;
            return this.prompt.answer_mode === 'voice' ? this.answer.trim().length > 0 : (this.code.trim().length > 0 || this.answer.trim().length > 0);
        },
        skip() { if (confirm('Skip this question? Saying what you do know is usually better than skipping.')) this.send(true); },
        async send(skip) {
            if (this.busy) return;
            this.busy = true; this.error = ''; this.notice = '';
            const events = this.takeEvents();
            const body = {
                turn: this.turn, skip: !!skip, answer: this.answer, code: this.prompt.answer_mode !== 'voice' ? this.code : null,
                explanation_mode: this.micOff || this.source === 'none' ? 'typed' : 'voice',
                transcription_source: this.micOff ? 'none' : this.source, voice_seconds: Math.round(this.voiceSeconds * 10) / 10,
                run_output: this.prompt.answer_mode !== 'voice' ? this.runSummary() : null, mic_off: this.micOff, stats: this.stats, events,
            };
            let r;
            try { r = await this.post(cfg.urls.answer, body); } catch (e) { r = { ok: false, status: 0, data: {} }; }
            this.busy = false;
            if (r.ok) { this.apply(r.data); return; }
            this.events = events.concat(this.events);
            if (r.data.reason === 'wrong_turn') { this.resync(); return; }
            if (r.data.reason === 'finished') { this.scoring(); return; }
            if (r.status === 410 || r.data.reason === 'expired' || r.data.reason === 'closed') { this.ending = true; this.error = r.data.message; setTimeout(() => location.href = cfg.urls.results, 4000); return; }
            this.error = r.data.message || (r.status === 0 ? 'No connection. Your answer is still here: press Send again.' : 'Your answer could not be sent. Press Send again.');
        },
        async resync() {
            const r = await fetch(cfg.urls.state, { headers: { Accept: 'application/json' } });
            if (r.ok) this.apply(await r.json());
        },
        async finish(reason) {
            if (this.ending) return;
            if (reason !== 'time_up' && !confirm('End the interview now? It will be scored on the answers you gave.')) return;
            this.ending = true;
            const r = await this.post(cfg.urls.finish, { reason, events: this.takeEvents() });
            if (!r.ok && r.data.reason !== 'finished') { this.ending = false; this.error = r.data.message || 'The interview could not be ended. Try again.'; return; }
            this.scoring();
        },
        scoring() {
            this.ending = true; this.phase = 'scoring'; this.overlay = false; this.prompt = null;
            try { if (this.stream) this.stream.getTracks().forEach(t => t.stop()); } catch (e) {}
            try { if (this.isFullscreen()) (document.exitFullscreen || document.webkitExitFullscreen).call(document); } catch (e) {}
            let tries = 0;
            const poll = async () => {
                tries++;
                try {
                    const r = await fetch(cfg.urls.scoreStatus, { headers: { Accept: 'application/json' } });
                    const d = await r.json();
                    if (d.ready || tries > 100) { location.href = d.url || cfg.urls.results; return; }
                } catch (e) {}
                setTimeout(poll, 3000);
            };
            poll();
        },
    };
}
</script>
@endpush
