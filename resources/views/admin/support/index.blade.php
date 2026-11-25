@extends('layouts.app')

@section('title', 'Support')

@section('content')
@php
    $openHelp = $help->where('status', 'open')->count();
    $input = 'w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm placeholder-dark-500 focus:border-primary-500';
@endphp
<div class="space-y-5" x-data="{ tab: @js($tab) }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Support</h1>
            <p class="text-dark-400 text-sm">Who needs help and why, the weekly bar, and fellows' questions for a human.</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="tab" :value="tab">
            <select name="track" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All tracks</option>
                @foreach($tracks as $t)<option value="{{ $t->id }}" @selected($trackId === $t->id)>{{ $t->name }}</option>@endforeach
            </select>
        </form>
    </div>

    @foreach(['success' => 'green', 'error' => 'red'] as $key => $color)
        @if(session($key))<div class="p-4 rounded-xl border bg-{{ $color }}-500/10 border-{{ $color }}-500/30 text-{{ $color }}-400">{{ session($key) }}</div>@endif
    @endforeach
    @if($errors->any())<div class="p-4 rounded-xl border bg-red-500/10 border-red-500/30 text-red-400 text-sm">{{ $errors->first() }}</div>@endif

    <div class="flex flex-wrap gap-2">
        @foreach(['risk' => '🚩 Needs support (' . $atRisk->count() . ')', 'standing' => '📏 Weekly bar', 'help' => '🙋 Ask a human' . ($openHelp ? " ({$openHelp} open)" : '')] as $k => $label)
            <button type="button" @click="tab = '{{ $k }}'" :class="tab === '{{ $k }}' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'" class="px-4 py-2 rounded-lg font-medium">{{ $label }}</button>
        @endforeach
    </div>

    {{-- NEEDS SUPPORT --}}
    <div x-show="tab === 'risk'" class="space-y-3">
        <p class="text-dark-400 text-sm">Signals come from what fellows actually do: quiet days, stuck activities, work sent back, deadlines, quiz accuracy, "Stuck?" presses, Base Camp gaps and the weekly bar. Fellows also receive kind nudges automatically (each at most once a week).</p>
        @forelse($atRisk as $row)
            @php $f = $row['fellow']; @endphp
            <div class="card p-5" x-data="{ act: null }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-white font-semibold">{{ $f->name }} <span class="text-dark-500 text-sm font-normal">· {{ $f->email }}</span></p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($row['signals'] as $s)
                                <span class="px-2 py-1 rounded-lg text-xs {{ $s['weight'] >= 3 ? 'bg-red-500/15 text-red-300' : ($s['weight'] === 2 ? 'bg-amber-500/15 text-amber-300' : 'bg-dark-700 text-dark-300') }}" title="{{ $s['detail'] }}">{{ $s['label'] }}: {{ $s['detail'] }}</span>
                            @endforeach
                        </div>
                        @if($row['buddy'])<p class="text-dark-400 text-xs mt-2">🤝 Support buddy: {{ $row['buddy']->buddy?->name }}</p>@endif
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary text-sm" @click="act = act === 'msg' ? null : 'msg'">💬 Message</button>
                        <button type="button" class="btn-secondary text-sm" @click="act = act === 'buddy' ? null : 'buddy'">🤝 Buddy</button>
                    </div>
                </div>
                <form x-show="act === 'msg'" x-cloak method="POST" action="{{ route('admin.support.nudge', $f) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="message" class="{{ $input }}" minlength="10" maxlength="1000" required placeholder="A personal note, e.g. 'Hi {{ explode(' ', trim($f->name))[0] }}, I saw you're stuck on... can we talk tomorrow at 4?'">
                    <button class="btn-primary text-sm">Send</button>
                </form>
                <form x-show="act === 'buddy'" x-cloak method="POST" action="{{ route('admin.support.buddy', $f) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <select name="buddy_id" class="{{ $input }}" style="max-width: 260px" required>
                        <option value="">Choose a classmate...</option>
                        @foreach($classmates($f) as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <input name="note" class="{{ $input }}" style="max-width: 360px" maxlength="500" placeholder="Note for both (optional)">
                    <button class="btn-primary text-sm">Assign and notify both</button>
                </form>
            </div>
        @empty
            <div class="card p-8 text-center text-dark-400">No support signals right now. 🎉</div>
        @endforelse
    </div>

    {{-- WEEKLY BAR --}}
    <div x-show="tab === 'standing'" x-cloak class="space-y-4">
        <div class="card p-5 text-sm text-dark-300">
            Bar: {{ $settings['active_days'] }} active days, {{ $settings['practice_days'] }} practice days, {{ $settings['progress_actions'] }} curriculum {{ Str::plural('action', $settings['progress_actions']) }} per week.
            Penalty {{ number_format($settings['fee']) }} FCFA from miss #{{ $settings['fee_from'] }} in a row. Counting from {{ $settings['start']->format('j M Y') }}{{ $settings['enabled'] ? '' : ' (currently switched off)' }}.
            <a href="{{ route('admin.settings') }}" class="text-primary-400">Change in Settings → Standing</a>.
            Penalty fees appear in <a href="{{ route('admin.fees.index') }}" class="text-primary-400">Fees</a>, where you can also waive them with a reason.
        </div>
        <div class="card p-5">
            <h2 class="text-white font-semibold">Last week ({{ $lastWeek->format('j M') }} – {{ $lastWeek->copy()->endOfWeek()->format('j M') }})</h2>
            @if($standings->isEmpty())
                <p class="text-dark-400 text-sm mt-2">Not evaluated yet (runs every Monday at 01:00), or the weekly bar had not started.</p>
            @else
                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-sm">
                        <thead><tr class="text-dark-400 text-left"><th class="py-2">Fellow</th><th>Active</th><th>Practice</th><th>Curriculum</th><th>Result</th><th>Misses in a row</th><th></th></tr></thead>
                        <tbody class="text-dark-200">
                            @foreach($standings as $w)
                                <tr class="border-t border-dark-700" x-data="{ ex: false }">
                                    <td class="py-2">{{ $w->fellow?->name }}</td>
                                    <td>{{ $w->active_days }}</td><td>{{ $w->practice_days }}</td><td>{{ $w->progress_waived ? 'waived' : $w->progress_actions }}</td>
                                    <td>@if($w->exempt)<span class="text-dark-300" title="{{ $w->exempt_reason }}">Excused</span>@elseif($w->met)<span class="text-green-400">Met</span>@else<span class="text-amber-300">{{ ucfirst($w->action) }}</span>@endif
                                        @if($w->fee)<span class="text-dark-500 text-xs"> · fee {{ $w->fee->status_label }}</span>@endif</td>
                                    <td>{{ $w->consecutive_misses }}</td>
                                    <td class="text-right">
                                        @if(!$w->met && !$w->exempt)
                                            <button type="button" class="text-primary-400 text-xs" @click="ex = !ex">Excuse week</button>
                                            <form x-show="ex" x-cloak method="POST" action="{{ route('admin.support.excuse', $w) }}" class="flex gap-1 mt-1">
                                                @csrf
                                                <input name="reason" class="{{ $input }}" minlength="10" maxlength="500" required placeholder="Reason (e.g. hospitalised, no internet)">
                                                <button class="btn-primary text-xs">OK</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($recentMisses->isNotEmpty())
            <div class="card p-5">
                <h2 class="text-white font-semibold">Missed weeks (last 6 weeks)</h2>
                <ul class="text-sm text-dark-300 mt-2 space-y-1">
                    @foreach($recentMisses as $w)
                        <li>{{ $w->week_start->format('j M') }} · {{ $w->fellow?->name }} · {{ $w->consecutive_misses }} in a row · {{ $w->action }}{{ $w->fee ? ' · fee ' . strtolower($w->fee->status_label) : '' }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- ASK A HUMAN --}}
    <div x-show="tab === 'help'" x-cloak class="space-y-3">
        @forelse($help as $h)
            <div class="card p-5 {{ $h->status === 'open' ? 'border border-amber-500/30' : '' }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-white font-semibold">{{ $h->fellow?->name }} <span class="text-dark-500 text-sm font-normal">· {{ $h->created_at->diffForHumans() }}{{ $h->activity ? ' · ' . $h->activity->title : '' }}</span></p>
                    <span class="text-xs font-semibold {{ $h->status === 'open' ? 'text-amber-300' : 'text-green-400' }}">{{ $h->status === 'open' ? 'Open' : 'Answered by ' . ($h->answerer?->name ?? 'team') }}</span>
                </div>
                <p class="text-dark-100 mt-2 whitespace-pre-line">{{ $h->question }}</p>
                @if($h->answer)
                    <div class="mt-3 p-3 rounded-lg bg-dark-800 text-dark-200 text-sm whitespace-pre-line">{{ $h->answer }}</div>
                @else
                    <form method="POST" action="{{ route('admin.support.help.answer', $h) }}" class="mt-3 space-y-2">
                        @csrf
                        <textarea name="answer" rows="3" class="{{ $input }}" minlength="5" maxlength="3000" required placeholder="Your answer (markdown allowed). Hints are better than full solutions."></textarea>
                        <button class="btn-primary text-sm">Send answer</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="card p-8 text-center text-dark-400">No questions yet.</div>
        @endforelse
    </div>
</div>
@endsection
