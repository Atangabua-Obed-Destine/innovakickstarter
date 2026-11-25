@extends('layouts.app')

@section('title', 'Job Market')

@section('content')
@include('partials.track.styles')
@php
    $role = $ready['role'];
    $pct = $ready['percent'];
    $n = $data['summary']['postings'] ?? count($data['postings'] ?? []);
    $markets = $data['summary']['by_market'] ?? [];
    $pf = fn ($f) => (int) round($f * 100);
    $states = ['have' => ['✅', 'You have proof', 'text-green-400'], 'growing' => ['🌱', 'Growing', 'text-amber-300'], 'missing' => ['⭕', 'Not yet', 'text-red-300'],
        'beyond' => ['🧭', 'Beyond this program', 'text-sky-300'], 'unmeasured' => ['ℹ️', 'Not measured here', 'text-dark-400']];
    $loopIcons = ['passed' => '✅', 'practising' => '🌱', 'not_yet' => '⭕', 'info' => 'ℹ️'];
@endphp
<style>
    .jm-hero { border-radius: 18px; padding: 24px; background: radial-gradient(120% 140% at 0% 0%, color-mix(in srgb, var(--color-primary-600) 32%, #0f141d), #0f141d 60%); border: 1px solid color-mix(in srgb, var(--color-primary-500) 40%, transparent); }
    .jm-ring { width: 132px; height: 132px; flex-shrink: 0; }
    .jm-tab { padding: 8px 14px; border-radius: 10px; font-size: 14px; font-weight: 600; color: rgb(148 163 184); background: rgba(148,163,184,.08); }
    .jm-tab.on { color: #fff; background: var(--color-primary-600); }
    .jm-skill { padding: 16px; border-radius: 14px; background: rgba(15,20,29,.6); border: 1px solid rgba(148,163,184,.14); }
    .jm-bar { height: 8px; border-radius: 99px; background: rgba(148,163,184,.14); overflow: hidden; position: relative; }
    .jm-bar > i { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 99px; }
    .jm-quote { border-left: 3px solid color-mix(in srgb, var(--color-primary-400) 70%, transparent); padding: 4px 10px; color: rgb(203 213 225); font-size: 13px; font-style: italic; }
    .jm-role { padding: 14px; border-radius: 14px; border: 1px solid rgba(148,163,184,.16); background: rgba(15,20,29,.55); }
    .jm-role.on { border-color: var(--color-primary-400); background: color-mix(in srgb, var(--color-primary-600) 16%, #0f141d); }
    .jm-stage { display: flex; gap: 14px; align-items: flex-start; padding: 14px; border-radius: 14px; background: rgba(15,20,29,.6); border: 1px solid rgba(148,163,184,.14); }
    .jm-stage.passed { border-color: rgba(34,197,94,.45); }
    .jm-num { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; background: rgba(148,163,184,.15); flex-shrink: 0; }
    .jm-stage.passed .jm-num { background: #16a34a; }
</style>
<div class="max-w-5xl mx-auto space-y-5" x-data="{ tab: @js($tab) }">
    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))<div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>@endif
    @endforeach

    {{-- HERO: readiness for the target role --}}
    <div class="jm-hero">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <svg class="jm-ring" viewBox="0 0 120 120" role="img" aria-label="{{ $pct }}% job-ready">
                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(148,163,184,.18)" stroke-width="12"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--color-primary-400)" stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="{{ round(314.16 * $pct / 100, 1) }} 314.16" transform="rotate(-90 60 60)"/>
                <text x="60" y="58" text-anchor="middle" fill="#fff" font-size="26" font-weight="800">{{ $pct }}%</text>
                <text x="60" y="78" text-anchor="middle" fill="rgb(148,163,184)" font-size="10">job-ready</text>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">💼 Job Market · {{ $track->name }}</p>
                <h1 class="text-2xl font-bold text-white mt-1">You have proof for {{ $pct }}% of what {{ $role['title'] }} adverts ask for</h1>
                <p class="text-dark-300 mt-2">Based on {{ $n }} real entry-level job adverts we studied ({{ collect($markets)->map(fn ($c, $m) => "{$c} {$m}")->implode(', ') }}). Only reviewed work counts as proof: approved activities and certificates.</p>
                @if($ready['gap'])
                    <p class="mt-3 text-white">🎯 Biggest gap: <strong>{{ $ready['gap']['label'] }}</strong> <span class="text-dark-300">(asked in {{ $pf($ready['gap']['role_frequency']) }}% of {{ $role['title'] }} adverts)</span>
                        @if(!empty($ready['gap']['p']['next']))· next: <a class="text-primary-300 underline" href="{{ route('curriculum.activity.show', $ready['gap']['p']['next'][0]['id']) }}">{{ $ready['gap']['p']['next'][0]['title'] }}</a>@endif
                    </p>
                @endif
                @if($enrolment?->offer_unlocked_at)
                    <a href="{{ route('career.offer') }}" class="btn-primary inline-block mt-4">📨 Open your mock job offer</a>
                @else
                    <p class="text-dark-400 text-sm mt-3">📨 Reach {{ \App\Services\JobMarketService::OFFER_READINESS }}% and pass {{ \App\Services\JobMarketService::OFFER_STAGES }} hiring-loop stages to unlock a mock job offer.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach(['map' => '📊 What employers ask', 'roles' => '🪜 Target roles', 'advert' => '🔍 You vs a job advert', 'loop' => '🎤 Interview loop', 'sources' => '📚 Sources'] as $k => $label)
            <button type="button" class="jm-tab" :class="tab === '{{ $k }}' && 'on'" @click="tab = '{{ $k }}'">{{ $label }}</button>
        @endforeach
    </div>

    {{-- WHAT EMPLOYERS ASK --}}
    <div x-show="tab === 'map'" class="space-y-3">
        @if(!empty($data['insights']))
            <div class="card p-5" x-data="{ more: false }">
                <h3 class="text-white font-semibold">🧠 What the research found</h3>
                <div class="grid md:grid-cols-2 gap-3 mt-3">
                    @foreach($data['insights'] as $i => $ins)
                        <div class="p-3 rounded-lg bg-dark-800/70" @if($i >= 2) x-show="more" x-cloak @endif>
                            <p class="text-white text-sm font-medium">{{ $ins['icon'] }} {{ $ins['title'] }}</p>
                            <p class="text-dark-300 text-sm mt-1">{{ $ins['text'] }}</p>
                        </div>
                    @endforeach
                </div>
                @if(count($data['insights']) > 2)<button type="button" class="text-primary-400 text-xs mt-2" @click="more = !more" x-text="more ? 'Show less' : 'Show all {{ count($data['insights']) }} findings'"></button>@endif
            </div>
        @endif
        <p class="text-dark-300 text-sm">Skills ranked by how often {{ $role['title'] }} adverts ask for them. The grey bar is the market; the coloured bar is your proof.</p>
        @foreach($ready['taught'] as $s)
            @php $p = $s['p']; $proofPct = (int) round($p['proof'] * 100); @endphp
            <div class="jm-skill" x-data="{ open: false }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-white font-semibold">{{ $s['label'] }} <span class="text-dark-400 font-normal text-sm">· asked in {{ $pf($s['role_frequency']) }}% of adverts</span></p>
                    <p class="text-sm {{ $proofPct >= 100 ? 'text-green-400' : ($proofPct > 0 ? 'text-amber-300' : 'text-dark-400') }}">{{ $proofPct >= 100 ? '✅ Proven' : $proofPct . '% proven' }} · {{ $p['evidence'] }}</p>
                </div>
                <div class="jm-bar mt-3"><i style="width: {{ $pf($s['role_frequency']) }}%; background: rgba(148,163,184,.35)"></i><i style="width: {{ $pf($s['role_frequency']) * $p['proof'] }}%; background: var(--color-primary-400)"></i></div>
                @if(!empty($s['why']))<p class="text-dark-300 text-sm mt-2">{{ $s['why'] }}</p>@endif
                <button type="button" class="text-primary-400 text-xs mt-2" @click="open = !open" x-text="open ? 'Hide details' : 'What adverts say · how to prove it'"></button>
                <div x-show="open" x-cloak class="mt-3 space-y-2">
                    @foreach(array_slice($s['quotes'] ?? [], 0, 3) as $q)
                        <p class="jm-quote">"{{ $q['text'] }}" <span class="not-italic text-dark-500">· <a href="{{ $q['url'] }}" target="_blank" rel="noopener nofollow" class="underline">{{ $q['company'] }}</a>, {{ $q['market'] }}</span></p>
                    @endforeach
                    @if(!empty($p['next']))
                        <p class="text-dark-300 text-sm">Prove it next:
                            @foreach($p['next'] as $a)<a href="{{ route('curriculum.activity.show', $a['id']) }}" class="text-primary-300 underline">{{ $a['title'] }}</a>{{ !$loop->last ? ' · ' : '' }}@endforeach
                        </p>
                    @endif
                    @if(!empty($s['note']))<p class="text-dark-500 text-xs">{{ $s['note'] }}</p>@endif
                </div>
            </div>
        @endforeach

        @if($ready['beyond'])
            <div class="card p-5">
                <h3 class="text-white font-semibold">🧭 Also asked for, beyond this program</h3>
                <p class="text-dark-400 text-sm">These don't count in your score, because the program doesn't teach them (yet). Free places to start:</p>
                <div class="grid md:grid-cols-2 gap-3 mt-3">
                    @foreach($ready['beyond'] as $s)
                        <div class="p-3 rounded-lg bg-dark-800/70">
                            <p class="text-white text-sm font-medium">{{ $s['label'] }} <span class="text-dark-400 font-normal">· {{ $pf($s['role_frequency']) }}% of adverts</span></p>
                            @foreach($s['beyond'] ?? [] as $r)<a href="{{ $r['url'] }}" target="_blank" rel="noopener" class="block text-primary-300 text-xs underline mt-1">{{ $r['title'] }}</a>@endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @php $info = collect($data['skills'])->where('kind', 'info'); @endphp
        @if($info->isNotEmpty())
            <div class="card p-5">
                <h3 class="text-white font-semibold">ℹ️ Good to know</h3>
                <ul class="text-dark-300 text-sm mt-2 space-y-1">
                    @foreach($info as $s)<li><strong class="text-white">{{ $s['label'] }}</strong> ({{ $pf($s['frequency']) }}% of adverts): {{ $s['why'] ?? '' }}</li>@endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- TARGET ROLES --}}
    <div x-show="tab === 'roles'" x-cloak class="space-y-3">
        <p class="text-dark-300 text-sm">Entry-level roles that data adverts are for. Pick the one you are aiming for: your score and gaps follow it.</p>
        <div class="grid md:grid-cols-2 gap-3">
            @foreach($roles as $r)
                <div class="jm-role {{ $r['key'] === $role['key'] ? 'on' : '' }}">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-white font-semibold">{{ $r['title'] }}</p>
                        <span class="text-2xl font-extrabold text-white">{{ $r['percent'] }}%</span>
                    </div>
                    <p class="text-dark-400 text-sm mt-1">{{ $r['description'] ?? '' }}</p>
                    <p class="text-dark-500 text-xs mt-1">{{ $r['postings'] ?? 0 }} adverts studied · top asks: {{ collect($r['skills'] ?? [])->sortDesc()->keys()->take(4)->map(fn ($k) => collect($data['skills'])->firstWhere('key', $k)['label'] ?? $k)->implode(', ') }}</p>
                    @if($r['key'] !== $role['key'])
                        <form method="POST" action="{{ route('career.role') }}" class="mt-3">@csrf<input type="hidden" name="role" value="{{ $r['key'] }}"><button class="btn-secondary text-sm">Aim for this role</button></form>
                    @else
                        <p class="text-primary-300 text-sm mt-3">🎯 Your target role</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- YOU VS A JOB ADVERT --}}
    <div x-show="tab === 'advert'" x-cloak class="space-y-4">
        <form method="POST" action="{{ route('career.advert') }}" class="card p-6" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <h3 class="text-white font-semibold text-lg">🔍 You vs a job advert</h3>
            <p class="text-dark-300 text-sm mt-1">Found a job you like (anywhere: LinkedIn, a company site, Jobberman...)? Paste the whole advert. You'll see what it asks for, what you already have proof of, and exactly what to do next.</p>
            <textarea name="advert" rows="8" maxlength="12000" required class="w-full mt-3 bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-dark-500 focus:border-primary-500"
                      placeholder="Paste the job title, the responsibilities and the requirements...">{{ old('advert') }}</textarea>
            <button class="btn-primary mt-3" :disabled="busy" x-text="busy ? 'Reading the advert (up to a minute)...' : 'Compare me with this advert'"></button>
        </form>

        @if($current)
            @php $res = $current->result; @endphp
            <div class="card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-dark-400 text-xs uppercase tracking-wider">Result</p>
                        <h3 class="text-white font-bold text-xl">{{ $current->title }}{{ $current->company ? ' · ' . $current->company : '' }}</h3>
                        <p class="text-dark-400 text-sm">{{ ucfirst($res['seniority'] ?? 'unknown') }} level{{ !empty($res['years']) ? ' · asks for ' . $res['years'] . '+ years' : '' }}</p>
                    </div>
                    <p class="text-4xl font-extrabold text-white">{{ $current->match }}%<span class="text-dark-400 text-sm font-normal block text-right">match</span></p>
                </div>
                @if(in_array($res['seniority'] ?? '', ['mid', 'senior'], true) || ($res['years'] ?? 0) >= 3)
                    <p class="mt-3 p-3 rounded-lg bg-amber-500/10 text-amber-300 text-sm">This advert is above entry level. Keep it as a goal, and look for junior, graduate or intern versions of the same role now.</p>
                @endif
                <div class="space-y-2 mt-4">
                    @foreach($res['requirements'] as $r)
                        @php [$icon, $label, $cls] = $states[$r['state']] ?? $states['unmeasured']; @endphp
                        <div class="p-3 rounded-lg bg-dark-800/70">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="text-dark-100 text-sm">{{ $icon }} {{ $r['text'] }} <span class="text-dark-500 text-xs">({{ $r['importance'] === 'must' ? 'required' : 'nice to have' }})</span></p>
                                <span class="text-xs {{ $cls }}">{{ $label }}{{ $r['evidence'] ? ' · ' . $r['evidence'] : '' }}</span>
                            </div>
                            @if(!empty($r['next']) && in_array($r['state'], ['growing', 'missing'], true))
                                <p class="text-xs mt-1 text-dark-400">Next: @foreach($r['next'] as $a)<a href="{{ route('curriculum.activity.show', $a['id']) }}" class="text-primary-300 underline">{{ $a['title'] }}</a>{{ !$loop->last ? ' · ' : '' }}@endforeach</p>
                            @endif
                            @if(!empty($r['beyond']))
                                <p class="text-xs mt-1 text-dark-400">Learn it free: @foreach($r['beyond'] as $b)<a href="{{ $b['url'] }}" target="_blank" rel="noopener" class="text-primary-300 underline">{{ $b['title'] }}</a>{{ !$loop->last ? ' · ' : '' }}@endforeach</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(!empty($res['plan']))
                    <div class="mt-4 p-4 rounded-lg bg-primary-500/10 border border-primary-500/20">
                        <p class="text-white font-semibold">🗺️ Your 3-step plan for this job</p>
                        <ol class="list-decimal list-inside text-dark-200 text-sm mt-2 space-y-1">
                            @foreach($res['plan'] as $a)<li><a href="{{ route('curriculum.activity.show', $a['id']) }}" class="text-primary-300 underline">{{ $a['title'] }}</a> (Chapter {{ $a['chapter'] }})</li>@endforeach
                        </ol>
                    </div>
                @endif
                <p class="text-dark-500 text-xs mt-3">The AI only reads the advert; your match comes from your reviewed work. It is a guide, not a guarantee.</p>
            </div>
        @endif

        @if($checks->isNotEmpty())
            <div class="card p-5">
                <h3 class="text-white font-semibold">Adverts you checked</h3>
                <div class="space-y-1 mt-2">
                    @foreach($checks as $c)
                        <a href="{{ route('career.index', ['check' => $c->id]) }}" class="flex justify-between text-sm p-2 rounded-lg hover:bg-dark-800 {{ $current?->id === $c->id ? 'bg-dark-800' : '' }}">
                            <span class="text-dark-200">{{ $c->title }}{{ $c->company ? ' · ' . $c->company : '' }} <span class="text-dark-500">· {{ $c->created_at->diffForHumans() }}</span></span>
                            <span class="text-white font-semibold">{{ $c->match }}%</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- INTERVIEW LOOP --}}
    <div x-show="tab === 'loop'" x-cloak class="space-y-3">
        <p class="text-dark-300 text-sm">The hiring steps most employers use for entry-level data roles, from published hiring processes. Your evidence for each comes from what you do here.</p>
        @foreach($hiringLoop as $i => $st)
            <div class="jm-stage {{ $st['status'] }}">
                <span class="jm-num">{{ $st['status'] === 'passed' ? '✓' : $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-white font-semibold">{{ $st['name'] }}</p>
                        <span class="text-xs {{ $st['status'] === 'passed' ? 'text-green-400' : ($st['status'] === 'practising' ? 'text-amber-300' : 'text-dark-400') }}">{{ $loopIcons[$st['status']] ?? '' }} {{ ['passed' => 'Ready', 'practising' => 'Practising', 'not_yet' => 'Not yet', 'info' => ''][$st['status']] ?? '' }}</span>
                    </div>
                    <p class="text-dark-300 text-sm mt-1">{{ $st['what_happens'] ?? '' }}</p>
                    @if($st['evidence'])<p class="text-dark-400 text-xs mt-1">Your evidence: {{ $st['evidence'] }}</p>@endif
                    @if($st['status'] !== 'passed' && $st['how'])<p class="text-primary-200 text-xs mt-1">💡 {{ $st['how'] }}</p>@endif
                </div>
            </div>
        @endforeach
        @if(!empty($data['interview_loop']['examples']))
            <div class="card p-5">
                <h3 class="text-white font-semibold">How real employers describe their process</h3>
                <div class="grid md:grid-cols-2 gap-3 mt-3">
                    @foreach($data['interview_loop']['examples'] as $ex)
                        <div class="p-3 rounded-lg bg-dark-800/70">
                            <p class="text-white text-sm font-medium">{{ $ex['company'] }} <span class="text-dark-400 font-normal">· {{ $ex['role'] }}</span></p>
                            <p class="text-dark-300 text-xs mt-1">{{ collect($ex['stages'])->pluck('name')->implode(' → ') }}</p>
                            <a href="{{ $ex['source_url'] }}" target="_blank" rel="noopener nofollow" class="text-primary-400 text-xs underline">Source</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- SOURCES --}}
    <div x-show="tab === 'sources'" x-cloak class="card p-6">
        <h3 class="text-white font-semibold">The {{ $n }} adverts behind these numbers</h3>
        <p class="text-dark-400 text-sm">Real entry-level adverts, researched {{ \Carbon\Carbon::parse($data['researched_on'])->format('F Y') }}. Some may have closed since. We refresh this research regularly.</p>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-sm">
                <thead><tr class="text-dark-400 text-left"><th class="py-2">Role</th><th>Employer</th><th>Where</th><th></th></tr></thead>
                <tbody class="text-dark-200">
                    @foreach($data['postings'] as $p)
                        <tr class="border-t border-dark-700"><td class="py-2">{{ $p['title'] }}</td><td>{{ $p['company'] }}</td><td>{{ $p['location'] ?? $p['market'] }}</td>
                            <td><a href="{{ $p['url'] }}" target="_blank" rel="noopener nofollow" class="text-primary-400 underline">Advert</a></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-dark-500 text-xs text-center">Job-ready means "has proof for what employers ask", not a promise of a job. Keep building, keep applying.</p>
</div>
@endsection
