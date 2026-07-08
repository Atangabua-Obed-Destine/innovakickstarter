@extends('layouts.app')

@section('title', 'Cohort Details - ' . $cohort->name)

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('admin.cohorts.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors mt-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                @php
                    $trackColors = [
                        'software-engineering' => 'bg-primary-500',
                        'data-science' => 'bg-teal-500',
                        'product-management' => 'bg-blue-500',
                        'digital-marketing' => 'bg-amber-500',
                        'ui-ux-design' => 'bg-pink-500',
                    ];
                    $trackColor = $trackColors[$cohort->track?->slug ?? ''] ?? 'bg-gray-500';
                    
                    $statusClasses = match($cohort->status) {
                        'active' => 'bg-green-600/20 text-green-400 border-green-500/30',
                        'upcoming' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
                        'completed' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
                        'draft' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
                        'archived' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
                        'cancelled' => 'bg-red-600/20 text-red-400 border-red-500/30',
                        default => 'bg-dark-600/20 text-dark-400 border-dark-500/30'
                    };
                @endphp
                <div class="flex items-center gap-3 mb-2">
                    <span class="w-3 h-3 rounded-full {{ $trackColor }}"></span>
                    <span class="text-dark-400">{{ $cohort->track?->name ?? 'No Track' }}</span>
                    <span class="badge {{ $statusClasses }}">{{ $cohort->status_label }}</span>
                </div>
                <h1 class="text-2xl font-bold text-white">{{ $cohort->name }}</h1>
                @if($cohort->description)
                    <p class="text-dark-400 mt-1 max-w-2xl">{{ $cohort->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cohorts.edit', $cohort) }}" class="btn btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            @if(!in_array($cohort->status, ['archived', 'cancelled']))
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn btn-secondary">
                    Status Actions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-48 bg-dark-800 border border-dark-700 rounded-lg shadow-xl z-10">
                    @if($cohort->status === 'draft')
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="upcoming">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-200 hover:bg-dark-700 rounded-t-lg">
                                → Launch as Upcoming
                            </button>
                        </form>
                    @endif
                    @if($cohort->status === 'upcoming')
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="active">
                            <button type="submit" class="w-full text-left px-4 py-2 text-green-400 hover:bg-dark-700 rounded-t-lg">
                                → Activate Cohort
                            </button>
                        </form>
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-200 hover:bg-dark-700">
                                ← Back to Draft
                            </button>
                        </form>
                    @endif
                    @if($cohort->status === 'active')
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full text-left px-4 py-2 text-purple-400 hover:bg-dark-700 rounded-t-lg">
                                ✓ Mark Completed
                            </button>
                        </form>
                    @endif
                    @if($cohort->status === 'completed')
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="archived">
                            <button type="submit" class="w-full text-left px-4 py-2 text-dark-400 hover:bg-dark-700 rounded-t-lg">
                                Archive Cohort
                            </button>
                        </form>
                    @endif
                    @if(in_array($cohort->status, ['draft', 'upcoming', 'active']))
                        <form action="{{ route('admin.cohorts.transition', $cohort) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this cohort?')">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-400 hover:bg-dark-700 rounded-b-lg border-t border-dark-700">
                                ✕ Cancel Cohort
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
                <span class="text-{{ $cohort->fill_percentage >= 90 ? 'amber' : 'primary' }}-400 text-sm">{{ $cohort->fill_percentage }}%</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $cohort->fellows_count }}<span class="text-dark-500 text-lg">/{{ $cohort->max_fellows }}</span></p>
            <div class="h-1.5 bg-dark-700 rounded-full overflow-hidden mt-2">
                <div class="h-full {{ $cohort->fill_percentage >= 90 ? 'bg-amber-500' : 'bg-primary-500' }} rounded-full" 
                     style="width: {{ $cohort->fill_percentage }}%"></div>
            </div>
            @if($cohort->canEnroll())
                <p class="text-xs text-dark-500 mt-1">{{ $cohort->spots_remaining }} spots available</p>
            @endif
        </div>

        <!-- Timeline -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Timeline</span>
            <p class="text-lg font-semibold text-white mt-1">
                {{ $cohort->start_date->format('M j') }} - {{ $cohort->end_date->format('M j, Y') }}
            </p>
            <p class="text-dark-500 text-sm mt-1">{{ $cohort->duration_weeks }} weeks total</p>
            @if($cohort->isActive())
                <p class="text-green-400 text-sm">Week {{ $cohort->current_week }} • {{ $cohort->days_remaining }} days left</p>
            @elseif($cohort->isUpcoming())
                <p class="text-blue-400 text-sm">Starts in {{ $cohort->days_until_start }} days</p>
            @endif
        </div>

        <!-- Average Score -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Average Score</span>
            <p class="text-2xl font-bold {{ $cohort->avg_score >= 80 ? 'text-green-400' : ($cohort->avg_score >= 60 ? 'text-amber-400' : 'text-dark-400') }} mt-1">
                {{ number_format($cohort->avg_score, 1) }}%
            </p>
            <p class="text-dark-500 text-sm mt-1">Career Capital</p>
        </div>

        <!-- Completion Rate -->
        <div class="card p-4">
            <span class="text-dark-400 text-sm">Completion Rate</span>
            <p class="text-2xl font-bold text-primary-400 mt-1">{{ $cohort->completion_rate }}%</p>
            <p class="text-dark-500 text-sm mt-1">{{ $completedFellows->count() }} of {{ $activeFellows->count() + $completedFellows->count() }} completed</p>
        </div>
    </div>

    <!-- Progress Bar (Active Cohorts) -->
    @if($cohort->isActive())
    <div class="card p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-dark-400">Cohort Progress</span>
            <span class="text-dark-200">{{ $cohort->progress_percentage }}%</span>
        </div>
        <div class="h-3 bg-dark-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-green-500 to-primary-500 rounded-full transition-all" 
                 style="width: {{ $cohort->progress_percentage }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-dark-500 mt-2">
            <span>{{ $cohort->start_date->format('M j, Y') }}</span>
            <span>Week {{ $cohort->current_week }} of {{ $cohort->duration_weeks }}</span>
            <span>{{ $cohort->end_date->format('M j, Y') }}</span>
        </div>
    </div>
    @endif

    <!-- Fellows Management -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-dark-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-white">Fellows ({{ $activeFellows->count() + $completedFellows->count() }})</h2>
            
            @if($cohort->canEnroll() && $availableFellows->count() > 0)
            <div x-data="{ showModal: false }">
                <button @click="showModal = true" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Fellows
                </button>

                <!-- Add Fellows Modal -->
                <div x-show="showModal" 
                     x-transition
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                     style="display: none;">
                    <div @click.away="showModal = false" class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-lg mx-4 max-h-[80vh] overflow-hidden">
                        <div class="p-4 border-b border-dark-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">Add Fellows to {{ $cohort->name }}</h3>
                            <button @click="showModal = false" class="text-dark-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <form action="{{ route('admin.cohorts.enroll', $cohort) }}" method="POST">
                            @csrf
                            <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                                <div>
                                    <label class="block text-sm font-medium text-dark-300 mb-2">Select Fellow *</label>
                                    <select name="fellow_id" class="form-input w-full" required>
                                        <option value="">Choose a fellow...</option>
                                        @foreach($availableFellows as $fellow)
                                            <option value="{{ $fellow->id }}">{{ $fellow->name }} ({{ $fellow->email }})</option>
                                        @endforeach
                                    </select>
                                    <p class="text-dark-500 text-xs mt-1">{{ $availableFellows->count() }} fellows available</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-dark-300 mb-2">Notes (Optional)</label>
                                    <textarea name="notes" rows="2" class="form-input w-full" 
                                              placeholder="Any notes about this enrollment..."></textarea>
                                </div>
                            </div>
                            <div class="p-4 border-t border-dark-700 flex justify-end gap-3">
                                <button type="button" @click="showModal = false" class="btn btn-ghost">Cancel</button>
                                <button type="submit" class="btn btn-primary">Enroll Fellow</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @elseif(!$cohort->canEnroll())
            <span class="text-dark-500 text-sm">Enrollment closed</span>
            @endif
        </div>

        <!-- Fellows Tabs -->
        <div x-data="{ tab: 'active' }" class="border-b border-dark-700">
            <nav class="flex -mb-px">
                <button @click="tab = 'active'" 
                        :class="tab === 'active' ? 'border-primary-500 text-primary-400' : 'border-transparent text-dark-400 hover:text-white'"
                        class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    Active ({{ $activeFellows->count() }})
                </button>
                <button @click="tab = 'completed'" 
                        :class="tab === 'completed' ? 'border-primary-500 text-primary-400' : 'border-transparent text-dark-400 hover:text-white'"
                        class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    Completed ({{ $completedFellows->count() }})
                </button>
                <button @click="tab = 'dropped'" 
                        :class="tab === 'dropped' ? 'border-primary-500 text-primary-400' : 'border-transparent text-dark-400 hover:text-white'"
                        class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                    Dropped ({{ $droppedFellows->count() }})
                </button>
            </nav>
        
            <!-- Active Fellows Table -->
            <div x-show="tab === 'active'">
                @if($activeFellows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-750">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Fellow</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Activities</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Rank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Enrolled</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-dark-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach($activeFellows as $fellow)
                            <tr class="hover:bg-dark-750">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-dark-600 flex items-center justify-center text-white font-medium">
                                            {{ strtoupper(substr($fellow->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ $fellow->name }}</p>
                                            <p class="text-dark-500 text-sm">{{ $fellow->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $fellow->pivot->status === 'active' ? 'bg-green-600/20 text-green-400' : 'bg-blue-600/20 text-blue-400' }}">
                                        {{ ucfirst($fellow->pivot->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium {{ $fellow->pivot->cohort_score >= 80 ? 'text-green-400' : ($fellow->pivot->cohort_score >= 60 ? 'text-amber-400' : 'text-dark-300') }}">
                                        {{ number_format($fellow->pivot->cohort_score, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-dark-300">
                                    {{ $fellow->pivot->activities_completed }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($fellow->pivot->rank)
                                    <span class="text-dark-300">#{{ $fellow->pivot->rank }}</span>
                                    @else
                                    <span class="text-dark-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-dark-400 text-sm">
                                    {{ $fellow->pivot->enrolled_at ? \Carbon\Carbon::parse($fellow->pivot->enrolled_at)->format('M j, Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($cohort->isActive() || $cohort->isCompleted())
                                        <form action="{{ route('admin.cohorts.complete-fellow', [$cohort, $fellow]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300 text-sm" title="Mark Completed">
                                                ✓ Complete
                                            </button>
                                        </form>
                                        @endif
                                        <div x-data="{ showRemove: false }">
                                            <button @click="showRemove = true" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                                            <div x-show="showRemove" 
                                                 x-transition
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                                 style="display: none;">
                                                <div @click.away="showRemove = false" class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-md mx-4">
                                                    <div class="p-4 border-b border-dark-700">
                                                        <h3 class="text-lg font-semibold text-white">Remove {{ $fellow->name }}</h3>
                                                    </div>
                                                    <form action="{{ route('admin.cohorts.remove-fellow', [$cohort, $fellow]) }}" method="POST">
                                                        @csrf
                                                        <div class="p-4">
                                                            <label class="block text-sm font-medium text-dark-300 mb-2">Reason for removal *</label>
                                                            <textarea name="reason" rows="2" class="form-input w-full" required
                                                                      placeholder="e.g., Withdrew from program, moved to different cohort..."></textarea>
                                                        </div>
                                                        <div class="p-4 border-t border-dark-700 flex justify-end gap-3">
                                                            <button type="button" @click="showRemove = false" class="btn btn-ghost">Cancel</button>
                                                            <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">Remove Fellow</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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
                    <p class="text-dark-500">No active fellows in this cohort.</p>
                    @if($cohort->canEnroll())
                        <p class="text-dark-400 text-sm mt-2">Use the "Add Fellows" button to enroll fellows.</p>
                    @endif
                </div>
                @endif
            </div>

            <!-- Completed Fellows Table -->
            <div x-show="tab === 'completed'" style="display: none;">
                @if($completedFellows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-750">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Fellow</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Final Score</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Activities</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Final Rank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach($completedFellows as $fellow)
                            <tr class="hover:bg-dark-750">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-600/30 flex items-center justify-center text-purple-400 font-medium">
                                            {{ strtoupper(substr($fellow->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ $fellow->name }}</p>
                                            <p class="text-dark-500 text-sm">{{ $fellow->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-purple-400">{{ number_format($fellow->pivot->cohort_score, 1) }}%</span>
                                </td>
                                <td class="px-4 py-3 text-dark-300">{{ $fellow->pivot->activities_completed }}</td>
                                <td class="px-4 py-3 text-dark-300">#{{ $fellow->pivot->rank ?? '-' }}</td>
                                <td class="px-4 py-3 text-dark-400 text-sm">
                                    {{ $fellow->pivot->completed_at ? \Carbon\Carbon::parse($fellow->pivot->completed_at)->format('M j, Y') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-dark-500">
                    No fellows have completed this cohort yet.
                </div>
                @endif
            </div>

            <!-- Dropped Fellows Table -->
            <div x-show="tab === 'dropped'" style="display: none;">
                @if($droppedFellows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-dark-750">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Fellow</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-dark-400 uppercase">Dropped</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach($droppedFellows as $fellow)
                            <tr class="hover:bg-dark-750">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-600/30 flex items-center justify-center text-red-400 font-medium">
                                            {{ strtoupper(substr($fellow->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-dark-300">{{ $fellow->name }}</p>
                                            <p class="text-dark-500 text-sm">{{ $fellow->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge {{ $fellow->pivot->status === 'dropped' ? 'bg-yellow-600/20 text-yellow-400' : 'bg-red-600/20 text-red-400' }}">
                                        {{ ucfirst($fellow->pivot->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-dark-400">{{ $fellow->pivot->drop_reason ?? 'No reason provided' }}</td>
                                <td class="px-4 py-3 text-dark-500 text-sm">
                                    {{ $fellow->pivot->dropped_at ? \Carbon\Carbon::parse($fellow->pivot->dropped_at)->format('M j, Y') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-dark-500">
                    No fellows have dropped from this cohort.
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cohort Details -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Info Card -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Cohort Information</h3>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-dark-400">Track</dt>
                    <dd class="text-white">{{ $cohort->track?->name ?? 'None' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Duration</dt>
                    <dd class="text-white">{{ $cohort->duration_weeks }} weeks</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Capacity</dt>
                    <dd class="text-white">{{ $cohort->min_fellows }} - {{ $cohort->max_fellows }} fellows</dd>
                </div>
                @if($cohort->enrollment_opens_at)
                <div class="flex justify-between">
                    <dt class="text-dark-400">Enrollment Window</dt>
                    <dd class="text-white">{{ $cohort->enrollment_opens_at->format('M j') }} - {{ $cohort->enrollment_closes_at?->format('M j, Y') ?? 'Start' }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-dark-400">Created By</dt>
                    <dd class="text-white">{{ $cohort->creator?->name ?? 'System' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Created</dt>
                    <dd class="text-white">{{ $cohort->created_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Activity Summary -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Activity Summary</h3>
            <dl class="space-y-4">
                <div class="flex justify-between">
                    <dt class="text-dark-400">Total Activities</dt>
                    <dd class="text-white">{{ $cohort->activities_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Avg Activities/Fellow</dt>
                    <dd class="text-white">
                        {{ $cohort->fellows_count > 0 ? number_format($cohort->activities_count / $cohort->fellows_count, 1) : '0' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Active Fellows</dt>
                    <dd class="text-white">{{ $activeFellows->count() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Completed Fellows</dt>
                    <dd class="text-white">{{ $completedFellows->count() }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-dark-400">Dropped Fellows</dt>
                    <dd class="text-white">{{ $droppedFellows->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
