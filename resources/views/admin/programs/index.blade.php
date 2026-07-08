@extends('layouts.app')

@section('title', 'Manage Programs')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manage Programs</h1>
            <p class="text-dark-400">Administer fellowship programs and batches</p>
        </div>
        <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Program
        </a>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-600/20 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-600/20 border border-red-500/30 text-red-400 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-600/20 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-lg">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('info'))
        <div class="bg-blue-600/20 border border-blue-500/30 text-blue-400 px-4 py-3 rounded-lg">
            {{ session('info') }}
        </div>
    @endif

    <!-- Filters & Search -->
    <form method="GET" action="{{ route('admin.programs.index') }}" class="card p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search programs..." class="form-input pl-10">
            </div>
            <select name="status" class="form-input w-full sm:w-40">
                <option value="">All Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <select name="year" class="form-input w-full sm:w-32">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['year']))
                <a href="{{ route('admin.programs.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-4 lg:grid-cols-8 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-dark-400 text-xs">Total Programs</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-400">{{ $stats['active'] }}</p>
            <p class="text-dark-400 text-xs">Active</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-blue-400">{{ $stats['enrolling'] }}</p>
            <p class="text-dark-400 text-xs">Enrolling</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-cyan-400">{{ $stats['upcoming'] }}</p>
            <p class="text-dark-400 text-xs">Upcoming</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-purple-400">{{ $stats['graduated'] }}</p>
            <p class="text-dark-400 text-xs">Graduated</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-primary-400">{{ $stats['total_fellows'] }}</p>
            <p class="text-dark-400 text-xs">Total Fellows</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-amber-400">{{ $stats['total_graduates'] }}</p>
            <p class="text-dark-400 text-xs">Graduates</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-teal-400">{{ $stats['employed_alumni'] }}</p>
            <p class="text-dark-400 text-xs">Employed Alumni</p>
        </div>
    </div>

    <!-- Programs Grid -->
    @if($programs->count() > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($programs as $program)
            @php
                $statusClasses = match($program->status) {
                    'active' => 'bg-green-600/20 text-green-400 border-green-500/30',
                    'enrolling' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                    'upcoming' => 'bg-cyan-600/20 text-cyan-400 border-cyan-500/30',
                    'graduated' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
                    'draft' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
                    'archived' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
                    default => 'bg-dark-600/20 text-dark-400 border-dark-500/30'
                };
            @endphp
            <div class="card overflow-hidden hover:border-primary-500/50 transition-colors">
                <!-- Header -->
                <div class="p-4 border-b border-dark-700">
                    <div class="flex items-start justify-between">
                        <div>
                            @if($program->sponsor_name)
                            <div class="flex items-center gap-2 mb-2">
                                @if($program->sponsor_logo)
                                    <img src="{{ $program->sponsor_logo }}" alt="{{ $program->sponsor_name }}" class="h-5 w-auto">
                                @endif
                                <span class="text-dark-400 text-sm">{{ $program->sponsor_name }}</span>
                            </div>
                            @endif
                            <h3 class="text-lg font-semibold text-white">{{ $program->name }}</h3>
                            @if($program->start_date)
                                <p class="text-dark-500 text-sm mt-1">{{ $program->start_date->format('M Y') }}</p>
                            @endif
                        </div>
                        <span class="badge {{ $statusClasses }}">{{ $statuses[$program->status] ?? ucfirst($program->status) }}</span>
                    </div>
                </div>

                <!-- Stats -->
                <div class="p-4 space-y-4">
                    <!-- Fellows Count -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-dark-400 text-sm">Fellows</span>
                            <span class="text-dark-200 font-medium">
                                {{ $program->fellows_count ?? 0 }}@if($program->max_capacity)/{{ $program->max_capacity }}@endif
                            </span>
                        </div>
                        @if($program->max_capacity)
                        @php
                            $fillPercentage = ($program->fellows_count / $program->max_capacity) * 100;
                        @endphp
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $fillPercentage >= 90 ? 'bg-amber-500' : 'bg-primary-500' }} rounded-full" 
                                 style="width: {{ min($fillPercentage, 100) }}%"></div>
                        </div>
                        @endif
                    </div>

                    <!-- Status Breakdown -->
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-dark-800/50 rounded-lg p-2">
                            <p class="text-lg font-semibold text-green-400">{{ $program->active_fellows_count ?? 0 }}</p>
                            <p class="text-dark-500 text-xs">Active</p>
                        </div>
                        <div class="bg-dark-800/50 rounded-lg p-2">
                            <p class="text-lg font-semibold text-purple-400">{{ $program->graduates_count ?? 0 }}</p>
                            <p class="text-dark-500 text-xs">Graduated</p>
                        </div>
                        <div class="bg-dark-800/50 rounded-lg p-2">
                            @php
                                $employmentRate = ($program->fellows_count ?? 0) > 0 
                                    ? round(($program->employed_count ?? 0) / ($program->fellows_count ?? 1) * 100) 
                                    : 0;
                            @endphp
                            <p class="text-lg font-semibold text-teal-400">{{ $employmentRate }}%</p>
                            <p class="text-dark-500 text-xs">Employed</p>
                        </div>
                    </div>

                    <!-- Dates -->
                    @if($program->start_date && $program->end_date)
                    <div class="flex items-center gap-4 text-sm pt-2 border-t border-dark-700">
                        <div class="flex items-center gap-2 text-dark-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $program->start_date->format('M j, Y') }}</span>
                        </div>
                        <span class="text-dark-600">→</span>
                        <span class="text-dark-400">{{ $program->end_date->format('M j, Y') }}</span>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="p-4 border-t border-dark-700 flex items-center justify-between">
                    <a href="{{ route('admin.programs.show', $program) }}" class="text-primary-400 text-sm hover:underline">View Details</a>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.programs.edit', $program) }}" 
                           class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" 
                           title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @if($program->status === 'draft' || ($program->fellows_count === 0))
                        <form action="{{ route('admin.programs.destroy', $program) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this program?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors" 
                                    title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-dark-500 text-sm">
            Showing {{ $programs->firstItem() ?? 0 }} to {{ $programs->lastItem() ?? 0 }} of {{ $programs->total() }} programs
        </p>
        {{ $programs->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-dark-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
        </svg>
        <h3 class="text-xl font-semibold text-white mb-2">No Programs Found</h3>
        <p class="text-dark-400 mb-6">
            @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['year']))
                No programs match your filters. Try adjusting your search criteria.
            @else
                Get started by creating your first program.
            @endif
        </p>
        @if(empty($filters['search']) && empty($filters['status']) && empty($filters['year']))
            <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create First Program
            </a>
        @else
            <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">Clear Filters</a>
        @endif
    </div>
    @endif
</div>
@endsection
