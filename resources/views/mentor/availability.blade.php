@extends('layouts.app')

@section('title', 'Availability Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Availability Settings</h1>
            <p class="text-dark-400 mt-1">Set your weekly availability for conducting mock interviews with fellows.</p>
        </div>
        <a href="{{ route('mentor.dashboard') }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
    </div>

    <form method="POST" action="{{ route('mentor.availability.update') }}" class="space-y-4">
        @csrf

        @php
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $timeSlots = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
        @endphp

        @foreach($days as $day)
        <div class="card p-5" x-data="{ expanded: {{ json_encode($availability[$day]['available'] ?? false) }} }">
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="availability[{{ $day }}][available]" value="0">
                    <input type="checkbox" 
                           name="availability[{{ $day }}][available]" 
                           value="1" 
                           {{ ($availability[$day]['available'] ?? false) ? 'checked' : '' }}
                           @change="expanded = $event.target.checked"
                           class="w-5 h-5 rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500">
                    <span class="text-white font-semibold text-lg">{{ ucfirst($day) }}</span>
                </label>
                <button type="button" @click="expanded = !expanded" class="p-2 text-dark-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>
            
            <div x-show="expanded" x-transition class="mt-4 pt-4 border-t border-dark-700">
                <p class="text-dark-400 text-sm mb-3">Select available time slots (WAT - West Africa Time):</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach($timeSlots as $slot)
                    <label class="flex items-center justify-center p-2 rounded-lg border cursor-pointer transition-colors
                        {{ in_array($slot, $availability[$day]['slots'] ?? []) ? 'border-primary-500 bg-primary-600/20 text-primary-400' : 'border-dark-700 bg-dark-800 text-dark-400 hover:border-dark-600' }}">
                        <input type="checkbox" 
                               name="availability[{{ $day }}][slots][]" 
                               value="{{ $slot }}" 
                               {{ in_array($slot, $availability[$day]['slots'] ?? []) ? 'checked' : '' }}
                               class="hidden">
                        <span class="text-sm font-medium">{{ $slot }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Availability
            </button>
        </div>
    </form>
</div>
@endsection
