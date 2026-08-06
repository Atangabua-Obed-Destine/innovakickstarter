@extends('layouts.app')

@section('title', $talent->name . ' - Talent Profile')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('recruiter.marketplace.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Marketplace
    </a>

    <!-- Profile Header -->
    <div class="card overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600/30 to-blue-600/30 h-32"></div>
        <div class="p-6 -mt-16">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($talent->avatar_url)
                        <img src="{{ Storage::url($talent->avatar_url) }}" alt="{{ $talent->name }}" class="w-28 h-28 rounded-full object-cover ring-4 ring-dark-900">
                    @else
                        <div class="w-28 h-28 rounded-full bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-white font-bold text-4xl ring-4 ring-dark-900">
                            {{ $talent->initials }}
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $talent->name }}</h1>
                            <p class="text-dark-300 text-lg">{{ $talent->headline ?? 'IKS Fellow' }}</p>
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-sm">
                                <span class="flex items-center gap-1 text-primary-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    {{ $primaryTrack?->track?->name ?? 'General' }}
                                </span>
                                @if($talent->location)
                                <span class="flex items-center gap-1 text-dark-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $talent->location }}
                                </span>
                                @endif
                                <span class="flex items-center gap-1 text-dark-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Joined {{ $talent->created_at->format('M Y') }}
                                </span>
                            </div>
                            @if($talent->open_to_opportunities)
                            <div class="mt-3">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-600/20 text-green-400 rounded-full text-sm">
                                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                    Available for opportunities
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <form action="{{ route($isShortlisted ? 'recruiter.talent.unshortlist' : 'recruiter.talent.shortlist', $talent) }}" method="POST">
                                @csrf
                                @if($isShortlisted) @method('DELETE') @endif
                                <button type="submit" class="btn {{ $isShortlisted ? 'btn-primary' : 'btn-outline' }}">
                                    <svg class="w-5 h-5" fill="{{ $isShortlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    {{ $isShortlisted ? 'Shortlisted' : 'Shortlist' }}
                                </button>
                            </form>
                            @if($talent->email)
                            <a href="mailto:{{ $talent->email }}" class="btn btn-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Contact
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">About</h2>
                @if($talent->bio)
                    <p class="text-dark-300 leading-relaxed">{{ $talent->bio }}</p>
                @else
                    <p class="text-dark-500 italic">No bio provided yet.</p>
                @endif
            </div>

            <!-- Career Capital Score Breakdown -->
            @php
                $score = $primaryTrack?->score ?? 0;
                $calculator = app(\App\Services\CareerCapitalCalculator::class);
                $track = $primaryTrack?->track;
                $pillars = [];
                if ($track) {
                    $pillars = [
                        ['name' => 'Technical Skills', 'score' => round($calculator->calculateTechnicalScore($talent, $track)), 'icon' => '💻'],
                        ['name' => 'Interview Performance', 'score' => round($calculator->calculateInterviewScore($talent, $track)), 'icon' => '💬'],
                        ['name' => 'Portfolio Quality', 'score' => round($calculator->calculatePortfolioScore($talent, $track)), 'icon' => '📁'],
                        ['name' => 'Collaboration', 'score' => round($calculator->calculateCollaborationScore($talent, $track)), 'icon' => '🤝'],
                    ];
                }
            @endphp
            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">Career Capital Score</h2>
                    <div class="text-right">
                        <p class="text-3xl font-bold {{ $score >= 80 ? 'text-green-400' : ($score >= 60 ? 'text-teal-400' : 'text-amber-400') }}">{{ number_format($score, 3) }}%</p>
                        <p class="text-dark-500 text-sm">{{ ucfirst($primaryTrack?->tier ?? 'Rookie') }}</p>
                    </div>
                </div>

                @if(!empty($pillars))
                <!-- Score Visualization -->
                <div class="grid sm:grid-cols-4 gap-4 mb-6">
                    @foreach($pillars as $pillar)
                        <div class="text-center p-4 bg-dark-800 rounded-lg">
                            <span class="text-2xl">{{ $pillar['icon'] }}</span>
                            <div class="w-16 h-16 mx-auto my-3 relative">
                                <svg class="w-16 h-16 -rotate-90">
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="currentColor" stroke-width="6" class="text-dark-700"/>
                                    <circle cx="32" cy="32" r="28" fill="none" stroke="currentColor" stroke-width="6" 
                                            stroke-dasharray="{{ 2 * 3.14159 * 28 }}" 
                                            stroke-dashoffset="{{ 2 * 3.14159 * 28 * (1 - $pillar['score'] / 100) }}"
                                            class="{{ $pillar['score'] >= 90 ? 'text-green-500' : ($pillar['score'] >= 80 ? 'text-teal-500' : 'text-amber-500') }}"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-white">
                                    {{ $pillar['score'] }}%
                                </span>
                            </div>
                            <p class="text-dark-300 text-xs">{{ $pillar['name'] }}</p>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Verified Badge -->
                <div class="flex items-center gap-3 p-4 bg-green-600/10 border border-green-500/30 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    <div>
                        <p class="text-green-400 font-medium">I-NNOVA Verified</p>
                        <p class="text-dark-400 text-sm">Score validated through {{ $talent->activities->count() }} activities and {{ $talent->interviewSessions->count() ?? 0 }} mock interviews</p>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            @php
                $allSkills = collect();
                foreach ($talent->activities as $activity) {
                    if ($activity->tech_stack) {
                        $allSkills = $allSkills->merge($activity->tech_stack);
                    }
                }
                $skillCounts = $allSkills->countBy()->sortDesc();
                $topSkills = $skillCounts->keys()->take(12)->toArray();
            @endphp
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Skills & Technologies</h2>
                @if(!empty($topSkills))
                    <div class="flex flex-wrap gap-2">
                        @foreach($topSkills as $skill)
                            <span class="px-3 py-1.5 bg-primary-600/20 text-primary-400 rounded-lg text-sm border border-primary-600/30">{{ $skill }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-dark-500 italic">No skills recorded yet.</p>
                @endif
            </div>

            <!-- Projects from Activities -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Projects</h2>
                @if($talent->activities->where('type', 'project')->count() > 0)
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($talent->activities->where('type', 'project')->take(4) as $project)
                            <div class="p-4 bg-dark-800 rounded-lg">
                                <h4 class="text-dark-200 font-medium">{{ $project->title }}</h4>
                                <p class="text-dark-500 text-sm mt-1">{{ \Illuminate\Support\Str::limit($project->description, 100) }}</p>
                                @if($project->tech_stack)
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        @foreach(array_slice($project->tech_stack, 0, 4) as $tech)
                                            <span class="px-2 py-0.5 bg-dark-700 text-dark-400 rounded text-xs">{{ $tech }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($project->github_url || $project->live_url)
                                    <div class="flex gap-2 mt-3">
                                        @if($project->github_url)
                                            <a href="{{ $project->github_url }}" target="_blank" class="text-xs text-primary-400 hover:underline">GitHub</a>
                                        @endif
                                        @if($project->live_url)
                                            <a href="{{ $project->live_url }}" target="_blank" class="text-xs text-teal-400 hover:underline">Live Demo</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-dark-500 italic">No projects shared yet.</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Activities Completed</span>
                        <span class="text-dark-200 font-medium">{{ $talent->activities->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Mock Interviews</span>
                        <span class="text-dark-200 font-medium">{{ $talent->interviewSessions->count() ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Track</span>
                        <span class="text-primary-400 font-medium">{{ $primaryTrack?->track?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Tier</span>
                        <span class="text-amber-400 font-medium">{{ ucfirst($primaryTrack?->tier ?? 'Rookie') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-dark-400">Member Since</span>
                        <span class="text-dark-200">{{ $talent->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Contact</h3>
                <div class="space-y-3">
                    @if($talent->email)
                    <a href="mailto:{{ $talent->email }}" class="flex items-center gap-3 text-dark-300 hover:text-primary-400 transition-colors">
                        <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $talent->email }}
                    </a>
                    @endif
                    @if($talent->linkedin_url)
                    <a href="{{ $talent->linkedin_url }}" target="_blank" class="flex items-center gap-3 text-dark-300 hover:text-primary-400 transition-colors">
                        <svg class="w-5 h-5 text-dark-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        LinkedIn
                    </a>
                    @endif
                    @if($talent->github_url)
                    <a href="{{ $talent->github_url }}" target="_blank" class="flex items-center gap-3 text-dark-300 hover:text-primary-400 transition-colors">
                        <svg class="w-5 h-5 text-dark-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub
                    </a>
                    @endif
                    @if($talent->portfolio_url)
                    <a href="{{ $talent->portfolio_url }}" target="_blank" class="flex items-center gap-3 text-dark-300 hover:text-primary-400 transition-colors">
                        <svg class="w-5 h-5 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                        Portfolio
                    </a>
                    @endif
                </div>
            </div>

            <!-- Download Resume -->
            @if($talent->resume_url)
            <div class="card p-6">
                <a href="{{ Storage::url($talent->resume_url) }}" target="_blank" class="w-full btn btn-outline inline-flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Resume
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
