@extends('layouts.app')

@section('title', 'Mentorship Pods')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Mentorship Pods</h1>
            <p class="text-dark-400 mt-1">Manage fellow squads and peer accountability groups.</p>
        </div>
        <a href="{{ route('admin.mentorship-pods.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Pod
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4 border border-dark-700 bg-dark-800">
        <form action="{{ route('admin.mentorship-pods.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4" x-data="{ submit() { this.$el.submit() } }">
            <div class="flex-1">
                <label for="track_id" class="sr-only">Track</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <select name="track_id" id="track_id" class="form-input pl-10" @change="submit">
                        <option value="all">All Tracks</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ request('track_id') == $track->id ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="w-full sm:w-48">
                <label for="status" class="sr-only">Status</label>
                <div class="relative">
                    <select name="status" id="status" class="form-input" @change="submit">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active Pods</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed Pods</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Pods Grid -->
    @if($pods->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pods as $pod)
                <div class="card border {{ $pod->is_active ? 'border-dark-700 hover:border-primary-500/50' : 'border-dark-800 opacity-75' }} bg-dark-800 transition-colors flex flex-col h-full relative overflow-hidden group">
                    @if($pod->color)
                        <div class="absolute top-0 left-0 w-full h-1" style="background-color: {{ $pod->color }}"></div>
                    @endif
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" 
                                     style="background-color: {{ $pod->color ?? '#374151' }}20; color: {{ $pod->color ?? '#9CA3AF' }}">
                                    {{ $pod->emoji ?? '🫂' }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-white text-lg leading-tight">{{ $pod->display_name }}</h3>
                                    <p class="text-xs text-dark-400 mt-1 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full inline-block" style="background-color: {{ $pod->track->color ?? '#8B5CF6' }}"></span>
                                        {{ $pod->track->name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-dark-700/50">
                            <div class="flex items-center gap-2">
                                <div class="avatar avatar-sm">
                                    @if($pod->lead->avatar)
                                        <img src="{{ $pod->lead->avatar }}" alt="{{ $pod->lead->name }}">
                                    @else
                                        {{ $pod->lead->initials }}
                                    @endif
                                </div>
                                <div class="text-xs">
                                    <p class="text-dark-300 font-medium">Lead</p>
                                    <p class="text-white">{{ $pod->lead->first_name }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1 text-sm font-medium {{ $pod->active_member_count >= $pod->max_members ? 'text-emerald-400' : 'text-primary-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                {{ $pod->active_member_count }} / {{ $pod->max_members }}
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.mentorship-pods.show', $pod) }}" class="absolute inset-0 z-10"><span class="sr-only">View Pod</span></a>
                </div>
            @endforeach
        </div>
        
        <div class="mt-6">
            {{ $pods->links() }}
        </div>
    @else
        <div class="card p-12 text-center border border-dark-700 bg-dark-800">
            <div class="w-16 h-16 rounded-full bg-dark-700 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white">No pods found</h3>
            <p class="text-dark-400 mt-2 max-w-md mx-auto">There are no mentorship pods matching your current filters. Create one to group fellows together.</p>
            @if(request('track_id') !== 'all' || request('status') === 'closed')
                <a href="{{ route('admin.mentorship-pods.index') }}" class="btn btn-outline border-dark-600 hover:border-dark-500 mt-6 inline-block">Clear Filters</a>
            @else
                <a href="{{ route('admin.mentorship-pods.create') }}" class="btn btn-primary mt-6 inline-block">Create First Pod</a>
            @endif
        </div>
    @endif
</div>
@endsection
