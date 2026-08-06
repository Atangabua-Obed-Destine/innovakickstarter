@extends('layouts.app')

@section('title', 'Talent Marketplace')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Talent Marketplace</h1>
            <p class="text-dark-400">Discover top career-ready talent from I-NNOVA's fellowship programs</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('recruiter.shortlist.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                My Shortlist ({{ $shortlistCount ?? 0 }})
            </a>
            <button class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Set Job Alert
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid sm:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-primary-400">{{ $totalTalent ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Available Talent</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-teal-400">{{ $highPerformers ?? 0 }}</p>
            <p class="text-dark-400 text-sm">80%+ Career Score</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-blue-400">{{ $trackCount ?? 0 }}</p>
            <p class="text-dark-400 text-sm">Career Tracks</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-amber-400">{{ $shortlistCount ?? 0 }}</p>
            <p class="text-dark-400 text-sm">In Your Shortlist</p>
        </div>
    </div>

    <!-- Search & Filters -->
    <form action="{{ route('recruiter.marketplace.index') }}" method="GET" class="card p-6">
        <div class="flex flex-col lg:flex-row gap-4 mb-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name, skills, or keywords..." class="form-input pl-10">
            </div>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
        </div>

        <!-- Advanced Filters -->
        <div x-data="{ showFilters: true }" class="space-y-4">
            <button type="button" @click="showFilters = !showFilters" class="flex items-center gap-2 text-primary-400 text-sm hover:underline">
                <svg class="w-4 h-4 transition-transform" :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                Advanced Filters
            </button>

            <div x-show="showFilters" x-transition class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Career Track</label>
                    <select name="track_id" class="form-input">
                        <option value="">All Tracks</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ ($filters['track_id'] ?? '') == $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Career Capital Score</label>
                    <select name="min_score" class="form-input">
                        <option value="">Any Score</option>
                        <option value="90" {{ ($filters['min_score'] ?? '') == '90' ? 'selected' : '' }}>90%+ (Exceptional)</option>
                        <option value="80" {{ ($filters['min_score'] ?? '') == '80' ? 'selected' : '' }}>80%+ (Excellent)</option>
                        <option value="70" {{ ($filters['min_score'] ?? '') == '70' ? 'selected' : '' }}>70%+ (Good)</option>
                        <option value="60" {{ ($filters['min_score'] ?? '') == '60' ? 'selected' : '' }}>60%+ (Developing)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Skills</label>
                    <input type="text" name="skills" value="{{ $filters['skills'] ?? '' }}" class="form-input" placeholder="e.g., Python, React, SQL...">
                </div>
                <div>
                    <label class="form-label">Tier</label>
                    <select name="tier" class="form-input">
                        <option value="">All Tiers</option>
                        <option value="elite" {{ ($filters['tier'] ?? '') == 'elite' ? 'selected' : '' }}>Elite</option>
                        <option value="professional" {{ ($filters['tier'] ?? '') == 'professional' ? 'selected' : '' }}>Professional</option>
                        <option value="intern" {{ ($filters['tier'] ?? '') == 'intern' ? 'selected' : '' }}>Intern</option>
                        <option value="rookie" {{ ($filters['tier'] ?? '') == 'rookie' ? 'selected' : '' }}>Rookie</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Location</label>
                    <input type="text" name="location" value="{{ $filters['location'] ?? '' }}" class="form-input" placeholder="e.g., Douala, Yaoundé...">
                </div>
                <div>
                    <label class="form-label">Availability</label>
                    <select name="availability" class="form-input">
                        <option value="">All</option>
                        <option value="immediate" {{ ($filters['availability'] ?? '') == 'immediate' ? 'selected' : '' }}>Available Now</option>
                        <option value="2_weeks" {{ ($filters['availability'] ?? '') == '2_weeks' ? 'selected' : '' }}>In 2 Weeks</option>
                        <option value="1_month" {{ ($filters['availability'] ?? '') == '1_month' ? 'selected' : '' }}>In 1 Month</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Sort By</label>
                    <select name="sort_by" class="form-input">
                        <option value="score" {{ ($filters['sort_by'] ?? '') == 'score' ? 'selected' : '' }}>Score: High to Low</option>
                        <option value="name" {{ ($filters['sort_by'] ?? '') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="newest" {{ ($filters['sort_by'] ?? '') == 'newest' ? 'selected' : '' }}>Recently Joined</option>
                        <option value="tier" {{ ($filters['sort_by'] ?? '') == 'tier' ? 'selected' : '' }}>By Tier</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('recruiter.marketplace.index') }}" class="btn btn-outline w-full text-center">Clear Filters</a>
                </div>
            </div>
        </div>

        <!-- Active Filters -->
        @if(collect($filters)->filter()->isNotEmpty())
        <div class="flex flex-wrap gap-2 pt-4 border-t border-dark-700 mt-4">
            <span class="text-dark-400 text-sm">Active filters:</span>
            @foreach($filters as $key => $value)
                @if(!empty($value) && $key !== 'page')
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-600/20 text-primary-400 rounded text-sm">
                        {{ ucwords(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? implode(', ', $value) : $value }}
                        <a href="{{ route('recruiter.marketplace.index', array_merge($filters, [$key => ''])) }}" class="hover:text-white">&times;</a>
                    </span>
                @endif
            @endforeach
            <a href="{{ route('recruiter.marketplace.index') }}" class="text-dark-400 text-sm hover:text-white">Clear all</a>
        </div>
        @endif
    </form>

    <!-- Results Count & View Toggle -->
    <div class="flex items-center justify-between">
        <p class="text-dark-400">Showing <span class="text-white font-medium">{{ $talent->total() }}</span> candidates</p>
        <div class="flex items-center gap-2">
            <button class="p-2 bg-primary-600 text-white rounded-lg" title="Grid View">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </button>
            <button class="p-2 bg-dark-800 text-dark-400 hover:text-white rounded-lg" title="List View">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Talent Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($talent as $fellow)
            @php
                $primaryTrack = $fellow->primaryTrack;
                $score = $primaryTrack?->score ?? 0;
                $skills = is_array($fellow->skills) ? array_slice($fellow->skills, 0, 5) : [];
            @endphp
            <div class="card overflow-hidden hover:border-primary-500/50 transition-all group">
                <!-- Header with Score -->
                <div class="relative p-6 pb-12 bg-gradient-to-br from-dark-800 to-dark-900">
                    <!-- Shortlist Button -->
                    <form action="{{ route('recruiter.talent.shortlist', $fellow) }}" method="POST" class="absolute top-3 right-3">
                        @csrf
                        <button type="submit" class="p-2 text-dark-400 hover:text-red-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Avatar & Score Circle -->
                    <div class="relative w-20 h-20 mx-auto">
                        @if($fellow->avatar_url)
                            <img src="{{ Storage::url($fellow->avatar_url) }}" alt="{{ $fellow->name }}" class="w-20 h-20 rounded-full object-cover">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-2xl">
                                {{ $fellow->initials }}
                            </div>
                        @endif
                        <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-full bg-dark-900 border-2 {{ $score >= 80 ? 'border-green-500' : ($score >= 70 ? 'border-teal-500' : 'border-amber-500') }} flex items-center justify-center">
                            <span class="text-xs font-bold text-white">{{ number_format($score, 0) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 -mt-6 relative">
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-semibold text-white">{{ $fellow->name }}</h3>
                        <p class="text-dark-400 text-sm">{{ $fellow->headline ?? 'IKS Fellow' }}</p>
                        <div class="flex items-center justify-center gap-2 mt-2 text-sm">
                            <span class="text-primary-400">{{ $primaryTrack?->track?->name ?? 'General' }}</span>
                            @if($fellow->location)
                                <span class="text-dark-600">•</span>
                                <span class="text-dark-400">{{ $fellow->location }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Skills -->
                    @if(!empty($skills))
                    <div class="flex flex-wrap justify-center gap-1 mb-4">
                        @foreach(array_slice($skills, 0, 3) as $skill)
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-300 rounded text-xs">{{ $skill }}</span>
                        @endforeach
                        @if(count($skills) > 3)
                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 rounded text-xs">+{{ count($skills) - 3 }}</span>
                        @endif
                    </div>
                    @endif

                    <!-- Availability Badge -->
                    <div class="text-center mb-4">
                        @if($fellow->open_to_opportunities)
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-600/20 text-green-400 rounded-full text-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                Available Now
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-dark-700 text-dark-400 rounded-full text-xs">
                                Not Available
                            </span>
                        @endif
                    </div>

                    <!-- Action -->
                    <a href="{{ route('recruiter.talent.show', $fellow) }}" 
                       class="block w-full btn btn-outline text-center group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-all">
                        View Profile
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-dark-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-lg font-medium">No talent found</p>
                <p class="text-sm">Try adjusting your search filters</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($talent->hasPages())
    <div class="flex items-center justify-center">
        {{ $talent->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
