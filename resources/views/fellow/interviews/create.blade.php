@extends('layouts.app')

@section('title', 'Schedule Interview')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('interviews.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Interviews
    </a>

    <!-- Page Header -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-white mb-2">Schedule Mock Interview</h1>
        <p class="text-dark-400">
            Practice your interview skills with AI or human mentors. Choose your preferred interview type and time slot.
        </p>
    </div>

    <!-- Interview Form -->
    <form action="{{ route('interviews.store') }}" method="POST" class="space-y-6" x-data="{ mode: 'ai', type: 'technical' }">
        @csrf

        <!-- Interview Mode Selection -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Interview Mode
            </h2>

            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($interviewModes as $interviewMode)
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="{{ $interviewMode->value }}" 
                               x-model="mode" class="hidden peer"
                               {{ old('mode', 'ai') == $interviewMode->value ? 'checked' : '' }}>
                        <div class="p-4 rounded-lg border-2 transition-all
                                    peer-checked:border-primary-500 peer-checked:bg-primary-600/10
                                    border-dark-600 hover:border-dark-500">
                            <div class="flex items-center gap-3 mb-2">
                                @if($interviewMode->value === 'ai')
                                    <span class="text-2xl">🤖</span>
                                @else
                                    <span class="text-2xl">👤</span>
                                @endif
                                <span class="text-white font-medium">{{ $interviewMode->label() }}</span>
                            </div>
                            <p class="text-dark-400 text-sm">
                                @if($interviewMode->value === 'ai')
                                    Practice anytime with our AI interviewer. Get instant feedback.
                                @else
                                    Schedule with an experienced mentor for personalized feedback.
                                @endif
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('mode')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Interview Type -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Interview Type
            </h2>

            <div class="grid sm:grid-cols-2 gap-3">
                @foreach($interviewTypes as $interviewType)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $interviewType->value }}" 
                               x-model="type" class="hidden peer"
                               {{ old('type', 'technical') == $interviewType->value ? 'checked' : '' }}
                               {{ ($availability[$interviewType->value] ?? true) ? '' : 'disabled' }}>
                        <div class="p-3 rounded-lg border transition-all flex items-center gap-3
                                    {{ ($availability[$interviewType->value] ?? true) ? 'peer-checked:border-primary-500 peer-checked:bg-primary-600/10 border-dark-600 hover:border-dark-500' : 'border-dark-700 opacity-50 cursor-not-allowed' }}">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center
                                @switch($interviewType->value)
                                    @case('technical') bg-purple-600/20 text-purple-400 @break
                                    @case('behavioral') bg-blue-600/20 text-blue-400 @break
                                    @case('case_study') bg-teal-600/20 text-teal-400 @break
                                    @case('system_design') bg-amber-600/20 text-amber-400 @break
                                    @default bg-dark-700 text-dark-400
                                @endswitch">
                                @switch($interviewType->value)
                                    @case('technical')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                        @break
                                    @case('behavioral')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        @break
                                    @case('case_study')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        @break
                                    @case('system_design')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        @break
                                @endswitch
                            </div>
                            <div>
                                <span class="text-white font-medium">{{ $interviewType->label() }}</span>
                                @if(!($availability[$interviewType->value] ?? true))
                                    <span class="text-red-400 text-xs block">Limit reached</span>
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('type')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Track Selection -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Career Track
            </h2>

            <select name="track_id" class="form-input" required>
                <option value="">Select a track...</option>
                @foreach($tracks as $track)
                    <option value="{{ $track->id }}" {{ old('track_id', $preselectedTrack ?? '') == $track->id ? 'selected' : '' }}>
                        {{ $track->name }}
                    </option>
                @endforeach
            </select>
            @error('track_id')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Scheduling (for human interviews) -->
        <template x-if="mode === 'human'">
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule Time
                </h2>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="scheduled_at" class="form-label">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" 
                               class="form-input" min="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                               value="{{ old('scheduled_at') }}">
                        @error('scheduled_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="duration_minutes" class="form-label">Duration</label>
                        <select name="duration_minutes" id="duration_minutes" class="form-input">
                            <option value="30" {{ old('duration_minutes', 45) == 30 ? 'selected' : '' }}>30 minutes</option>
                            <option value="45" {{ old('duration_minutes', 45) == 45 ? 'selected' : '' }}>45 minutes</option>
                            <option value="60" {{ old('duration_minutes', 45) == 60 ? 'selected' : '' }}>60 minutes</option>
                        </select>
                        @error('duration_minutes')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Available Slots Preview -->
                @if(count($availableSlots ?? []) > 0)
                    <div class="mt-4">
                        <p class="text-dark-400 text-sm mb-2">Popular time slots:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($availableSlots as $slot)
                                <button type="button" class="px-3 py-1 bg-dark-700 hover:bg-dark-600 text-dark-200 rounded text-sm transition-colors"
                                        onclick="document.getElementById('scheduled_at').value = '{{ $slot->format('Y-m-d\TH:i') }}'">
                                    {{ $slot->format('M j, g:i A') }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </template>

        <!-- AI Interview Duration (hidden for AI) -->
        <template x-if="mode === 'ai'">
            <input type="hidden" name="duration_minutes" value="30">
            <input type="hidden" name="scheduled_at" value="{{ now()->format('Y-m-d\TH:i') }}">
        </template>

        <!-- Focus Areas -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Focus Areas (Optional)
            </h2>

            <div x-data="{ areas: [] }" class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    @foreach(['Data Structures', 'Algorithms', 'System Design', 'Problem Solving', 'Communication', 'Leadership', 'Teamwork', 'Project Management'] as $area)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="focus_areas[]" value="{{ $area }}" class="hidden peer"
                                   {{ in_array($area, old('focus_areas', [])) ? 'checked' : '' }}>
                            <span class="px-3 py-1.5 rounded-full text-sm border transition-colors
                                        peer-checked:bg-primary-600/20 peer-checked:border-primary-500 peer-checked:text-primary-400
                                        border-dark-600 text-dark-300 hover:border-dark-500">
                                {{ $area }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Additional Notes -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Additional Notes (Optional)
            </h2>

            <textarea name="notes" rows="3" class="form-input" 
                      placeholder="Any specific areas you'd like to focus on or questions you have...">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- What to Expect -->
        <div class="card p-6 bg-dark-800/50">
            <h3 class="text-lg font-semibold text-white mb-3">What to Expect</h3>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <template x-if="mode === 'ai'">
                    <div class="col-span-2">
                        <ul class="space-y-2 text-dark-300">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Start immediately - no scheduling required
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                5-7 questions tailored to your track and type
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Instant feedback and scoring after each answer
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Earn Career Capital points upon completion
                            </li>
                        </ul>
                    </div>
                </template>
                <template x-if="mode === 'human'">
                    <div class="col-span-2">
                        <ul class="space-y-2 text-dark-300">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Matched with an experienced mentor in your field
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Real-time interaction via video call
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Detailed written feedback after the interview
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Career advice and networking opportunities
                            </li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('interviews.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <template x-if="mode === 'ai'">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Start AI Interview
                    </span>
                </template>
                <template x-if="mode === 'human'">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Schedule Interview
                    </span>
                </template>
            </button>
        </div>
    </form>
</div>
@endsection
