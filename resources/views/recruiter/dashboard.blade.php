@extends('layouts.app')

@section('title', 'Recruiter Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Welcome back, {{ auth()->user()->name ?? 'Recruiter' }}</h1>
            <p class="text-dark-400">Discover and connect with top talent from I-NNOVA</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('recruiter.marketplace.index') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Browse Talent
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Active Talents</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $activeTalents ?? '2,847' }}</p>
                    <p class="text-green-400 text-sm mt-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        +156 this month
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-primary-600/20">
                    <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">My Shortlist</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $shortlistCount ?? 8 }}</p>
                    <p class="text-dark-400 text-sm mt-1">
                        {{ $interviewing ?? 2 }} interviewing
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-teal-600/20">
                    <svg class="w-8 h-8 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Messages</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $unreadMessages ?? 0 }}</p>
                    <p class="text-blue-400 text-sm mt-1">
                        {{ $newResponses ?? 0 }} new responses
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-blue-600/20">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Hires This Year</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $hiresThisYear ?? 0 }}</p>
                    <p class="text-green-400 text-sm mt-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        +50% vs last year
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-green-600/20">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Featured Talents -->
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-white">Featured Talent</h2>
                <a href="{{ route('recruiter.marketplace.index') }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium">
                    View All →
                </a>
            </div>
            <div class="space-y-4">
                @forelse($featuredTalent ?? [] as $talent)
                    <div class="flex items-center gap-4 p-4 bg-dark-800 rounded-xl hover:bg-dark-700 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($talent->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-dark-200 font-medium truncate">{{ $talent->name ?? 'Fellow' }}</h3>
                                @if($talent->tier ?? false)
                                <span class="px-2 py-0.5 text-xs font-medium 
                                    {{ $talent->tier === 'elite' ? 'bg-yellow-600/20 text-yellow-400' : '' }}
                                    {{ $talent->tier === 'professional' ? 'bg-purple-600/20 text-purple-400' : '' }}
                                    {{ $talent->tier === 'intern' ? 'bg-blue-600/20 text-blue-400' : '' }}
                                    {{ $talent->tier === 'rookie' ? 'bg-gray-600/20 text-gray-400' : '' }} rounded">
                                    {{ ucfirst($talent->tier) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-dark-400 text-sm truncate">{{ $talent->headline ?? $talent->primaryTrack?->track?->name ?? 'Fellow' }}</p>
                            @if($talent->skills ?? false)
                            <div class="flex items-center gap-2 mt-2">
                                @foreach(array_slice($talent->skills ?? [], 0, 3) as $skill)
                                    <span class="px-2 py-0.5 bg-dark-700 text-dark-400 rounded text-xs">{{ $skill }}</span>
                                @endforeach
                                @if(count($talent->skills ?? []) > 3)
                                    <span class="text-dark-500 text-xs">+{{ count($talent->skills) - 3 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-center mr-4">
                                <p class="text-2xl font-bold text-primary-400">{{ $talent->primary_score ?? 0 }}</p>
                                <p class="text-dark-500 text-xs">Score</p>
                            </div>
                            <a href="{{ route('recruiter.talent.show', $talent->id) }}" class="btn btn-outline py-2 text-sm">
                                View
                            </a>
                            <button class="p-2 text-dark-400 hover:text-red-400 hover:bg-dark-600 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-dark-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-dark-300 font-medium mb-1">No featured talent yet</h3>
                        <p class="text-dark-500 text-sm">Check back later for featured fellows</p>
                        <a href="{{ route('recruiter.marketplace.index') }}" class="btn btn-primary mt-4">Browse Marketplace</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Marketplace Stats -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Marketplace Overview</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-primary-500 to-blue-600"></div>
                            <span class="text-dark-300">Total Talent</span>
                        </div>
                        <span class="text-dark-300 font-medium">{{ number_format($marketplaceStats['total_talent'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-green-500 to-teal-600"></div>
                            <span class="text-dark-300">Elite Talent</span>
                        </div>
                        <span class="text-dark-300 font-medium">{{ number_format($marketplaceStats['by_tier']['elite'] ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-dark-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-600"></div>
                            <span class="text-dark-300">Joined This Month</span>
                        </div>
                        <span class="text-dark-300 font-medium">{{ number_format($marketplaceStats['recently_joined'] ?? 0) }}</span>
                    </div>
                </div>
                <a href="{{ route('recruiter.marketplace.index') }}" class="btn btn-outline w-full mt-4">
                    Browse Marketplace
                </a>
            </div>

            <!-- Your Shortlist -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Your Shortlist</h2>
                    <a href="{{ route('recruiter.shortlist.index') }}" class="text-primary-400 text-sm hover:underline">View all</a>
                </div>
                <div class="space-y-4">
                    @forelse($shortlist ?? [] as $item)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($item->fellow->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-dark-200 font-medium truncate">{{ $item->fellow->name ?? 'Fellow' }}</p>
                                <p class="text-dark-500 text-xs">Added {{ $item->created_at?->diffForHumans() ?? 'recently' }}</p>
                            </div>
                            <a href="{{ route('recruiter.talent.show', $item->fellow_id ?? 1) }}" class="text-primary-400 hover:text-primary-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-dark-500 text-sm">No fellows in your shortlist yet</p>
                            <a href="{{ route('recruiter.marketplace.index') }}" class="text-primary-400 text-sm hover:underline">Find talent</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Search</h2>
        <form action="{{ route('recruiter.marketplace.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by skills, job titles, or keywords..." class="form-input w-full">
            </div>
            <select name="track_id" class="form-input lg:w-48">
                <option value="">All Tracks</option>
                @foreach($marketplaceStats['by_track'] ?? [] as $trackName => $count)
                    <option value="{{ $trackName }}">{{ $trackName }} ({{ $count }})</option>
                @endforeach
            </select>
            <select name="min_score" class="form-input lg:w-40">
                <option value="">Min Score</option>
                <option value="90">90%+</option>
                <option value="80">80%+</option>
                <option value="70">70%+</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
        </form>
    </div>
</div>
@endsection
