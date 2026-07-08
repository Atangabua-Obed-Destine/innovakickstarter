@extends('layouts.app')

@section('title', 'Interview Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Interview Management</h1>
            <p class="text-dark-400 mt-1">Manage all mock interview sessions across the platform</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.interviews.analytics') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Analytics
            </a>
            <a href="{{ route('admin.interviews.export', request()->query()) }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
                    <p class="text-dark-400 text-sm">Total</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['scheduled'] }}</p>
                    <p class="text-dark-400 text-sm">Scheduled</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['completed'] }}</p>
                    <p class="text-dark-400 text-sm">Completed</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center">
                    <span class="text-purple-400">🤖</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['ai_interviews'] }}</p>
                    <p class="text-dark-400 text-sm">AI Interviews</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['unassigned'] }}</p>
                    <p class="text-dark-400 text-sm">Needs Mentor</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="flex flex-wrap gap-4 text-sm">
        <div class="flex items-center gap-2 text-dark-400">
            <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
            In Progress: {{ $stats['in_progress'] }}
        </div>
        <div class="flex items-center gap-2 text-dark-400">
            <span class="w-2 h-2 rounded-full bg-red-400"></span>
            Cancelled: {{ $stats['cancelled'] }}
        </div>
        <div class="flex items-center gap-2 text-dark-400">
            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
            Human Interviews: {{ $stats['human_interviews'] }}
        </div>
        <div class="flex items-center gap-2 text-dark-400">
            <span class="w-2 h-2 rounded-full bg-green-400"></span>
            Today: {{ $stats['today'] }}
        </div>
        @if($stats['avg_score'])
        <div class="flex items-center gap-2 text-dark-400">
            <span class="w-2 h-2 rounded-full bg-primary-400"></span>
            Avg Score: {{ number_format($stats['avg_score'], 1) }}%
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.interviews.index') }}" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Search fellow name or email..."
                       class="input-field w-full">
            </div>

            <!-- Status Filter -->
            <select name="status" class="input-field w-40">
                <option value="">All Statuses</option>
                @foreach($interviewStatuses as $status)
                    <option value="{{ $status->value }}" {{ ($filters['status'] ?? '') === $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <!-- Mode Filter -->
            <select name="mode" class="input-field w-40">
                <option value="">All Modes</option>
                @foreach($interviewModes as $mode)
                    <option value="{{ $mode->value }}" {{ ($filters['mode'] ?? '') === $mode->value ? 'selected' : '' }}>
                        {{ $mode->label() }}
                    </option>
                @endforeach
            </select>

            <!-- Type Filter -->
            <select name="type" class="input-field w-44">
                <option value="">All Types</option>
                @foreach($interviewTypes as $type)
                    <option value="{{ $type->value }}" {{ ($filters['type'] ?? '') === $type->value ? 'selected' : '' }}>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>

            <!-- Track Filter -->
            <select name="track_id" class="input-field w-44">
                <option value="">All Tracks</option>
                @foreach($tracks as $track)
                    <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>
                        {{ $track->name }}
                    </option>
                @endforeach
            </select>

            <!-- Date Range -->
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" 
                   class="input-field w-36" placeholder="From">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" 
                   class="input-field w-36" placeholder="To">

            <!-- Unassigned Toggle -->
            <label class="flex items-center gap-2 text-dark-300 cursor-pointer">
                <input type="checkbox" name="unassigned" value="1" 
                       {{ ($filters['unassigned'] ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-500 focus:ring-primary-500">
                <span class="text-sm">Unassigned only</span>
            </label>

            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.interviews.index') }}" class="btn btn-outline">Clear</a>
        </form>
    </div>

    <!-- Interviews Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800 border-b border-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Fellow</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Mode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Track</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Scheduled</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase tracking-wider">Mentor</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-dark-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($interviews as $interview)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <!-- Fellow -->
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-xs font-bold text-white">
                                        {{ strtoupper(substr($interview->fellow?->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.fellows.show', $interview->fellow_id) }}" 
                                           class="text-dark-100 font-medium hover:text-primary-400">
                                            {{ $interview->fellow?->name ?? 'Unknown' }}
                                        </a>
                                        <p class="text-dark-500 text-xs">{{ $interview->fellow?->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Type -->
                            <td class="px-4 py-4">
                                <span class="text-dark-200 text-sm">{{ $interview->type->shortLabel() }}</span>
                            </td>

                            <!-- Mode -->
                            <td class="px-4 py-4">
                                @if($interview->mode->value === 'ai')
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-purple-600/20 text-purple-400">
                                        🤖 AI
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-cyan-600/20 text-cyan-400">
                                        👤 Human
                                    </span>
                                @endif
                            </td>

                            <!-- Track -->
                            <td class="px-4 py-4">
                                <span class="text-dark-300 text-sm">{{ $interview->track?->name ?? 'N/A' }}</span>
                            </td>

                            <!-- Scheduled -->
                            <td class="px-4 py-4">
                                @if($interview->scheduled_at)
                                    <div class="text-dark-200 text-sm">{{ $interview->scheduled_at->format('M j, Y') }}</div>
                                    <div class="text-dark-500 text-xs">{{ $interview->scheduled_at->format('g:i A') }}</div>
                                @else
                                    <span class="text-dark-500 text-sm">Not scheduled</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-600/20 text-gray-400',
                                        'scheduled' => 'bg-blue-600/20 text-blue-400',
                                        'in_progress' => 'bg-yellow-600/20 text-yellow-400',
                                        'completed' => 'bg-green-600/20 text-green-400',
                                        'cancelled' => 'bg-red-600/20 text-red-400',
                                        'no_show' => 'bg-orange-600/20 text-orange-400',
                                    ];
                                    $statusClass = $statusColors[$interview->status->value] ?? 'bg-gray-600/20 text-gray-400';
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $interview->status->label() }}
                                </span>
                            </td>

                            <!-- Score -->
                            <td class="px-4 py-4">
                                @if($interview->score !== null)
                                    @php
                                        $scoreClass = $interview->score >= 80 ? 'text-green-400' : 
                                                     ($interview->score >= 60 ? 'text-amber-400' : 'text-red-400');
                                    @endphp
                                    <span class="font-bold {{ $scoreClass }}">{{ number_format($interview->score, 0) }}%</span>
                                @else
                                    <span class="text-dark-500">—</span>
                                @endif
                            </td>

                            <!-- Mentor -->
                            <td class="px-4 py-4">
                                @if($interview->mode->value === 'human')
                                    @if($interview->interviewer)
                                        <span class="text-dark-200 text-sm">{{ $interview->interviewer->name }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-600/20 text-amber-400">
                                            Unassigned
                                        </span>
                                    @endif
                                @else
                                    <span class="text-dark-500 text-sm">N/A</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.interviews.show', $interview) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg text-primary-400 hover:bg-primary-600/10 transition-colors">
                                    View
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-dark-400">No interviews found matching your criteria.</p>
                                    @if(count(array_filter($filters)))
                                        <a href="{{ route('admin.interviews.index') }}" class="text-primary-400 hover:underline">Clear filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($interviews->hasPages())
            <div class="px-4 py-3 border-t border-dark-700">
                {{ $interviews->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
