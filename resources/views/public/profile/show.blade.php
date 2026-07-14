<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $fellow->bio ? \Illuminate\Support\Str::limit($fellow->bio, 160) : 'Career Capital Profile - I-NNOVA IKS Platform' }}">

    <title>{{ $fellow->name }} - Career Capital Profile | IKS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-dark-900 text-dark-300 antialiased">
    <!-- Navigation -->
    <nav class="bg-dark-800 border-b border-dark-700">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-600 to-blue-600 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">IKS</span>
                </div>
                <span class="text-xl font-bold text-white">Career Capital</span>
            </a>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="text-dark-400 hover:text-white transition-colors text-sm">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary py-2 px-4 text-sm">Join Platform</a>
            </div>
        </div>
    </nav>

    <!-- Profile Content -->
    <main class="max-w-5xl mx-auto px-4 py-8">
        <!-- Profile Header -->
        <div class="card p-8 mb-6">
            <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                <!-- Photo -->
                <div class="relative">
                    @if($fellow->avatar_url)
                        <img src="{{ asset('storage/' . $fellow->avatar_url) }}" alt="{{ $fellow->name }}" class="w-32 h-32 rounded-full object-cover shadow-2xl shadow-primary-500/25">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white text-4xl font-bold shadow-2xl shadow-primary-500/25">
                            {{ $fellow->initials }}
                        </div>
                    @endif
                    @if($fellow->open_to_opportunities)
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-green-500 border-4 border-dark-800 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-white">{{ $fellow->name }}</h1>
                        <span class="px-3 py-1 bg-primary-600/20 text-primary-400 rounded-full text-sm font-medium">
                            {{ $primaryTrack?->track?->name ?? 'Fellow' }}
                        </span>
                    </div>
                    <p class="text-dark-400 text-lg mb-4">{{ $fellow->headline ?? ($primaryTrack?->track?->name ? $primaryTrack->track->name . ' Fellow' : 'IKS Fellow') }}</p>
                    
                    <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-dark-500 mb-4">
                        @if($fellow->location)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $fellow->location }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Joined {{ $fellow->created_at?->format('M Y') }}
                        </span>
                        @if($fellow->open_to_opportunities)
                        <span class="flex items-center gap-1 text-green-400">
                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                            Available for opportunities
                        </span>
                        @endif
                    </div>

                    @if($fellow->bio)
                    <p class="text-dark-300 max-w-2xl">{{ $fellow->bio }}</p>
                    @endif
                </div>

                <!-- Career Capital Score -->
                <div class="flex flex-col items-center">
                    @php $score = $primaryTrack?->score ?? 0; @endphp
                    <div class="relative w-36 h-36">
                        <svg class="w-36 h-36 -rotate-90">
                            <circle cx="72" cy="72" r="64" fill="none" stroke="currentColor" stroke-width="8" class="text-dark-700"/>
                            <circle cx="72" cy="72" r="64" fill="none" stroke="url(#gradient)" stroke-width="8" 
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ 2 * 3.14159 * 64 }}" 
                                    stroke-dashoffset="{{ 2 * 3.14159 * 64 * (1 - $score / 100) }}"/>
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#7C3AED"/>
                                    <stop offset="100%" stop-color="#14B8A6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-4xl font-bold text-white">{{ number_format($score, 3) }}%</span>
                            <span class="text-dark-500 text-xs">Career Capital</span>
                        </div>
                    </div>
                    <p class="text-dark-500 text-sm mt-2">{{ ucfirst($primaryTrack?->tier ?? 'Rookie') }} Tier</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Career Capital Breakdown -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Career Capital Breakdown
                    </h2>
                    <div class="grid sm:grid-cols-2 gap-6">
                        @forelse($careerCapitalBreakdown ?? [] as $pillar)
                            <div class="p-4 bg-dark-800 rounded-xl">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 rounded-lg bg-gradient-to-br {{ $pillar['color'] }}/20">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pillar['icon'] }}"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-dark-200 font-medium">{{ $pillar['name'] }}</h3>
                                            <p class="text-dark-500 text-xs">{{ $pillar['desc'] }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xl font-bold text-white">{{ number_format($pillar['score'], 3) }}%</span>
                                </div>
                                <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r {{ $pillar['color'] }} rounded-full transition-all duration-1000" 
                                         style="width: {{ $pillar['score'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center text-dark-500 py-8">
                                <p>Career capital data not yet available</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Skills -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Skills & Technologies
                    </h2>

                    <!-- Skills Tags from Activities -->
                    <div class="flex flex-wrap gap-2">
                        @forelse($topSkills ?? [] as $skill)
                            <span class="px-3 py-1.5 bg-gradient-to-r from-teal-600/20 to-green-600/20 text-teal-400 rounded-lg text-sm font-medium border border-teal-600/30">{{ $skill }}</span>
                        @empty
                            <p class="text-dark-500 text-sm">No skills recorded yet</p>
                        @endforelse
                    </div>

                    @if($fellow->linkedin_url || $fellow->github_url || $fellow->portfolio_url)
                    <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-dark-700">
                        @if($fellow->linkedin_url)
                        <a href="{{ $fellow->linkedin_url }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-blue-600/20 text-blue-400 rounded-lg text-sm hover:bg-blue-600/30 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            LinkedIn
                        </a>
                        @endif
                        @if($fellow->github_url)
                        <a href="{{ $fellow->github_url }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-gray-600/20 text-gray-400 rounded-lg text-sm hover:bg-gray-600/30 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            GitHub
                        </a>
                        @endif
                        @if($fellow->portfolio_url)
                        <a href="{{ $fellow->portfolio_url }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-primary-600/20 text-primary-400 rounded-lg text-sm hover:bg-primary-600/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            Portfolio
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Experience / Projects -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Projects & Experience
                    </h2>
                    <div class="space-y-6">
                        @forelse($experience ?? [] as $exp)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-600/20 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    @if(!$loop->last)
                                    <div class="w-0.5 flex-1 bg-dark-700 mt-2"></div>
                                    @endif
                                </div>
                                <div class="pb-6">
                                    <h3 class="text-dark-200 font-semibold">{{ $exp['title'] }}</h3>
                                    <p class="text-primary-400 text-sm">{{ $exp['company'] }}</p>
                                    <p class="text-dark-500 text-xs mb-2">{{ $exp['period'] }}</p>
                                    <p class="text-dark-400 text-sm">{{ $exp['desc'] }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-dark-500 py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <p>No projects shared yet</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Education -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                        </svg>
                        Education & Certifications
                    </h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-dark-800 rounded-xl">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-dark-200 font-semibold">B.Sc. Computer Science</h3>
                                    <p class="text-primary-400 text-sm">University of Yaoundé I</p>
                                    <p class="text-dark-500 text-xs">2020 - 2024</p>
                                </div>
                                <span class="px-2 py-1 bg-green-600/20 text-green-400 text-xs rounded">Graduated</span>
                            </div>
                        </div>
                        <div class="p-4 bg-dark-800 rounded-xl">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-dark-200 font-semibold">I-NNOVA Kickstarter Fellow</h3>
                                    <p class="text-primary-400 text-sm">I-NNOVA CMR</p>
                                    <p class="text-dark-500 text-xs">Cohort 2024-A</p>
                                </div>
                                <span class="px-2 py-1 bg-primary-600/20 text-primary-400 text-xs rounded">Active</span>
                            </div>
                        </div>
                        <div class="p-4 bg-dark-800 rounded-xl">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-dark-200 font-semibold">AWS Certified Cloud Practitioner</h3>
                                    <p class="text-primary-400 text-sm">Amazon Web Services</p>
                                    <p class="text-dark-500 text-xs">2024</p>
                                </div>
                                <span class="px-2 py-1 bg-amber-600/20 text-amber-400 text-xs rounded">Certified</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Contact Card (for recruiters) -->
                <div class="card p-6 bg-gradient-to-br from-primary-900/50 to-blue-900/50 border-primary-800/50">
                    <h3 class="text-lg font-semibold text-white mb-4">Interested in this candidate?</h3>
                    <p class="text-dark-400 text-sm mb-6">Sign in as a recruiter to view contact information and connect with this talent.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary w-full justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign In to Connect
                    </a>
                    <p class="text-dark-500 text-xs text-center mt-4">Don't have an account? <a href="{{ route('register') }}" class="text-primary-400 hover:underline">Register as Recruiter</a></p>
                </div>

                <!-- Quick Stats -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick Facts</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-2 border-b border-dark-700">
                            <span class="text-dark-400">Track</span>
                            <span class="text-dark-200 font-medium">{{ $primaryTrack?->track?->name ?? 'Not enrolled' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-dark-700">
                            <span class="text-dark-400">Joined</span>
                            <span class="text-dark-200 font-medium">{{ $fellow->created_at?->format('M Y') ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-dark-700">
                            <span class="text-dark-400">Career Capital</span>
                            <span class="text-primary-400 font-bold">{{ number_format($primaryTrack?->score ?? 0, 3) }}%</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-dark-700">
                            <span class="text-dark-400">Activities Completed</span>
                            <span class="text-dark-200 font-medium">{{ $fellow->activities->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-dark-700">
                            <span class="text-dark-400">Mock Interviews</span>
                            <span class="text-dark-200 font-medium">{{ $fellow->interviewSessions->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-dark-400">Tier</span>
                            <span class="text-teal-400 font-medium">{{ ucfirst($primaryTrack?->tier ?? 'rookie') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Achievements -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Achievements
                    </h3>
                    @php
                        $achievements = [];
                        // Generate dynamic achievements based on fellow's data
                        if (($primaryTrack?->score ?? 0) >= 80) {
                            $achievements[] = ['icon' => '🏆', 'label' => 'Top Performer'];
                        }
                        if ($fellow->activities->count() >= 10) {
                            $achievements[] = ['icon' => '⚡', 'label' => 'Active Contributor'];
                        }
                        if ($fellow->activities->count() >= 25) {
                            $achievements[] = ['icon' => '🎯', 'label' => 'Goal Crusher'];
                        }
                        if ($fellow->interviewSessions->count() >= 3) {
                            $achievements[] = ['icon' => '💬', 'label' => 'Interview Ready'];
                        }
                        if ($fellow->activities->where('type', 'project')->count() >= 3) {
                            $achievements[] = ['icon' => '💡', 'label' => 'Project Builder'];
                        }
                        if ($fellow->activities->where('type', 'open_source')->count() >= 1) {
                            $achievements[] = ['icon' => '🌐', 'label' => 'Open Source'];
                        }
                        // Add a welcome badge for all
                        if (empty($achievements)) {
                            $achievements[] = ['icon' => '🚀', 'label' => 'Getting Started'];
                        }
                    @endphp
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($achievements as $badge)
                            <div class="text-center p-3 bg-dark-800 rounded-xl">
                                <span class="text-2xl">{{ $badge['icon'] }}</span>
                                <p class="text-dark-500 text-xs mt-1">{{ $badge['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Languages -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Languages</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-dark-300">English</span>
                            <div class="flex gap-1">
                                @for($i = 0; $i < 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i < 5 ? 'bg-primary-500' : 'bg-dark-700' }}"></div>
                                @endfor
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-dark-300">French</span>
                            <div class="flex gap-1">
                                @for($i = 0; $i < 5; $i++)
                                    <div class="w-2 h-2 rounded-full {{ $i < 4 ? 'bg-primary-500' : 'bg-dark-700' }}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark-800 border-t border-dark-700 py-8 mt-12">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <p class="text-dark-500 text-sm">
                Powered by <a href="{{ route('home') }}" class="text-primary-400 hover:underline">IKS Career Capital Platform</a> • 
                © {{ date('Y') }} I-NNOVA CMR
            </p>
        </div>
    </footer>
</body>
</html>
