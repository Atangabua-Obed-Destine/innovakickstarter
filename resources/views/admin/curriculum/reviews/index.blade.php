@extends('layouts.app')

@section('title', 'Review Queue')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Curriculum Review Queue</h1>
            <p class="text-dark-400 mt-1">Review fellow submissions, approve or request revisions</p>
        </div>
        <div>
            <form method="GET" action="{{ route('admin.curriculum.reviews') }}" class="flex items-center gap-3">
                <select name="track_id" onchange="this.form.submit()"
                        class="bg-dark-800 border border-dark-600 rounded-lg px-4 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <option value="">All Tracks</option>
                    @foreach($tracks as $t)
                        <option value="{{ $t->id }}" {{ ($track?->id ?? '') === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">
        {{ session('success') }}
    </div>
    @endif

    @if($pendingReviews->count() > 0)
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-dark-700 text-left">
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Fellow</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Activity</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Track</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Peer</th>
                    <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @foreach($pendingReviews as $progress)
                <tr class="hover:bg-dark-800/50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 text-sm font-bold">
                                {{ strtoupper(substr($progress->fellow->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-white text-sm font-medium">{{ $progress->fellow->name ?? 'Unknown' }}</p>
                                <p class="text-dark-500 text-xs">{{ $progress->fellow->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-white text-sm">{{ Str::limit($progress->curriculumActivity->title ?? '', 40) }}</p>
                        <p class="text-dark-500 text-xs">{{ $progress->curriculumActivity->type?->label() ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-dark-300 text-sm">{{ $progress->curriculumActivity->track->name ?? '' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColor = match($progress->status?->value ?? $progress->status ?? '') {
                                'submitted' => 'text-blue-400 bg-blue-500/10',
                                'peer_review' => 'text-amber-400 bg-amber-500/10',
                                'under_review' => 'text-purple-400 bg-purple-500/10',
                                default => 'text-dark-400 bg-dark-700',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full text-xs {{ $statusColor }}">
                            {{ $progress->status?->label() ?? ucfirst($progress->status ?? 'N/A') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-dark-400 text-sm">
                        {{ $progress->submitted_at ? $progress->submitted_at->diffForHumans() : '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($progress->peer_rating)
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-xs {{ $i <= $progress->peer_rating ? 'text-amber-400' : 'text-dark-600' }}">★</span>
                                @endfor
                            </div>
                        @else
                            <span class="text-dark-600 text-xs">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.curriculum.reviews.show', $progress->id) }}" class="btn-primary text-xs px-3 py-1.5">
                            Review
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($pendingReviews->hasPages())
    <div class="mt-4">{{ $pendingReviews->links() }}</div>
    @endif

    @else
    <div class="card p-12 text-center">
        <div class="text-4xl mb-4">✅</div>
        <h3 class="text-white font-semibold text-lg">All caught up!</h3>
        <p class="text-dark-400 mt-1">No submissions pending review.</p>
    </div>
    @endif
</div>
@endsection
