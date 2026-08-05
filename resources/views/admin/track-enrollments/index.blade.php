@extends('layouts.app')

@section('title', 'Track Enrollments')

@section('content')
@php
    $styles = [
        'pending'        => ['label' => 'Pending Review', 'class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-orange-600/20 text-orange-400 border-orange-500/30'],
        'approved'       => ['label' => 'Approved',       'class' => 'bg-green-600/20 text-green-400 border-green-500/30'],
        'rejected'       => ['label' => 'Rejected',       'class' => 'bg-red-600/20 text-red-400 border-red-500/30'],
    ];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Track Enrollments</h1>
        <p class="text-dark-400">Review additional-track requests from fellows. Their first track is auto-approved right after onboarding.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(['pending' => 'Pending', 'needs_revision' => 'Needs Revision', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a href="{{ route('admin.track-enrollments.index', ['status' => $key]) }}"
               class="card p-4 hover:border-primary-500/50 transition-colors {{ ($filters['status'] ?? '') === $key ? 'border-primary-500' : '' }}">
                <p class="text-dark-400 text-sm">{{ $label }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $counts[$key] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    <div class="card p-4">
        <form method="GET" action="{{ route('admin.track-enrollments.index') }}" class="flex flex-col lg:flex-row gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search fellow by name or email..." class="form-input flex-1">
            <select name="track_id" class="form-input w-full lg:w-48">
                <option value="">All tracks</option>
                @foreach($tracks as $t)
                    <option value="{{ $t->id }}" {{ ($filters['track_id'] ?? '') === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-input w-full lg:w-48">
                <option value="">Action needed</option>
                @foreach($styles as $k => $s)
                    <option value="{{ $k }}" {{ ($filters['status'] ?? '') === $k ? 'selected' : '' }}>{{ $s['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(!empty(array_filter($filters ?? [])))
                <a href="{{ route('admin.track-enrollments.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Fellow</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Requested Track</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Motivation</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Requested</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($enrollments as $e)
                        @php $s = $styles[$e->status] ?? ['label' => ucfirst($e->status), 'class' => 'bg-dark-700 text-dark-300']; @endphp
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-medium text-sm">
                                        {{ strtoupper(substr($e->fellow?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-dark-200 font-medium">{{ $e->fellow?->name ?? 'Unknown' }}</p>
                                        <p class="text-dark-500 text-xs">{{ $e->fellow?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-200 font-medium">{{ $e->track?->name }}</p>
                                @if($e->track?->category)
                                    <p class="text-dark-500 text-xs">{{ ucfirst($e->track->category->value) }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <p class="text-dark-300 text-sm line-clamp-2">{{ $e->motivation ?: '—' }}</p>
                            </td>
                            <td class="py-4 px-4 text-dark-400 text-sm">{{ $e->requested_at?->diffForHumans() ?? '—' }}</td>
                            <td class="py-4 px-4 text-center"><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin.track-enrollments.show', $e) }}" class="btn btn-outline py-1.5 px-3 text-sm">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-dark-500">No track enrollment requests match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $enrollments->links() }}</div>
</div>
@endsection
