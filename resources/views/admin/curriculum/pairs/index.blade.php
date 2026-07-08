@extends('layouts.app')

@section('title', "Accountability Pairs — {$track->name}")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
                <a href="{{ route('admin.curriculum.index', $track) }}" class="hover:text-white transition">{{ $track->name }} Curriculum</a>
                <span>/</span>
                <span class="text-primary-400">Accountability Pairs</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Accountability Pairs</h1>
            <p class="text-dark-400 mt-1">Manage accountability partnerships for {{ $track->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.curriculum.pairs.rotate', $track) }}" method="POST"
                  onsubmit="return confirm('Rotate all pairs? This will deactivate current pairs and create new random pairs.')">
                @csrf
                <button type="submit" class="btn-secondary text-sm">
                    🔄 Rotate Pairs
                </button>
            </form>
            <form action="{{ route('admin.curriculum.pairs.auto', $track) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary text-sm">
                    🤝 Auto-Pair Unpaired
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center text-xl">🤝</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $pairStats['active_pairs'] ?? 0 }}</p>
                    <p class="text-dark-400 text-sm">Active Pairs</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center text-xl">👤</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $pairStats['unpaired_fellows'] ?? 0 }}</p>
                    <p class="text-dark-400 text-sm">Unpaired Fellows</p>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center text-xl">⭐</div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ number_format($pairStats['avg_reviews'] ?? 0, 1) }}</p>
                    <p class="text-dark-400 text-sm">Avg Reviews/Pair</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pairs List -->
    @if($activePairs->count() > 0)
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-dark-700 text-left">
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Fellow A</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider text-center">🤝</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Fellow B</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Milestone</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Reviews</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Paired</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @foreach($activePairs as $pair)
                <tr class="hover:bg-dark-800/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-xs font-bold">
                                {{ strtoupper(substr($pair->fellowA->name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="text-white text-sm">{{ $pair->fellowA->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-dark-500">↔</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 text-xs font-bold">
                                {{ strtoupper(substr($pair->fellowB->name ?? 'B', 0, 1)) }}
                            </div>
                            <span class="text-white text-sm">{{ $pair->fellowB->name ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-dark-300 text-sm">
                        {{ $pair->milestone->title ?? 'All' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-white text-sm font-medium">{{ $pair->review_count ?? 0 }}</span>
                        <span class="text-dark-500 text-xs">reviews</span>
                    </td>
                    <td class="px-6 py-4 text-dark-400 text-sm">
                        {{ $pair->created_at?->diffForHumans() ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card p-12 text-center">
        <div class="text-4xl mb-4">🤝</div>
        <h3 class="text-white font-semibold text-lg">No Active Pairs</h3>
        <p class="text-dark-400 mt-1">Click "Auto-Pair Unpaired" to create accountability partnerships.</p>
    </div>
    @endif
</div>
@endsection
