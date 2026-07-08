@extends('layouts.app')

@section('title', 'My Badges')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
                <a href="{{ route('curriculum.index') }}" class="hover:text-white transition">Curriculum</a>
                <span>/</span>
                <span class="text-primary-400">Badges</span>
            </div>
            <h1 class="text-2xl font-bold text-white">My Badges</h1>
            <p class="text-dark-400 mt-1">Your earned achievements and milestones</p>
        </div>
        <div class="text-right">
            <p class="text-3xl font-bold text-primary-400">{{ $badges->count() }}</p>
            <p class="text-dark-400 text-sm">badges earned</p>
        </div>
    </div>

    @if($badges->count() > 0)
    {{-- Badge Type Groups --}}
    @php
        $grouped = $badges->groupBy(fn($b) => $b->type?->value ?? $b->type ?? 'other');
        $typeOrder = ['milestone', 'streak', 'track_completion', 'achievement', 'power_week', 'peer_champion'];
    @endphp

    @foreach($typeOrder as $typeValue)
        @if(isset($grouped[$typeValue]))
        @php
            $typeBadges = $grouped[$typeValue];
            $typeEnum = \App\Enums\BadgeType::tryFrom($typeValue);
        @endphp
        <div>
            <h2 class="text-white font-semibold text-lg mb-3 flex items-center gap-2">
                <span>{{ $typeEnum?->icon() ?? '🏅' }}</span>
                {{ $typeEnum?->label() ?? ucfirst($typeValue) }} Badges
                <span class="text-dark-500 text-sm font-normal">({{ $typeBadges->count() }})</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($typeBadges as $badge)
                <div class="card p-5 hover:ring-1 hover:ring-primary-500/50 transition group relative">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center text-3xl"
                             style="background: {{ ($badge->color ?? '#8B5CF6') }}20;">
                            {{ $badge->icon ?? '🏅' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-semibold text-sm">{{ $badge->name }}</h3>
                            @if($badge->description)
                            <p class="text-dark-400 text-xs mt-0.5">{{ Str::limit($badge->description, 60) }}</p>
                            @endif
                            <p class="text-dark-500 text-xs mt-1">
                                Earned {{ $badge->earned_at?->format('M d, Y') ?? $badge->created_at?->format('M d, Y') ?? '' }}
                            </p>
                        </div>
                    </div>

                    {{-- Share Button (for shareable badge types) --}}
                    @if($badge->type && (\App\Enums\BadgeType::tryFrom($badge->type?->value ?? $badge->type)?->isShareable() ?? false))
                    <div class="mt-3 pt-3 border-t border-dark-700 flex items-center justify-between">
                        @if($badge->shared_at)
                            <span class="text-dark-500 text-xs">✓ Shared</span>
                        @else
                            <span class="text-dark-500 text-xs">Shareable</span>
                        @endif
                        <button onclick="shareBadge('{{ $badge->id }}')"
                                class="text-primary-400 hover:text-primary-300 text-xs font-medium transition">
                            {{ $badge->shared_at ? '🔗 Copy Link' : '📤 Share' }}
                        </button>
                    </div>
                    @endif

                    {{-- Track/Milestone Context --}}
                    @if($badge->track || $badge->milestone)
                    <div class="absolute top-2 right-2">
                        <span class="text-dark-500 text-[10px] bg-dark-800 px-2 py-0.5 rounded-full">
                            {{ $badge->track->name ?? $badge->milestone->title ?? '' }}
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach

    @else
    <div class="card p-12 text-center">
        <div class="text-6xl mb-4">🏅</div>
        <h3 class="text-white font-semibold text-lg">No Badges Yet</h3>
        <p class="text-dark-400 mt-2 max-w-md mx-auto">
            Complete curriculum activities, maintain streaks, and reach milestones to earn badges.
            Your first badge awaits!
        </p>
        <a href="{{ route('curriculum.index') }}" class="btn-primary mt-6 inline-block">
            Go to Curriculum →
        </a>
    </div>
    @endif
</div>

<script>
function shareBadge(badgeId) {
    fetch(`/curriculum/badges/${badgeId}/share`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.shareable_url) {
            navigator.clipboard.writeText(data.shareable_url).then(() => {
                alert('Badge link copied to clipboard!');
            }).catch(() => {
                prompt('Copy this link:', data.shareable_url);
            });
        }
    })
    .catch(err => console.error('Share failed:', err));
}
</script>
@endsection
