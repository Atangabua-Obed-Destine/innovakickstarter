@extends('layouts.app')

@section('title', 'Orientation · ' . $track->name)

@section('content')
@include('partials.track.styles')
@php
    $fellow = auth()->user();
    $manager = $experience?->manager();
    $levels = [
        'rookie' => $experience?->levelTitle(\App\Enums\Tier::ROOKIE) ?? 'Rookie',
        'intern' => $experience?->levelTitle(\App\Enums\Tier::INTERN) ?? 'Intern',
    ];
    $skillLabels = collect($skills)->pluck('label', 'key');
    $meta = [
        'welcome' => ['icon' => '🪪', 'label' => 'Day one'],
        'day' => ['icon' => '⏰', 'label' => 'A day in the job'],
        'myths' => ['icon' => '🔍', 'label' => 'Myth or fact'],
        'ladder' => ['icon' => '🪜', 'label' => 'The career ladder'],
        'skills' => ['icon' => '🌌', 'label' => 'What seniors master'],
        'toolkit' => ['icon' => '🧰', 'label' => 'Your toolkit'],
        'plan' => ['icon' => '🗓️', 'label' => 'Plan your weeks'],
        'quiz' => ['icon' => '✅', 'label' => 'The rules'],
        'commit' => ['icon' => '✍️', 'label' => 'Your commitment'],
    ];
    $cfg = [
        'sections' => $sections,
        'done' => array_values(array_intersect($record->sections_done ?? [], $sections)),
        'completed' => (bool) $record->completed_at,
        'quizPassed' => (bool) $record->quiz_passed_at,
        'day' => $content['day'] ?? [],
        'myths' => $content['myths'] ?? [],
        'ladder' => array_reverse($content['ladder'] ?? []),
        'skills' => $skills,
        'skillLabels' => $skillLabels,
        'toolkit' => $toolkit,
        'plan' => $plan,
        'quiz' => $quiz,
        'defaults' => $defaults,
        'letterCanOpen' => $letterCanOpen,
        'letterOpened' => (bool) $record->letter_opened_at,
        'urls' => [
            'section' => route('curriculum.orientation.section'),
            'quiz' => route('curriculum.orientation.quiz'),
            'complete' => route('curriculum.orientation.complete'),
            'letter' => route('curriculum.orientation.letter'),
            'curriculum' => route('curriculum.index'),
        ],
        'csrf' => csrf_token(),
    ];
@endphp
<style>
    [x-cloak] { display: none !important; }
    .or-rail { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; }
    .or-dot { flex: 1 0 auto; min-width: 44px; display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 8px 6px; border-radius: 12px; border: 1px solid rgba(148,163,184,.15);
        background: rgba(15,20,29,.5); color: rgb(148 163 184); font-size: 11px; transition: all .2s; }
    .or-dot .ic { font-size: 18px; filter: grayscale(1); opacity: .6; transition: all .2s; }
    .or-dot.done .ic, .or-dot.on .ic { filter: none; opacity: 1; }
    .or-dot.done { border-color: color-mix(in srgb, var(--color-primary-500) 35%, transparent); color: var(--color-primary-200); }
    .or-dot.on { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 22%, #0f141d); color: #fff; box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary-500) 18%, transparent); }
    .or-dot:disabled { cursor: not-allowed; }
    .or-stage { animation: or-in .35s ease both; }
    @keyframes or-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .or-h { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1.25; }
    .or-kicker { color: var(--color-primary-300); font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }

    /* Staff card */
    .idc { position: relative; width: 100%; max-width: 340px; border-radius: 18px; overflow: hidden; color: #0f172a; background: #f8fafc;
        box-shadow: 0 24px 50px rgba(0,0,0,.45); transform-style: preserve-3d; animation: idc-in .9s cubic-bezier(.2,.8,.2,1) both; }
    @keyframes idc-in { from { opacity: 0; transform: perspective(800px) rotateY(-70deg) translateY(20px); } to { opacity: 1; transform: perspective(800px) rotateY(0); } }
    .idc-top { padding: 14px 18px; color: #fff; background: linear-gradient(135deg, var(--color-primary-600), var(--color-primary-900)); display: flex; align-items: center; justify-content: space-between; }
    .idc-photo { width: 76px; height: 76px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; color: #fff;
        background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-800)); border: 3px solid #fff; box-shadow: 0 6px 16px rgba(0,0,0,.2); }
    .idc-row { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
    .idc-val { font-size: 14px; font-weight: 700; color: #0f172a; }
    .idc-bar { height: 26px; background: repeating-linear-gradient(90deg, #0f172a 0 2px, transparent 2px 4px, #0f172a 4px 5px, transparent 5px 8px); opacity: .8; border-radius: 3px; }
    .idc-stamp { position: absolute; right: 18px; bottom: 14px; background: rgba(248,250,252,.85); padding: 4px 10px; border: 3px solid; border-radius: 8px; font-weight: 900; font-size: 13px; letter-spacing: .08em; transform: rotate(-12deg); }
    .idc-stamp.pending { color: #d97706; border-color: #d97706; opacity: .75; }
    .idc-stamp.ok { color: #16a34a; border-color: #16a34a; animation: stamp .5s cubic-bezier(.2,1.6,.4,1) both; }
    @keyframes stamp { from { opacity: 0; transform: rotate(-12deg) scale(2.4); } to { opacity: 1; transform: rotate(-12deg) scale(1); } }
    .or-letter p { animation: or-in .6s ease both; }
    .or-letter p:nth-child(2) { animation-delay: .15s; } .or-letter p:nth-child(3) { animation-delay: .3s; } .or-letter p:nth-child(4) { animation-delay: .45s; } .or-letter p:nth-child(5) { animation-delay: .6s; }

    /* Day */
    .tl { display: flex; gap: 8px; overflow-x: auto; padding: 4px 2px 8px; }
    .tl button { flex: 0 0 auto; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 10px 14px; border-radius: 14px; border: 1px solid rgba(148,163,184,.18); background: rgba(15,20,29,.55); color: rgb(203 213 225); }
    .tl button .t { font-family: ui-monospace, Menlo, monospace; font-size: 12px; color: rgb(148 163 184); }
    .tl button.seen { border-color: color-mix(in srgb, var(--color-primary-500) 40%, transparent); }
    .tl button.on { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 24%, #0f141d); color: #fff; transform: translateY(-2px); }
    .tl-card { border-radius: 16px; padding: 20px; background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 25%, #0f141d), #0f141d); border: 1px solid color-mix(in srgb, var(--color-primary-500) 30%, transparent); }

    /* Myths */
    .myth { border-radius: 18px; padding: 26px; text-align: center; background: radial-gradient(120% 120% at 50% 0%, color-mix(in srgb, var(--color-primary-700) 30%, #0f141d), #0f141d); border: 1px solid rgba(148,163,184,.18); }
    .myth-claim { font-size: 1.3rem; font-weight: 700; color: #fff; line-height: 1.4; }
    .myth-btn { padding: 12px 26px; border-radius: 14px; font-weight: 700; font-size: 1rem; border: 2px solid; transition: transform .1s; }
    .myth-btn:active { transform: scale(.96); }
    .myth-no { color: #fca5a5; border-color: rgba(248,113,113,.6); } .myth-no:hover { background: rgba(239,68,68,.1); }
    .myth-yes { color: #86efac; border-color: rgba(74,222,128,.6); } .myth-yes:hover { background: rgba(34,197,94,.1); }
    .cc-muted { color: #64748b; } .cc-body { color: #334155; } .cc-line { border-top: 1px solid #cbd5e1; }

    /* Ladder */
    .rung { width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; text-align: left; border: 1px solid rgba(148,163,184,.16); background: rgba(15,20,29,.55); transition: all .2s; }
    .rung:hover { border-color: color-mix(in srgb, var(--color-primary-400) 60%, transparent); }
    .rung.on { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 22%, #0f141d); }
    .rung.beyond { border-style: dashed; }
    .rung-ic { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: rgba(148,163,184,.1); flex-shrink: 0; }
    .flag { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; white-space: nowrap; }
    .finish-line { display: flex; align-items: center; gap: 8px; color: #fbbf24; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin: 6px 0; }
    .finish-line::before, .finish-line::after { content: ''; flex: 1; height: 2px; background: repeating-linear-gradient(90deg, #fbbf24 0 8px, transparent 8px 14px); }

    /* Constellation */
    .cons { width: 100%; max-width: 420px; margin: 0 auto; display: block; }
    .cons .node { cursor: pointer; }
    .cons .node circle { transition: all .2s; }
    .cons .node:hover circle.c, .cons .node.on circle.c { r: 36; }
    .cons line { stroke: color-mix(in srgb, var(--color-primary-400) 35%, transparent); stroke-width: 1.5; stroke-dasharray: 4 4; }
    .cons .node.on line { stroke: var(--color-primary-300); }
    .ch-strip { display: flex; gap: 4px; }
    .ch-strip span { flex: 1; text-align: center; padding: 6px 0; border-radius: 8px; font-size: 12px; font-weight: 700; background: rgba(148,163,184,.08); color: rgb(100 116 139); transition: all .25s; }
    .ch-strip span.hit { background: var(--color-primary-500); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 14px color-mix(in srgb, var(--color-primary-500) 35%, transparent); }

    /* Toolkit flip cards */
    .flip { perspective: 900px; height: 190px; cursor: pointer; }
    .flip-in { position: relative; width: 100%; height: 100%; transition: transform .55s cubic-bezier(.2,.8,.2,1); transform-style: preserve-3d; }
    .flip.on .flip-in { transform: rotateY(180deg); }
    .flip-f, .flip-b { position: absolute; inset: 0; border-radius: 16px; padding: 16px; backface-visibility: hidden; -webkit-backface-visibility: hidden; border: 1px solid rgba(148,163,184,.18); }
    .flip-f { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; text-align: center; background: rgba(15,20,29,.7); }
    .flip-b { transform: rotateY(180deg); overflow-y: auto; background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 35%, #0f141d), #0f141d); border-color: color-mix(in srgb, var(--color-primary-500) 40%, transparent); }

    /* Plan */
    .seg { display: flex; height: 38px; border-radius: 10px; overflow: hidden; gap: 2px; }
    .seg div { display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; background: color-mix(in srgb, var(--color-primary-600) 70%, #0f141d); }
    .seg div:nth-child(even) { background: color-mix(in srgb, var(--color-primary-500) 70%, #0f141d); }
    .or-range { width: 100%; accent-color: var(--color-primary-500); }
    .daypick button { width: 46px; padding: 8px 0; border-radius: 10px; font-size: 13px; font-weight: 600; border: 1px solid rgba(148,163,184,.2); color: rgb(148 163 184); }
    .daypick button.on { background: var(--color-primary-600); border-color: var(--color-primary-500); color: #fff; }

    /* Quiz & commitment */
    .qz-opt { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(148,163,184,.2); cursor: pointer; color: rgb(226 232 240); font-size: 14px; }
    .qz-opt.picked { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 14%, transparent); }
    .or-input { color-scheme: dark; width: 100%; background: #0b0f16; border: 1px solid rgba(148,163,184,.25); border-radius: 10px; padding: 10px 12px; color: #fff; }
    .or-input:focus { outline: none; border-color: var(--color-primary-500); }
    .commit-card { border-radius: 18px; padding: 22px; color: #0f172a; background: #fdfcf7; box-shadow: 0 20px 40px rgba(0,0,0,.4); position: relative; }
    .commit-card .sig { font-family: 'Segoe Script', 'Brush Script MT', 'Lucida Handwriting', cursive; font-size: 24px; color: #1e3a8a; min-height: 34px; }
    .envelope { border-radius: 14px; padding: 18px; display: flex; align-items: center; gap: 14px; background: #f5efe0; color: #3f3a2e; border: 1px solid #e5dcc3; }
    .confetti { position: fixed; inset: 0; pointer-events: none; z-index: 90; overflow: hidden; }
    .confetti i { position: absolute; top: -12px; width: 9px; height: 14px; border-radius: 2px; animation: fall linear forwards; }
    @keyframes fall { to { transform: translateY(105vh) rotate(720deg); opacity: .9; } }
</style>

<div class="max-w-5xl mx-auto space-y-5" x-data="orientation(@js($cfg))" x-init="start()">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('curriculum.index') }}" class="text-dark-400 hover:text-white text-sm">← My curriculum</a>
        <span class="text-dark-400 text-sm">{{ $experience?->emblem() }} {{ $track->name }} · Chapter 0</span>
    </div>
    @if(session('warning'))<div class="p-4 rounded-xl border bg-amber-500/10 border-amber-500/30 text-amber-300">{{ session('warning') }}</div>@endif

    {{-- Progress rail --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-white font-semibold">Orientation <span class="text-dark-400 font-normal text-sm" x-text="'· ' + doneCount() + ' of ' + sections.length + ' done'"></span></p>
            <span class="text-dark-400 text-xs" x-show="!completed">{{ $required ? 'Chapter 1 opens when you finish' : 'Recommended for everyone in the track' }}</span>
            <span class="text-green-400 text-xs font-semibold" x-show="completed" x-cloak>✓ Complete</span>
        </div>
        <div class="or-rail">
            @foreach($sections as $s)
                <button type="button" class="or-dot" :class="{ on: step === '{{ $s }}', done: isDone('{{ $s }}') }" :disabled="!canVisit('{{ $s }}')" @click="go('{{ $s }}')" title="{{ $meta[$s]['label'] }}">
                    <span class="ic">{{ $meta[$s]['icon'] }}</span><span class="hidden sm:block">{{ $meta[$s]['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ========================= 1. WELCOME ========================= --}}
    <section x-show="step === 'welcome'" class="or-stage card p-6 sm:p-8">
        <div class="grid md:grid-cols-2 gap-8 items-start">
            <div class="flex flex-col items-center gap-4">
                <div class="idc">
                    <div class="idc-top">
                        <span class="font-bold">{{ $experience?->company() ?? 'I-NNOVA Kickstarter' }}</span>
                        <span class="text-2xl">{{ $experience?->emblem() }}</span>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="idc-photo">{{ mb_strtoupper(mb_substr($fellow->name, 0, 1) . mb_substr(explode(' ', trim($fellow->name))[1] ?? '', 0, 1)) }}</div>
                            <div class="min-w-0">
                                <p class="idc-row">Name</p>
                                <p class="idc-val truncate">{{ $fellow->name }}</p>
                                <p class="idc-row mt-2">Role</p>
                                <p class="idc-val">{{ $levels['rookie'] }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div><p class="idc-row">Team</p><p class="idc-val text-sm">{{ $experience?->team() ?? $track->name }}</p></div>
                            <div><p class="idc-row">Staff no.</p><p class="idc-val text-sm">{{ $staffNumber }}</p></div>
                            <div><p class="idc-row">Started</p><p class="idc-val text-sm">{{ $startedOn->format('j M Y') }}</p></div>
                            <div><p class="idc-row">Next level</p><p class="idc-val text-sm">{{ $levels['intern'] }}</p></div>
                        </div>
                        <div class="idc-bar mt-4"></div>
                    </div>
                    <span class="idc-stamp pending" x-show="!completed">ORIENTATION PENDING</span>
                    <span class="idc-stamp ok" x-show="completed" x-cloak>ONBOARDED ✓</span>
                </div>
                @if($classmates->isNotEmpty())
                    <p class="text-dark-400 text-sm text-center">👋 {{ $classmates->take(3)->implode(', ') }}{{ $classmates->count() > 3 ? ' and others' : '' }} already finished orientation.</p>
                @endif
            </div>
            <div>
                <p class="or-kicker">Day one</p>
                <h1 class="or-h mt-1">Welcome{{ $experience?->team() ? ' to the ' . $experience->team() : '' }}, {{ explode(' ', trim($fellow->name))[0] }}.</h1>
                <div class="flex items-center gap-3 mt-5">
                    <div class="tx-avatar">{{ $manager['initials'] ?? 'IK' }}</div>
                    <div>
                        <p class="text-white font-semibold text-sm">{{ $manager['name'] ?? 'The I-NNOVA team' }}</p>
                        <p class="text-dark-400 text-xs">{{ $manager['title'] ?? 'I-NNOVA Kickstarter' }}</p>
                    </div>
                </div>
                <div class="lesson-md or-letter mt-4">
                    {{ \App\Support\LessonMarkdown::render($content['welcome'] ?? "Welcome to the **{$track->name}** track.\n\nEvery expert in this field started exactly where you are today. What made them senior was not talent: it was many small problems solved well, explained clearly, week after week.\n\nThis orientation shows you the whole road, how the program works, and how to plan your weeks. Take your time.") }}
                </div>
                <button type="button" class="btn-primary px-6 py-3 mt-6" @click="next()">Accept my staff card →</button>
            </div>
        </div>
    </section>

    {{-- ========================= 2. DAY IN THE LIFE ========================= --}}
    @if(in_array('day', $sections, true))
    <section x-show="step === 'day'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">A day in the job</p>
        <h2 class="or-h mt-1">What does a {{ $experience?->role() ?? 'professional' }} actually do all day?</h2>
        <p class="text-dark-300 mt-2">Tap each moment of a real working day{{ $experience?->company() ? ' at ' . $experience->company() : '' }}. Every skill you see is one you'll practise in this track.</p>
        <div class="tl mt-5">
            <template x-for="(m, i) in day" :key="i">
                <button type="button" :class="{ on: dayIndex === i, seen: daySeen.includes(i) }" @click="openMoment(i)">
                    <span class="text-xl" x-text="m.icon || '•'"></span>
                    <span class="t" x-text="m.time"></span>
                </button>
            </template>
        </div>
        <template x-if="dayIndex !== null">
            <div class="tl-card mt-4 or-stage" :key="dayIndex">
                <div class="flex items-start gap-4">
                    <div class="text-4xl" x-text="day[dayIndex].icon"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-dark-400 font-mono text-sm" x-text="day[dayIndex].time"></p>
                        <h3 class="text-white text-xl font-bold" x-text="day[dayIndex].title"></h3>
                        <p class="text-dark-200 mt-2" x-text="day[dayIndex].story"></p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="tx-chip" x-show="day[dayIndex].skill" x-text="'🎯 Skill: ' + (skillLabels[day[dayIndex].skill] || day[dayIndex].skill)"></span>
                            <span class="tx-chip" x-show="day[dayIndex].chapter" x-text="'📘 You learn this in Chapter ' + day[dayIndex].chapter + chapterName(day[dayIndex].chapter)"></span>
                        </div>
                        <p class="text-primary-200 text-sm mt-3 italic" x-show="day[dayIndex].lesson" x-text="'💡 ' + day[dayIndex].lesson"></p>
                    </div>
                </div>
            </div>
        </template>
        <p class="text-dark-400 text-sm mt-4" x-show="dayIndex === null">👆 Start with the first moment of the day.</p>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <div class="flex items-center gap-3">
                <span class="text-dark-400 text-sm" x-text="daySeen.length + ' / ' + day.length + ' moments'"></span>
                <button type="button" class="btn-primary" :disabled="daySeen.length < day.length && !isDone('day')" @click="next()">Continue →</button>
            </div>
        </div>
    </section>
    @endif

    {{-- ========================= 3. MYTHS ========================= --}}
    @if(in_array('myths', $sections, true))
    <section x-show="step === 'myths'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">Myth or fact?</p>
        <h2 class="or-h mt-1">Let's clear up a few things people believe about this job.</h2>
        <div class="mt-6" x-show="mythIndex < myths.length">
            <div class="myth">
                <p class="text-dark-400 text-sm" x-text="'Claim ' + (mythIndex + 1) + ' of ' + myths.length"></p>
                <p class="myth-claim mt-3" x-text="myths[mythIndex]?.claim"></p>
                <div class="flex justify-center gap-4 mt-6" x-show="mythAnswer === null">
                    <button type="button" class="myth-btn myth-no" @click="answerMyth(false)">🙅 Myth</button>
                    <button type="button" class="myth-btn myth-yes" @click="answerMyth(true)">✅ Fact</button>
                </div>
                <div class="mt-5" x-show="mythAnswer !== null" x-cloak>
                    <p class="text-lg font-bold" :class="mythAnswer === myths[mythIndex]?.truth ? 'text-green-400' : 'text-amber-300'"
                       x-text="(mythAnswer === myths[mythIndex]?.truth ? 'Spot on! ' : 'Not quite. ') + 'It\'s a ' + (myths[mythIndex]?.truth ? 'FACT.' : 'MYTH.')"></p>
                    <p class="text-dark-200 mt-2 max-w-xl mx-auto" x-text="myths[mythIndex]?.explain"></p>
                    <button type="button" class="btn-primary mt-5" @click="nextMyth()" x-text="mythIndex + 1 < myths.length ? 'Next claim →' : 'See my score'"></button>
                </div>
            </div>
        </div>
        <div class="mt-6 myth" x-show="mythIndex >= myths.length" x-cloak>
            <p class="text-5xl">🧠</p>
            <p class="myth-claim mt-3" x-text="'You got ' + mythScore + ' of ' + myths.length + ' right.'"></p>
            <p class="text-dark-300 mt-2" x-text="mythScore >= myths.length - 1 ? 'You already think like an analyst: question what everyone believes.' : 'That\'s exactly why we start here. Good analysts check what everyone believes.'"></p>
            <button type="button" class="text-primary-300 text-sm mt-4 hover:text-white" @click="mythIndex = 0; mythScore = 0; mythAnswer = null">Play again</button>
        </div>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <button type="button" class="btn-primary" :disabled="mythIndex < myths.length && !isDone('myths')" @click="next()">Continue →</button>
        </div>
    </section>
    @endif

    {{-- ========================= 4. LADDER ========================= --}}
    @if(in_array('ladder', $sections, true))
    <section x-show="step === 'ladder'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">The career ladder</p>
        <h2 class="or-h mt-1">From your first day to the top of the field</h2>
        <p class="text-dark-300 mt-2">Every senior climbed these same rungs. Tap each one to see what people at that level do, and how this program gets you started.</p>
        <div class="grid md:grid-cols-2 gap-6 mt-6">
            <div class="space-y-2">
                <template x-for="(r, i) in ladder" :key="r.key || i">
                    <div>
                        <div class="finish-line" x-show="r.marker === 'finish'">🏁 Program finish line</div>
                        <button type="button" class="rung" :class="{ on: rungIndex === i, beyond: isBeyond(i) }" @click="openRung(i)">
                            <span class="rung-ic" x-text="r.icon || '•'"></span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-white font-semibold" x-text="r.title"></span>
                                <span class="block text-dark-400 text-xs" x-text="r.years"></span>
                            </span>
                            <span class="flag bg-primary-500/20 text-primary-300" x-show="r.marker === 'you'">📍 You are here</span>
                            <span class="flag bg-amber-500/20 text-amber-300" x-show="r.marker === 'finish'">Your goal</span>
                            <span class="text-green-400" x-show="rungSeen.includes(i)">✓</span>
                        </button>
                    </div>
                </template>
                <p class="text-dark-500 text-xs mt-2">{{ $content['ladder_note'] ?? '' }}</p>
            </div>
            <div>
                <template x-if="rungIndex !== null">
                    <div class="tl-card or-stage" :key="'r' + rungIndex">
                        <p class="text-4xl" x-text="ladder[rungIndex].icon"></p>
                        <h3 class="text-white text-xl font-bold mt-2" x-text="ladder[rungIndex].title"></h3>
                        <p class="text-dark-400 text-sm" x-text="ladder[rungIndex].years"></p>
                        <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider mt-4">What they do</p>
                        <p class="text-dark-200 mt-1" x-text="ladder[rungIndex].does"></p>
                        <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider mt-4">Trusted with</p>
                        <p class="text-dark-200 mt-1" x-text="ladder[rungIndex].trusted"></p>
                        <div class="mt-4 p-3 rounded-lg bg-dark-800/70" x-show="ladder[rungIndex].program">
                            <p class="text-amber-300 text-xs font-semibold uppercase tracking-wider" x-text="isBeyond(rungIndex) ? '🌱 Seeds you plant in the program' : '🎓 In this program'"></p>
                            <p class="text-dark-200 text-sm mt-1" x-text="ladder[rungIndex].program"></p>
                        </div>
                    </div>
                </template>
                <div class="tl-card text-center" x-show="rungIndex === null">
                    <p class="text-4xl">🪜</p>
                    <p class="text-dark-300 mt-2">Start at the bottom: that's you, today.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <div class="flex items-center gap-3">
                <span class="text-dark-400 text-sm" x-text="rungSeen.length + ' / ' + ladder.length + ' levels explored'"></span>
                <button type="button" class="btn-primary" :disabled="rungSeen.length < Math.min(3, ladder.length) && !isDone('ladder')" @click="next()">Continue →</button>
            </div>
        </div>
    </section>
    @endif

    {{-- ========================= 5. SKILLS ========================= --}}
    @if(in_array('skills', $sections, true))
    <section x-show="step === 'skills'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">What seniors master</p>
        <h2 class="or-h mt-1">Everything a senior can do, and where you'll start learning it</h2>
        <p class="text-dark-300 mt-2">Tap a star. You'll see what a senior can do with that skill, and the exact chapters and activities in your curriculum that train it.</p>
        <div class="grid md:grid-cols-2 gap-6 mt-6 items-center">
            <svg class="cons" viewBox="0 0 400 400" role="img" aria-label="Skill constellation">
                <defs>
                    <radialGradient id="or-glow"><stop offset="0%" stop-color="var(--color-primary-400)" stop-opacity=".55"/><stop offset="100%" stop-color="var(--color-primary-400)" stop-opacity="0"/></radialGradient>
                </defs>
                <circle cx="200" cy="200" r="70" fill="url(#or-glow)"/>
                <circle cx="200" cy="200" r="40" fill="var(--color-primary-700)" stroke="var(--color-primary-300)" stroke-width="2"/>
                <text x="200" y="196" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">Senior</text>
                <text x="200" y="212" text-anchor="middle" fill="#fff" font-size="10">{{ \Illuminate\Support\Str::limit($experience?->role() ?? $track->name, 16) }}</text>
                {{-- Drawn on the server: Alpine's x-for doesn't work inside SVG. --}}
                @foreach($skills as $i => $s)
                    @php
                        $angle = -M_PI / 2 + $i * 2 * M_PI / max(1, count($skills));
                        $x = round(200 + 150 * cos($angle), 1);
                        $y = round(200 + 150 * sin($angle), 1);
                    @endphp
                    <g class="node" :class="{ on: skillIndex === {{ $i }} }" @click="openSkill({{ $i }})" tabindex="0" @keydown.enter="openSkill({{ $i }})" role="button" aria-label="{{ $s['label'] }}">
                        <line x1="200" y1="200" x2="{{ $x }}" y2="{{ $y }}"></line>
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="38" fill="url(#or-glow)" x-show="skillIndex === {{ $i }}"></circle>
                        <circle class="c" cx="{{ $x }}" cy="{{ $y }}" r="32" :fill="skillSeen.includes({{ $i }}) ? 'var(--color-primary-600)' : '#1a212d'" stroke="var(--color-primary-400)" stroke-width="2"></circle>
                        @php
                            $words = preg_split('/\s+/', trim($s['label']));
                            $lines = mb_strlen($s['label']) > 9 && count($words) > 1 ? [array_shift($words), implode(' ', $words)] : [$s['label']];
                            $size = max(array_map('mb_strlen', $lines)) > 10 ? 8 : 10;
                        @endphp
                        <text x="{{ $x }}" y="{{ $y + (count($lines) > 1 ? -2 : 4) }}" text-anchor="middle" fill="#fff" font-size="{{ $size }}" font-weight="700">
                            @foreach($lines as $li => $line)<tspan x="{{ $x }}" dy="{{ $li ? 11 : 0 }}">{{ $line }}</tspan>@endforeach
                        </text>
                    </g>
                @endforeach
            </svg>
            <div>
                <template x-if="skillIndex !== null">
                    <div class="tl-card or-stage" :key="'s' + skillIndex">
                        <h3 class="text-white text-xl font-bold" x-text="skills[skillIndex].label"></h3>
                        <p class="text-dark-400 text-sm" x-text="skills[skillIndex].description"></p>
                        <template x-if="skills[skillIndex].senior">
                            <div class="mt-4">
                                <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider">A senior can...</p>
                                <p class="text-dark-100 mt-1" x-text="skills[skillIndex].senior"></p>
                            </div>
                        </template>
                        <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider mt-4">Where you'll train it</p>
                        <div class="ch-strip mt-2">
                            <template x-for="c in plan.chapters" :key="c.number">
                                <span :class="skills[skillIndex].chapters.includes(c.number) && 'hit'" :title="c.title" x-text="c.number"></span>
                            </template>
                        </div>
                        <ul class="mt-3 space-y-1 text-sm text-dark-200">
                            <template x-for="(a, j) in skills[skillIndex].activities.slice(0, 6)" :key="j">
                                <li><span class="text-dark-500" x-text="'Ch ' + a.chapter + ' · '"></span><span x-text="a.title"></span></li>
                            </template>
                        </ul>
                        <p class="text-dark-500 text-xs mt-2" x-show="skills[skillIndex].activities.length > 6" x-text="'+ ' + (skills[skillIndex].activities.length - 6) + ' more activities'"></p>
                        <p class="text-dark-500 text-xs mt-2" x-show="!skills[skillIndex].activities.length">This skill grows across the whole track.</p>
                    </div>
                </template>
                <div class="tl-card text-center" x-show="skillIndex === null">
                    <p class="text-4xl">✨</p>
                    <p class="text-dark-300 mt-2">{{ count($skills) }} skills make a senior {{ $experience?->role() ?? 'professional' }}. Tap one to begin.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <div class="flex items-center gap-3">
                <span class="text-dark-400 text-sm" x-text="skillSeen.length + ' / ' + skills.length + ' skills explored'"></span>
                <button type="button" class="btn-primary" :disabled="skillSeen.length < Math.min(3, skills.length) && !isDone('skills')" @click="next()">Continue →</button>
            </div>
        </div>
    </section>
    @endif

    {{-- ========================= 6. TOOLKIT ========================= --}}
    <section x-show="step === 'toolkit'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">Your toolkit</p>
        <h2 class="or-h mt-1">How the program works for you</h2>
        <p class="text-dark-300 mt-2">Flip the cards. Each one is something you'll use, and what it does for your career.</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-6">
            <template x-for="(t, i) in toolkit" :key="i">
                <div class="flip" :class="flipped.includes(i) && 'on'" @click="flip(i)" @keydown.enter="flip(i)" tabindex="0" role="button" :aria-label="t.name">
                    <div class="flip-in">
                        <div class="flip-f">
                            <span class="text-4xl" x-text="t.icon"></span>
                            <span class="text-white font-semibold" x-text="t.name"></span>
                            <span class="text-dark-500 text-xs">Tap to flip</span>
                        </div>
                        <div class="flip-b">
                            <p class="text-white font-semibold text-sm" x-text="t.icon + ' ' + t.name"></p>
                            <p class="text-dark-200 text-sm mt-1" x-text="t.what"></p>
                            <p class="text-primary-200 text-sm mt-2" x-text="'→ ' + t.gives"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div class="grid md:grid-cols-2 gap-4 mt-8">
            <div class="tl-card">
                <p class="text-white font-semibold">🌟 Habits that work</p>
                <ul class="mt-3 space-y-2">
                    @foreach($habits['do'] as $h)<li class="text-dark-200 text-sm flex gap-2"><span>{{ $h['icon'] }}</span><span>{{ $h['text'] }}</span></li>@endforeach
                </ul>
            </div>
            <div class="tl-card" style="background: linear-gradient(135deg, rgba(127,29,29,.25), #0f141d); border-color: rgba(248,113,113,.3);">
                <p class="text-white font-semibold">🚧 Traps that make fellows fall behind</p>
                <ul class="mt-3 space-y-2">
                    @foreach($habits['avoid'] as $h)<li class="text-dark-200 text-sm flex gap-2"><span>{{ $h['icon'] }}</span><span>{{ $h['text'] }}</span></li>@endforeach
                </ul>
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <div class="flex items-center gap-3">
                <span class="text-dark-400 text-sm" x-text="flipped.length + ' / ' + toolkit.length + ' cards flipped'"></span>
                <button type="button" class="btn-primary" :disabled="flipped.length < Math.min(5, toolkit.length) && !isDone('toolkit')" @click="next()">Continue →</button>
            </div>
        </div>
    </section>

    {{-- ========================= 7. PLAN ========================= --}}
    <section x-show="step === 'plan'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">Plan your weeks</p>
        <h2 class="or-h mt-1">How much time will you give this?</h2>
        <p class="text-dark-300 mt-2" x-text="'The track has ' + plan.chapters.length + ' chapters planned over about ' + plan.weeks + ' weeks. The required work is roughly ' + plan.total_hours + ' hours: about ' + plan.pace + ' hours a week to keep up with deadlines.'"></p>
        <div class="seg mt-5" aria-hidden="true">
            <template x-for="c in plan.chapters" :key="c.number">
                <div :style="'flex:' + Math.max(1, c.days)" :title="'Chapter ' + c.number + ': ' + c.title + ' · about ' + c.hours + ' h'" x-text="c.number"></div>
            </template>
        </div>
        <p class="text-dark-500 text-xs mt-1">Each block is a chapter, sized by its planned days. Hover for the estimated hours.</p>

        <div class="grid md:grid-cols-2 gap-6 mt-6">
            <div>
                <label class="text-white font-semibold" for="or-hours">Hours I can study each week: <span class="text-primary-300" x-text="form.weekly_hours"></span></label>
                <input id="or-hours" type="range" min="2" max="30" step="1" class="or-range mt-3" x-model.number="form.weekly_hours">
                <p class="text-white font-semibold mt-5">My study days</p>
                <div class="daypick flex flex-wrap gap-2 mt-2">
                    <template x-for="d in days" :key="d.k">
                        <button type="button" :class="form.study_days.includes(d.k) && 'on'" @click="toggleDay(d.k)" x-text="d.l"></button>
                    </template>
                </div>
            </div>
            <div class="tl-card" :style="paceOk() ? '' : 'border-color: rgba(251,191,36,.45)'">
                <p class="text-5xl font-extrabold text-white" x-text="weeksNeeded() + ' weeks'"></p>
                <p class="text-dark-300 text-sm">to finish the required work at <span x-text="form.weekly_hours"></span> hours a week</p>
                <p class="mt-3 font-semibold" :class="paceOk() ? 'text-green-400' : 'text-amber-300'"
                   x-text="paceOk() ? '✅ You keep pace with the ' + plan.weeks + '-week plan, with some time for practice.' : '⚠️ That is slower than the ' + plan.weeks + '-week plan, so deadlines would pile up. Aim for at least ' + plan.pace + ' hours.'"></p>
                <p class="text-dark-300 text-sm mt-3" x-show="form.study_days.length" x-text="'≈ ' + perDay() + ' on each of your ' + form.study_days.length + ' study days.'"></p>
                <p class="text-dark-500 text-xs mt-3">Estimates are rough: some fellows are faster, some slower. What matters most is showing up regularly.</p>
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <button type="button" class="btn-primary" :disabled="!form.study_days.length" @click="next()">This is my plan →</button>
        </div>
    </section>

    {{-- ========================= 8. QUIZ ========================= --}}
    <section x-show="step === 'quiz'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">The rules</p>
        <h2 class="or-h mt-1">A few questions to make sure nothing surprises you later</h2>
        <p class="text-dark-300 mt-2">Get them all right to continue. You can try again as often as you need.</p>
        <div class="space-y-5 mt-6">
            <template x-for="(q, qi) in quiz" :key="q.key">
                <div class="tl-card" :style="quizResults[q.key] ? (quizResults[q.key].correct ? 'border-color: rgba(34,197,94,.5)' : 'border-color: rgba(248,113,113,.5)') : ''">
                    <p class="text-white font-semibold" x-text="(qi + 1) + '. ' + q.question"></p>
                    <div class="space-y-2 mt-3">
                        <template x-for="(o, oi) in q.options" :key="oi">
                            <label class="qz-opt" :class="quizAnswers[q.key] === oi && 'picked'">
                                <input type="radio" :name="'q-' + q.key" class="mt-1 accent-primary-500" :value="oi" @change="quizAnswers[q.key] = oi; delete quizResults[q.key]" :checked="quizAnswers[q.key] === oi" :disabled="quizPassed">
                                <span x-text="o"></span>
                            </label>
                        </template>
                    </div>
                    <p class="text-green-400 text-sm mt-2" x-show="quizResults[q.key]?.correct" x-text="'✅ ' + (quizResults[q.key]?.explain || 'Right!')"></p>
                    <p class="text-red-300 text-sm mt-2" x-show="quizResults[q.key] && !quizResults[q.key].correct">Not quite. Read the options again and pick another.</p>
                </div>
            </template>
        </div>
        <p class="text-amber-300 text-sm mt-3" x-show="quizError" x-text="quizError"></p>
        <div class="flex items-center justify-between mt-6">
            <button type="button" class="btn-secondary" @click="back()">← Back</button>
            <button type="button" class="btn-primary" x-show="!quizPassed" :disabled="busy || Object.keys(quizAnswers).length < quiz.length" @click="submitQuiz()" x-text="busy ? 'Checking...' : 'Check my answers'"></button>
            <button type="button" class="btn-primary" x-show="quizPassed" x-cloak @click="next()">All correct, continue →</button>
        </div>
    </section>

    {{-- ========================= 9. COMMITMENT ========================= --}}
    <section x-show="step === 'commit'" x-cloak class="or-stage card p-6 sm:p-8">
        <p class="or-kicker">Your commitment</p>
        <h2 class="or-h mt-1">Make a promise to yourself</h2>
        <p class="text-dark-300 mt-2">People who write down a clear goal and a plan are far more likely to follow through. Your letter stays sealed until you finish the track; then you can open it and see how far you came.</p>
        <div class="grid md:grid-cols-2 gap-6 mt-6">
            <form class="space-y-4" @submit.prevent="commit()">
                <div>
                    <label class="text-dark-300 text-sm">The role I'm working towards</label>
                    <input class="or-input mt-1" x-model="form.goal_role" maxlength="120" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-dark-300 text-sm">By (month)</label>
                        <input type="month" class="or-input mt-1" x-model="form.goal_month" :min="minMonth" required>
                    </div>
                    <div>
                        <label class="text-dark-300 text-sm">Hours per week</label>
                        <input type="number" min="2" max="60" class="or-input mt-1" x-model.number="form.weekly_hours" required>
                    </div>
                </div>
                <div>
                    <label class="text-dark-300 text-sm">✉️ A letter to my future self: why I'm doing this, and what I want to be true when I finish</label>
                    <textarea class="or-input mt-1" rows="6" x-model="form.why" maxlength="2000" placeholder="Dear future me, ..." required></textarea>
                    <p class="text-dark-500 text-xs mt-1" x-text="form.why.trim().length < 40 ? (40 - form.why.trim().length) + ' more characters' : 'Sealed until you complete the track.'"></p>
                </div>
                <div>
                    <label class="text-dark-300 text-sm">Sign with your full name</label>
                    <input class="or-input mt-1" x-model="form.signature" maxlength="120" placeholder="{{ $fellow->name }}" required>
                </div>
                <p class="text-amber-300 text-sm" x-show="commitError" x-text="commitError"></p>
                <div class="flex items-center justify-between pt-2">
                    <button type="button" class="btn-secondary" @click="back()">← Back</button>
                    <button type="submit" class="btn-primary px-6 py-3" :disabled="busy || !quizPassed || form.why.trim().length < 40 || !form.signature.trim()"
                            x-text="busy ? 'Saving...' : (completed ? 'Update my commitment' : 'Sign and start Chapter 1 🚀')"></button>
                </div>
                <p class="text-dark-500 text-xs" x-show="!quizPassed">Pass the rules quiz first.</p>
            </form>
            <div class="space-y-4">
                <div class="commit-card">
                    <p class="text-xs uppercase tracking-widest cc-muted">My commitment · {{ $track->name }}</p>
                    <p class="text-lg font-bold mt-2">I, <span x-text="form.signature || '{{ $fellow->name }}'"></span>, am becoming a <span x-text="form.goal_role || '...'"></span> by <span x-text="monthLabel()"></span>.</p>
                    <p class="mt-2 cc-body">I will study <strong x-text="form.weekly_hours"></strong> hours a week, on <span x-text="dayList()"></span>. When I'm stuck, I'll ask. When I learn something, I'll explain it.</p>
                    <div class="sig mt-4" x-text="form.signature"></div>
                    <p class="text-xs cc-muted cc-line pt-1 mt-1">Signature · {{ now()->format('j F Y') }}</p>
                </div>
                <div class="envelope">
                    <span class="text-4xl">✉️</span>
                    <div class="flex-1">
                        <p class="font-semibold" x-text="letterText ? 'Your letter, opened' : 'Letter to your future self'"></p>
                        <p class="text-sm" x-show="!letterText" x-text="letterCanOpen ? 'You finished the track. You can open it now!' : 'Sealed until you complete every required activity of the track.'"></p>
                        <p class="text-sm mt-2 whitespace-pre-line" x-show="letterText" x-text="letterText"></p>
                    </div>
                    <button type="button" class="btn-primary text-sm px-3 py-2" x-show="letterCanOpen && !letterText" @click="openLetter()">Open</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================= CELEBRATION ========================= --}}
    <div x-show="celebrate" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 80; background: rgba(3,7,18,.82);">
        <div class="card p-8 max-w-md w-full text-center or-stage">
            <p class="text-6xl">🎉</p>
            <h2 class="or-h mt-3">You're officially on the team!</h2>
            <p class="text-dark-300 mt-2">Your staff card is stamped. Chapter 1 is open, and your first activity is waiting for you.</p>
            <a :href="urls.curriculum" class="btn-primary px-6 py-3 mt-6 inline-block">Go to Chapter 1 →</a>
        </div>
    </div>
    <div class="confetti" x-ref="confetti" aria-hidden="true"></div>
</div>
@if($track)
    @include('partials.mentor.chat', ['track' => $track, 'contextType' => 'general', 'contextId' => null])
@endif
@endsection

@push('scripts')
<script>
function orientation(cfg) {
    const order = cfg.sections;
    return {
        ...cfg, step: order[0], busy: false,
        dayIndex: null, daySeen: [],
        mythIndex: 0, mythAnswer: null, mythScore: 0,
        rungIndex: null, rungSeen: [],
        skillIndex: null, skillSeen: [],
        flipped: [],
        quizAnswers: {}, quizResults: {}, quizError: '',
        form: Object.assign({}, cfg.defaults, { study_days: [...cfg.defaults.study_days] }),
        commitError: '', celebrate: false, letterText: '',
        minMonth: new Date().toISOString().slice(0, 7),
        days: [{ k: 'mon', l: 'Mon' }, { k: 'tue', l: 'Tue' }, { k: 'wed', l: 'Wed' }, { k: 'thu', l: 'Thu' }, { k: 'fri', l: 'Fri' }, { k: 'sat', l: 'Sat' }, { k: 'sun', l: 'Sun' }],
        start() {
            // Resume at the first section not done yet (or the commitment when everything is done).
            const firstOpen = order.find(s => !this.done.includes(s));
            this.step = this.completed ? order[0] : (firstOpen || order[order.length - 1]);
        },
        isDone(s) { return this.done.includes(s); },
        doneCount() { return order.filter(s => this.done.includes(s)).length; },
        canVisit(s) { const i = order.indexOf(s); return this.completed || i === 0 || order.slice(0, i).every(x => this.done.includes(x)); },
        go(s) { if (this.canVisit(s)) { this.step = s; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
        markDone(s) {
            if (!this.done.includes(s)) this.done.push(s);
            if (s === 'quiz' || s === 'commit') return;
            fetch(cfg.urls.section, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ section: s }) }).catch(() => {});
        },
        next() {
            this.markDone(this.step);
            const i = order.indexOf(this.step);
            if (i + 1 < order.length) this.go(order[i + 1]);
        },
        back() { const i = order.indexOf(this.step); if (i > 0) this.go(order[i - 1]); },
        chapterName(n) { const c = this.plan.chapters[n - 1]; return c ? ': ' + c.title : ''; },
        openMoment(i) { this.dayIndex = i; if (!this.daySeen.includes(i)) this.daySeen.push(i); },
        answerMyth(v) { this.mythAnswer = v; if (v === this.myths[this.mythIndex].truth) this.mythScore++; },
        nextMyth() { this.mythIndex++; this.mythAnswer = null; },
        isBeyond(i) { const f = this.ladder.findIndex(r => r.marker === 'finish'); return f >= 0 && i < f; },
        openRung(i) { this.rungIndex = i; if (!this.rungSeen.includes(i)) this.rungSeen.push(i); },
        pos(i) { const n = this.skills.length, a = -Math.PI / 2 + i * 2 * Math.PI / n; return { x: 200 + 150 * Math.cos(a), y: 200 + 150 * Math.sin(a) }; },
        openSkill(i) { this.skillIndex = i; if (!this.skillSeen.includes(i)) this.skillSeen.push(i); },
        flip(i) { if (this.flipped.includes(i)) this.flipped = this.flipped.filter(x => x !== i); else this.flipped.push(i); },
        weeksNeeded() { return Math.max(1, Math.ceil(this.plan.total_hours / Math.max(1, this.form.weekly_hours))); },
        paceOk() { return this.form.weekly_hours >= this.plan.pace; },
        perDay() { const h = this.form.weekly_hours / Math.max(1, this.form.study_days.length); return h >= 1 ? (Math.round(h * 2) / 2) + ' hours' : Math.round(h * 60) + ' minutes'; },
        toggleDay(k) { this.form.study_days = this.form.study_days.includes(k) ? this.form.study_days.filter(x => x !== k) : [...this.form.study_days, k]; },
        dayList() {
            const names = this.days.filter(d => this.form.study_days.includes(d.k)).map(d => d.l);
            return names.length ? (names.length > 1 ? names.slice(0, -1).join(', ') + ' and ' + names[names.length - 1] : names[0]) : '...';
        },
        monthLabel() {
            if (!this.form.goal_month) return '...';
            const [y, m] = this.form.goal_month.split('-').map(Number);
            return new Date(y, m - 1, 1).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        },
        async submitQuiz() {
            this.busy = true; this.quizError = '';
            try {
                const res = await fetch(cfg.urls.quiz, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify({ answers: this.quizAnswers }) });
                const d = await res.json();
                if (!res.ok) throw new Error();
                this.quizResults = d.results;
                if (d.passed) { this.quizPassed = true; this.markDone('quiz'); }
                else { this.quizError = Object.values(d.results).filter(r => !r.correct).length + ' to fix. Change those answers and check again.'; }
            } catch (e) { this.quizError = 'Could not check right now. Try again.'; }
            this.busy = false;
        },
        async commit() {
            this.busy = true; this.commitError = '';
            try {
                const res = await fetch(cfg.urls.complete, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }, body: JSON.stringify(this.form) });
                const d = await res.json().catch(() => ({}));
                if (!res.ok || !d.ok) {
                    this.commitError = d.error || (d.errors ? Object.values(d.errors).flat()[0] : 'Could not save. Try again.');
                } else {
                    const first = !this.completed;
                    this.completed = true; this.markDone('commit');
                    if (first) { this.celebrate = true; this.confetti(); } else { this.commitError = ''; alert('Your commitment was updated.'); }
                }
            } catch (e) { this.commitError = 'No connection. Try again.'; }
            this.busy = false;
        },
        async openLetter() {
            const res = await fetch(cfg.urls.letter, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf } });
            const d = await res.json().catch(() => ({}));
            if (d.ok) this.letterText = d.why;
        },
        confetti() {
            const box = this.$refs.confetti, colors = ['#14b8a6', '#2dd4bf', '#fbbf24', '#f472b6', '#60a5fa', '#a78bfa', '#ffffff'];
            for (let i = 0; i < 90; i++) {
                const p = document.createElement('i');
                p.style.left = Math.random() * 100 + 'vw';
                p.style.background = colors[i % colors.length];
                p.style.animationDuration = (2.2 + Math.random() * 2.2) + 's';
                p.style.animationDelay = (Math.random() * .6) + 's';
                p.style.transform = 'rotate(' + Math.random() * 360 + 'deg)';
                box.appendChild(p);
            }
            setTimeout(() => { box.innerHTML = ''; }, 5500);
        },
    };
}
</script>
@endpush
