@extends('layouts.app')

@section('title', 'My weekly bar')

@section('content')
@php
    $fee = number_format($settings['fee']) . ' FCFA';
    $stateInfo = [
        'good' => ['✅', 'Good standing', 'text-green-400', 'You are meeting the weekly bar. Keep the rhythm.'],
        'warning' => ['⚠️', 'Warning', 'text-amber-300', 'You missed last week\'s bar. Meet it this week to clear the warning.' . ($settings['fee'] > 0 ? " Missing it again means a {$fee} penalty and a hold on your progress." : '')],
        'hold' => ['⛔', 'On hold', 'text-red-400', 'Starting and submitting activities, interviews and races are on hold until your penalty is paid or waived by the team. You can still learn, review and ask for help.'],
        'at_risk' => ['🚨', 'Final warning', 'text-red-400', 'You have missed the weekly bar several weeks in a row. Your place in the program is at risk and the team will review it. Please contact them.'],
    ][$state];
    $days = collect(range(0, 6))->map(fn ($i) => $week['week_start']->copy()->addDays($i));
@endphp
<style>
    .sb-meter { height: 12px; border-radius: 99px; background: rgba(148,163,184,.15); overflow: hidden; }
    .sb-meter > div { height: 100%; border-radius: 99px; transition: width .4s; }
    .sb-day { flex: 1; text-align: center; padding: 10px 4px; border-radius: 12px; background: rgba(15,20,29,.6); border: 1px solid rgba(148,163,184,.12); }
    .sb-day.on { background: color-mix(in srgb, var(--color-primary-600) 25%, #0f141d); border-color: color-mix(in srgb, var(--color-primary-400) 55%, transparent); }
    .sb-day.today { outline: 2px solid var(--color-primary-400); }
</style>
<div class="max-w-4xl mx-auto space-y-5">
    <div class="card p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-primary-300 text-xs font-bold uppercase tracking-wider">📏 My weekly bar</p>
                <h1 class="text-2xl font-bold text-white mt-1">This week{{ $week['met'] ? ': bar met! 🎉' : '' }}</h1>
                <p class="text-dark-400 text-sm">{{ $week['week_start']->format('j M') }} to {{ $week['week_start']->copy()->endOfWeek()->format('j M') }} · {{ $week['days_left'] }} {{ Str::plural('day', $week['days_left']) }} left</p>
            </div>
            <div class="text-right">
                <p class="text-2xl">{{ $stateInfo[0] }} <span class="{{ $stateInfo[2] }} font-bold text-lg">{{ $stateInfo[1] }}</span></p>
                <p class="text-dark-400 text-sm max-w-sm">{{ $stateInfo[3] }}</p>
            </div>
        </div>
        @unless($week['enabled'])
            <p class="mt-4 p-3 rounded-lg bg-primary-500/10 text-primary-200 text-sm">The weekly bar starts counting from the week of {{ $week['starts_on']->format('j F Y') }}. Until then, use this page to build the habit: nothing is counted yet.</p>
        @endunless

        <div class="grid md:grid-cols-3 gap-4 mt-5">
            @foreach($week['items'] as $item)
                @php $pct = $item['need'] > 0 ? min(100, $item['have'] / $item['need'] * 100) : 100; @endphp
                <div class="p-4 rounded-xl bg-dark-800/60 border border-dark-700">
                    <div class="flex items-center justify-between">
                        <p class="text-white font-semibold">{{ $item['met'] ? '✅' : '⏳' }} {{ $item['label'] }}</p>
                        <p class="text-white font-bold">{{ $item['have'] }}<span class="text-dark-400 font-normal">/{{ $item['need'] }}</span></p>
                    </div>
                    <div class="sb-meter mt-3"><div style="width: {{ $pct }}%; background: {{ $item['met'] ? '#22c55e' : 'var(--color-primary-500)' }}"></div></div>
                    <p class="text-dark-500 text-xs mt-2">{{ $item['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex gap-2 mt-5">
            @foreach($days as $d)
                @php $kinds = $week['by_day'][$d->toDateString()] ?? null; @endphp
                <div class="sb-day {{ $kinds ? 'on' : '' }} {{ $d->isToday() ? 'today' : '' }}" title="{{ $kinds ? implode(', ', array_keys($kinds)) : 'no activity' }}">
                    <p class="text-dark-400 text-xs">{{ $d->format('D') }}</p>
                    <p class="text-lg">{{ $kinds ? '🔥' : ($d->isFuture() ? '·' : '–') }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @if($penalties->isNotEmpty())
        <div class="card p-6 border border-red-500/30">
            <h2 class="text-white font-semibold text-lg">⛔ Penalty to settle</h2>
            <p class="text-dark-300 text-sm mt-1">Pay through the Fees page (Mobile Money or bank, then upload the receipt). If you had a serious reason (illness, no internet, family), contact the team: they can waive it.</p>
            <div class="space-y-2 mt-4">
                @foreach($penalties as $p)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-lg bg-dark-800">
                        <span class="text-dark-200">{{ $p->title }} · <strong class="text-white">{{ number_format((float) $p->balance) }} FCFA</strong> due</span>
                        <a href="{{ route('fees.show', $p) }}" class="btn-primary text-sm">Pay now</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card p-6">
        <h2 class="text-white font-semibold text-lg">How the weekly bar works</h2>
        <div class="grid md:grid-cols-2 gap-4 mt-3 text-sm text-dark-300">
            <div>
                <p class="text-white font-medium">Every week (Monday to Sunday)</p>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Be active on at least <strong class="text-white">{{ $settings['active_days'] }}</strong> days</li>
                    <li>Practise on at least <strong class="text-white">{{ $settings['practice_days'] }}</strong> days (review, quick checks, Practice Lab, typing, Base Camp)</li>
                    <li>Make at least <strong class="text-white">{{ $settings['progress_actions'] }}</strong> curriculum {{ Str::plural('action', $settings['progress_actions']) }} (start or submit work, tick lesson steps)</li>
                </ul>
                <p class="mt-2">Logging in alone doesn't count: only real learning does.</p>
            </div>
            <div>
                <p class="text-white font-medium">If you miss it</p>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>{{ $settings['fee_from'] > 1 ? 'First miss: a warning.' : 'Each miss: a penalty.' }}</li>
                    @if($settings['fee'] > 0)<li>{{ $settings['fee_from'] === 2 ? 'Second week in a row' : 'Week ' . $settings['fee_from'] . ' in a row' }}: a <strong class="text-white">{{ $fee }}</strong> penalty, and your progress is on hold until it is paid. Each further week adds another {{ $fee }}.</li>@endif
                    <li>After that: a final warning, and the team reviews your place in the program.</li>
                    <li>Meet the bar once and the count starts again from zero.</li>
                </ul>
                <p class="mt-2">Your first week in the program never counts, and if nothing is open for you to work on, the curriculum part is waived.</p>
            </div>
        </div>
    </div>

    @if($history->isNotEmpty())
        <div class="card p-6">
            <h2 class="text-white font-semibold text-lg">Past weeks</h2>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead><tr class="text-dark-400 text-left"><th class="py-2">Week of</th><th>Active</th><th>Practice</th><th>Curriculum</th><th>Result</th></tr></thead>
                    <tbody class="text-dark-200">
                        @foreach($history as $h)
                            <tr class="border-t border-dark-700">
                                <td class="py-2">{{ $h->week_start->format('j M Y') }}</td>
                                <td>{{ $h->active_days }}</td><td>{{ $h->practice_days }}</td><td>{{ $h->progress_waived ? 'waived' : $h->progress_actions }}</td>
                                <td>
                                    @if($h->exempt)<span class="text-dark-300">Excused</span>
                                    @elseif($h->met)<span class="text-green-400">✅ Met</span>
                                    @else<span class="text-amber-300">{{ ['warning' => '⚠️ Missed (warning)', 'fee' => '⛔ Missed (penalty)', 'final' => '🚨 Missed (final warning)'][$h->action] ?? 'Missed' }}</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
