@extends('layouts.app')

@section('title', 'Internship Reviews')

@section('content')
@php
    $statusStyles = [
        'pending'        => ['label' => 'Pending Review', 'class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30'],
        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-orange-600/20 text-orange-400 border-orange-500/30'],
        'approved'       => ['label' => 'Approved',       'class' => 'bg-green-600/20 text-green-400 border-green-500/30'],
        'rejected'       => ['label' => 'Rejected',       'class' => 'bg-red-600/20 text-red-400 border-red-500/30'],
        'active'         => ['label' => 'Active',         'class' => 'bg-blue-600/20 text-blue-400 border-blue-500/30'],
        'completed'      => ['label' => 'Completed',      'class' => 'bg-primary-600/20 text-primary-400 border-primary-500/30'],
        'withdrawn'      => ['label' => 'Withdrawn',      'class' => 'bg-dark-600/40 text-dark-300 border-dark-500/30'],
    ];
@endphp

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Internship Reviews</h1>
        <p class="text-dark-400">Verify institution, supervisor and documentation for academic and corporate fellows.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stat tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(['pending' => 'Pending', 'needs_revision' => 'Needs Revision', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a href="{{ route('admin.internships.index', ['status' => $key]) }}"
               class="card p-4 hover:border-primary-500/50 transition-colors {{ ($filters['status'] ?? '') === $key ? 'border-primary-500' : '' }}">
                <p class="text-dark-400 text-sm">{{ $label }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $counts[$key] ?? 0 }}</p>
            </a>
        @endforeach
    </div>

    @if(($counts['drafts'] ?? 0) > 0)
        <div class="rounded-xl border border-dark-700 bg-dark-800/40 p-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-dark-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-dark-400 text-sm">
                <span class="text-white font-medium">{{ $counts['drafts'] }} internship draft{{ $counts['drafts'] > 1 ? 's' : '' }}</span>
                still in progress. They'll appear here automatically once the fellow finishes onboarding.
            </p>
        </div>
    @endif

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.internships.index') }}" class="flex flex-col lg:flex-row gap-3">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search fellow, institution or supervisor..."
                   class="form-input flex-1">
            <select name="type" class="form-input w-full lg:w-48">
                <option value="">All types</option>
                <option value="academic"  {{ ($filters['type'] ?? '') === 'academic'  ? 'selected' : '' }}>Academic</option>
                <option value="corporate" {{ ($filters['type'] ?? '') === 'corporate' ? 'selected' : '' }}>Corporate</option>
            </select>
            <select name="status" class="form-input w-full lg:w-48">
                <option value="">Action needed</option>
                @foreach($statusStyles as $key => $meta)
                    <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(!empty(array_filter($filters ?? [])))
                <a href="{{ route('admin.internships.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Fellow</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Type</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Institution</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Supervisor</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Duration</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Submitted</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($profiles as $profile)
                        @php $s = $statusStyles[$profile->status] ?? ['label' => ucfirst($profile->status), 'class' => 'bg-dark-700 text-dark-300']; @endphp
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-medium text-sm">
                                        {{ strtoupper(substr($profile->fellow?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-dark-200 font-medium">{{ $profile->fellow?->name ?? 'Unknown' }}</p>
                                        <p class="text-dark-500 text-xs">{{ $profile->fellow?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="badge {{ $profile->type === 'academic' ? 'bg-blue-600/20 text-blue-400 border-blue-500/30' : 'bg-teal-600/20 text-teal-400 border-teal-500/30' }}">
                                    {{ ucfirst($profile->type) }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-200">{{ $profile->institution_name }}</p>
                                @if($profile->department)
                                    <p class="text-dark-500 text-xs">{{ $profile->department }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-200 text-sm">{{ $profile->supervisor_name }}</p>
                                <p class="text-dark-500 text-xs">{{ $profile->supervisor_email }}</p>
                            </td>
                            <td class="py-4 px-4 text-dark-300 text-sm">{{ $profile->duration_label }}</td>
                            <td class="py-4 px-4 text-dark-400 text-sm">{{ $profile->created_at?->diffForHumans() }}</td>
                            <td class="py-4 px-4 text-center">
                                <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin.internships.show', $profile) }}" class="btn btn-outline py-1.5 px-3 text-sm">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-dark-500">No internship profiles match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $profiles->links() }}</div>
</div>
@endsection
