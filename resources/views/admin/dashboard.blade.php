@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
            <p class="text-dark-400">Overview of platform performance and key metrics</p>
        </div>
        <div class="flex gap-2">
            <select class="form-input py-2 text-sm w-auto">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 90 days</option>
                <option>All time</option>
            </select>
            <button class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Fellows -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-{{ ($growthRates['fellows'] ?? 0) >= 0 ? 'green' : 'red' }}-400 text-sm">
                    <svg class="w-4 h-4{{ ($growthRates['fellows'] ?? 0) < 0 ? ' rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    {{ ($growthRates['fellows'] ?? 0) >= 0 ? '+' : '' }}{{ $growthRates['fellows'] ?? 0 }}%
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $metrics['total_fellows'] ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Total Fellows</p>
        </div>

        <!-- Active Cohorts -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-teal-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <span class="text-dark-400 text-sm">Active</span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $metrics['active_tracks'] ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Active Tracks</p>
        </div>

        <!-- Active Programs -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <a href="{{ route('admin.programs.index') }}" class="text-indigo-400 text-sm hover:underline">View all</a>
            </div>
            <p class="text-3xl font-bold text-white">{{ $metrics['active_programs'] ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Active Programs <span class="text-dark-500">({{ $metrics['program_enrollments'] ?? 0 }} enrolled)</span></p>
        </div>

        <!-- Pending Activities -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                @if(($metrics['pending_activities'] ?? 0) > 0)
                <span class="flex items-center gap-1 text-orange-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending
                </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-white">{{ $metrics['pending_activities'] ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Pending Activities</p>
        </div>

        <!-- Completion Rate -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-{{ ($growthRates['interviews'] ?? 0) >= 0 ? 'green' : 'red' }}-400 text-sm">
                    <svg class="w-4 h-4{{ ($growthRates['interviews'] ?? 0) < 0 ? ' rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    {{ ($growthRates['interviews'] ?? 0) >= 0 ? '+' : '' }}{{ $growthRates['interviews'] ?? 0 }}%
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $metrics['interviews_today'] ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Interviews Today</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Enrollment Trends -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Enrollment Trends</h3>
                <select class="form-input py-1.5 text-sm w-auto">
                    <option>By Month</option>
                    <option>By Week</option>
                </select>
            </div>
            <div class="h-64 flex items-end justify-between gap-2">
                @php $maxEnrollment = max(1, collect($enrollmentTrends ?? [])->max('count')); @endphp
                @foreach($enrollmentTrends ?? [] as $month)
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <span class="text-dark-400 text-xs">{{ $month['count'] }}</span>
                        <div class="w-full bg-gradient-to-t from-primary-600 to-primary-400 rounded-t transition-all hover:opacity-80" 
                             style="height: {{ ($month['count'] / $maxEnrollment) * 100 }}%"></div>
                        <span class="text-dark-500 text-xs">{{ $month['short'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Career Capital Distribution -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Score Distribution</h3>
                <span class="text-dark-400 text-sm">All fellows</span>
            </div>
            <div class="space-y-4">
                @foreach($scoreDistribution ?? [] as $dist)
                    <div class="flex items-center gap-4">
                        <span class="text-dark-400 text-sm w-20">{{ $dist['range'] }}</span>
                        <div class="flex-1 h-6 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $dist['color'] }} rounded-full flex items-center justify-end pr-2" 
                                 style="width: {{ $dist['percent'] }}%">
                                @if($dist['percent'] >= 15)
                                <span class="text-white text-xs font-medium">{{ $dist['count'] }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-dark-300 text-sm w-10 text-right">{{ $dist['percent'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.tracks.index') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Manage Tracks</p>
                        <p class="text-dark-500 text-sm">Set up and manage learning tracks</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                
                <a href="{{ route('admin.cohorts.index') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-cyan-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Manage Cohorts</p>
                        <p class="text-dark-500 text-sm">{{ $metrics['active_cohorts'] ?? 0 }} active cohorts</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                
                <a href="{{ route('admin.programs.index') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Manage Programs</p>
                        <p class="text-dark-500 text-sm">{{ $metrics['active_programs'] ?? 0 }} active programs</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                
                <a href="{{ route('admin.fellows.index') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-teal-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Manage Fellows</p>
                        <p class="text-dark-500 text-sm">View and manage fellows</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                
                <a href="{{ route('admin.activities.queue') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Activity Queue</p>
                        <p class="text-dark-500 text-sm">Review pending activities</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                
                <a href="{{ route('admin.audit-logs') }}" class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-dark-200 font-medium">Audit Logs</p>
                        <p class="text-dark-500 text-sm">View system activity logs</p>
                    </div>
                    <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
                <a href="{{ route('admin.audit-logs') }}" class="text-primary-400 text-sm hover:underline">View all</a>
            </div>
            <div class="space-y-4">
                @forelse($recentLogs ?? [] as $log)
                    <div class="flex items-start gap-4 p-3 bg-dark-800 rounded-lg">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $log->action === 'created' ? 'text-green-400 bg-green-600/20' : '' }}
                            {{ $log->action === 'updated' ? 'text-blue-400 bg-blue-600/20' : '' }}
                            {{ $log->action === 'deleted' ? 'text-red-400 bg-red-600/20' : '' }}
                            {{ !in_array($log->action ?? '', ['created', 'updated', 'deleted']) ? 'text-purple-400 bg-purple-600/20' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($log->action === 'created')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                @elseif($log->action === 'updated')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                @elseif($log->action === 'deleted')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                @endif
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-dark-200">
                                <span class="font-medium">{{ ucfirst($log->action ?? 'Action') }} {{ class_basename($log->auditable_type ?? 'Item') }}</span>
                                @if($log->justification ?? false)
                                    <span class="text-dark-400">- {{ Str::limit($log->justification, 50) }}</span>
                                @endif
                            </p>
                            <p class="text-dark-500 text-sm">{{ $log->admin->name ?? 'System' }} • {{ $log->created_at?->diffForHumans() ?? '' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-dark-400">
                        <p>No recent activity to display</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Track Performance Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-dark-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Track Performance</h3>
                <a href="{{ route('admin.tracks.index') }}" class="text-primary-400 text-sm hover:underline">Manage tracks</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-6 text-dark-400 font-medium">Track</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Fellows</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Avg Score</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Completion</th>
                        <th class="text-center py-3 px-6 text-dark-400 font-medium">Status</th>
                        <th class="text-right py-3 px-6 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($tracks ?? [] as $track)
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-6">
                                <div>
                                    <p class="text-dark-200 font-medium">{{ $track->name }}</p>
                                    <p class="text-dark-500 text-sm">{{ $track->category?->label() ?? 'General' }}</p>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center text-dark-300">{{ $track->fellows_count ?? 0 }}</td>
                            <td class="py-4 px-6 text-center">
                                @php
                                    $avgScore = $track->fellows()->avg('fellow_tracks.score');
                                    $totalActivities = \App\Models\Activity::whereHas('fellow', fn($q) => $q->whereHas('tracks', fn($t) => $t->where('tracks.id', $track->id)))->count();
                                    $completedActivities = \App\Models\Activity::where('status', 'approved')->whereHas('fellow', fn($q) => $q->whereHas('tracks', fn($t) => $t->where('tracks.id', $track->id)))->count();
                                    $completionRate = $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0;
                                @endphp
                                <span class="font-medium text-primary-400">
                                    {{ $avgScore !== null ? number_format($avgScore, 1) : '--' }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 h-2 bg-dark-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary-500 rounded-full" style="width: {{ $completionRate }}%"></div>
                                    </div>
                                    <span class="text-dark-400 text-sm">{{ $completionRate }}%</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="badge {{ $track->is_active ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-dark-600/20 text-dark-400 border-dark-500/30' }}">
                                    {{ $track->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tracks.edit', $track->id) }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-dark-400">
                                No tracks available. <a href="{{ route('admin.tracks.index') }}" class="text-primary-400 hover:underline">Create a track</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alerts & Notifications -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">System Alerts</h3>
        <div class="space-y-3">
            @forelse($systemAlerts ?? [] as $alert)
                <div class="flex items-start gap-4 p-4 bg-{{ $alert['color'] }}-600/10 border border-{{ $alert['color'] }}-500/30 rounded-lg">
                    <svg class="w-5 h-5 text-{{ $alert['color'] }}-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($alert['type'] === 'warning')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                    <div class="flex-1">
                        <p class="text-{{ $alert['color'] }}-400 font-medium">{{ $alert['title'] }}</p>
                        <p class="text-dark-400 text-sm mt-1">{{ $alert['subtitle'] }}</p>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-dark-400">
                    <p>No alerts at the moment. Everything looks good!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
