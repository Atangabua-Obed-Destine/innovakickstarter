@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">
    <!-- Profile Header -->
    <div class="card overflow-hidden">
        <!-- Cover Image -->
        <div class="h-32 bg-gradient-to-r from-primary-600 via-primary-700 to-primary-800 relative">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20100%20100%22%3E%3Cpath%20d%3D%22M0%200h100v100H0z%22%20fill%3D%22none%22%2F%3E%3Cpath%20d%3D%22M20%2030%20Q40%200%2060%2030%20T100%2030%22%20stroke%3D%22rgba(255%2C255%2C255%2C0.1)%22%20fill%3D%22none%22%20stroke-width%3D%222%22%2F%3E%3Cpath%20d%3D%22M0%2050%20Q20%2020%2040%2050%20T80%2050%20T120%2050%22%20stroke%3D%22rgba(255%2C255%2C255%2C0.05)%22%20fill%3D%22none%22%20stroke-width%3D%222%22%2F%3E%3C%2Fsvg%3E')] opacity-30"></div>
        </div>
        
        <!-- Profile Info -->
        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-12">
                <!-- Avatar -->
                <div class="relative">
                    <div class="w-24 h-24 rounded-2xl bg-dark-800 border-4 border-dark-900 overflow-hidden shadow-xl">
                        @if($user->avatar_url)
                            <img src="{{ Storage::url($user->avatar_url) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                <span class="text-3xl font-bold text-white">{{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}</span>
                            </div>
                        @endif
                    </div>
                    @if($user->is_verified)
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center border-2 border-dark-900">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif
                </div>
                
                <!-- Name & Title -->
                <div class="flex-1 pt-4 sm:pt-0">
                    <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                    <p class="text-dark-400">{{ $user->headline ?? 'Fellow at IKS Innova' }}</p>
                </div>
                
                <!-- Edit Button -->
                <a href="{{ route('profile.edit') }}" class="btn btn-secondary self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Left Column - Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Contact Info -->
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">Contact Information</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-dark-500 text-xs">Email</p>
                            <p class="text-white">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    @if($user->phone)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-dark-500 text-xs">Phone</p>
                            <p class="text-white">{{ $user->phone }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($user->location)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-dark-500 text-xs">Location</p>
                            <p class="text-white">{{ $user->location }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-dark-500 text-xs">Member Since</p>
                            <p class="text-white">{{ $user->created_at->format('F Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">Social Links</h2>
                <div class="space-y-3">
                    @if($user->linkedin_url)
                    <a href="{{ $user->linkedin_url }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-[#0077B5]/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#0077B5]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                            </svg>
                        </div>
                        <span class="text-white group-hover:text-primary-400 transition-colors">LinkedIn</span>
                        <svg class="w-4 h-4 text-dark-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                    
                    @if($user->github_url)
                    <a href="{{ $user->github_url }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                        </div>
                        <span class="text-white group-hover:text-primary-400 transition-colors">GitHub</span>
                        <svg class="w-4 h-4 text-dark-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                    
                    @if($user->twitter_url)
                    <a href="{{ $user->twitter_url }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-black/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </div>
                        <span class="text-white group-hover:text-primary-400 transition-colors">X (Twitter)</span>
                        <svg class="w-4 h-4 text-dark-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                    
                    @if($user->portfolio_url)
                    <a href="{{ $user->portfolio_url }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg bg-dark-800 hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <span class="text-white group-hover:text-primary-400 transition-colors">Portfolio</span>
                        <svg class="w-4 h-4 text-dark-500 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                    
                    @if(!$user->linkedin_url && !$user->github_url && !$user->twitter_url && !$user->portfolio_url)
                    <p class="text-dark-500 text-sm text-center py-4">No social links added yet.</p>
                    @endif
                </div>
            </div>

            <!-- Skills -->
            @if($user->skills && count($user->skills) > 0)
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">Skills</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->skills as $skill)
                        <span class="px-3 py-1.5 bg-dark-700 text-dark-200 rounded-lg text-sm">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Bio -->
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">About</h2>
                @if($user->bio)
                    <p class="text-dark-300 whitespace-pre-line">{{ $user->bio }}</p>
                @else
                    <p class="text-dark-500 italic">No bio added yet. <a href="{{ route('profile.edit') }}" class="text-primary-400 hover:text-primary-300">Add one now</a></p>
                @endif
            </div>

            <!-- Track Progress -->
            @if($user->fellowTracks && $user->fellowTracks->count() > 0)
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">Career Tracks</h2>
                <div class="space-y-4">
                    @foreach($user->fellowTracks as $fellowTrack)
                    <div class="p-4 bg-dark-800 rounded-xl">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-white">{{ $fellowTrack->track->name ?? 'Career Track' }}</h3>
                                    <p class="text-dark-500 text-sm">{{ $fellowTrack->is_primary ? 'Primary Track' : 'Secondary Track' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-primary-400">{{ number_format($fellowTrack->score ?? 0) }}</p>
                                <p class="text-dark-500 text-xs">Score</p>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-3">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-dark-400">Progress to next tier</span>
                                <span class="text-dark-400">{{ min(100, ($fellowTrack->score ?? 0) % 100) }}%</span>
                            </div>
                            <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-primary-500 to-primary-400 rounded-full transition-all" 
                                     style="width: {{ min(100, ($fellowTrack->score ?? 0) % 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Activities -->
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Recent Activities</h2>
                    <a href="{{ route('activities.index') }}" class="text-primary-400 hover:text-primary-300 text-sm">View All</a>
                </div>
                
                @if($user->activities && $user->activities->count() > 0)
                <div class="space-y-3">
                    @foreach($user->activities as $activity)
                    <div class="flex items-center gap-4 p-3 bg-dark-800 rounded-lg">
                        <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-white truncate">{{ $activity->title }}</h4>
                            <p class="text-dark-500 text-sm">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-green-400 font-semibold">+{{ $activity->points_awarded ?? 0 }}</span>
                            <p class="text-dark-500 text-xs">points</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-dark-800 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="text-dark-400 mb-4">No activities yet</p>
                    <a href="{{ route('activities.index') }}" class="btn btn-primary btn-sm">
                        Log Your First Activity
                    </a>
                </div>
                @endif
            </div>

            <!-- Account Settings Quick Links -->
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-4 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white group-hover:text-primary-400 transition-colors">Edit Profile</p>
                            <p class="text-dark-500 text-sm">Update your information</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-4 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white group-hover:text-primary-400 transition-colors">Dashboard</p>
                            <p class="text-dark-500 text-sm">View your overview</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('activities.index') }}" class="flex items-center gap-3 p-4 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white group-hover:text-primary-400 transition-colors">Activities</p>
                            <p class="text-dark-500 text-sm">Log your progress</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('weekly-progress.index') }}" class="flex items-center gap-3 p-4 bg-dark-800 rounded-lg hover:bg-dark-700 transition-colors group">
                        <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-white group-hover:text-primary-400 transition-colors">Weekly Progress</p>
                            <p class="text-dark-500 text-sm">Track your streak</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
