@extends('layouts.app')

@section('title', 'Practice Lab')

@section('content')
@include('partials.track.styles')
@php
    $skillLabels = collect($experience?->skills() ?? [])->pluck('label', 'key');
    $kindIcon = ['sql' => '🗄️', 'python' => '🐍', 'quiz' => '❓'];
@endphp
<style>
    .pl-stat { border-radius: 14px; padding: 16px; background: rgba(15, 20, 29, 0.6); border: 1px solid rgba(148, 163, 184, 0.15); }
    .pl-daily { border-radius: 16px; padding: 20px; border: 1px solid color-mix(in srgb, var(--color-primary-500) 40%, transparent);
        background: linear-gradient(135deg, color-mix(in srgb, var(--color-primary-600) 30%, #0f141d), #0f141d); }
    .pl-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border-radius: 10px; background: rgba(15, 20, 29, 0.5);
        border: 1px solid rgba(148, 163, 184, 0.12); transition: border-color .15s; }
    .pl-item:hover { border-color: color-mix(in srgb, var(--color-primary-500) 50%, transparent); }
    .pl-dots { letter-spacing: 2px; color: var(--color-primary-400); font-size: 11px; }
</style>
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Practice Lab</h1>
        <p class="text-dark-400 mt-1">Short challenges on the {{ $experience?->company() ?? $track->name }} data. Solve them right here in your browser: they check themselves instantly. Practice XP is just for you and your track's leaderboard; it doesn't change your Career Capital.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="pl-stat"><p class="text-dark-400 text-xs">Practice streak</p><p class="text-2xl font-bold text-white">🔥 {{ $stats['streak'] }} {{ Str::plural('day', $stats['streak']) }}</p></div>
        <div class="pl-stat"><p class="text-dark-400 text-xs">XP this week</p><p class="text-2xl font-bold text-white">{{ $stats['week_xp'] }}</p></div>
        <div class="pl-stat"><p class="text-dark-400 text-xs">Total XP</p><p class="text-2xl font-bold text-white">{{ $stats['xp'] }}</p></div>
        <div class="pl-stat"><p class="text-dark-400 text-xs">Solved</p><p class="text-2xl font-bold text-white">{{ $stats['solved'] }} / {{ $stats['total'] }}</p></div>
    </div>

    @if($daily)
        <div class="pl-daily">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="tx-chip">Today's challenge · +50% XP</span>
                    <h2 class="text-xl font-bold text-white mt-2">{{ $kindIcon[$daily->kind] ?? '' }} {{ $daily->title }}</h2>
                    @php $dailySkill = $skillLabels[$daily->skill] ?? ucfirst((string) $daily->skill); @endphp
                    <p class="text-dark-300 text-sm mt-1">{{ $daily->kindLabel() }}{{ $dailySkill !== $daily->kindLabel() ? ' · ' . $dailySkill : '' }} · {{ (int) round($daily->xp * 1.5) }} XP today</p>
                </div>
                @if($solved->contains($daily->id))
                    <a href="{{ route('practice.show', $daily) }}" class="btn btn-secondary whitespace-nowrap">✓ Solved: review it</a>
                @else
                    <a href="{{ route('practice.show', $daily) }}" class="btn btn-primary whitespace-nowrap">Start today's challenge</a>
                @endif
            </div>
            @if(!$stats['practiced_today'])
                <p class="text-dark-400 text-xs mt-3">Solve any challenge today to {{ $stats['streak'] > 0 ? 'keep your streak' : 'start a streak' }}.</p>
            @endif
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">
            @forelse($bySkill as $skill => $items)
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-white font-semibold">{{ $skillLabels[$skill] ?? ucfirst($skill) }}</h3>
                        <span class="text-dark-400 text-xs">{{ $items->filter(fn ($c) => $solved->contains($c->id))->count() }} / {{ $items->count() }} solved</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($items as $c)
                            <a href="{{ route('practice.show', $c) }}" class="pl-item">
                                <span class="flex items-center gap-3 min-w-0">
                                    <span aria-hidden="true">{{ $solved->contains($c->id) ? '✅' : ($kindIcon[$c->kind] ?? '•') }}</span>
                                    <span class="min-w-0">
                                        <span class="text-white text-sm block truncate">{{ $c->title }}</span>
                                        <span class="text-dark-500 text-xs">{{ $c->kindLabel() }}{{ $c->kind === 'python' && $c->needs_pandas ? ' · pandas' : '' }}</span>
                                    </span>
                                </span>
                                <span class="flex items-center gap-3 flex-shrink-0">
                                    <span class="pl-dots" title="Difficulty {{ $c->difficulty }} of 3">{{ str_repeat('●', $c->difficulty) }}{{ str_repeat('○', 3 - $c->difficulty) }}</span>
                                    <span class="text-dark-400 text-xs">{{ $c->xp }} XP</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="card p-10 text-center text-dark-400">No challenges for this track yet.</div>
            @endforelse
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h3 class="text-white font-semibold mb-1">This week's leaderboard</h3>
                <p class="text-dark-500 text-xs mb-3">{{ $track->name }} · resets every Monday</p>
                @forelse($leaderboard as $i => $row)
                    <div class="flex items-center justify-between py-2 {{ $row->fellow_id === auth()->id() ? 'text-primary-300 font-semibold' : 'text-dark-200' }}">
                        <span class="text-sm">{{ ['🥇', '🥈', '🥉'][$i] ?? ($i + 1) . '.' }} {{ $row->display_name }}{{ $row->fellow_id === auth()->id() ? ' (you)' : '' }}</span>
                        <span class="text-sm">{{ (int) $row->xp }} XP</span>
                    </div>
                @empty
                    <p class="text-dark-400 text-sm">Nobody has scored this week yet. Be the first!</p>
                @endforelse
            </div>
            <div class="card p-5 text-sm text-dark-300 space-y-2">
                <h3 class="text-white font-semibold">How it works</h3>
                <p>🗄️ <strong class="text-white">SQL</strong> runs on a copy of the sales database inside your browser (about 1 MB to load, once).</p>
                <p>🐍 <strong class="text-white">Python</strong> runs in your browser too. The first time, it downloads about 10 MB (pandas about 12 MB more). After that it's cached.</p>
                <p>❓ <strong class="text-white">Quick questions</strong> need no download at all.</p>
                <p>Stuck? You get a hint after a wrong try, and the explanation after {{ \App\Services\PracticeService::ATTEMPTS_BEFORE_REVEAL }} tries.</p>
            </div>
        </div>
    </div>
</div>
@endsection
