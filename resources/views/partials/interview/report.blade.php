{{--
    Interview report (engine sessions and mentor-scored sessions).
    @param \App\Models\InterviewSession $interview
    @param string $audience 'fellow' | 'reviewer'
    All AI and fellow text is escaped ({{ }}).
--}}
@php
    $audience = $audience ?? 'fellow';
    $reviewer = $audience === 'reviewer';
    $card = (array) ($interview->scorecard ?? []);
    $score = $interview->score !== null ? (float) $interview->score : null;
    $passMark = (int) ($card['pass_mark'] ?? $interview->context['pass_mark'] ?? 70);
    $signal = $interview->hire_signal;
    $signalColors = ['strong_hire' => '#22c55e', 'hire' => '#4ade80', 'lean_hire' => '#facc15', 'lean_no' => '#fb923c', 'no_hire' => '#f87171'];
    $metrics = (array) ($interview->communication_metrics ?? []);
    $integrity = (array) ($interview->integrity ?? []);
    $level = $interview->integrity_level;
    $levelColors = ['clean' => '#22c55e', 'review' => '#facc15', 'concern' => '#f87171'];
    $responses = collect((array) ($interview->responses ?? []));
    $modeLabel = fn ($r) => match (true) {
        !empty($r['skipped']) => 'skipped',
        ($r['answer_mode'] ?? '') === 'sql' => 'SQL editor' . (($r['explanation_mode'] ?? 'none') !== 'none' ? ' + ' . (($r['explanation_mode'] ?? '') === 'voice' ? 'spoken' : 'typed') . ' explanation' : ''),
        ($r['answer_mode'] ?? '') === 'code' => 'code editor' . (($r['explanation_mode'] ?? 'none') !== 'none' ? ' + ' . (($r['explanation_mode'] ?? '') === 'voice' ? 'spoken' : 'typed') . ' explanation' : ''),
        ($r['explanation_mode'] ?? '') === 'typed' => 'typed (microphone off)',
        default => 'spoken',
    };
    $badge = 'display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;';
@endphp

<div class="space-y-6">
    {{-- Outcome --}}
    <div class="card p-6">
        @if($interview->scoring_status === 'queued')
            <p class="text-white font-semibold">Scoring your interview…</p>
            <p class="text-dark-400 text-sm mt-1">This takes a minute or two. You can leave this page; you will get a notification.</p>
        @elseif($score === null)
            <p class="text-amber-400 font-semibold">Not scored</p>
            <p class="text-dark-400 text-sm mt-1">
                @if($interview->scoring_status === 'failed')
                    The automatic scoring did not work. A reviewer will score this interview; it does not count until then.
                @else
                    This interview has no score and does not count.
                @endif
            </p>
        @else
            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <p class="text-dark-400 text-sm">Overall</p>
                    <p class="text-3xl font-bold text-white">{{ rtrim(rtrim(number_format($score, 1), '0'), '.') }}<span class="text-dark-400 text-lg">/100</span></p>
                </div>
                @if($signal)
                <div>
                    <p class="text-dark-400 text-sm">Hire signal</p>
                    <span style="{{ $badge }}background: {{ $signalColors[$signal] ?? '#94a3b8' }}22; color: {{ $signalColors[$signal] ?? '#94a3b8' }}; font-size: 15px;">{{ \App\Services\Interview\BlueprintService::signalLabel($signal) }}</span>
                </div>
                @endif
                <div>
                    <p class="text-dark-400 text-sm">{{ $interview->is_practice ? 'Practice (pass mark ' . $passMark . ')' : 'Pass mark ' . $passMark }}</p>
                    @if($score >= $passMark)
                        <span style="{{ $badge }}background:#22c55e22;color:#4ade80;font-size:15px;">Passed</span>
                    @else
                        <span style="{{ $badge }}background:#f8717122;color:#f87171;font-size:15px;">Not passed</span>
                    @endif
                </div>
                @if(($card['by'] ?? 'ai') !== 'ai')
                    <p class="text-dark-400 text-sm">Scored by {{ ($card['by'] ?? '') === 'mentor' ? 'your mentor' : 'a reviewer' }}{{ !empty($card['mentor']) ? ' (' . $card['mentor'] . ')' : '' }}.</p>
                @endif
            </div>
            @if(!empty($card['summary']))
                <div class="text-dark-200 mt-4">{{ \App\Support\SafeMarkdown::render($card['summary']) }}</div>
            @endif
        @endif
    </div>

    {{-- Rubric --}}
    @if(!empty($card['dimensions']))
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Scorecard</h3>
        <div class="space-y-4">
            @foreach($card['dimensions'] as $d)
                @php $pct = (int) ($d['score'] ?? 0); @endphp
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-white font-medium">{{ $d['label'] ?? $d['key'] }}</span>
                        <span class="text-dark-300 text-sm">{{ isset($d['level']) ? 'Level ' . $d['level'] . ' of 4' : ($d['out_of_10'] ?? '') . (isset($d['out_of_10']) ? '/10' : '') }}</span>
                    </div>
                    <div class="w-full bg-dark-700 rounded-full h-2 mt-1">
                        <div class="h-2 rounded-full" style="width: {{ max(3, min(100, $pct)) }}%; background: {{ $pct >= 75 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                    @if(!empty($d['level_text']))<p class="text-dark-400 text-xs mt-1">{{ $d['level_text'] }}</p>@endif
                    @if(!empty($d['comment']))<div class="text-dark-300 text-sm mt-1">{{ \App\Support\SafeMarkdown::render($d['comment']) }}</div>@endif
                    @foreach((array) ($d['evidence'] ?? []) as $quote)
                        <p class="text-dark-300 text-sm mt-1" style="border-left: 3px solid #475569; padding-left: 8px; font-style: italic;">“{{ $quote }}”</p>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($card['strengths']) || !empty($card['improvements']))
    <div class="grid md:grid-cols-2 gap-6">
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">Strengths</h3>
            <ul class="space-y-2">@foreach((array) ($card['strengths'] ?? []) as $item)<li class="text-dark-200 text-sm">✓ {{ \App\Support\SafeMarkdown::render($item) }}</li>@endforeach</ul>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">To improve</h3>
            <ul class="space-y-2">@foreach((array) ($card['improvements'] ?? []) as $item)<li class="text-dark-200 text-sm">→ {{ \App\Support\SafeMarkdown::render($item) }}</li>@endforeach</ul>
        </div>
    </div>
    @endif

    @if(!empty($card['next_steps']))
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-3">Next steps</h3>
        <div class="space-y-3">
            @foreach($card['next_steps'] as $step)
                <div class="p-3 bg-dark-800 rounded-lg">
                    <div class="text-dark-200 text-sm">{{ \App\Support\SafeMarkdown::render($step['text']) }}</div>
                    @if(!empty($step['links']))
                        <div class="flex flex-wrap gap-3 mt-2">
                            @foreach($step['links'] as $link)
                                <a href="{{ $link['url'] }}" class="text-primary-400 hover:text-primary-300 text-sm">{{ ($link['kind'] ?? '') === 'practice' ? '🧪' : '📘' }} {{ $link['title'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Questions --}}
    @if(!empty($card['questions']) || $responses->isNotEmpty())
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Question by question</h3>
        <div class="space-y-5">
            @php
                $questions = !empty($card['questions']) ? $card['questions']
                    : collect((array) ($interview->plan['items'] ?? []))->map(fn ($i) => ['n' => $i['n'], 'round' => $i['round_label'] ?? '', 'question' => $i['question'], 'strong_answer' => null, 'assessment' => null, 'asked' => false])->all();
            @endphp
            @foreach($questions as $q)
                @php $thread = $responses->where('item', $q['n'])->values(); @endphp
                @if(!($q['asked'] ?? true) && $thread->isEmpty()) @continue @endif
                <div class="p-4 bg-dark-800 rounded-lg">
                    <p class="text-dark-400 text-xs" style="text-transform: uppercase; letter-spacing: .05em;">Question {{ $q['n'] }} · {{ $q['round'] }}</p>
                    @foreach($thread as $r)
                        <div class="text-white font-medium mt-2">{{ ($r['kind'] ?? 'main') === 'follow_up' ? 'Follow-up: ' : '' }}{{ \App\Support\SafeMarkdown::render($r['question']) }}</div>
                        <p class="text-dark-400 text-xs mt-1">Your answer ({{ $modeLabel($r) }}{{ isset($r['time_taken_seconds']) ? ', ' . gmdate($r['time_taken_seconds'] >= 3600 ? 'H:i:s' : 'i:s', (int) $r['time_taken_seconds']) : '' }}{{ !empty($r['late']) ? ', after the time limit' : '' }})</p>
                        @if(!empty($r['code']))
                            <pre class="bg-dark-900 rounded-lg p-3 mt-1 text-sm text-dark-200" style="white-space: pre-wrap; overflow-x: auto; font-family: ui-monospace, Menlo, Consolas, monospace;">{{ $r['code'] }}</pre>
                        @endif
                        @if(trim((string) ($r['answer'] ?? '')) !== '')
                            <p class="text-dark-200 text-sm mt-1" style="white-space: pre-line;">{{ $r['answer'] }}</p>
                        @elseif(!empty($r['skipped']))
                            <p class="text-dark-500 text-sm mt-1">(skipped)</p>
                        @endif
                        @if($reviewer && !empty($r['evaluation']['notes']))
                            <p class="text-dark-500 text-xs mt-1">Interviewer notes: {{ implode(' · ', (array) $r['evaluation']['notes']) }}</p>
                        @elseif($reviewer && empty($r['evaluation']['evaluated']))
                            <p class="text-dark-500 text-xs mt-1">Not evaluated during the interview ({{ $r['evaluation']['reason'] ?? 'n/a' }}).</p>
                        @endif
                    @endforeach
                    @if(!empty($q['assessment']))
                        <div class="text-dark-300 text-sm mt-3"><span class="text-dark-400">Assessment:</span> {{ \App\Support\SafeMarkdown::render($q['assessment']) }}</div>
                    @endif
                    @if(!empty($q['strong_answer']))
                        <div class="mt-3 p-3 rounded-lg" style="background: rgba(34,197,94,.08); border: 1px solid rgba(34,197,94,.25);">
                            <p class="text-xs font-semibold" style="color:#4ade80;">What a strong answer includes</p>
                            <div class="text-dark-200 text-sm mt-1">{{ \App\Support\SafeMarkdown::render($q['strong_answer']) }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Communication and integrity --}}
    <div class="grid md:grid-cols-2 gap-6">
        @if(!empty($metrics) && isset($metrics['spoken_answers']))
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">How you spoke</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-dark-400">Words per minute</span><span class="text-white">{{ $metrics['words_per_minute'] ?? '—' }}</span></div>
                <p class="text-dark-500 text-xs">A clear interview pace is about 110-160 words a minute.</p>
                <div class="flex justify-between"><span class="text-dark-400">Filler words (um, uh, like, you know…)</span><span class="text-white">{{ $metrics['filler_words'] ?? 0 }}{{ isset($metrics['filler_words_per_min']) ? ' (' . $metrics['filler_words_per_min'] . '/min)' : '' }}</span></div>
                <div class="flex justify-between"><span class="text-dark-400">Answers spoken / typed</span><span class="text-white">{{ $metrics['spoken_answers'] ?? 0 }} / {{ $metrics['typed_answers'] ?? 0 }}</span></div>
            </div>
        </div>
        @endif
        @if($interview->engine_version)
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-3">Integrity</h3>
            @if($reviewer)
                <span style="{{ $badge }}background: {{ $levelColors[$level] ?? '#94a3b8' }}22; color: {{ $levelColors[$level] ?? '#94a3b8' }};">{{ ucfirst($level ?? 'not analysed') }}</span>
                <ul class="mt-3 space-y-1">
                    @forelse((array) ($integrity['reasons'] ?? []) as $reason)
                        <li class="text-dark-200 text-sm">• {{ $reason }}</li>
                    @empty
                        <li class="text-dark-400 text-sm">Nothing unusual was logged.</li>
                    @endforelse
                </ul>
                <p class="text-dark-500 text-xs mt-3">Flags never fail an interview. Full screen was required; no camera. Away time: {{ (int) ($integrity['durations']['away_seconds'] ?? 0) }} s.</p>
            @else
                <p class="text-dark-200 text-sm">{{ \App\Services\Interview\IntegrityAnalyzer::summaryForFellow($integrity) }}</p>
                <p class="text-dark-500 text-xs mt-2">Your reviewer sees this report. Nothing here fails an interview on its own.</p>
            @endif
        </div>
        @endif
    </div>

    @if($reviewer && $interview->engine_version)
    <div class="card p-6" x-data="{ open: false }">
        <button type="button" class="text-white font-semibold" @click="open = !open">Full transcript <span x-text="open ? '▲' : '▼'"></span></button>
        <div x-show="open" x-cloak class="space-y-2 mt-4">
            @foreach((array) ($interview->transcript ?? []) as $m)
                <div class="p-3 rounded-lg {{ ($m['role'] ?? '') === 'fellow' ? 'bg-dark-700' : 'bg-dark-800' }}">
                    <p class="text-dark-400 text-xs">{{ ($m['role'] ?? '') === 'fellow' ? 'Fellow · ' . match ($m['mode'] ?? '') { 'voice' => 'spoken', 'typed' => 'typed', 'code' => 'code', 'sql' => 'SQL', default => 'answer' } : 'Interviewer' }}{{ !empty($m['at']) ? ' · ' . \Carbon\Carbon::parse($m['at'])->format('H:i:s') : '' }}</p>
                    @if(!empty($m['code']))<pre class="text-sm text-dark-200 mt-1" style="white-space: pre-wrap; font-family: ui-monospace, Menlo, Consolas, monospace;">{{ $m['code'] }}</pre>@endif
                    @if(($m['role'] ?? '') === 'fellow')
                        <p class="text-dark-200 text-sm mt-1" style="white-space: pre-line;">{{ $m['text'] ?? '' }}</p>
                    @else
                        <div class="text-dark-200 text-sm mt-1">{{ \App\Support\SafeMarkdown::render($m['text'] ?? '') }}</div>
                    @endif
                </div>
            @endforeach
        </div>
        @php $ctx = (array) ($interview->context ?? []); @endphp
        <div class="mt-4 text-dark-400 text-xs space-y-1">
            <p>Questions from: {{ $interview->plan['source'] ?? 'n/a' }}{{ !empty($ctx['blueprint']['generic']) ? ' (generic blueprint)' : ' (blueprint v' . ($ctx['blueprint']['version'] ?? '?') . ')' }} · Finished: {{ $ctx['finish_reason'] ?? 'n/a' }}</p>
            @if(!empty($ctx['project']['activity']))<p>Project used: {{ $ctx['project']['activity'] }}{{ !empty($ctx['project']['github']['repo']) ? ' (' . $ctx['project']['github']['repo'] . ')' : '' }}</p>@endif
            @if(!empty($ctx['level']['weak_spots']))<p>Known weak spots: {{ implode('; ', $ctx['level']['weak_spots']) }}</p>@endif
        </div>
    </div>
    @endif
</div>
