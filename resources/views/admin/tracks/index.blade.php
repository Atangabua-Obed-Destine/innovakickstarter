@extends('layouts.app')

@section('title', 'Track Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Track Management</h1>
            <p class="text-dark-400 mt-1">Manage learning tracks and their settings</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-primary-500/20 text-primary-400">
                {{ $tracks->count() }} tracks
            </span>
        </div>
    </div>

    <!-- Track Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $tracks->count() }}</p>
                    <p class="text-dark-400 text-sm">Total Tracks</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $tracks->where('is_active', true)->count() }}</p>
                    <p class="text-dark-400 text-sm">Active Tracks</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $tracks->sum('fellow_tracks_count') }}</p>
                    <p class="text-dark-400 text-sm">Total Enrollments</p>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    @php
                        $avgEnrollment = $tracks->count() > 0 ? round($tracks->sum('fellow_tracks_count') / $tracks->count(), 1) : 0;
                    @endphp
                    <p class="text-2xl font-bold text-white">{{ $avgEnrollment }}</p>
                    <p class="text-dark-400 text-sm">Avg per Track</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracks Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-dark-700">
            <h3 class="text-lg font-semibold text-white">All Tracks</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium">Track</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Category</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Enrolled</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Duration</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-6 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($tracks as $track)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-4">
                                    @php
                                        $trackColor = $track->color ?? '#6366f1';
                                    @endphp
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                                         style="background-color: {{ $trackColor }}20; color: {{ $trackColor }}">
                                        <x-track-icon :icon="$track->icon" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $track->name }}</p>
                                        @if($track->description)
                                            <p class="text-dark-400 text-sm line-clamp-1">{{ Str::limit($track->description, 50) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-dark-700 text-dark-300">
                                    {{ $track->category?->value ?? 'General' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="text-white font-medium">{{ $track->fellow_tracks_count }}</span>
                                <span class="text-dark-500">fellows</span>
                            </td>
                            <td class="py-4 px-6 text-center text-dark-300">
                                {{ $track->duration_weeks ?? 12 }} weeks
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($track->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-dark-600 text-dark-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-dark-500 mr-1.5"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.curriculum.index', $track) }}" 
                                       class="p-2 text-dark-400 hover:text-purple-400 hover:bg-dark-700 rounded-lg transition-colors"
                                       title="Manage Curriculum">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.tracks.edit', $track) }}" 
                                       class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors"
                                       title="Edit Track">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="w-16 h-16 mx-auto rounded-full bg-dark-700 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <p class="text-dark-400">No tracks found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
