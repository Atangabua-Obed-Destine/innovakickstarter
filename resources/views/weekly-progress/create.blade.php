@extends('layouts.app')

@section('title', 'Weekly Check-in')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('weekly-progress.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Weekly Progress
        </a>
        <h1 class="text-2xl font-bold text-white">Submit Weekly Check-in</h1>
        <p class="text-dark-400 mt-1">Track your progress across all 4 pillars this week.</p>
    </div>

    <!-- Check-in Form -->
    <form action="{{ route('weekly-progress.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Week Info Card -->
        <div class="card p-6 bg-gradient-to-r from-primary-900/20 to-primary-800/20 border-primary-800/30">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl bg-primary-600/20 flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Week {{ $weekNumber }}, {{ $year }}</h2>
                    <p class="text-dark-400">{{ $weekStart->format('F d') }} - {{ $weekEnd->format('F d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- 4 Pillars Section -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-6">Complete Your 4 Pillars</h3>
            <p class="text-dark-400 text-sm mb-6">
                To keep your score from freezing, you must complete at least one activity in each pillar every week.
            </p>
            
            <div class="grid sm:grid-cols-2 gap-4">
                <!-- BUILD Pillar -->
                <label class="pillar-checkbox relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition-all border-dark-600 hover:border-dark-500 bg-dark-800/50">
                    <input type="hidden" name="build_completed" value="0">
                    <input 
                        type="checkbox" 
                        name="build_completed" 
                        value="1"
                        class="peer sr-only"
                        {{ old('build_completed') ? 'checked' : '' }}
                    >
                    <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-white">BUILD</p>
                        <p class="text-dark-400 text-sm">Submit project/code contribution</p>
                    </div>
                    <div class="check-circle absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center">
                        <svg class="check-icon w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>

                <!-- BRAND Pillar -->
                <label class="pillar-checkbox relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition-all border-dark-600 hover:border-dark-500 bg-dark-800/50">
                    <input type="hidden" name="brand_completed" value="0">
                    <input 
                        type="checkbox" 
                        name="brand_completed" 
                        value="1"
                        class="peer sr-only"
                        {{ old('brand_completed') ? 'checked' : '' }}
                    >
                    <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-white">BRAND</p>
                        <p class="text-dark-400 text-sm">Publish content (blog, LinkedIn, Twitter)</p>
                    </div>
                    <div class="check-circle absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center">
                        <svg class="check-icon w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>

                <!-- INTERVIEW Pillar -->
                <label class="pillar-checkbox relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition-all border-dark-600 hover:border-dark-500 bg-dark-800/50">
                    <input type="hidden" name="interview_completed" value="0">
                    <input 
                        type="checkbox" 
                        name="interview_completed" 
                        value="1"
                        class="peer sr-only"
                        {{ old('interview_completed') ? 'checked' : '' }}
                    >
                    <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-white">INTERVIEW</p>
                        <p class="text-dark-400 text-sm">Complete a mock interview session</p>
                    </div>
                    <div class="check-circle absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center">
                        <svg class="check-icon w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>

                <!-- COLLABORATE Pillar -->
                <label class="pillar-checkbox relative flex items-start p-4 rounded-xl border-2 cursor-pointer transition-all border-dark-600 hover:border-dark-500 bg-dark-800/50">
                    <input type="hidden" name="collaborate_completed" value="0">
                    <input 
                        type="checkbox" 
                        name="collaborate_completed" 
                        value="1"
                        class="peer sr-only"
                        {{ old('collaborate_completed') ? 'checked' : '' }}
                    >
                    <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-white">COLLABORATE</p>
                        <p class="text-dark-400 text-sm">Code reviews, mentoring, peer sessions</p>
                    </div>
                    <div class="check-circle absolute top-4 right-4 w-6 h-6 rounded-full border-2 border-dark-600 flex items-center justify-center">
                        <svg class="check-icon w-4 h-4 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>
            </div>
        </div>

        <!-- Warning Card -->
        <div class="card p-6 bg-gradient-to-r from-amber-900/20 to-orange-900/20 border-amber-800/30">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-600/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white mb-2">Score Freeze Warning</h3>
                    <p class="text-dark-300 text-sm">
                        If you don't complete all 4 pillars by Sunday 11:59 PM, your score will be frozen until you complete 
                        all pillars in a future week. Keep your momentum going!
                    </p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('weekly-progress.index') }}" class="btn btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Submit Check-in
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.pillar-checkbox').forEach(function(label) {
        const checkbox = label.querySelector('input[type="checkbox"]');
        const checkCircle = label.querySelector('.check-circle');
        const checkIcon = label.querySelector('.check-icon');
        
        function updateState() {
            if (checkbox.checked) {
                label.classList.remove('border-dark-600');
                label.classList.add('border-green-500', 'bg-green-600/10');
                checkCircle.classList.remove('border-dark-600');
                checkCircle.classList.add('border-green-500', 'bg-green-500');
                checkIcon.classList.remove('hidden');
            } else {
                label.classList.add('border-dark-600');
                label.classList.remove('border-green-500', 'bg-green-600/10');
                checkCircle.classList.add('border-dark-600');
                checkCircle.classList.remove('border-green-500', 'bg-green-500');
                checkIcon.classList.add('hidden');
            }
        }
        
        checkbox.addEventListener('change', updateState);
        updateState(); // Initial state
    });
});
</script>
@endsection
