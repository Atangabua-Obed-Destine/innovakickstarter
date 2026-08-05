@extends('layouts.app')

@section('title', 'Fellow: ' . $fellow->name)

@section('content')
<div class="space-y-6" x-data="fellowManager()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.fellows.index') }}" class="hover:text-white">Fellows</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">{{ $fellow->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">Fellow Profile</h1>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('admin.fellows.toggle-status', $fellow) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn {{ $fellow->is_active ? 'btn-outline text-amber-400 border-amber-400 hover:bg-amber-400/10' : 'btn-primary' }}">
                    @if($fellow->is_active)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Deactivate
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activate
                    @endif
                </button>
            </form>
            <form action="{{ route('admin.fellows.impersonate', $fellow) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline border-blue-500 text-blue-400 hover:bg-blue-500/10" title="Impersonate Fellow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Impersonate
                </button>
            </form>
            <a href="{{ route('admin.fellows.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Header Card -->
            <div class="card p-6">
                <div class="flex items-start gap-6">
                    <!-- Avatar -->
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary-500 to-blue-600 flex items-center justify-center text-3xl font-bold text-white shadow-lg">
                        {{ strtoupper(substr($fellow->name, 0, 2)) }}
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-2xl font-bold text-white">{{ $fellow->name }}</h2>
                            <span class="badge {{ $fellow->is_active ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-red-600/20 text-red-400 border-red-500/30' }}">
                                {{ $fellow->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-dark-400 mb-3">{{ $fellow->email }}</p>
                        
                        @php
                            $primaryTrack = $fellow->fellowTracks->firstWhere('is_primary', true);
                        @endphp

                        @if($primaryTrack)
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    <span class="text-dark-200">{{ $primaryTrack->track->name ?? 'No Track' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-primary-400">{{ number_format($primaryTrack->score ?? 0, 3) }}%</span>
                                    <span class="badge bg-{{ $primaryTrack->tier_enum->color() }}-600/20 text-{{ $primaryTrack->tier_enum->color() }}-400 border-{{ $primaryTrack->tier_enum->color() }}-500/30">
                                        {{ ucfirst($primaryTrack->tier ?? 'Rookie') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Stats -->
                    <div class="text-right space-y-1">
                        <p class="text-dark-500 text-sm">Joined</p>
                        <p class="text-dark-200">{{ $fellow->created_at->format('M j, Y') }}</p>
                        <p class="text-dark-500 text-sm mt-3">Last Active</p>
                        <p class="text-dark-200">{{ $fellow->last_login_at ? $fellow->last_login_at->diffForHumans() : 'Never' }}</p>
                    </div>
                </div>
            </div>

            @if($fellow->internshipProfile)
                @php
                    $ip = $fellow->internshipProfile;
                    $ipStyles = [
                        'pending'        => ['label' => 'Pending Review', 'class' => 'bg-amber-600/20 text-amber-400 border-amber-500/30'],
                        'needs_revision' => ['label' => 'Needs Revision', 'class' => 'bg-orange-600/20 text-orange-400 border-orange-500/30'],
                        'approved'       => ['label' => 'Approved',       'class' => 'bg-green-600/20 text-green-400 border-green-500/30'],
                        'rejected'       => ['label' => 'Rejected',       'class' => 'bg-red-600/20 text-red-400 border-red-500/30'],
                        'active'         => ['label' => 'Active',         'class' => 'bg-blue-600/20 text-blue-400 border-blue-500/30'],
                        'completed'      => ['label' => 'Completed',      'class' => 'bg-primary-600/20 text-primary-400 border-primary-500/30'],
                        'withdrawn'      => ['label' => 'Withdrawn',      'class' => 'bg-dark-600/40 text-dark-300 border-dark-500/30'],
                    ];
                    $ips = $ipStyles[$ip->status] ?? ['label' => ucfirst($ip->status), 'class' => 'bg-dark-700 text-dark-300'];
                @endphp
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Internship Profile
                            <span class="badge {{ $ips['class'] }}">{{ $ips['label'] }}</span>
                        </h3>
                        <a href="{{ route('admin.internships.show', $ip) }}" class="text-primary-400 hover:text-primary-300 text-sm">Open review →</a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Type</dt>
                            <dd class="text-dark-200">{{ ucfirst($ip->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Institution</dt>
                            <dd class="text-dark-200">{{ $ip->institution_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Supervisor</dt>
                            <dd class="text-dark-200">{{ $ip->supervisor_name }}<br><span class="text-dark-500 text-xs">{{ $ip->supervisor_email }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-dark-500 text-xs uppercase tracking-wide">Duration</dt>
                            <dd class="text-dark-200">{{ $ip->duration_label }}</dd>
                        </div>
                    </dl>
                    @if($ip->approved_start_date && $ip->approved_end_date)
                        @php
                            $progress = $ip->progress_percent ?? 0;
                            $bar = $ip->is_expired ? 'from-dark-500 to-dark-600'
                                 : ($progress >= 80 ? 'from-amber-500 to-red-500'
                                 : 'from-primary-500 to-teal-500');
                        @endphp
                        <div class="mt-4 pt-4 border-t border-dark-700">
                            <div class="flex justify-between text-xs text-dark-400 mb-1">
                                <span>{{ $ip->approved_start_date->format('M j, Y') }}</span>
                                <span>{{ $ip->approved_end_date->format('M j, Y') }}</span>
                            </div>
                            <div class="h-2 bg-dark-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r {{ $bar }}" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-[11px] text-dark-500 mt-1">
                                {{ $ip->days_elapsed ?? 0 }}/{{ $ip->total_days ?? 0 }} days
                                @if($ip->is_expired)
                                    · ended {{ $ip->approved_end_date->diffForHumans() }}
                                @else
                                    · {{ $ip->days_remaining ?? 0 }} days remaining
                                @endif
                            </p>
                        </div>
                    @endif
                    @if($ip->reviewed_at)
                        <p class="mt-4 text-xs text-dark-500">Last reviewed by {{ $ip->reviewer?->name ?? 'admin' }} on {{ $ip->reviewed_at->format('M j, Y H:i') }}</p>
                    @endif
                </div>
            @endif

            <!-- Career Capital Breakdown by Track -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Career Capital by Track
                </h3>

                @if($fellow->fellowTracks->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($fellow->fellowTracks as $fellowTrack)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
                                    'needs_revision' => 'bg-orange-600/20 text-orange-400 border-orange-500/30',
                                    'approved' => 'bg-green-600/20 text-green-400 border-green-500/30',
                                    'rejected' => 'bg-red-600/20 text-red-400 border-red-500/30',
                                ];
                                $statusColor = $statusColors[$fellowTrack->status] ?? 'bg-dark-600/20 text-dark-300 border-dark-500/30';
                            @endphp
                            <div class="p-4 bg-dark-800 rounded-lg {{ $fellowTrack->is_primary ? 'ring-2 ring-primary-500' : '' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-medium text-dark-200">{{ $fellowTrack->track->name ?? 'Unknown Track' }}</span>
                                        @if($fellowTrack->is_primary)
                                            <span class="badge bg-primary-600/20 text-primary-400 border-primary-500/30 text-xs">Primary</span>
                                        @endif
                                        <span class="badge {{ $statusColor }} text-xs">{{ ucfirst(str_replace('_', ' ', $fellowTrack->status ?? 'approved')) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xl font-bold text-white">{{ number_format($fellowTrack->score ?? 0, 3) }}%</span>
                                        <span class="badge badge-{{ $fellowTrack->tier ?? 'rookie' }}">{{ ucfirst($fellowTrack->tier ?? 'Rookie') }}</span>
                                        
                                        <!-- Actions -->
                                        <div class="flex items-center gap-1 ml-2">
                                            @if(!$fellowTrack->is_primary && ($fellowTrack->status ?? 'approved') === 'approved')
                                            <form action="{{ route('admin.fellows.make-primary', [$fellow, $fellowTrack->track_id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-blue-400 hover:text-blue-300 hover:bg-blue-400/10 rounded transition-colors" title="Make Primary">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.fellows.remove-track', [$fellow, $fellowTrack->track_id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this track? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-400/10 rounded transition-colors" title="Remove Track">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(($fellowTrack->status ?? 'approved') === 'approved')
                                    <!-- Progress Bar -->
                                    <div class="h-3 bg-dark-700 rounded-full overflow-hidden mb-3">
                                        <div class="h-full bg-gradient-to-r from-primary-600 to-blue-500 rounded-full transition-all duration-500"
                                             style="width: {{ min($fellowTrack->score ?? 0, 100) }}%"></div>
                                    </div>

                                    <!-- Category Breakdown -->
                                    @if($fellowTrack->category_scores)
                                        <div class="grid grid-cols-5 gap-2">
                                            @foreach($fellowTrack->category_scores as $category => $score)
                                                <div class="text-center">
                                                    <div class="text-xs text-dark-500 mb-1">{{ ucfirst($category) }}</div>
                                                    <div class="text-sm font-medium text-dark-200">{{ $score }}%</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-dark-400 mt-2">
                                        This track enrollment is currently {{ str_replace('_', ' ', $fellowTrack->status) }}.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-dark-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-dark-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p>No tracks enrolled yet</p>
                    </div>
                @endif
            </div>

            <!-- Recent Activities -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Recent Activities
                    </h3>
                    <a href="{{ route('admin.activities.queue') }}?fellow_id={{ $fellow->id }}" class="text-primary-400 hover:text-primary-300 text-sm">
                        View All →
                    </a>
                </div>

                @if($fellow->activities->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($fellow->activities as $activity)
                            <div class="flex items-center gap-4 p-3 bg-dark-800 rounded-lg">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg
                                    @if($activity->status === 'approved') bg-green-600/20 text-green-400
                                    @elseif($activity->status === 'pending') bg-amber-600/20 text-amber-400
                                    @else bg-red-600/20 text-red-400
                                    @endif">
                                    {{ $activity->type?->icon() ?? '📝' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-dark-200 truncate">{{ $activity->title }}</p>
                                    <p class="text-sm text-dark-500">{{ $activity->type?->label() ?? 'Activity' }} • {{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($activity->points_earned > 0)
                                        <span class="text-green-400 font-medium">+{{ $activity->points_earned }} pts</span>
                                    @endif
                                    <span class="badge 
                                        @if($activity->status === 'approved') bg-green-600/20 text-green-400 border-green-500/30
                                        @elseif($activity->status === 'pending') bg-amber-600/20 text-amber-400 border-amber-500/30
                                        @else bg-red-600/20 text-red-400 border-red-500/30
                                        @endif">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-dark-500">
                        <p>No activities submitted yet</p>
                    </div>
                @endif
            </div>

            <!-- Interview Sessions -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                    Interview Sessions
                </h3>

                @if($fellow->interviewSessions->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($fellow->interviewSessions as $interview)
                            <div class="flex items-center gap-4 p-3 bg-dark-800 rounded-lg">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                    @if($interview->mode->value === 'ai') bg-purple-600/20 text-purple-400
                                    @else bg-blue-600/20 text-blue-400
                                    @endif">
                                    @if($interview->mode->value === 'ai')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-dark-200">{{ $interview->type->label() }}</p>
                                    <p class="text-sm text-dark-500">{{ ucfirst($interview->mode->value) }} • {{ $interview->created_at->format('M j, Y') }}</p>
                                </div>
                                @if($interview->score)
                                    <div class="text-right">
                                        <span class="text-lg font-bold {{ $interview->score >= 70 ? 'text-green-400' : ($interview->score >= 50 ? 'text-amber-400' : 'text-red-400') }}">
                                            {{ $interview->score }}%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-dark-500">
                        <p>No interviews completed yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button @click="showScoreModal = true" class="btn btn-outline w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Adjust Score Manually
                    </button>
                    <button class="btn btn-outline w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Email
                    </button>
                    <button class="btn btn-outline w-full justify-start">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Reset Password
                    </button>
                </div>
            </div>

            <!-- Weekly Progress -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Weekly Progress</h3>
                
                @if($fellow->weeklyProgress->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($fellow->weeklyProgress->take(4) as $week)
                            <div class="p-3 bg-dark-800 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-dark-400">Week of {{ $week->week_start->format('M j') }}</span>
                                    @if($week->all_pillars_completed)
                                        <span class="badge bg-green-600/20 text-green-400">Complete</span>
                                    @else
                                        <span class="badge bg-amber-600/20 text-amber-400">Incomplete</span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <div class="text-xl {{ $week->build_completed ? 'text-green-400' : 'text-dark-600' }}">
                                        {{ $week->build_completed ? '✓' : '○' }}
                                        <div class="text-[10px] text-dark-500">Build</div>
                                    </div>
                                    <div class="text-xl {{ $week->brand_completed ? 'text-green-400' : 'text-dark-600' }}">
                                        {{ $week->brand_completed ? '✓' : '○' }}
                                        <div class="text-[10px] text-dark-500">Brand</div>
                                    </div>
                                    <div class="text-xl {{ $week->interview_completed ? 'text-green-400' : 'text-dark-600' }}">
                                        {{ $week->interview_completed ? '✓' : '○' }}
                                        <div class="text-[10px] text-dark-500">Interview</div>
                                    </div>
                                    <div class="text-xl {{ $week->collaborate_completed ? 'text-green-400' : 'text-dark-600' }}">
                                        {{ $week->collaborate_completed ? '✓' : '○' }}
                                        <div class="text-[10px] text-dark-500">Collab</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-dark-500">
                        <p>No weekly progress data</p>
                    </div>
                @endif
            </div>

            <!-- Audit Log -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Recent Changes</h3>
                
                @if($auditLogs->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($auditLogs->take(5) as $log)
                            <div class="p-3 bg-dark-800 rounded-lg text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-dark-200">{{ $log->action }}</span>
                                    <span class="text-dark-500">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-dark-400">{{ Str::limit($log->justification, 80) }}</p>
                                @if($log->admin)
                                    <p class="text-dark-500 text-xs mt-1">by {{ $log->admin->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-dark-500">
                        <p>No audit history</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Score Adjustment Modal -->
    <div x-show="showScoreModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showScoreModal = false">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60" @click="showScoreModal = false"></div>
            
            <div class="relative bg-dark-800 rounded-2xl shadow-xl max-w-lg w-full p-6 border border-dark-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-white">Adjust Career Capital Score</h3>
                    <button @click="showScoreModal = false" class="text-dark-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="form-label">Select Track</label>
                        <select name="track_id" class="form-input">
                            @foreach($fellow->fellowTracks as $ft)
                                <option value="{{ $ft->track_id }}">
                                    {{ $ft->track->name ?? 'Unknown' }} 
                                    ({{ ucfirst(str_replace('_', ' ', $ft->status ?? 'approved')) }} - Current: {{ number_format($ft->score ?? 0, 3) }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">New Score (%)</label>
                        <input type="number" name="new_score" class="form-input" min="0" max="100" step="0.001" required>
                    </div>

                    <div>
                        <label class="form-label">Justification (Required)</label>
                        <textarea name="justification" rows="3" class="form-input" minlength="10" required
                                  placeholder="Explain why this adjustment is being made..."></textarea>
                        <p class="text-xs text-dark-500 mt-1">This will be logged in the audit trail</p>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showScoreModal = false" class="btn btn-outline flex-1">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary flex-1">
                            Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fellowManager() {
    return {
        showScoreModal: false
    }
}
</script>
@endpush
@endsection
