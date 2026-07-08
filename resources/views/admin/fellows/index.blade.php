@extends('layouts.app')

@section('title', 'Manage Fellows')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manage Fellows</h1>
            <p class="text-dark-400">View and manage all fellows across cohorts</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import
            </button>
            <button type="button" class="btn btn-primary" x-data="" x-on:click="$dispatch('open-invite-modal')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Invite Fellow
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.fellows.index') }}" class="flex flex-col lg:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name or email..." class="form-input pl-10">
            </div>
            <select name="track_id" class="form-input w-full lg:w-48">
                <option value="">All Tracks</option>
                @foreach($tracks as $track)
                    <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>
                        {{ $track->name }}
                    </option>
                @endforeach
            </select>
            <select name="tier" class="form-input w-full lg:w-40">
                <option value="">All Tiers</option>
                <option value="rookie" {{ ($filters['tier'] ?? '') === 'rookie' ? 'selected' : '' }}>Rookie</option>
                <option value="intern" {{ ($filters['tier'] ?? '') === 'intern' ? 'selected' : '' }}>Intern</option>
                <option value="professional" {{ ($filters['tier'] ?? '') === 'professional' ? 'selected' : '' }}>Professional</option>
                <option value="elite" {{ ($filters['tier'] ?? '') === 'elite' ? 'selected' : '' }}>Elite</option>
            </select>
            <select name="status" class="form-input w-full lg:w-40">
                <option value="">All Status</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(!empty(array_filter($filters ?? [])))
                <a href="{{ route('admin.fellows.index') }}" class="btn btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    <div class="flex items-center justify-between p-4 bg-dark-800 rounded-lg" x-data="{ selected: 0 }">
        <div class="flex items-center gap-4">
            <input type="checkbox" class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
            <span class="text-dark-400 text-sm"><span x-text="selected">0</span> selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button class="btn btn-outline py-1.5 px-3 text-sm" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Email
            </button>
            <button class="btn btn-outline py-1.5 px-3 text-sm" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Fellows Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Fellow</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Type / Internship</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Cohort / Track</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Score</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Activities</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Last Active</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($fellows as $fellow)
                        @php
                            $primaryTrack = $fellow->primaryTrack?->track;
                            $score = $fellow->primaryTrack?->score ?? 0;
                            $tier = $fellow->primaryTrack?->tier ?? 'rookie';
                        @endphp
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500" value="{{ $fellow->id }}">
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-medium">
                                        {{ strtoupper(substr($fellow->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-dark-200 font-medium">{{ $fellow->name }}</p>
                                        <p class="text-dark-500 text-sm">{{ $fellow->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                @php
                                    $ft = $fellow->fellow_type?->value ?? null;
                                    $ip = $fellow->internshipProfile;
                                    $ipMap = [
                                        'pending'        => ['Pending',    'bg-amber-600/20 text-amber-400 border-amber-500/30'],
                                        'needs_revision' => ['Revision',   'bg-orange-600/20 text-orange-400 border-orange-500/30'],
                                        'approved'       => ['Approved',   'bg-green-600/20 text-green-400 border-green-500/30'],
                                        'rejected'       => ['Rejected',   'bg-red-600/20 text-red-400 border-red-500/30'],
                                        'active'         => ['Active',     'bg-blue-600/20 text-blue-400 border-blue-500/30'],
                                        'completed'      => ['Completed',  'bg-primary-600/20 text-primary-400 border-primary-500/30'],
                                        'withdrawn'      => ['Withdrawn',  'bg-dark-600/40 text-dark-300 border-dark-500/30'],
                                    ];
                                @endphp
                                @if($ft)
                                    <p class="text-dark-200 text-sm capitalize">{{ str_replace('_', ' ', $ft) }}</p>
                                @else
                                    <p class="text-dark-500 text-sm">—</p>
                                @endif
                                @if($ip)
                                    @php [$label, $class] = $ipMap[$ip->status] ?? [ucfirst($ip->status), 'bg-dark-700 text-dark-300']; @endphp
                                    <a href="{{ route('admin.internships.show', $ip) }}" class="inline-block mt-1">
                                        <span class="badge {{ $class }} text-xs">{{ $label }}</span>
                                    </a>
                                    @if(in_array($ip->status, ['approved', 'active']) && $ip->approved_end_date)
                                        <p class="text-[11px] text-dark-500 mt-1">
                                            @if($ip->is_expired)
                                                Ended {{ $ip->approved_end_date->diffForHumans() }}
                                            @elseif($ip->status === 'approved')
                                                Starts {{ $ip->approved_start_date?->diffForHumans() }}
                                            @else
                                                {{ $ip->days_remaining }}d left · {{ $ip->progress_percent }}%
                                            @endif
                                        </p>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-200">{{ ucfirst($tier) }}</p>
                                <p class="text-dark-500 text-sm">{{ $primaryTrack?->name ?? 'No Track' }}</p>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="w-12 h-12 relative">
                                        <svg class="w-12 h-12 -rotate-90">
                                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="4" class="text-dark-700"/>
                                            <circle cx="24" cy="24" r="20" fill="none" stroke="currentColor" stroke-width="4" 
                                                    stroke-dasharray="{{ 2 * 3.14159 * 20 }}" 
                                                    stroke-dashoffset="{{ 2 * 3.14159 * 20 * (1 - min($score, 100) / 100) }}"
                                                    class="{{ $score >= 80 ? 'text-green-500' : ($score >= 60 ? 'text-amber-500' : 'text-red-500') }}"/>
                                        </svg>
                                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                                            {{ min(round($score), 100) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="text-dark-300">{{ $fellow->primaryTrack?->approved_activities_count ?? 0 }}</span>
                            </td>
                            <td class="py-4 px-4 text-center text-dark-400 text-sm">
                                {{ $fellow->last_login_at ? $fellow->last_login_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusClasses = $fellow->is_active 
                                        ? 'bg-green-600/20 text-green-400 border-green-500/30'
                                        : 'bg-amber-600/20 text-amber-400 border-amber-500/30';
                                @endphp
                                <span class="badge {{ $statusClasses }}">{{ $fellow->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.fellows.show', $fellow) }}" 
                                       class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <button class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('admin.fellows.toggle-status', $fellow) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-2 text-dark-400 hover:text-{{ $fellow->is_active ? 'amber' : 'green' }}-400 hover:bg-dark-700 rounded-lg transition-colors" 
                                                title="{{ $fellow->is_active ? 'Deactivate' : 'Activate' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($fellow->is_active)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @endif
                                            </svg>
                                        </button>
                                    </form>
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition
                                             class="absolute right-0 mt-2 w-48 bg-dark-800 rounded-lg shadow-xl border border-dark-700 z-50">
                                            <a href="{{ route('admin.fellows.show', $fellow) }}" class="flex items-center gap-2 px-4 py-2 text-dark-200 hover:bg-dark-700 rounded-t-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                                View Progress
                                            </a>
                                            <button class="flex items-center gap-2 px-4 py-2 text-dark-200 hover:bg-dark-700 w-full text-left">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                Reset Password
                                            </button>
                                            <hr class="border-dark-700">
                                            <button class="flex items-center gap-2 px-4 py-2 text-red-400 hover:bg-dark-700 w-full rounded-b-lg text-left">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Remove Fellow
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-dark-400">No fellows found</p>
                                    <p class="text-dark-500 text-sm">Invite fellows to get started</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-dark-700">
            {{ $fellows->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
