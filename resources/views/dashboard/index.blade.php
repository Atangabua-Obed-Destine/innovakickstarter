@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    @isset($internshipProfile)
        @php
            $ip = $internshipProfile;
            $progress = $ip->progress_percent ?? 0;
            $ipTone = match($ip->status) {
                'active'   => ['dot' => 'bg-emerald-400', 'accent' => 'text-emerald-400', 'label' => 'Active'],
                'approved' => ['dot' => 'bg-blue-400',    'accent' => 'text-blue-400',    'label' => 'Approved · not started'],
                'completed'=> ['dot' => 'bg-dark-500',    'accent' => 'text-dark-300',    'label' => 'Completed'],
                default    => ['dot' => 'bg-amber-400',   'accent' => 'text-amber-400',   'label' => ucfirst(str_replace('_', ' ', $ip->status))],
            };
            $bar = $ip->is_expired ? 'from-dark-500 to-dark-600'
                 : ($progress >= 80 ? 'from-amber-500 to-red-500'
                 : ($progress >= 40 ? 'from-teal-500 to-primary-500'
                 : 'from-primary-500 to-blue-500'));
        @endphp
        <div class="card p-5 border-l-4 border-primary-500">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full {{ $ipTone['dot'] }} {{ $ip->status === 'active' ? 'animate-pulse' : '' }}"></span>
                    <div class="min-w-0">
                        <p class="text-white font-semibold truncate">
                            {{ ucfirst($ip->type) }} internship &middot; {{ $ip->institution_name }}
                        </p>
                        <p class="text-xs {{ $ipTone['accent'] }}">{{ $ipTone['label'] }}</p>
                    </div>
                </div>

                @if($ip->approved_start_date && $ip->approved_end_date)
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between text-xs text-dark-400 mb-1">
                            <span>{{ $ip->approved_start_date->format('M j') }}</span>
                            <span>{{ $ip->approved_end_date->format('M j, Y') }}</span>
                        </div>
                        <div class="h-2 bg-dark-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r {{ $bar }}" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] mt-1">
                            <span class="text-dark-500">{{ $ip->days_elapsed ?? 0 }}/{{ $ip->total_days ?? 0 }} days</span>
                            <span class="{{ $ipTone['accent'] }} font-medium">
                                @if($ip->is_expired)
                                    Ended {{ $ip->approved_end_date->diffForHumans() }}
                                @else
                                    {{ $ip->days_remaining ?? 0 }} days remaining
                                @endif
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endisset

    @isset($pendingTrackEnrollments)
        @if($pendingTrackEnrollments->isNotEmpty())
            <div class="card p-4 border-l-4 border-amber-500 bg-amber-500/5">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-amber-300 font-semibold">
                            {{ $pendingTrackEnrollments->count() }} track {{ $pendingTrackEnrollments->count() > 1 ? 'requests are' : 'request is' }} awaiting admin review
                        </p>
                        <ul class="mt-1 text-amber-200/80 text-sm space-y-0.5">
                            @foreach($pendingTrackEnrollments as $req)
                                <li>
                                    <span class="font-medium">{{ $req->track?->name }}</span>
                                    · requested {{ $req->requested_at?->diffForHumans() }}
                                    @if($req->status === 'needs_revision')
                                        · <span class="text-orange-300">revision requested</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('tracks.select') }}" class="inline-block mt-2 text-amber-300 hover:text-amber-200 text-sm font-medium">Manage requests →</a>
                    </div>
                </div>
            </div>
        @endif
    @endisset

    <!-- Welcome Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">
                Welcome back, {{ explode(' ', $user->name)[0] }}! 👋
            </h1>
            <p class="text-dark-400 mt-1">
                Track your progress and continue building your Career Capital.
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($streak > 0)
                <div class="flex items-center gap-2 px-4 py-2 bg-orange-600/20 rounded-lg">
                    <svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-orange-400 font-semibold">{{ $streak }} day streak!</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Track Switcher -->
    @if($fellowTracks->count() > 0)
    {{-- Active Track Summary (switching is now in the header navbar) --}}
    <div class="card p-4 border-l-4 border-violet-500">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    {{-- Health pulse on dashboard card --}}
                    @if($primaryTrack)
                        @php
                            $dashPulse = $primaryTrack->health_status ?? 'dormant';
                            $dashPulseColor = match($dashPulse) {
                                'active'  => 'bg-emerald-400',
                                'cooling' => 'bg-amber-400',
                                default   => 'bg-red-400',
                            };
                        @endphp
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full {{ $dashPulseColor }} ring-2 ring-dark-900 {{ $dashPulse === 'active' ? 'animate-pulse' : '' }}"></span>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm text-dark-400">Viewing:</span>
                        <span class="text-white font-semibold">{{ $primaryTrack?->track?->name ?? 'None Selected' }}</span>
                        @if($primaryTrack)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-violet-600/20 text-violet-400">
                                {{ number_format($primaryTrack->score, 1) }}% &middot; {{ ucfirst($primaryTrack->tier) }}
                            </span>
                            @if($primaryTrack->is_primary)
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-600/20 text-emerald-400">Primary</span>
                            @endif
                        @endif
                        {{-- Achievement badges inline --}}
                        @foreach(($trackSwitcherMeta['achievements'] ?? []) as $badge)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-dark-700 text-dark-300 cursor-help"
                                  title="{{ $badge['name'] }}: {{ $badge['description'] }}">
                                {{ $badge['icon'] }} {{ $badge['name'] }}
                            </span>
                        @endforeach
                    </div>
                    @if($fellowTracks->count() > 1)
                        <p class="text-dark-500 text-xs mt-0.5">Use the track switcher in the header to change tracks · <a href="{{ route('dashboard.track-comparison') }}" class="text-violet-400 hover:text-violet-300">Compare all tracks →</a></p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($fellowTracks->count() > 1)
                <a href="{{ route('dashboard.track-comparison') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-amber-400 border border-amber-500/30 rounded-lg hover:bg-amber-600/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Compare Tracks
                </a>
                @endif
                <a href="{{ route('tracks.select') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-400 border border-primary-500/30 rounded-lg hover:bg-primary-600/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Enroll in New Track
                </a>
            </div>
        </div>
    </div>

    {{-- Track Health At-a-Glance (only show if multi-track) --}}
    @if($fellowTracks->count() > 1)
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-dark-200">Track Health Overview</h4>
            <a href="{{ route('dashboard.track-comparison') }}" class="text-[11px] text-dark-500 hover:text-dark-300 transition-colors">Details →</a>
        </div>
        <div class="grid grid-cols-{{ min(4, $fellowTracks->count()) }} gap-3">
            @foreach($fellowTracks as $ft)
            @php
                $ftHealth = match($ft->health_status ?? 'dormant') {
                    'active'  => ['color' => 'emerald', 'label' => 'Active', 'dot' => 'bg-emerald-400'],
                    'cooling' => ['color' => 'amber', 'label' => 'Cooling', 'dot' => 'bg-amber-400'],
                    default   => ['color' => 'red', 'label' => 'Dormant', 'dot' => 'bg-red-400'],
                };
                $isViewing = $primaryTrack && $ft->track_id === $primaryTrack->track_id;
            @endphp
            <div class="flex items-center gap-3 p-3 rounded-lg {{ $isViewing ? 'bg-violet-600/10 border border-violet-500/20' : 'bg-dark-800/50 border border-dark-700' }}">
                <span class="w-2.5 h-2.5 rounded-full {{ $ftHealth['dot'] }} flex-shrink-0 {{ $ft->health_status === 'active' ? 'animate-pulse' : '' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium truncate {{ $isViewing ? 'text-violet-300' : 'text-dark-200' }}">{{ $ft->track?->name ?? 'Unknown' }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] text-dark-500">{{ number_format($ft->score, 1) }}%</span>
                        <span class="text-[10px] text-{{ $ftHealth['color'] }}-400">{{ $ftHealth['label'] }}</span>
                        @if($ft->days_since_activity !== null && $ft->days_since_activity > 0)
                            <span class="text-[10px] text-dark-600">{{ $ft->days_since_activity }}d ago</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        {{-- Smart nudge banner --}}
        @php
            $dormantNudges = $fellowTracks->filter(fn($ft) => $ft->nudge !== null);
        @endphp
        @if($dormantNudges->count() > 0)
        <div class="mt-3 p-2.5 rounded-lg bg-amber-600/10 border border-amber-500/20">
            <p class="text-[11px] text-amber-400 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>
                    {{ $dormantNudges->count() }} track{{ $dormantNudges->count() > 1 ? 's need' : ' needs' }} attention. 
                    {{ $dormantNudges->take(2)->map(fn($ft) => $ft->track?->name)->implode(' & ') }}
                    {{ $dormantNudges->count() > 2 ? ' and ' . ($dormantNudges->count() - 2) . ' more' : '' }}.
                    <a href="{{ route('dashboard.track-comparison') }}" class="underline hover:text-amber-300">View recommendations</a>
                </span>
            </p>
        </div>
        @endif
    </div>
    @endif
    @else
    <!-- No tracks enrolled - CTA -->
    <div class="card p-6 text-center border-dashed border-2 border-dark-600">
        <div class="w-16 h-16 rounded-full bg-primary-600/20 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-white mb-2">Get Started with a Track</h3>
        <p class="text-dark-400 text-sm mb-4">Choose a career track to begin building your Career Capital and unlock opportunities.</p>
        <a href="{{ route('tracks.select') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
            Browse Available Tracks
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
    @endif

    <!-- Current Cohort Banner -->
    @if($currentCohort)
    <div class="card p-5 border-l-4 border-teal-500 bg-gradient-to-r from-teal-900/20 to-transparent">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-semibold text-white">{{ $currentCohort->name }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full 
                            {{ $currentCohort->status === 'active' ? 'bg-green-600/20 text-green-400' : 'bg-blue-600/20 text-blue-400' }}">
                            {{ ucfirst($currentCohort->status) }}
                        </span>
                    </div>
                    <p class="text-dark-400 text-sm">{{ $currentCohort->track?->name ?? 'Unknown Track' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $stats['cohortSize'] }}</p>
                    <p class="text-dark-500 text-xs">Fellows</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-teal-400">#{{ $stats['rank'] ?? '-' }}</p>
                    <p class="text-dark-500 text-xs">Your Rank</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $currentCohort->progress_percentage }}%</p>
                    <p class="text-dark-500 text-xs">Progress</p>
                </div>
                @if($currentCohort->days_remaining > 0)
                <div class="text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ $currentCohort->days_remaining }}</p>
                    <p class="text-dark-500 text-xs">Days Left</p>
                </div>
                @endif
            </div>
        </div>
        @if($currentCohort->status === 'active')
        <div class="mt-4">
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-dark-400">Cohort Progress</span>
                <span class="text-teal-400">Week {{ $currentCohort->current_week }} of {{ $currentCohort->duration_weeks }}</span>
            </div>
            <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-teal-600 to-teal-400 rounded-full transition-all duration-500" 
                     style="width: {{ $currentCohort->progress_percentage }}%"></div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Current Program Banner -->
    @if($currentProgram ?? null)
    <div class="card p-5 border-l-4 border-indigo-500 bg-gradient-to-r from-indigo-900/20 to-transparent">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-semibold text-white">{{ $currentProgram->name }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full 
                            {{ $currentProgram->status === 'active' ? 'bg-green-600/20 text-green-400' : 'bg-blue-600/20 text-blue-400' }}">
                            {{ ucfirst($currentProgram->status) }}
                        </span>
                    </div>
                    <p class="text-dark-400 text-sm">{{ $currentProgram->description ? Str::limit($currentProgram->description, 80) : 'Fellowship Program' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                @if($currentProgram->enrolled_count !== null)
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $currentProgram->enrolled_count }}</p>
                    <p class="text-dark-500 text-xs">Fellows</p>
                </div>
                @endif
                @if($programEnrollment?->certificate_issued_at)
                <div class="text-center">
                    <span class="inline-flex items-center gap-1 text-amber-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-semibold text-sm">Certified</span>
                    </span>
                </div>
                @endif
                @if($currentProgram->progress_percentage > 0)
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $currentProgram->progress_percentage }}%</p>
                    <p class="text-dark-500 text-xs">Progress</p>
                </div>
                @endif
                @if($currentProgram->days_remaining > 0)
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-400">{{ $currentProgram->days_remaining }}</p>
                    <p class="text-dark-500 text-xs">Days Left</p>
                </div>
                @endif
            </div>
        </div>
        @if($currentProgram->status === 'active' && $currentProgram->start_date && $currentProgram->end_date)
        <div class="mt-4">
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-dark-400">Program Progress</span>
                <span class="text-indigo-400">{{ $currentProgram->start_date->format('M j') }} - {{ $currentProgram->end_date->format('M j, Y') }}</span>
            </div>
            <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-indigo-600 to-indigo-400 rounded-full transition-all duration-500" 
                     style="width: {{ $currentProgram->progress_percentage }}%"></div>
            </div>
        </div>
        @endif
        @if($currentProgram->milestones && count($currentProgram->milestones) > 0)
        <div class="mt-4 pt-4 border-t border-dark-700">
            <div class="flex items-center gap-4 overflow-x-auto">
                <span class="text-dark-400 text-xs whitespace-nowrap">Milestones:</span>
                @foreach(array_slice($currentProgram->milestones, 0, 4) as $milestone)
                    @php
                        $isPast = isset($milestone['target_date']) && \Carbon\Carbon::parse($milestone['target_date'])->isPast();
                    @endphp
                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                        @if($isPast)
                            <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                        <span class="text-xs {{ $isPast ? 'text-dark-400 line-through' : 'text-dark-300' }}">{{ $milestone['name'] ?? 'Milestone' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Career Capital Overview -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Score Card -->
        <div class="lg:col-span-1">
            <div class="card p-6 h-full">
                <div class="text-center">
                    <h3 class="text-sm font-medium text-dark-400 mb-4">Your Career Capital Score</h3>
                    
                    @php
                        $currentScore = $primaryTrack?->score ?? 0;
                        $tier = $primaryTrack?->tier ?? 'rookie';
                        $tierClasses = [
                            'rookie' => 'bg-slate-600/20 text-slate-400',
                            'intern' => 'bg-blue-600/20 text-blue-400',
                            'professional' => 'bg-purple-600/20 text-purple-400',
                            'elite' => 'bg-amber-600/20 text-amber-400',
                        ];
                        $tierClass = $tierClasses[$tier] ?? $tierClasses['rookie'];
                    @endphp
                    
                    <!-- Score Circle -->
                    <div class="relative inline-flex items-center justify-center mb-4" x-data="{ score: 0 }" x-init="setTimeout(() => { let i = setInterval(() => { score += 2; if(score >= {{ $currentScore }}) { score = {{ $currentScore }}; clearInterval(i); } }, 30) }, 300)">
                        <svg class="w-40 h-40 transform -rotate-90">
                            <circle cx="80" cy="80" r="70" fill="none" stroke="#334155" stroke-width="10"/>
                            <circle cx="80" cy="80" r="70" fill="none" stroke="url(#scoreGradient)" stroke-width="10" stroke-linecap="round"
                                    :stroke-dasharray="`${score * 4.4} 440`"/>
                            <defs>
                                <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#7c3aed"/>
                                    <stop offset="50%" stop-color="#1e40af"/>
                                    <stop offset="100%" stop-color="#14b8a6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-4xl font-bold text-white" x-text="score"></span>
                            <span class="text-dark-400 text-sm">out of 100</span>
                        </div>
                    </div>
                    
                    <!-- Tier Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full {{ $tierClass }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-medium capitalize">{{ $tier }} Level</span>
                    </div>
                    
                    <p class="text-dark-400 text-sm mt-4">
                        @if($currentScore < 20)
                            You're just getting started! Complete more activities to grow.
                        @elseif($currentScore < 40)
                            Good progress! Keep building your skills.
                        @elseif($currentScore < 60)
                            You're developing nicely. Focus on interviews.
                        @elseif($currentScore < 80)
                            Impressive! You're becoming highly marketable.
                        @else
                            Outstanding! You're in the top tier of talent.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Pillar Breakdown -->
        <div class="lg:col-span-2">
            <div class="card p-6 h-full">
                <h3 class="text-lg font-semibold text-white mb-6">Career Capital Breakdown</h3>
                
                @if($scoreBreakdown)
                <div class="grid sm:grid-cols-2 gap-6">
                    <!-- Technical Skills -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-purple-600/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                </div>
                                <span class="text-dark-200 font-medium">Technical Skills</span>
                            </div>
                            <span class="text-purple-400 font-semibold">{{ number_format($scoreBreakdown['categories']['technical']['score'] ?? 0, 0) }}%</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-600 rounded-full transition-all duration-500" style="width: {{ $scoreBreakdown['categories']['technical']['score'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-dark-500">Projects, certifications & code quality</p>
                    </div>
                    
                    <!-- Interview Performance -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-600/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <span class="text-dark-200 font-medium">Interview Performance</span>
                            </div>
                            <span class="text-blue-400 font-semibold">{{ number_format($scoreBreakdown['categories']['interview']['score'] ?? 0, 0) }}%</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full transition-all duration-500" style="width: {{ $scoreBreakdown['categories']['interview']['score'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-dark-500">Mock interviews & communication</p>
                    </div>
                    
                    <!-- Portfolio -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-teal-600/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <span class="text-dark-200 font-medium">Portfolio</span>
                            </div>
                            <span class="text-teal-400 font-semibold">{{ number_format($scoreBreakdown['categories']['portfolio']['score'] ?? 0, 0) }}%</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-teal-600 rounded-full transition-all duration-500" style="width: {{ $scoreBreakdown['categories']['portfolio']['score'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-dark-500">Projects, blogs & open source</p>
                    </div>
                    
                    <!-- Collaboration -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-600/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <span class="text-dark-200 font-medium">Collaboration</span>
                            </div>
                            <span class="text-amber-400 font-semibold">{{ number_format($scoreBreakdown['categories']['collaboration']['score'] ?? 0, 0) }}%</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-600 rounded-full transition-all duration-500" style="width: {{ $scoreBreakdown['categories']['collaboration']['score'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-dark-500">Mentoring, peer reviews & workshops</p>
                    </div>
                    
                    <!-- Learning -->
                    <div class="space-y-2 sm:col-span-2 sm:max-w-[calc(50%-0.75rem)]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-rose-600/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <span class="text-dark-200 font-medium">Learning</span>
                            </div>
                            <span class="text-rose-400 font-semibold">{{ number_format($scoreBreakdown['categories']['learning']['score'] ?? 0, 0) }}%</span>
                        </div>
                        <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                            <div class="h-full bg-rose-600 rounded-full transition-all duration-500" style="width: {{ $scoreBreakdown['categories']['learning']['score'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-dark-500">Courses, certifications & workshops</p>
                    </div>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-dark-400">No track selected yet. Complete activities to see your breakdown.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Activities This Month -->
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Activities This Month</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $activitiesThisMonth }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 text-sm">
                @if($activityGrowth > 0)
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-green-400">+{{ $activityGrowth }}%</span>
                @elseif($activityGrowth < 0)
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                    <span class="text-red-400">{{ $activityGrowth }}%</span>
                @else
                    <span class="text-dark-500">No change</span>
                @endif
                <span class="text-dark-500">vs last month</span>
            </div>
        </div>
        
        <!-- Pending Activities -->
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Pending Review</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $pendingActivitiesCount }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 text-sm">
                <span class="text-dark-500">Awaiting mentor review</span>
            </div>
        </div>
        
        <!-- Current Streak -->
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Current Streak</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $streak }} days</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-orange-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 text-sm">
                <span class="text-dark-500">Keep it going!</span>
            </div>
        </div>
        
        <!-- Upcoming Interviews -->
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Upcoming Interviews</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $upcomingInterviews->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 text-sm">
                @if($upcomingInterviews->first())
                    <span class="text-blue-400">Next: {{ $upcomingInterviews->first()->scheduled_at?->format('M d') }}</span>
                @else
                    <span class="text-dark-500">None scheduled</span>
                @endif
            </div>
        </div>
        
        <!-- Cohort Rank -->
        @if($currentCohort)
        <div class="card p-5 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Cohort Rank</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        @if($stats['rank'])
                            #{{ $stats['rank'] }}
                        @else
                            <span class="text-dark-500">-</span>
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-1 mt-3 text-sm">
                <span class="text-dark-500">{{ $currentCohort->name }}</span>
                <span class="text-dark-600">•</span>
                <span class="text-teal-400">{{ $stats['cohortSize'] }} fellows</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Two Column Layout -->
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Activities -->
        <div class="card">
            <div class="p-5 border-b border-dark-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Recent Activities</h3>
                    <a href="{{ route('activities.index') }}" class="text-sm text-primary-400 hover:text-primary-300 transition-colors">
                        View All →
                    </a>
                </div>
            </div>
            <div class="divide-y divide-dark-700">
                @forelse($recentActivities as $activity)
                    <div class="p-4 flex items-center gap-4 hover:bg-dark-800/50 transition-colors">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            @switch($activity->type)
                                @case('project') bg-teal-600/20 text-teal-400 @break
                                @case('certification') bg-amber-600/20 text-amber-400 @break
                                @case('workshop') bg-purple-600/20 text-purple-400 @break
                                @case('course') bg-rose-600/20 text-rose-400 @break
                                @case('blog_post') bg-blue-600/20 text-blue-400 @break
                                @default bg-dark-700 text-dark-400
                            @endswitch">
                            @switch($activity->type)
                                @case('project')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    @break
                                @case('certification')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    @break
                                @default
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                            @endswitch
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-dark-100 font-medium truncate">{{ $activity->title }}</p>
                            <p class="text-dark-500 text-sm">{{ $activity->type->label() }} • {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                        <div>
                            @if($activity->status->value === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600/20 text-green-400">
                                    +{{ $activity->points_earned ?? 0 }} pts
                                </span>
                            @elseif($activity->status->value === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-600/20 text-amber-400">
                                    Pending
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-dark-600 text-dark-400">
                                    {{ $activity->status->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-dark-400 mb-4">No activities yet</p>
                        <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Start Your First Activity
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Notifications & Upcoming -->
        <div class="space-y-6">
            <!-- Upcoming Interviews -->
            <div class="card">
                <div class="p-5 border-b border-dark-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Upcoming Interviews</h3>
                        <a href="{{ route('interviews.index') }}" class="text-sm text-primary-400 hover:text-primary-300 transition-colors">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-dark-700">
                    @forelse($upcomingInterviews as $interview)
                        <div class="p-4 flex items-center gap-4 hover:bg-dark-800/50 transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-dark-100 font-medium truncate">{{ ucfirst(str_replace('_', ' ', $interview->type->value)) }}</p>
                                <p class="text-dark-500 text-sm">{{ $interview->scheduled_at->format('M d, Y - g:i A') }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600/20 text-blue-400">
                                Scheduled
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-dark-400 mb-3">No upcoming interviews</p>
                            <a href="{{ route('interviews.index') }}" class="text-sm text-primary-400 hover:text-primary-300">
                                Schedule one →
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="card">
                <div class="p-5 border-b border-dark-700">
                    <h3 class="text-lg font-semibold text-white">Notifications</h3>
                </div>
                <div class="divide-y divide-dark-700">
                    @forelse($notifications as $notification)
                        <div class="p-4 flex items-start gap-3 hover:bg-dark-800/50 transition-colors">
                            <div class="w-2 h-2 rounded-full bg-primary-500 mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-dark-200 text-sm">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                <p class="text-dark-500 text-xs mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-dark-400">No new notifications</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Pillars Alert -->
    @if(count($missingPillars) > 0)
    <div class="card p-5 border-l-4 border-amber-500 bg-amber-900/10">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-amber-400 font-semibold">Boost Your Career Capital</h4>
                <p class="text-dark-300 text-sm mt-1">
                    Focus on these areas to improve your score:
                    <span class="text-amber-400 font-medium">{{ implode(', ', $missingPillars) }}</span>
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Weekly Progress -->
    @if($weeklyProgressHistory && count($weeklyProgressHistory) > 0)
    <div class="card p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Weekly Progress</h3>
            <div class="flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-primary-600"></div>
                    <span class="text-dark-400">Score Progress</span>
                </div>
            </div>
        </div>
        
        <div class="h-32 flex items-end justify-between gap-2">
            @foreach($weeklyProgressHistory->reverse() as $progress)
                @php
                    $height = min(100, max(10, ($progress->score_change ?? 0) + 50));
                @endphp
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="w-full bg-primary-600/60 hover:bg-primary-600 rounded-t transition-colors" style="height: {{ $height }}px" title="Week {{ $progress->week_number }}: {{ $progress->score_change > 0 ? '+' : '' }}{{ $progress->score_change ?? 0 }}"></div>
                    <span class="text-xs text-dark-500">W{{ $progress->week_number }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
