@php
    $user = auth()->user();
    $currentRoute = request()->route()?->getName() ?? '';
@endphp

{{-- Fellow Navigation --}}
@if($user->hasRole('fellow'))
    <div class="space-y-1">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Main</p>
        
        <a href="{{ route('dashboard') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'dashboard') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('activities.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'activities') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span>Activities</span>
        </a>

        <a href="{{ route('interviews.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'interviews') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Mock Interviews</span>
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Progress</p>
        
        <a href="{{ route('curriculum.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'curriculum') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Curriculum</span>
        </a>

        <a href="{{ route('weekly-progress.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'weekly-progress') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Weekly Check-in</span>
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Account</p>
        
        <a href="{{ route('profile.show') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'profile') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>My Profile</span>
        </a>

        <a href="{{ route('fees.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'fees') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>My Fees</span>
        </a>

        <a href="{{ route('attendance.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'attendance') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>My Attendance</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
            @csrf
            <button type="submit" class="nav-link w-full text-left text-red-400 hover:bg-red-500/10 hover:text-red-300 mt-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>

{{-- Admin Navigation --}}
@elseif($user->hasRole('admin'))
    <div class="space-y-1">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Overview</p>
        
        <a href="{{ route('admin.dashboard') }}" 
           class="nav-link {{ $currentRoute === 'admin.dashboard' ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Dashboard</span>
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Management</p>
        
        <a href="{{ route('admin.activities.queue') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.activities') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Activity Review</span>
            @php
                $pendingCount = \App\Models\Activity::where('status', 'pending')->count();
            @endphp
            @if($pendingCount > 0)
                <span class="ml-auto badge badge-warning">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.tracks.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.tracks') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Tracks</span>
        </a>

        <a href="{{ route('admin.cohorts.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.cohorts') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Cohorts</span>
            @php
                $activeCohorts = \App\Models\Cohort::where('status', 'active')->count();
            @endphp
            @if($activeCohorts > 0)
                <span class="ml-auto badge badge-success">{{ $activeCohorts }}</span>
            @endif
        </a>

        <a href="{{ route('admin.programs.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.programs') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <span>Programs</span>
            @php
                $activePrograms = \App\Models\Program::whereIn('status', ['active', 'enrolling'])->count();
            @endphp
            @if($activePrograms > 0)
                <span class="ml-auto badge badge-primary">{{ $activePrograms }}</span>
            @endif
        </a>

        <a href="{{ route('admin.fellows.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.fellows') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Fellows</span>
        </a>

        <a href="{{ route('admin.internships.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'admin.internships') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Internships</span>
            @php
                $pendingInternships = \App\Models\InternshipProfile::whereIn('status', ['pending', 'needs_revision'])
                    ->whereHas('fellow', fn ($u) => $u->whereNotNull('onboarding_completed_at'))
                    ->count();
            @endphp
            @if($pendingInternships > 0)
                <span class="ml-auto badge badge-warning">{{ $pendingInternships }}</span>
            @endif
        </a>

        <a href="{{ route('admin.track-enrollments.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'admin.track-enrollments') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Track Enrollments</span>
            @php
                $pendingEnrollments = \App\Models\FellowTrack::whereIn('status', ['pending', 'needs_revision'])->count();
            @endphp
            @if($pendingEnrollments > 0)
                <span class="ml-auto badge badge-warning">{{ $pendingEnrollments }}</span>
            @endif
        </a>

        <a href="{{ route('admin.recruiters.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.recruiters') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span>Recruiters</span>
        </a>

        <a href="{{ route('admin.mentors.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.mentors') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Mentors</span>
        </a>

        <a href="{{ route('admin.curriculum.reviews') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.curriculum') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Curriculum</span>
            @php
                $pendingReviews = \App\Models\FellowCurriculumProgress::where('status', 'under_review')->count();
            @endphp
            @if($pendingReviews > 0)
                <span class="ml-auto badge badge-warning">{{ $pendingReviews }}</span>
            @endif
        </a>

        <a href="{{ route('admin.fees.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.fees') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Fee Reports</span>
        </a>

        <a href="{{ route('admin.payment-verifications.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.payment-verifications') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Payment Verifications</span>
            @php
                $pendingVerifications = \App\Models\FeePayment::where('status', 'submitted')->count();
            @endphp
            @if($pendingVerifications > 0)
                <span class="ml-auto badge badge-warning">{{ $pendingVerifications }}</span>
            @endif
        </a>

        <a href="{{ route('admin.attendance.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.attendance') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Attendance Tracking</span>
            @php
                $activeSession = \App\Models\AttendanceSession::where('status', 'active')->first();
            @endphp
            @if($activeSession)
                <span class="ml-auto w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            @endif
        </a>

        <a href="{{ route('admin.interviews.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.interviews') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Interviews</span>
            @php
                $unassignedCount = \App\Models\InterviewSession::where('mode', 'human')
                    ->whereNull('interviewer_id')
                    ->where('status', 'scheduled')
                    ->count();
            @endphp
            @if($unassignedCount > 0)
                <span class="ml-auto badge badge-warning">{{ $unassignedCount }}</span>
            @endif
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Configuration</p>
        
        <a href="{{ route('admin.users.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>User Management</span>
        </a>

        <a href="{{ route('admin.roles.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.roles') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Roles & Permissions</span>
        </a>
        
        <a href="{{ route('admin.content.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.content') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Content (CMS)</span>
        </a>
        
        <a href="{{ route('admin.settings') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'admin.settings') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Settings</span>
        </a>
    </div>

{{-- Recruiter Navigation --}}
@elseif($user->hasRole('recruiter'))
    <div class="space-y-1">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Talent</p>
        
        <a href="{{ route('recruiter.dashboard') }}" 
           class="nav-link {{ $currentRoute === 'recruiter.dashboard' ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('recruiter.marketplace.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'recruiter.marketplace') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span>Talent Marketplace</span>
        </a>

        <a href="{{ route('recruiter.shortlist.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'recruiter.shortlist') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <span>Shortlist</span>
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Account</p>
        
        <a href="{{ route('recruiter.subscription.index') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'recruiter.subscription') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <span>Subscription</span>
        </a>
    </div>

{{-- Mentor Navigation --}}
@elseif($user->hasRole('mentor'))
    <div class="space-y-1">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Mentoring</p>
        
        <a href="{{ route('mentor.dashboard') }}" 
           class="nav-link {{ $currentRoute === 'mentor.dashboard' ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('mentor.interviews') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'mentor.interviews') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <span>Scheduled Interviews</span>
        </a>
    </div>

    <div class="space-y-1 mt-6">
        <p class="px-3 py-2 text-xs font-semibold text-dark-500 uppercase tracking-wider">Settings</p>
        
        <a href="{{ route('mentor.availability') }}" 
           class="nav-link {{ str_starts_with($currentRoute, 'mentor.availability') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>Availability</span>
        </a>

        <a href="{{ route('mentor.profile') }}" 
           class="nav-link {{ $currentRoute === 'mentor.profile' ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>My Profile</span>
        </a>
    </div>
@endif

