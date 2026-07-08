{{--
    Global Track Switcher v2 — Appears in the top navbar for fellows.
    
    Features:
    - Session-based instant track switching
    - Health Pulse indicators (green/yellow/red dots)
    - Quick Actions per track (activity, interview, curriculum)
    - Keyboard shortcut (Ctrl+K) to open
    - Smart Track Nudges for dormant tracks
    - Cross-Track Achievement Badges
    
    Powered by ResolveActiveTrack middleware which shares:
    - $activeTrack       (FellowTrack|null)
    - $fellowTracks      (Collection of FellowTrack with health metadata)
    - $trackSwitcherMeta (array — achievements, stats)
    
    @version 2.0
--}}

@if(auth()->check() && auth()->user()->hasRole('fellow') && isset($fellowTracks) && $fellowTracks->count() > 0)
<div x-data="trackSwitcher()" class="relative" @click.outside="open = false" @keydown.window.ctrl.k.prevent="open = !open">
    {{-- Trigger Button --}}
    <button 
        @click="open = !open" 
        class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg border transition-all duration-200"
        :class="open 
            ? 'bg-violet-600/20 border-violet-500/40 text-violet-300' 
            : 'bg-dark-800/60 border-dark-700 text-dark-200 hover:bg-dark-800 hover:border-dark-600 hover:text-dark-100'"
        title="Switch Track (Ctrl+K)"
    >
        {{-- Track Icon with Health Pulse --}}
        <div class="relative">
            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            {{-- Health Pulse Dot --}}
            @if($activeTrack)
                @php
                    $pulseStatus = $activeTrack->health_status ?? 'dormant';
                    $pulseColor = match($pulseStatus) {
                        'active'  => 'bg-emerald-400',
                        'cooling' => 'bg-amber-400',
                        default   => 'bg-red-400',
                    };
                @endphp
                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full {{ $pulseColor }} ring-2 ring-dark-900 {{ $pulseStatus === 'active' ? 'animate-pulse' : '' }}"></span>
            @endif
        </div>

        {{-- Track Name & Score --}}
        <div class="hidden sm:flex flex-col items-start leading-tight">
            <span class="text-xs font-semibold truncate max-w-[140px]">
                {{ $activeTrack?->track?->name ?? 'No Track' }}
            </span>
            @if($activeTrack)
            <span class="text-[10px] text-dark-400">
                {{ number_format($activeTrack->score, 1) }}% · {{ ucfirst($activeTrack->tier ?? 'rookie') }}
            </span>
            @endif
        </div>

        {{-- Chevron --}}
        @if($fellowTracks->count() > 1)
        <svg class="w-3.5 h-3.5 text-dark-400 transition-transform duration-200" 
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        @endif

        {{-- Keyboard Hint --}}
        <kbd class="hidden lg:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] text-dark-500 bg-dark-700/60 border border-dark-600 rounded font-mono">
            Ctrl+K
        </kbd>
    </button>

    {{-- Dropdown Panel --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        x-cloak
        class="absolute top-full right-0 mt-2 w-96 bg-dark-900 border border-dark-700 rounded-xl shadow-2xl shadow-black/40 z-50 overflow-hidden"
    >
        {{-- Header with achievements --}}
        <div class="px-4 py-3 border-b border-dark-700 bg-dark-800/50">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-dark-100">Switch Track</h4>
                    <p class="text-[11px] text-dark-500 mt-0.5">
                        {{ $fellowTracks->count() }} track{{ $fellowTracks->count() > 1 ? 's' : '' }} · Avg {{ $trackSwitcherMeta['averageScore'] ?? 0 }}%
                    </p>
                </div>
                <div class="flex items-center gap-1">
                    @foreach(($trackSwitcherMeta['achievements'] ?? []) as $achievement)
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-dark-700 text-sm cursor-help"
                              title="{{ $achievement['name'] }}: {{ $achievement['description'] }}">
                            {{ $achievement['icon'] }}
                        </span>
                    @endforeach
                    @if(empty($trackSwitcherMeta['achievements'] ?? []))
                        <span class="text-[10px] text-dark-600 italic">No badges yet</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Track List --}}
        <div class="p-2 max-h-[400px] overflow-y-auto">
            @foreach($fellowTracks as $ft)
            @php
                $isActive = $activeTrack && $ft->track_id === $activeTrack->track_id;
                $healthColor = match($ft->health_status ?? 'dormant') {
                    'active'  => 'emerald',
                    'cooling' => 'amber',
                    default   => 'red',
                };
                $healthLabel = match($ft->health_status ?? 'dormant') {
                    'active'  => 'Active',
                    'cooling' => 'Cooling',
                    default   => 'Dormant',
                };
                $healthBg = match($ft->health_status ?? 'dormant') {
                    'active'  => 'bg-emerald-600/20 text-emerald-400',
                    'cooling' => 'bg-amber-600/20 text-amber-400',
                    default   => 'bg-red-600/20 text-red-400',
                };
                $healthDot = match($ft->health_status ?? 'dormant') {
                    'active'  => 'bg-emerald-400',
                    'cooling' => 'bg-amber-400',
                    default   => 'bg-red-400',
                };
            @endphp
            <div class="relative mb-1">
                @if($isActive)
                    {{-- ═══ Active Track (expanded card) ═══ --}}
                    <div class="p-3 rounded-lg bg-violet-600/10 border border-violet-500/30">
                        <div class="flex items-start gap-3">
                            {{-- Icon with health --}}
                            <div class="relative flex-shrink-0">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full {{ $healthDot }} ring-2 ring-dark-900 {{ $ft->health_status === 'active' ? 'animate-pulse' : '' }}"></span>
                            </div>

                            <div class="flex-1 min-w-0">
                                {{-- Title row --}}
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm font-semibold text-violet-300 truncate">{{ $ft->track?->name ?? 'Unknown' }}</span>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-violet-600/20 text-violet-400">Active</span>
                                    @if($ft->is_primary)
                                        <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-emerald-600/20 text-emerald-400">Primary</span>
                                    @endif
                                    <span class="px-1.5 py-0.5 text-[9px] font-medium rounded {{ $healthBg }}">{{ $healthLabel }}</span>
                                </div>

                                {{-- Stats row --}}
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs text-dark-400">
                                        Score: <span class="text-white font-medium">{{ number_format($ft->score, 1) }}%</span>
                                    </span>
                                    <span class="text-xs text-dark-400">
                                        Tier: <span class="text-white font-medium capitalize">{{ $ft->tier ?? 'rookie' }}</span>
                                    </span>
                                    @if($ft->days_since_activity !== null)
                                        <span class="text-xs text-dark-500">
                                            {{ $ft->days_since_activity === 0 ? 'Active today' : $ft->days_since_activity . 'd ago' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Mini score bars --}}
                                <div class="grid grid-cols-3 gap-2 mt-2">
                                    <div>
                                        <div class="text-[9px] text-dark-500 mb-0.5">Technical</div>
                                        <div class="h-1 bg-dark-700 rounded-full"><div class="h-1 bg-purple-500 rounded-full" style="width: {{ min(100, $ft->technical_score ?? 0) }}%"></div></div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] text-dark-500 mb-0.5">Interview</div>
                                        <div class="h-1 bg-dark-700 rounded-full"><div class="h-1 bg-blue-500 rounded-full" style="width: {{ min(100, $ft->interview_score ?? 0) }}%"></div></div>
                                    </div>
                                    <div>
                                        <div class="text-[9px] text-dark-500 mb-0.5">Portfolio</div>
                                        <div class="h-1 bg-dark-700 rounded-full"><div class="h-1 bg-teal-500 rounded-full" style="width: {{ min(100, $ft->portfolio_score ?? 0) }}%"></div></div>
                                    </div>
                                </div>

                                {{-- Quick Actions --}}
                                <div class="flex items-center gap-2 mt-3 pt-2 border-t border-violet-500/20">
                                    <a href="{{ route('activities.create', ['track_id' => $ft->track_id]) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-violet-300 bg-violet-600/10 hover:bg-violet-600/20 rounded-md transition-colors"
                                       title="Log a new activity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Activity
                                    </a>
                                    <a href="{{ route('interviews.create', ['track_id' => $ft->track_id]) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-blue-300 bg-blue-600/10 hover:bg-blue-600/20 rounded-md transition-colors"
                                       title="Schedule an interview">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Interview
                                    </a>
                                    <a href="{{ route('curriculum.track', $ft->track_id) }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-teal-300 bg-teal-600/10 hover:bg-teal-600/20 rounded-md transition-colors"
                                       title="View curriculum">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        Curriculum
                                    </a>
                                    @if($fellowTracks->count() > 1)
                                    <a href="{{ route('dashboard.track-comparison') }}" 
                                       class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium text-amber-300 bg-amber-600/10 hover:bg-amber-600/20 rounded-md transition-colors"
                                       title="Compare all tracks">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        Compare
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Smart Nudge --}}
                        @if($ft->nudge)
                        <div class="mt-2 p-2 rounded-md bg-amber-600/10 border border-amber-500/20">
                            <p class="text-[10px] text-amber-400 flex items-center gap-1.5">
                                <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $ft->nudge }}
                            </p>
                        </div>
                        @endif
                    </div>
                @else
                    {{-- ═══ Inactive Track (clickable to switch) ═══ --}}
                    <form method="POST" action="{{ route('tracks.switch-active') }}">
                        @csrf
                        <input type="hidden" name="track_id" value="{{ $ft->track_id }}">
                        <button type="submit" class="w-full text-left p-3 rounded-lg hover:bg-dark-800 border border-transparent hover:border-dark-600 transition-all duration-150 group">
                            <div class="flex items-start gap-3">
                                {{-- Icon with health pulse --}}
                                <div class="relative flex-shrink-0">
                                    <div class="w-9 h-9 rounded-lg bg-dark-700 group-hover:bg-dark-600 flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 text-dark-400 group-hover:text-dark-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full {{ $healthDot }} ring-2 ring-dark-900 {{ $ft->health_status === 'active' ? 'animate-pulse' : '' }}"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-sm font-medium text-dark-200 group-hover:text-white truncate transition-colors">{{ $ft->track?->name ?? 'Unknown' }}</span>
                                        @if($ft->is_primary)
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded bg-emerald-600/20 text-emerald-400">Primary</span>
                                        @endif
                                        <span class="px-1.5 py-0.5 text-[9px] font-medium rounded {{ $healthBg }}">{{ $healthLabel }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-dark-500">
                                            Score: <span class="text-dark-300 font-medium">{{ number_format($ft->score, 1) }}%</span>
                                        </span>
                                        <span class="text-xs text-dark-500">
                                            Tier: <span class="text-dark-300 font-medium capitalize">{{ $ft->tier ?? 'rookie' }}</span>
                                        </span>
                                        @if($ft->days_since_activity !== null)
                                            <span class="text-[10px] text-dark-600">
                                                {{ $ft->days_since_activity === 0 ? 'Today' : $ft->days_since_activity . 'd ago' }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-dark-600">Never</span>
                                        @endif
                                    </div>

                                    {{-- Nudge for dormant --}}
                                    @if($ft->nudge)
                                    <p class="text-[10px] text-amber-500/80 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        {{ $ft->nudge }}
                                    </p>
                                    @endif
                                </div>
                                <svg class="w-4 h-4 text-dark-600 group-hover:text-dark-400 mt-1 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    </form>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Footer Actions --}}
        <div class="px-4 py-3 border-t border-dark-700 bg-dark-800/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('tracks.select') }}" class="inline-flex items-center gap-1.5 text-xs text-primary-400 hover:text-primary-300 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Track
                    </a>
                    @if($fellowTracks->count() > 1)
                    <a href="{{ route('dashboard.track-comparison') }}" class="inline-flex items-center gap-1.5 text-xs text-amber-400 hover:text-amber-300 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Compare
                    </a>
                    @endif
                </div>
                @if($activeTrack && !$activeTrack->is_primary)
                    <form method="POST" action="{{ route('tracks.switch-primary') }}" class="inline">
                        @csrf
                        <input type="hidden" name="track_id" value="{{ $activeTrack->track_id }}">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-emerald-400 hover:text-emerald-300 transition-colors"
                                onclick="return confirm('Set {{ $activeTrack->track?->name }} as your permanent primary track? (Max 2 switches per quarter)')">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Set as Primary
                        </button>
                    </form>
                @endif
            </div>
            <p class="text-[10px] text-dark-600 mt-2">Press <kbd class="px-1 py-0.5 bg-dark-700 rounded text-dark-400 font-mono">Ctrl+K</kbd> to toggle · Session-based switching</p>
        </div>
    </div>
</div>

<script>
    function trackSwitcher() {
        return {
            open: false,
        }
    }
</script>
@endif
