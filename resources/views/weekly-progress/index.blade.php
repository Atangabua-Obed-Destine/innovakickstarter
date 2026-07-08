@extends('layouts.app')

@section('title', 'Weekly Progress')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Weekly Check-in</h1>
            <p class="text-dark-400 mt-1">Track your weekly progress and maintain your streak.</p>
        </div>
        @if(!$hasSubmittedThisWeek)
            <a href="{{ route('weekly-progress.submit') }}" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Submit This Week's Check-in
            </a>
        @else
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-green-600/20 text-green-400 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Checked in this week!
            </span>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Current Streak</p>
                    <p class="text-2xl font-bold text-orange-400">{{ $stats['current_streak'] }} weeks</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-orange-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Total Weeks</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_weeks'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center">
                    <svg class="w-6 h-6 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Best Week Score</p>
                    <p class="text-2xl font-bold text-green-400">+{{ $stats['best_week_score'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-dark-400 text-sm">Total Activities</p>
                    <p class="text-2xl font-bold text-primary-400">{{ $stats['total_activities'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- This Week's Status -->
    @if($currentWeek)
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-white mb-4">This Week's Summary</h2>
        <div class="grid md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-2 {{ $currentWeek->build_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <p class="text-dark-400 text-sm mb-1">Build</p>
                <p class="font-semibold {{ $currentWeek->build_completed ? 'text-green-400' : 'text-dark-400' }}">
                    {{ $currentWeek->build_completed ? 'Complete' : 'Pending' }}
                </p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-2 {{ $currentWeek->brand_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <p class="text-dark-400 text-sm mb-1">Brand</p>
                <p class="font-semibold {{ $currentWeek->brand_completed ? 'text-green-400' : 'text-dark-400' }}">
                    {{ $currentWeek->brand_completed ? 'Complete' : 'Pending' }}
                </p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-2 {{ $currentWeek->interview_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="text-dark-400 text-sm mb-1">Interview</p>
                <p class="font-semibold {{ $currentWeek->interview_completed ? 'text-green-400' : 'text-dark-400' }}">
                    {{ $currentWeek->interview_completed ? 'Complete' : 'Pending' }}
                </p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-2 {{ $currentWeek->collaborate_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-400' }} flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-dark-400 text-sm mb-1">Collaborate</p>
                <p class="font-semibold {{ $currentWeek->collaborate_completed ? 'text-green-400' : 'text-dark-400' }}">
                    {{ $currentWeek->collaborate_completed ? 'Complete' : 'Pending' }}
                </p>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-dark-700 grid md:grid-cols-2 gap-6">
            <div>
                <p class="text-dark-400 text-sm mb-1">Total Points</p>
                <p class="text-2xl font-bold text-white">{{ $currentWeek->total_points }}</p>
            </div>
            <div>
                <p class="text-dark-400 text-sm mb-1">Status</p>
                <p class="text-2xl font-bold {{ $currentWeek->all_pillars_completed ? 'text-green-400' : 'text-amber-400' }}">
                    {{ $currentWeek->all_pillars_completed ? 'All Pillars Complete' : 'In Progress' }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Progress History -->
    <div class="card">
        <div class="p-5 border-b border-dark-700">
            <h2 class="text-lg font-semibold text-white">Progress History</h2>
        </div>
        
        @if($history->isEmpty())
            <div class="p-12 text-center">
                <div class="w-20 h-20 rounded-full bg-dark-800 flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-dark-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Check-ins Yet</h3>
                <p class="text-dark-400 mb-6 max-w-md mx-auto">
                    Start your weekly check-in habit to track your progress and maintain consistency.
                </p>
                <a href="{{ route('weekly-progress.submit') }}" class="btn btn-primary">
                    Submit Your First Check-in
                </a>
            </div>
        @else
            <div class="divide-y divide-dark-700">
                @foreach($history as $week)
                    <div class="p-5 hover:bg-dark-800/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <!-- Week Icon -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                {{ $week->all_pillars_completed ? 'bg-green-600/20 text-green-400' : 'bg-amber-600/20 text-amber-400' }}">
                                @if($week->all_pillars_completed)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            <!-- Week Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-medium text-white">
                                    Week {{ $week->week_number }}, {{ $week->year }}
                                </h3>
                                <p class="text-dark-500 text-sm">
                                    {{ $week->week_start->format('M d') }} - {{ $week->week_end->format('M d, Y') }}
                                </p>
                            </div>
                            
                            <!-- Pillars -->
                            <div class="hidden md:flex items-center gap-1">
                                <span class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium {{ $week->build_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-500' }}">B</span>
                                <span class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium {{ $week->brand_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-500' }}">Br</span>
                                <span class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium {{ $week->interview_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-500' }}">I</span>
                                <span class="w-8 h-8 rounded flex items-center justify-center text-xs font-medium {{ $week->collaborate_completed ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-500' }}">C</span>
                            </div>
                            
                            <!-- Points -->
                            <div class="text-center">
                                <p class="text-lg font-semibold text-white">{{ $week->total_points }}</p>
                                <p class="text-dark-500 text-xs">Points</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Tips -->
    <div class="card p-6 bg-gradient-to-r from-orange-900/20 to-amber-900/20 border-orange-800/30">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-600/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white mb-2">Keep Your Streak Going!</h3>
                <p class="text-dark-300 text-sm">
                    Consistent weekly check-ins help you stay accountable and track your growth over time. 
                    Submit your check-in every week to maintain your streak and build strong habits.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
