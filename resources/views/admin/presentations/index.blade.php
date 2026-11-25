@extends('layouts.app')

@section('title', 'Presentations')

@section('content')
@php
    $tabs = [
        \App\Models\Presentation::STATUS_SUBMITTED => 'Awaiting review',
        \App\Models\Presentation::STATUS_CHANGES_REQUESTED => 'Changes requested',
        \App\Models\Presentation::STATUS_APPROVED => 'Approved',
        \App\Models\Presentation::STATUS_REJECTED => 'Not accepted',
        'all' => 'All',
    ];
@endphp
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Presentations</h1>
        <p class="text-dark-400 mt-1">Demos, workshops and talks fellows gave. Approving one adds the amounts you choose to their Career Capital.</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.presentations.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm border {{ $status === $key ? 'border-primary-500 bg-primary-500/10 text-white' : 'border-dark-600 text-dark-300 hover:text-white' }}">
                {{ $label }}
                @if($key !== 'all' && ($counts[$key] ?? 0) > 0)
                    <span class="ml-1 text-dark-400">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    @if($presentations->count() > 0)
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-dark-700 text-left">
                            <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Fellow</th>
                            <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Presentation</th>
                            <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Track</th>
                            <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Given</th>
                            <th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        @foreach($presentations as $presentation)
                            <tr class="hover:bg-dark-800/50 transition">
                                <td class="px-6 py-4 text-white text-sm font-medium">{{ $presentation->fellow->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-white text-sm">{{ Str::limit($presentation->title, 60) }}</p>
                                    <p class="text-dark-500 text-xs">{{ $presentation->kindLabel() }} · {{ Str::limit($presentation->event_name, 40) }}</p>
                                </td>
                                <td class="px-6 py-4 text-dark-300 text-sm">{{ $presentation->track->name ?? '' }}</td>
                                <td class="px-6 py-4 text-dark-300 text-sm">{{ $presentation->presented_at?->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="badge {{ $presentation->statusBadgeClass() }}">{{ $presentation->statusLabel() }}</span>
                                    @if($presentation->submission_count > 1)
                                        <span class="text-dark-500 text-xs ml-1">resubmitted</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.presentations.show', $presentation) }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium">
                                        {{ $presentation->status === \App\Models\Presentation::STATUS_SUBMITTED ? 'Review' : 'View' }} →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $presentations->links() }}
    @else
        <div class="card p-10 text-center text-dark-400">Nothing here.</div>
    @endif
</div>
@endsection
