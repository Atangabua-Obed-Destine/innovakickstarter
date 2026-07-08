@extends('layouts.app')

@section('title', 'Mentor Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Mentor Profile</h1>
            <p class="text-dark-400 mt-1">Manage your mentor profile and interview preferences.</p>
        </div>
        <a href="{{ route('mentor.dashboard') }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Profile Card -->
    <div class="card p-6">
        <div class="flex items-start gap-6">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-2xl font-bold text-white flex-shrink-0">
                @if($mentor->avatar_url)
                    <img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}" class="w-full h-full object-cover rounded-2xl">
                @else
                    {{ strtoupper(substr($mentor->name, 0, 2)) }}
                @endif
            </div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-white">{{ $mentor->name }}</h2>
                <p class="text-dark-400">{{ $mentor->email }}</p>
                @if($mentor->location)
                <p class="text-dark-500 text-sm mt-1">{{ $mentor->location }}</p>
                @endif
                @if($mentor->bio)
                <p class="text-dark-300 mt-3">{{ $mentor->bio }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Specializations -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Specializations</h3>
        @if(!empty($specializations))
            <div class="flex flex-wrap gap-2">
                @foreach($specializations as $spec)
                <span class="px-3 py-1.5 bg-primary-600/20 text-primary-400 text-sm font-medium rounded-full">{{ $spec }}</span>
                @endforeach
            </div>
        @else
            <p class="text-dark-500">No specializations set. Contact an administrator to update your profile.</p>
        @endif
    </div>

    <!-- Interview Types -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Interview Types You Can Conduct</h3>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($interviewTypes as $type)
            <div class="flex items-center gap-3 p-3 bg-dark-800 rounded-lg">
                <div class="w-8 h-8 rounded-lg bg-{{ ['technical' => 'blue', 'behavioral' => 'green', 'system_design' => 'purple', 'mixed' => 'orange'][$type->value] ?? 'gray' }}-600/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-{{ ['technical' => 'blue', 'behavioral' => 'green', 'system_design' => 'purple', 'mixed' => 'orange'][$type->value] ?? 'gray' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-dark-200 text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $type->value)) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Links -->
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
        <div class="grid sm:grid-cols-2 gap-3">
            <a href="{{ route('mentor.availability') }}" class="flex items-center gap-3 p-4 bg-dark-800 hover:bg-dark-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-dark-200 font-medium">Manage Availability</span>
            </a>
            <a href="{{ route('mentor.interviews') }}" class="flex items-center gap-3 p-4 bg-dark-800 hover:bg-dark-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span class="text-dark-200 font-medium">View Interviews</span>
            </a>
        </div>
    </div>
</div>
@endsection
