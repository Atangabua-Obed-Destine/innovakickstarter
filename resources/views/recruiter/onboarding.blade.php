@extends('layouts.app')

@section('title', 'Welcome to I-NNOVA')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Welcome Banner -->
    <div class="card p-8 text-center border border-primary-600/30 bg-gradient-to-br from-primary-900/20 to-accent-900/20">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Welcome to I-NNOVA, {{ $user->name }}! 🎉</h1>
        <p class="text-dark-400 text-lg max-w-2xl mx-auto">Let's set up your recruiter account so you can start discovering top African tech talent.</p>
    </div>

    <!-- Steps -->
    <div class="space-y-4">
        @php
            $steps = [
                ['title' => 'Complete Your Company Profile', 'description' => 'Add your company name, industry, and size so fellows know who you are.', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'done' => !empty($user->company_name)],
                ['title' => 'Configure Hiring Preferences', 'description' => 'Specify which tracks, skills, and experience levels you\'re looking for.', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'done' => false],
                ['title' => 'Choose a Subscription Plan', 'description' => 'Select a plan that fits your hiring needs. Start with a free trial.', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'done' => false],
                ['title' => 'Browse the Talent Marketplace', 'description' => 'Search, filter, and shortlist top fellows for your open positions.', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'done' => false],
            ];
        @endphp

        @foreach($steps as $i => $step)
        <div class="card p-6 flex items-start gap-4 {{ $step['done'] ? 'border-l-4 border-green-500' : '' }}">
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $step['done'] ? 'bg-green-600/20 text-green-400' : 'bg-dark-700 text-dark-400' }}">
                @if($step['done'])
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @else
                    <span class="font-bold">{{ $i + 1 }}</span>
                @endif
            </div>
            <div class="flex-1">
                <h3 class="text-white font-semibold">{{ $step['title'] }}</h3>
                <p class="text-dark-400 text-sm mt-1">{{ $step['description'] }}</p>
            </div>
            <svg class="w-6 h-6 text-dark-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
            </svg>
        </div>
        @endforeach
    </div>

    <!-- Actions -->
    <div class="flex justify-center gap-4">
        <a href="{{ route('recruiter.subscription.index') }}" class="btn btn-primary btn-lg">
            Start Free Trial
            <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
        <a href="{{ route('recruiter.dashboard') }}" class="btn btn-secondary btn-lg">Skip for Now</a>
    </div>
</div>
@endsection
