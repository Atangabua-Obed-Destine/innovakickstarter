@extends('layouts.app')

@section('title', 'My Shortlist')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">My Shortlist</h1>
            <p class="text-dark-400">Manage your saved candidates</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export to CSV
            </button>
            <a href="{{ route('recruiter.marketplace.index') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Find More Talent
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid sm:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-primary-400">{{ $stats['total'] }}</p>
            <p class="text-dark-400 text-sm">Total Shortlisted</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-green-400">{{ $stats['contacted'] }}</p>
            <p class="text-dark-400 text-sm">Contacted</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-blue-400">{{ $stats['interviewing'] }}</p>
            <p class="text-dark-400 text-sm">Interviewing</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-teal-400">{{ $stats['hired'] }}</p>
            <p class="text-dark-400 text-sm">Hired</p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card p-4">
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
            <div class="flex gap-4 flex-1">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Search shortlist..." class="form-input pl-10">
                </div>
                <select class="form-input w-40">
                    <option value="">All Status</option>
                    <option>New</option>
                    <option>Contacted</option>
                    <option>Interviewing</option>
                    <option>Hired</option>
                    <option>Rejected</option>
                </select>
                <select class="form-input w-40">
                    <option value="">Sort By</option>
                    <option>Date Added</option>
                    <option>Score: High to Low</option>
                    <option>Name (A-Z)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-outline py-2" disabled>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Bulk Email
                </button>
            </div>
        </div>
    </div>

    <!-- Shortlist Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                        </th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Candidate</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Track / Skills</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Score</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Status</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Added</th>
                        <th class="text-center py-3 px-4 text-dark-400 font-medium">Notes</th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($shortlist as $item)
                        @php
                            $candidate = $item->fellow;
                            $primaryTrack = $candidate->fellowTracks->first();
                            $score = $primaryTrack?->score ?? 0;
                            $skills = collect();
                            foreach ($candidate->activities ?? [] as $activity) {
                                if ($activity->tech_stack) {
                                    $skills = $skills->merge($activity->tech_stack);
                                }
                            }
                            $topSkills = $skills->unique()->take(3)->toArray();
                        @endphp
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="py-4 px-4">
                                <input type="checkbox" class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    @if($candidate->avatar_url)
                                        <img src="{{ asset('storage/' . $candidate->avatar_url) }}" alt="{{ $candidate->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-medium">
                                            {{ $candidate->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('recruiter.talent.show', $candidate->id) }}" class="text-dark-200 font-medium hover:text-primary-400 transition-colors">
                                            {{ $candidate->name }}
                                        </a>
                                        <p class="text-dark-500 text-sm">{{ $candidate->headline ?? 'IKS Fellow' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <p class="text-dark-300 mb-1">{{ $primaryTrack?->track?->name ?? 'General' }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($topSkills, 0, 2) as $skill)
                                        <span class="px-2 py-0.5 bg-dark-700 text-dark-400 rounded text-xs">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($topSkills) > 2)
                                        <span class="px-2 py-0.5 bg-dark-700 text-dark-500 rounded text-xs">+{{ count($topSkills) - 2 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="w-10 h-10 relative">
                                        <svg class="w-10 h-10 -rotate-90">
                                            <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4" class="text-dark-700"/>
                                            <circle cx="20" cy="20" r="16" fill="none" stroke="currentColor" stroke-width="4" 
                                                    stroke-dasharray="{{ 2 * 3.14159 * 16 }}" 
                                                    stroke-dashoffset="{{ 2 * 3.14159 * 16 * (1 - $score / 100) }}"
                                                    class="{{ $score >= 80 ? 'text-green-500' : ($score >= 70 ? 'text-teal-500' : 'text-amber-500') }}"/>
                                        </svg>
                                        <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">
                                            {{ round($score) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $status = $item->status ?? 'new';
                                    $statusConfig = match($status) {
                                        'new' => ['class' => 'bg-dark-600/20 text-dark-300 border-dark-500/30', 'label' => 'New'],
                                        'contacted' => ['class' => 'bg-blue-600/20 text-blue-400 border-blue-500/30', 'label' => 'Contacted'],
                                        'interviewing' => ['class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30', 'label' => 'Interviewing'],
                                        'hired' => ['class' => 'bg-green-600/20 text-green-400 border-green-500/30', 'label' => 'Hired'],
                                        'rejected' => ['class' => 'bg-red-600/20 text-red-400 border-red-500/30', 'label' => 'Rejected'],
                                        default => ['class' => 'bg-dark-600/20 text-dark-300 border-dark-500/30', 'label' => 'New']
                                    };
                                @endphp
                                <select class="text-xs px-2 py-1 rounded border {{ $statusConfig['class'] }} bg-transparent cursor-pointer">
                                    <option {{ $status === 'new' ? 'selected' : '' }}>New</option>
                                    <option {{ $status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                    <option {{ $status === 'interviewing' ? 'selected' : '' }}>Interviewing</option>
                                    <option {{ $status === 'hired' ? 'selected' : '' }}>Hired</option>
                                    <option {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </td>
                            <td class="py-4 px-4 text-center text-dark-400 text-sm">
                                {{ $item->created_at->format('M j, Y') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php $notesCount = $item->notes ? 1 : 0; @endphp
                                @if($notesCount > 0)
                                    <button class="inline-flex items-center gap-1 text-dark-400 hover:text-white transition-colors" title="View Notes">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <span class="text-sm">{{ $notesCount }}</span>
                                    </button>
                                @else
                                    <button class="text-dark-500 hover:text-dark-300 transition-colors" title="Add Note">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('recruiter.talent.show', $candidate->id) }}" 
                                       class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors" title="View Profile">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <button class="p-2 text-dark-400 hover:text-primary-400 hover:bg-dark-700 rounded-lg transition-colors" title="Send Email">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                    <form action="{{ route('recruiter.talent.unshortlist', $candidate->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-700 rounded-lg transition-colors" title="Remove from Shortlist" onclick="return confirm('Remove from shortlist?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-dark-500">
                                <svg class="w-12 h-12 mx-auto text-dark-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <p>Your shortlist is empty</p>
                                <a href="{{ route('recruiter.marketplace.index') }}" class="text-primary-400 hover:underline mt-2 inline-block">Browse the talent marketplace</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-dark-700 flex items-center justify-between">
            <p class="text-dark-500 text-sm">Showing {{ $shortlist->count() }} candidate{{ $shortlist->count() !== 1 ? 's' : '' }}</p>
        </div>
    </div>
</div>
@endsection
