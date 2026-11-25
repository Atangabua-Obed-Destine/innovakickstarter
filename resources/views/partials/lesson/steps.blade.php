{{--
    Interactive lesson instructions: sections, steps to tick, self-check, "Stuck?" help and quick checks.
    @param array $sections         from App\Support\LessonSteps::parse()
    @param \App\Models\TrackCurriculumActivity $activity
    @param \App\Models\FellowCurriculumProgress|null $progress
    @param \Illuminate\Support\Collection $quickChecks
    @param bool $canTick
--}}
@php
    $allKeys = \App\Support\LessonSteps::keys($sections);
    $checked = array_values(array_intersect($progress?->checked_steps ?? [], $allKeys));
    $firstSteps = collect($sections)->search(fn ($s) => $s['kind'] === 'steps');
    $stepCount = $firstSteps !== false ? count($sections[$firstSteps]['items']) : 0;
    $placedChecks = $quickChecks->filter(fn ($c) => $firstSteps !== false && $c['after_step'] && $c['after_step'] <= $stepCount)->groupBy('after_step');
    $endChecks = $quickChecks->reject(fn ($c) => $firstSteps !== false && $c['after_step'] && $c['after_step'] <= $stepCount)->values();
    $mentorShort = app(\App\Services\MentorService::class)->persona($activity->track)['short'];
@endphp
<style>
    .ls-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .ls-bar { height: 8px; border-radius: 99px; background: rgba(148,163,184,.15); overflow: hidden; }
    .ls-bar > div { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--color-primary-600), var(--color-primary-400)); transition: width .4s ease; }
    .ls-sec + .ls-sec { margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(148,163,184,.12); }
    .ls-title { color: #fff; font-weight: 600; font-size: 1.02rem; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
    .ls-count { font-size: 12px; font-weight: 500; color: rgb(148 163 184); }
    .ls-list { list-style: none; margin: 10px 0 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .ls-item { display: flex; gap: 12px; align-items: flex-start; padding: 12px 14px; border-radius: 12px; background: rgba(15,20,29,.55); border: 1px solid rgba(148,163,184,.12); transition: border-color .2s, background .2s; }
    .ls-item.is-done { border-color: color-mix(in srgb, var(--color-primary-500) 45%, transparent); background: color-mix(in srgb, var(--color-primary-600) 10%, #0f141d); }
    .ls-item.is-done .ls-body { opacity: .72; }
    .ls-check { flex-shrink: 0; width: 28px; height: 28px; border-radius: 8px; border: 2px solid rgba(148,163,184,.45); display: flex; align-items: center; justify-content: center;
        color: transparent; font-weight: 700; font-size: 15px; transition: all .15s; background: transparent; }
    .ls-check:hover:not(:disabled) { border-color: var(--color-primary-400); }
    .ls-check.on { background: var(--color-primary-500); border-color: var(--color-primary-500); color: #fff; transform: scale(1.05); }
    .ls-check:disabled { cursor: not-allowed; opacity: .5; }
    .ls-num { flex-shrink: 0; width: 22px; text-align: right; color: rgb(100 116 139); font-size: 13px; font-weight: 600; padding-top: 4px; }
    .ls-body { flex: 1; min-width: 0; }
    .ls-body .lesson-md p:last-child { margin-bottom: 0; }
    .ls-stuck-btn { font-size: 12px; color: rgb(148 163 184); border: 1px solid rgba(148,163,184,.25); border-radius: 99px; padding: 2px 10px; margin-top: 8px; }
    .ls-stuck-btn:hover { color: #fff; border-color: var(--color-primary-400); }
    .ls-help { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .ls-help button { font-size: 12.5px; padding: 5px 11px; border-radius: 99px; background: color-mix(in srgb, var(--color-primary-600) 18%, transparent); color: var(--color-primary-200); border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent); }
    .ls-help button:hover { background: color-mix(in srgb, var(--color-primary-600) 32%, transparent); color: #fff; }
    .ls-done-banner { margin-top: 18px; padding: 14px 16px; border-radius: 12px; background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-600) 28%, #0f141d), #0f141d); border: 1px solid color-mix(in srgb, var(--color-primary-500) 45%, transparent); }
</style>
<div class="card p-6" x-data="lessonSteps(@js(['keys' => $allKeys, 'checked' => $checked, 'url' => route('curriculum.activity.steps', $activity), 'canTick' => $canTick, 'csrf' => csrf_token()]))">
    <div class="ls-head">
        <h3 class="text-white font-semibold text-lg flex items-center gap-2"><span class="text-blue-400">📋</span> Your lesson, step by step</h3>
        <span class="text-dark-300 text-sm"><span class="text-white font-semibold" x-text="done()"></span> of {{ count($allKeys) }} ticked</span>
    </div>
    <div class="ls-bar mt-3" role="progressbar" :aria-valuenow="pct()" aria-valuemin="0" aria-valuemax="100"><div :style="'width:' + pct() + '%'"></div></div>
    @unless($canTick)
        <p class="text-dark-400 text-sm mt-3">Read through the lesson first. Once you press <strong class="text-white">Start</strong>, you can tick off each step as you go, and your progress is saved.</p>
    @endunless
    <p class="text-amber-300 text-sm mt-2" x-show="error" x-text="error" x-cloak></p>

    <div class="mt-5">
        @foreach($sections as $si => $section)
            <div class="ls-sec" id="ls-{{ $section['key'] }}">
                @if($section['title'])
                    <h4 class="ls-title">
                        {{ $section['title'] }}
                        @if($section['kind'] !== 'text')
                            <span class="ls-count" x-text="countIn(@js(array_column($section['items'], 'key'))) + '/{{ count($section['items']) }}'"></span>
                        @endif
                    </h4>
                @endif
                @if(trim((string) $section['intro']) !== '')<div class="lesson-md">{{ $section['intro'] }}</div>@endif

                @if($section['kind'] !== 'text')
                    <ol class="ls-list">
                        @foreach($section['items'] as $item)
                            @if(!empty($item['lead']))
                                <li style="list-style:none" class="lesson-md mt-3">{{ $item['lead'] }}</li>
                            @endif
                            <li class="ls-item" :class="isOn('{{ $item['key'] }}') && 'is-done'">
                                <button type="button" class="ls-check" :class="isOn('{{ $item['key'] }}') && 'on'" @click="toggle('{{ $item['key'] }}')" :disabled="!canTick"
                                        :aria-pressed="isOn('{{ $item['key'] }}')" aria-label="Mark {{ $section['kind'] === 'checklist' ? 'item' : 'step' }} {{ $item['n'] }} as done">✓</button>
                                @if($section['kind'] === 'steps')<span class="ls-num">{{ $item['n'] }}.</span>@endif
                                <div class="ls-body">
                                    <div class="lesson-md">{{ $item['html'] }}</div>
                                    @if($section['kind'] === 'steps')
                                        @php $stepText = \Illuminate\Support\Str::limit($item['plain'], 300); @endphp
                                        <div x-data="{ open: false }">
                                            <button type="button" class="ls-stuck-btn" @click="open = !open" :aria-expanded="open">🆘 Stuck?</button>
                                            <div class="ls-help" x-show="open" x-cloak>
                                                <button type="button" @click="$dispatch('mentor-ask', { message: @js('I\'m on step ' . $item['n'] . ' of this lesson: "' . $stepText . '". Can you explain this step more simply, with a small example?') }); open = false">🧩 Explain it more simply</button>
                                                <button type="button" @click="$dispatch('mentor-ask', { message: @js('I\'m stuck on step ' . $item['n'] . ': "' . $stepText . '". Give me a hint for the next small thing to try, not the full answer.') }); open = false">💡 Give me a hint</button>
                                                <button type="button" @click="$dispatch('mentor-ask', { message: @js('For step ' . $item['n'] . ' ("' . $stepText . '"), show me a similar example on different data so I can see the idea, without solving my exercise.') }); open = false">🔎 Show an example</button>
                                                <button type="button" @click="$dispatch('ask-human', { question: @js('About step ' . $item['n'] . ' of "' . $activity->title . '" ("' . \Illuminate\Support\Str::limit($item['plain'], 120) . '"): ') , activity_id: @js($activity->id), step_key: @js($item['key']) }); open = false">🙋 Ask a human</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </li>
                            @if($si === $firstSteps && $placedChecks->has($item['n']))
                                @foreach($placedChecks[$item['n']] as $check)
                                    <li style="list-style:none">@include('partials.lesson.quick-check', ['check' => $check])</li>
                                @endforeach
                            @endif
                        @endforeach
                    </ol>
                @endif

                @if(trim((string) $section['outro']) !== '')<div class="lesson-md mt-3">{{ $section['outro'] }}</div>@endif

                @if($si === $firstSteps && $endChecks->isNotEmpty())
                    <div class="mt-4 space-y-3">
                        @foreach($endChecks as $check)
                            @include('partials.lesson.quick-check', ['check' => $check])
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="ls-done-banner" x-show="canTick && done() === keys.length && keys.length > 0" x-cloak x-transition>
        <p class="text-white font-semibold">🎉 Every step and self-check is ticked!</p>
        <p class="text-dark-200 text-sm mt-1">You're ready to submit. Tip: press <strong>Check my work first</strong> on the submit page to catch anything missing.</p>
    </div>
    <p class="text-dark-500 text-xs mt-4">Stuck on a step? Press 🆘 to ask {{ $mentorShort }} about exactly that step. You'll get hints, not answers.</p>
</div>

@once
@push('scripts')
<script>
function lessonSteps(cfg) {
    const chains = {}; // per step: saves for the same box happen in click order
    return {
        keys: cfg.keys, checked: cfg.checked, canTick: cfg.canTick, error: '',
        isOn(k) { return this.checked.includes(k); },
        done() { return this.checked.filter(k => this.keys.includes(k)).length; },
        countIn(list) { return list.filter(k => this.checked.includes(k)).length; },
        pct() { return this.keys.length ? Math.round(this.done() / this.keys.length * 100) : 0; },
        async toggle(k) {
            if (!this.canTick) return;
            const on = !this.isOn(k);
            this.checked = on ? [...this.checked, k] : this.checked.filter(x => x !== k);
            this.error = '';
            // Each tick is sent at once (the server locks the row); repeat clicks on the same box wait their turn.
            chains[k] = (chains[k] || Promise.resolve()).then(async () => {
                try {
                    // keepalive: the tick is still saved if the fellow leaves the page right away.
                    const res = await fetch(cfg.url, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ key: k, done: on }) });
                    if (!res.ok) throw new Error();
                } catch (e) {
                    this.checked = on ? this.checked.filter(x => x !== k) : [...this.checked, k];
                    this.error = 'Could not save that tick. Check your connection and try again.';
                }
            });
            await chains[k];
        },
    };
}
</script>
@endpush
@endonce
