@extends('layouts.app')

@section('title', 'View Mentorship Pod')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.mentorship-pods.index') }}" class="flex items-center gap-2 text-dark-400 hover:text-white transition-colors group">
            <div class="p-2 bg-dark-800 rounded-lg group-hover:bg-dark-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </div>
            <span class="text-sm font-medium">Back to Pods</span>
        </a>
    </div>

    <!-- Pod Header Card -->
    <div class="card overflow-hidden bg-dark-800 border-none shadow-2xl relative">
        @if($pod->color)
            <div class="absolute inset-x-0 top-0 h-2" style="background-color: {{ $pod->color }}"></div>
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background: radial-gradient(circle at top right, {{ $pod->color }}, transparent 70%)"></div>
        @endif

        <div class="p-6 sm:p-8 relative z-10 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-4xl shadow-lg border border-dark-600/50" 
                     style="background-color: {{ $pod->color ?? '#374151' }}20; color: {{ $pod->color ?? '#9CA3AF' }}">
                    {{ $pod->emoji ?? '🫂' }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight flex items-center gap-3">
                        {{ $pod->display_name }}
                        @if(!$pod->is_active)
                            <span class="badge badge-error text-sm font-normal">Closed</span>
                        @endif
                    </h1>
                    <div class="flex items-center gap-4 mt-2 text-dark-300">
                        <p class="flex items-center gap-1.5 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: {{ $pod->track->color ?? '#8B5CF6' }}"></span>
                            {{ $pod->track->name }}
                        </p>
                        <p class="flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            {{ $pod->active_member_count }} / {{ $pod->max_members }} Members
                        </p>
                    </div>
                    @if($pod->description)
                        <p class="mt-3 text-dark-400 text-sm max-w-2xl leading-relaxed">{{ $pod->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Danger Actions -->
            @if($pod->is_active)
            <div class="shrink-0 bg-dark-900/50 p-1.5 rounded-lg border border-dark-700/50">
                <form action="{{ route('admin.mentorship-pods.close', $pod) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this pod? This will deactivate all memberships and cannot be undone easily.');">
                    @csrf
                    <button type="submit" class="btn btn-outline border-transparent hover:border-red-500/30 text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors py-2 flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Close Pod
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Members Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Pod Members
            </h2>
        </div>

        @if($pod->is_active && !$pod->isFull())
            <div class="card bg-dark-800 border border-dark-700 p-6 mb-6">
                <h3 class="text-lg font-bold text-white mb-4">Add Member</h3>
                <form action="{{ route('admin.mentorship-pods.add-member', $pod) }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <select name="fellow_id" class="form-input flex-1" required>
                        <option value="">Select an eligible fellow...</option>
                        @foreach($eligibleFellows as $fellow)
                            <option value="{{ $fellow->id }}">{{ $fellow->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary whitespace-nowrap">Add to Pod</button>
                </form>
            </div>
        @endif
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($members as $member)
                <div class="card border {{ $member->is_lead ? 'border-primary-500/50 bg-primary-900/5' : 'border-dark-700 bg-dark-800' }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="avatar avatar-lg relative">
                                    @if($member->avatar)
                                        <img src="{{ $member->avatar }}" alt="{{ $member->name }}">
                                    @else
                                        {{ substr($member->name, 0, 2) }}
                                    @endif
                                    
                                    @if($member->is_lead)
                                        <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-accent-500 flex items-center justify-center text-white shadow-lg border-2 border-dark-800" title="Pod Lead">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6-4.8-6 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-lg flex items-center gap-2">
                                        <a href="{{ route('admin.fellows.show', $member->id) }}" class="hover:text-primary-400 transition-colors">{{ $member->name }}</a>
                                        @if($member->is_lead)
                                            <span class="badge badge-primary text-[10px] py-0 px-1.5">LEAD</span>
                                        @endif
                                    </h3>
                                    
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" style="background-color: {{ $member->tier['color'] }}15; color: {{ $member->tier['color'] }}; border-color: {{ $member->tier['color'] }}30;">
                                            {{ $member->tier['icon'] }} {{ $member->tier['label'] }}
                                        </span>
                                        <span class="text-sm font-bold text-white">{{ number_format($member->score, 3) }}% <span class="text-dark-400 font-normal">Score</span></span>
                                    </div>
                                </div>
                            </div>

                            @if($pod->is_active && !$member->is_lead)
                                <div class="flex items-center gap-2">
                                    <form action="{{ route('admin.mentorship-pods.change-lead', $pod) }}" method="POST" onsubmit="return confirm('Make this fellow the Pod Lead?');">
                                        @csrf
                                        <input type="hidden" name="new_lead_id" value="{{ $member->id }}">
                                        <button type="submit" class="p-2 text-dark-500 hover:text-accent-400 hover:bg-accent-500/10 rounded-lg transition-colors" title="Make Pod Lead">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l2.4 7.2h7.6l-6 4.8 2.4 7.2-6-4.8-6 4.8 2.4-7.2-6-4.8h7.6z"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.mentorship-pods.remove-member', [$pod->id, $member->id]) }}" method="POST" onsubmit="return confirm('Remove this fellow from the pod?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-dark-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Remove Member">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Score Breakdown Bar -->
                        @if($member->score_breakdown)
                            <div class="mt-6">
                                <div class="flex justify-between text-xs text-dark-400 mb-2">
                                    <span>Category Breakdown</span>
                                    <span>Joined {{ $member->joined_at->diffForHumans() }}</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-dark-700 flex overflow-hidden">
                                    @foreach($member->score_breakdown as $key => $cat)
                                        @if($cat['score'] > 0)
                                            <div style="width: {{ $cat['score'] }}%; background-color: {{ $cat['color'] }}" 
                                                 title="{{ $cat['label'] }}: {{ number_format($cat['score'], 3) }}%"
                                                 class="h-full"></div>
                                        @endif
                                    @endforeach
                                </div>
                                
                                <!-- Legend -->
                                <div class="flex flex-wrap gap-x-3 gap-y-1.5 mt-3">
                                    @foreach($member->score_breakdown as $key => $cat)
                                        @if($cat['score'] > 0)
                                            <div class="flex items-center gap-1.5 text-[10px] text-dark-400">
                                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat['color'] }}"></span>
                                                {{ $cat['label'] }}: {{ number_format($cat['score'], 3) }}%
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
