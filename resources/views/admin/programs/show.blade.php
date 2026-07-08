@extends('layouts.app')

@section('title', 'Program Details - ' . $program->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.programs.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors mt-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
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
                    $statuses = \App\Models\Program::getStatuses();
                @endphp
                @if($program->sponsor_name)
                <div class="flex items-center gap-2 mb-2">
                    @if($program->sponsor_logo)
                        <img src="{{ $program->sponsor_logo }}" alt="{{ $program->sponsor_name }}" class="h-6 w-auto">
                    @endif
                    <span class="text-dark-400">{{ $program->sponsor_name }}</span>
                </div>
                @endif
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-white">{{ $program->name }}</h1>
                    <span class="badge {{ $statusClasses }}">{{ $statuses[$program->status] ?? ucfirst($program->status) }}</span>
                </div>
                @if($program->description)
                    <p class="text-dark-400 mt-1 max-w-2xl">{{ $program->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @if($program->status !== 'archived')
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn btn-secondary">
                    Status Actions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-56 bg-dark-800 border border-dark-700 rounded-lg shadow-xl z-10">
                    @if($program->status === 'draft')
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="upcoming">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-200 hover:bg-dark-700 rounded-t-lg">
                                → Announce as Upcoming
                            </button>
                        </form>
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="enrolling">
                            <button type="submit" class="w-full text-left px-4 py-2 text-blue-400 hover:bg-dark-700">
                                → Open for Enrollment
                            </button>
                        </form>
                    @endif
                    @if($program->status === 'upcoming')
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="enrolling">
                            <button type="submit" class="w-full text-left px-4 py-2 text-blue-400 hover:bg-dark-700 rounded-t-lg">
                                → Open for Enrollment
                            </button>
                        </form>
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="w-full text-left px-4 py-2 text-green-400 hover:bg-dark-700">
                                → Activate Program
                            </button>
                        </form>
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-400 hover:bg-dark-700 rounded-b-lg">
                                ← Back to Draft
                            </button>
                        </form>
                    @endif
                    @if($program->status === 'enrolling')
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="w-full text-left px-4 py-2 text-green-400 hover:bg-dark-700 rounded-t-lg">
                                → Activate Program
                            </button>
                        </form>
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="upcoming">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-400 hover:bg-dark-700 rounded-b-lg">
                                ← Close Enrollment
                            </button>
                        </form>
                    @endif
                    @if($program->status === 'active')
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST"
                              onsubmit="return confirm('This will graduate all active fellows. Continue?')">
                            @csrf
                            <input type="hidden" name="status" value="graduated">
                            <button type="submit" class="w-full text-left px-4 py-2 text-purple-400 hover:bg-dark-700 rounded-t-lg">
                                🎓 Graduate Program
                            </button>
                        </form>
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-400 hover:bg-dark-700 rounded-b-lg">
                                Archive Program
                            </button>
                        </form>
                    @endif
                    @if($program->status === 'graduated')
                        <form action="{{ route('admin.programs.transition', $program) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-400 hover:bg-dark-700 rounded-lg">
                                Archive Program
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
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

    <!-- Overview Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Fellows -->
        <div class="card p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-dark-400 text-sm">Fellows</span>
                @if($program->max_capacity)
                    @php $fillPct = ($fellowStats['total'] / $program->max_capacity) * 100; @endphp
                    <span class="text-{{ $fillPct >= 90 ? 'amber' : 'primary' }}-400 text-sm">{{ round($fillPct) }}%</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-white">
                {{ $fellowStats['total'] }}
                @if($program->max_capacity)
                    <span class="text-dark-500 text-lg">/{{ $program->max_capacity }}</span>
                @endif
            </p>
            @if($program->max_capacity)
                <div class="h-1.5 bg-dark-700 rounded-full overflow-hidden mt-2">
                    <div class="h-full {{ $fillPct >= 90 ? 'bg-amber-500' : 'bg-primary-500' }} rounded-full" 
                         style="width: {{ min($fillPct, 100) }}%"></div>
                </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Timeline</span>
            @if($program->start_date && $program->end_date)
                <p class="text-lg font-semibold text-white mt-1">
                    {{ $program->start_date->format('M j') }} - {{ $program->end_date->format('M j, Y') }}
                </p>
                <p class="text-dark-500 text-sm mt-1">
                    {{ $program->start_date->diffInWeeks($program->end_date) }} weeks total
                </p>
            @else
                <p class="text-lg font-semibold text-dark-500 mt-1">Not Scheduled</p>
            @endif
        </div>

        <!-- Graduates -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Graduates</span>
            <p class="text-2xl font-bold text-purple-400 mt-1">{{ $fellowStats['completed'] }}</p>
            <p class="text-dark-500 text-sm mt-1">
                @if($fellowStats['total'] > 0)
                    {{ round(($fellowStats['completed'] / $fellowStats['total']) * 100) }}% completion
                @else
                    0% completion
                @endif
            </p>
        </div>

        <!-- Employment Rate -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Employment Rate</span>
            @php 
                $employedCount = $alumniStats['employed'] + $alumniStats['freelancing'];
                $employmentRate = $fellowStats['completed'] > 0 
                    ? round(($employedCount / $fellowStats['completed']) * 100) 
                    : 0;
            @endphp
            <p class="text-2xl font-bold text-teal-400 mt-1">{{ $employmentRate }}%</p>
            <p class="text-dark-500 text-sm mt-1">{{ $employedCount }} of {{ $fellowStats['completed'] }} alumni</p>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Fellow Status Breakdown -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fellow Status Distribution</h2>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-400">{{ $fellowStats['enrolled'] }}</p>
                        <p class="text-dark-500 text-sm">Enrolled</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-green-400">{{ $fellowStats['active'] }}</p>
                        <p class="text-dark-500 text-sm">Active</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-400">{{ $fellowStats['completed'] }}</p>
                        <p class="text-dark-500 text-sm">Completed</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-amber-400">{{ $fellowStats['dropped'] }}</p>
                        <p class="text-dark-500 text-sm">Dropped</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-dark-400">{{ $fellowStats['removed'] }}</p>
                        <p class="text-dark-500 text-sm">Removed</p>
                    </div>
                </div>
            </div>

            <!-- Alumni Employment Outcomes -->
            @if($fellowStats['completed'] > 0)
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Alumni Employment Outcomes</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-teal-400">{{ $alumniStats['employed'] }}</p>
                        <p class="text-dark-500 text-sm">Employed</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-400">{{ $alumniStats['freelancing'] }}</p>
                        <p class="text-dark-500 text-sm">Freelancing</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-purple-400">{{ $alumniStats['further_education'] }}</p>
                        <p class="text-dark-500 text-sm">Further Ed</p>
                    </div>
                    <div class="text-center p-4 bg-dark-800/50 rounded-lg">
                        <p class="text-2xl font-bold text-amber-400">{{ $alumniStats['seeking'] }}</p>
                        <p class="text-dark-500 text-sm">Seeking</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Track Distribution -->
            @if($trackDistribution->count() > 0)
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Fellow Distribution by Track</h2>
                <div class="space-y-3">
                    @foreach($trackDistribution as $track)
                        @php
                            $pct = $fellowStats['total'] > 0 ? ($track->count / $fellowStats['total']) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-dark-300">{{ $track->name }}</span>
                                <span class="text-dark-400 text-sm">{{ $track->count }} ({{ round($pct) }}%)</span>
                            </div>
                            <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Milestones Progress -->
            @if(count($milestoneProgress) > 0)
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Milestone Progress</h2>
                <div class="space-y-4">
                    @foreach($milestoneProgress as $milestone)
                        <div class="p-4 bg-dark-800/50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h3 class="font-medium text-white">{{ $milestone['name'] }}</h3>
                                    @if($milestone['description'])
                                        <p class="text-dark-500 text-sm">{{ $milestone['description'] }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-primary-400 font-medium">{{ $milestone['completion_rate'] }}%</span>
                                    <p class="text-dark-500 text-xs">{{ $milestone['completed_count'] }}/{{ $fellowStats['total'] }}</p>
                                </div>
                            </div>
                            <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full transition-all" 
                                     style="width: {{ $milestone['completion_rate'] }}%"></div>
                            </div>
                            @if($milestone['target_date'])
                                <p class="text-dark-500 text-xs mt-2">Target: {{ \Carbon\Carbon::parse($milestone['target_date'])->format('M j, Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Fellows List -->
            <div class="card">
                <div class="p-4 border-b border-dark-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">Fellows ({{ $fellowStats['total'] }})</h2>
                    <a href="{{ route('admin.programs.export', $program) }}" class="btn btn-sm btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </a>
                </div>
                
                @if($program->fellows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Fellow</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Track</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Enrolled</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-800">
                            @foreach($program->fellows as $fellow)
                                @php
                                    $pivot = $fellow->pivot;
                                    $pivotStatusClass = match($pivot->status) {
                                        'enrolled' => 'bg-blue-600/20 text-blue-400',
                                        'active' => 'bg-green-600/20 text-green-400',
                                        'completed' => 'bg-purple-600/20 text-purple-400',
                                        'dropped' => 'bg-amber-600/20 text-amber-400',
                                        'removed' => 'bg-red-600/20 text-red-400',
                                        default => 'bg-dark-600/20 text-dark-400',
                                    };
                                @endphp
                                <tr class="hover:bg-dark-800/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center text-sm font-medium">
                                                {{ strtoupper(substr($fellow->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-white font-medium">{{ $fellow->name }}</p>
                                                <p class="text-dark-500 text-sm">{{ $fellow->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-dark-300">{{ $fellow->track?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $pivotStatusClass }}">
                                            {{ $fellowStatuses[$pivot->status] ?? ucfirst($pivot->status) }}
                                        </span>
                                        @if($pivot->certificate_number)
                                            <span class="ml-1 text-xs text-purple-400" title="Certificate: {{ $pivot->certificate_number }}">🎓</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-dark-400 text-sm">
                                        {{ $pivot->enrolled_at?->format('M j, Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" class="p-1 text-dark-400 hover:text-white rounded">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" 
                                                 class="absolute right-0 mt-1 w-48 bg-dark-800 border border-dark-700 rounded-lg shadow-xl z-20">
                                                @if(in_array($pivot->status, ['enrolled', 'active']))
                                                    <form action="{{ route('admin.programs.graduate-fellow', [$program, $fellow]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="issue_certificate" value="1">
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-purple-400 hover:bg-dark-700 rounded-t-lg text-sm">
                                                            🎓 Graduate & Certificate
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($pivot->status === 'completed' && !$pivot->certificate_number)
                                                    <form action="{{ route('admin.programs.issue-certificate', [$program, $fellow]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-purple-400 hover:bg-dark-700 text-sm">
                                                            📜 Issue Certificate
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($pivot->status === 'completed')
                                                    <button type="button" 
                                                            @click="$dispatch('open-outcome-modal', { fellowId: '{{ $fellow->id }}', fellowName: '{{ $fellow->name }}' })"
                                                            class="w-full text-left px-4 py-2 text-teal-400 hover:bg-dark-700 text-sm">
                                                        📊 Update Outcome
                                                    </button>
                                                @endif
                                                @if($pivot->status !== 'removed')
                                                    <form action="{{ route('admin.programs.remove-fellow', [$program, $fellow]) }}" method="POST"
                                                          onsubmit="return confirm('Remove {{ $fellow->name }} from this program?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-red-400 hover:bg-dark-700 rounded-b-lg text-sm border-t border-dark-700">
                                                            Remove from Program
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center">
                    <p class="text-dark-500">No fellows enrolled yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            
            <!-- Quick Actions -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if(in_array($program->status, ['enrolling', 'active']))
                    <button type="button" 
                            @click="$dispatch('open-enroll-modal')"
                            class="w-full btn btn-primary justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Enroll Fellow
                    </button>
                    @endif
                    
                    <button type="button" 
                            @click="$dispatch('open-announce-modal')"
                            class="w-full btn btn-secondary justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        Send Announcement
                    </button>
                    
                    <a href="{{ route('admin.programs.export', $program) }}" class="w-full btn btn-ghost justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Data
                    </a>
                </div>
            </div>

            <!-- Program Details -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Program Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Status</dt>
                        <dd><span class="badge {{ $statusClasses }}">{{ $statuses[$program->status] ?? ucfirst($program->status) }}</span></dd>
                    </div>
                    @if($program->start_date)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Start Date</dt>
                        <dd class="text-white">{{ $program->start_date->format('M j, Y') }}</dd>
                    </div>
                    @endif
                    @if($program->end_date)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">End Date</dt>
                        <dd class="text-white">{{ $program->end_date->format('M j, Y') }}</dd>
                    </div>
                    @endif
                    @if($program->graduation_date)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Graduation</dt>
                        <dd class="text-white">{{ $program->graduation_date->format('M j, Y') }}</dd>
                    </div>
                    @endif
                    @if($program->max_capacity)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Capacity</dt>
                        <dd class="text-white">{{ $program->max_capacity }}</dd>
                    </div>
                    @endif
                    @if($program->certificate_prefix)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Certificate Prefix</dt>
                        <dd class="text-white font-mono">{{ $program->certificate_prefix }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Created</dt>
                        <dd class="text-white">{{ $program->created_at->format('M j, Y') }}</dd>
                    </div>
                    @if($program->creator)
                    <div class="flex justify-between">
                        <dt class="text-dark-400">Created By</dt>
                        <dd class="text-white">{{ $program->creator->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Recent Activity -->
            @if($recentEnrollments->count() > 0)
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Recent Enrollments</h3>
                <div class="space-y-3">
                    @foreach($recentEnrollments as $enrollment)
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center text-xs font-medium">
                                {{ strtoupper(substr($enrollment->fellow?->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white truncate">{{ $enrollment->fellow?->name ?? 'Unknown' }}</p>
                                <p class="text-dark-500 text-xs">{{ $enrollment->enrolled_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Enroll Fellow Modal -->
<div x-data="{ open: false }" 
     @open-enroll-modal.window="open = true"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" 
             x-transition
             class="relative bg-dark-800 border border-dark-700 rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Enroll Fellow</h3>
            <form action="{{ route('admin.programs.enroll', $program) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="fellow_id" class="block text-sm font-medium text-dark-300 mb-2">Select Fellow</label>
                        <select name="fellow_id" id="fellow_id" class="form-input w-full" required>
                            <option value="">Choose a fellow...</option>
                            @foreach($availableFellows as $fellow)
                                <option value="{{ $fellow->id }}">{{ $fellow->name }} ({{ $fellow->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-dark-300 mb-2">Initial Status</label>
                        <select name="status" id="status" class="form-input w-full">
                            <option value="enrolled">Enrolled</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-dark-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" id="notes" rows="2" class="form-input w-full" placeholder="Any notes about this enrollment..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll Fellow</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Announcement Modal -->
<div x-data="{ open: false }" 
     @open-announce-modal.window="open = true"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" 
             x-transition
             class="relative bg-dark-800 border border-dark-700 rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Send Announcement</h3>
            <form action="{{ route('admin.programs.announce', $program) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-dark-300 mb-2">Title</label>
                        <input type="text" name="title" id="title" class="form-input w-full" placeholder="Announcement title..." required>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-dark-300 mb-2">Message</label>
                        <textarea name="message" id="message" rows="4" class="form-input w-full" placeholder="Your announcement message..." required></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="send_email" id="send_email" value="1" class="form-checkbox">
                        <label for="send_email" class="text-dark-300 text-sm">Also send via email</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Outcome Modal -->
<div x-data="{ open: false, fellowId: null, fellowName: '' }" 
     @open-outcome-modal.window="open = true; fellowId = $event.detail.fellowId; fellowName = $event.detail.fellowName"
     x-show="open" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" class="fixed inset-0 bg-black/50" @click="open = false"></div>
        <div x-show="open" 
             x-transition
             class="relative bg-dark-800 border border-dark-700 rounded-xl shadow-xl max-w-lg w-full p-6">
            <h3 class="text-lg font-semibold text-white mb-1">Update Employment Outcome</h3>
            <p class="text-dark-400 text-sm mb-4" x-text="fellowName"></p>
            <form :action="'{{ route('admin.programs.update-outcome', [$program, '']) }}/' + fellowId" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="employment_status" class="block text-sm font-medium text-dark-300 mb-2">Employment Status</label>
                        <select name="employment_status" id="employment_status" class="form-input w-full" required>
                            @foreach($employmentStatuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="employer_name" class="block text-sm font-medium text-dark-300 mb-2">Employer Name</label>
                        <input type="text" name="employer_name" id="employer_name" class="form-input w-full" placeholder="Company name...">
                    </div>
                    <div>
                        <label for="job_title" class="block text-sm font-medium text-dark-300 mb-2">Job Title</label>
                        <input type="text" name="job_title" id="job_title" class="form-input w-full" placeholder="Position/role...">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="job_start_date" class="block text-sm font-medium text-dark-300 mb-2">Start Date</label>
                            <input type="date" name="job_start_date" id="job_start_date" class="form-input w-full">
                        </div>
                        <div>
                            <label for="salary_range" class="block text-sm font-medium text-dark-300 mb-2">Salary Range</label>
                            <input type="text" name="salary_range" id="salary_range" class="form-input w-full" placeholder="e.g., $50k-70k">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="open = false" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Outcome</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
