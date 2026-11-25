@extends('layouts.app')

@section('title', 'Typing Arena')

@section('content')
@include('partials.typing.styles')
@php $league = $leagues[$lobby['league']]; @endphp
<style>
    .ar-lane { position: relative; height: 46px; border-radius: 12px; background: rgba(15,20,29,.6); border: 1px solid rgba(148,163,184,.14); overflow: hidden; }
    .ar-lane.me { border-color: color-mix(in srgb, var(--color-primary-400) 70%, transparent); }
    .ar-track { position: absolute; left: 0; top: 0; bottom: 0; background: color-mix(in srgb, var(--color-primary-600) 20%, transparent); transition: width .5s linear; }
    .ar-runner { position: absolute; top: 50%; transform: translate(-50%, -50%); font-size: 26px; transition: left .5s linear; }
    .ar-name { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #fff; font-weight: 600; z-index: 1; text-shadow: 0 1px 2px #000; }
    .ar-wpm { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); font-size: 13px; color: rgb(203 213 225); z-index: 1; }
    .ar-flag { position: absolute; right: 0; top: 0; bottom: 0; width: 8px; background: repeating-linear-gradient(0deg, #fff 0 6px, #111 6px 12px); opacity: .6; }
    .ar-count { font-size: 5rem; font-weight: 900; color: #fff; text-align: center; line-height: 1; animation: ar-pop 1s ease infinite; }
    @keyframes ar-pop { 0% { transform: scale(1.3); opacity: .6; } 30% { transform: scale(1); opacity: 1; } }
</style>
<div class="max-w-4xl mx-auto space-y-5" x-data="arena(@js(['joinUrl' => route('typing.arena.join'), 'stateBase' => url('/typing/arena/races'), 'csrf' => csrf_token()]))">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('typing.index') }}" class="text-dark-400 hover:text-white">← Typing trainer</a>
        <span class="text-dark-400 text-sm">Your league: <strong class="text-white">{{ $league['icon'] }} {{ $league['label'] }}</strong> (by your recent speed)</span>
    </div>

    <div class="card p-6">
        <template x-if="!race">
            <div class="text-center py-6">
                <p class="text-6xl">🏁</p>
                <h1 class="text-3xl font-bold text-white mt-3">Typing Arena</h1>
                <p class="text-dark-300 mt-2 max-w-xl mx-auto">Race classmates live on the same text. You only race fellows in your league, so every race is fair. Nobody around? You race the ghost of your own best speed.</p>
                <p class="text-dark-400 text-sm mt-3">{{ $lobby['waiting'] ? $lobby['waiting'] . ' ' . Str::plural('fellow', $lobby['waiting']) . ' waiting in your league right now' : 'Nobody waiting in your league right now' }} · {{ $lobby['recently_racing'] }} raced in the last 10 minutes</p>
                <button type="button" class="btn-primary text-lg px-8 py-3 mt-5" @click="join()" :disabled="busy" x-text="busy ? 'Finding a race...' : 'Race now'"></button>
                <p class="text-amber-300 text-sm mt-3" x-show="error" x-text="error"></p>
            </div>
        </template>

        <template x-if="race">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <p class="text-white font-semibold" x-text="race.status === 'finished' ? 'Race finished' : (race.status === 'running' ? 'Go! Go! Go!' : (race.starts_in !== null ? 'Get ready...' : 'Waiting for racers...'))"></p>
                    <p class="text-dark-400 text-sm" x-show="race.status === 'waiting' && race.starts_in === null" x-text="'Starting with your ghost in ' + race.waiting_for + 's if nobody joins'"></p>
                </div>
                <div class="space-y-2">
                    <template x-for="(e, i) in lanes()" :key="e.name + i">
                        <div class="ar-lane" :class="e.me && 'me'">
                            <div class="ar-track" :style="'width:' + e.pct + '%'"></div>
                            <span class="ar-name" x-text="(e.place ? ['🥇','🥈','🥉'][e.place - 1] || ('#' + e.place) : '') + ' ' + e.name"></span>
                            <span class="ar-runner" :style="'left: calc(' + Math.min(86, Math.max(4, e.pct * 0.86)) + '%)'" x-text="e.ghost ? '👻' : (e.me ? '🏃' : '🏃‍♀️')"></span>
                            <span class="ar-wpm" x-text="e.wpm ? Math.round(e.wpm) + ' wpm' : ''"></span>
                            <span class="ar-flag"></span>
                        </div>
                    </template>
                </div>

                <div class="mt-5" x-show="race.status !== 'running' && race.status !== 'finished' && race.starts_in !== null && race.starts_in > 0">
                    <p class="ar-count" x-text="Math.ceil(race.starts_in)"></p>
                </div>
                <div class="mt-5" x-show="race.status === 'running' || (race.status === 'waiting' && race.starts_in !== null)">
                    <div class="te-box" tabindex="0" x-ref="box" @keydown="key($event)" @paste.prevent x-html="html"></div>
                    <p class="text-dark-400 text-sm mt-2" x-show="race.status === 'running' && !engine.done">Type the text exactly. A wrong key doesn't move you forward.</p>
                </div>

                <template x-if="race.status === 'finished' || (engine && engine.done)">
                    <div class="text-center mt-6">
                        <p class="text-5xl" x-text="myPlace() === 1 ? '🏆' : '🎉'"></p>
                        <p class="text-2xl font-bold text-white mt-2" x-text="myPlace() ? 'You finished #' + myPlace() : 'You finished!'"></p>
                        <p class="text-dark-300 mt-1" x-text="Math.round(engine.wpm()) + ' words/min · ' + Math.round(engine.accuracy()) + '% accuracy'"></p>
                        <p class="text-dark-400 text-sm mt-1" x-show="race.status !== 'finished'">Waiting for the others to finish...</p>
                        <div class="flex justify-center gap-3 mt-4" x-show="race.status === 'finished'">
                            <button type="button" class="btn-primary" @click="again()">Race again</button>
                            <a href="{{ route('typing.index') }}" class="btn-secondary">Back to the trainer</a>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    @include('fellow.typing.partials.boards', ['boards' => $boards, 'leagues' => $leagues])
</div>
@endsection

@push('scripts')
<script>
function arena(cfg) {
    return {
        race: null, engine: null, html: '', busy: false, error: '', poller: null, lastSent: 0, sending: false,
        async api(url, body) {
            const res = await fetch(url, body ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify(body) }
                : { headers: { 'Accept': 'application/json' } });
            const d = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(d.error || d.message || 'Something went wrong.');
            return d;
        },
        async join() {
            this.busy = true; this.error = '';
            try { this.apply(await this.api(cfg.joinUrl, {})); this.poller = setInterval(() => this.poll(), 900); }
            catch (e) { this.error = e.message; }
            this.busy = false;
        },
        apply(state) {
            const fresh = !this.race || this.race.id !== state.id;
            this.race = state;
            if (fresh) { this.engine = window.TypingEngine(state.text); this.html = this.engine.html(); }
            if (state.status === 'running') this.$nextTick(() => this.$refs.box && document.activeElement !== this.$refs.box && this.$refs.box.focus());
            if (state.status === 'finished' && this.poller) { clearInterval(this.poller); this.poller = null; }
        },
        async poll() {
            if (!this.race || this.sending) return;
            try {
                if (this.race.status === 'running' && !this.engine.done) await this.send(false);
                else this.apply(await this.api(cfg.stateBase + '/' + this.race.id));
            } catch (e) { /* keep polling */ }
        },
        async send(finished) {
            this.sending = true;
            try { this.apply(await this.api(cfg.stateBase + '/' + this.race.id + '/progress', { typed: this.engine.pos, errors: this.engine.errors, finished, accuracy: Math.round(this.engine.accuracy() * 10) / 10 })); }
            finally { this.sending = false; this.lastSent = Date.now(); }
        },
        key(ev) {
            if (!this.race || this.race.status !== 'running' || this.engine.done) return;
            const k = window.typingKeyOf(ev);
            if (!k) return;
            ev.preventDefault();
            this.engine.press(k);
            this.html = this.engine.html();
            if (this.engine.done) this.send(true);
        },
        lanes() {
            if (!this.race) return [];
            const len = this.race.length || 1;
            const rows = this.race.entries.map(e => ({ ...e, typed: e.me && this.engine ? Math.max(e.typed, this.engine.pos) : e.typed }))
                .map(e => ({ ...e, pct: Math.min(100, e.typed / len * 100), wpm: e.me && this.engine && this.engine.startedAt ? this.engine.wpm() : e.wpm }));
            if (this.race.ghost_wpm) {
                const typed = Math.min(len, this.race.ghost_wpm * 5 / 60 * (this.race.elapsed || 0));
                rows.push({ name: 'Your best (ghost)', ghost: true, typed, pct: typed / len * 100, wpm: this.race.ghost_wpm, place: null });
            }
            return rows;
        },
        myPlace() { return this.race?.entries.find(e => e.me)?.place || null; },
        again() { this.race = null; this.engine = null; this.join(); },
    };
}
</script>
@endpush
