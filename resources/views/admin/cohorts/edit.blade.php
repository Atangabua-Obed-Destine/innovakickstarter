@extends('layouts.app')

@section('title', 'Edit Cohort - ' . $cohort->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.cohorts.show', $cohort) }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Cohort</h1>
            <p class="text-dark-400">{{ $cohort->name }} • {{ $cohort->track?->name }}</p>
        </div>
    </div>

    <!-- Status Badge -->
    @php
        $statusClasses = match($cohort->status) {
            'active' => 'bg-green-600/20 text-green-400 border-green-500/30',
            'upcoming' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
            'completed' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
            'draft' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
            'archived' => 'bg-dark-600/20 text-dark-400 border-dark-500/30',
            'cancelled' => 'bg-red-600/20 text-red-400 border-red-500/30',
            default => 'bg-dark-600/20 text-dark-400 border-dark-500/30'
        };
    @endphp
    
    @if($cohort->hasStarted())
    <div class="bg-amber-600/20 border border-amber-500/30 text-amber-400 px-4 py-3 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span>This cohort has started. Some fields cannot be modified.</span>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('admin.cohorts.update', $cohort) }}" method="POST" class="space-y-6">
        @csrf
        @method('PATCH')
        
        <!-- Basic Information -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Basic Information</h2>
            
            <!-- Cohort Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-300 mb-2">Cohort Name *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $cohort->name) }}"
                       placeholder="e.g., Cohort 8, January 2025 Batch"
                       class="form-input w-full @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Track -->
            <div>
                <label for="track_id" class="block text-sm font-medium text-dark-300 mb-2">Track *</label>
                <select name="track_id" 
                        id="track_id" 
                        class="form-input w-full @error('track_id') border-red-500 @enderror {{ $cohort->fellows_count > 0 ? 'opacity-50' : '' }}"
                        {{ $cohort->fellows_count > 0 ? 'disabled' : '' }}
                        required>
                    <option value="">Select a track...</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ old('track_id', $cohort->track_id) == $track->id ? 'selected' : '' }}>
                            {{ $track->name }}
                        </option>
                    @endforeach
                </select>
                @if($cohort->fellows_count > 0)
                    <input type="hidden" name="track_id" value="{{ $cohort->track_id }}">
                    <p class="text-amber-400 text-sm mt-1">Cannot change track when fellows are enrolled</p>
                @endif
                @error('track_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          placeholder="Brief description of this cohort, goals, or special notes..."
                          class="form-input w-full @error('description') border-red-500 @enderror">{{ old('description', $cohort->description) }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Timeline -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Timeline</h2>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-dark-300 mb-2">Start Date *</label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           value="{{ old('start_date', $cohort->start_date->format('Y-m-d')) }}"
                           class="form-input w-full @error('start_date') border-red-500 @enderror {{ $cohort->hasStarted() ? 'opacity-50' : '' }}"
                           {{ $cohort->hasStarted() ? 'disabled' : '' }}
                           required>
                    @if($cohort->hasStarted())
                        <input type="hidden" name="start_date" value="{{ $cohort->start_date->format('Y-m-d') }}">
                        <p class="text-amber-400 text-sm mt-1">Cannot change after cohort has started</p>
                    @endif
                    @error('start_date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-dark-300 mb-2">End Date *</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           value="{{ old('end_date', $cohort->end_date->format('Y-m-d')) }}"
                           min="{{ $cohort->start_date->format('Y-m-d') }}"
                           class="form-input w-full @error('end_date') border-red-500 @enderror"
                           required>
                    @error('end_date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @if($cohort->hasStarted())
                        <p class="text-dark-500 text-sm mt-1">You can extend the end date</p>
                    @endif
                </div>
            </div>

            <!-- Enrollment Window (Optional) -->
            @if(!$cohort->hasStarted())
            <div class="pt-4 border-t border-dark-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-dark-300">Enrollment Window (Optional)</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="has_enrollment_window" class="form-checkbox" 
                               {{ old('enrollment_opens_at', $cohort->enrollment_opens_at) ? 'checked' : '' }}>
                        <span class="text-dark-400 text-sm">Set enrollment dates</span>
                    </label>
                </div>
                
                <div id="enrollment_dates" class="grid sm:grid-cols-2 gap-6 {{ old('enrollment_opens_at', $cohort->enrollment_opens_at) ? '' : 'hidden' }}">
                    <div>
                        <label for="enrollment_opens_at" class="block text-sm font-medium text-dark-300 mb-2">Enrollment Opens</label>
                        <input type="date" 
                               name="enrollment_opens_at" 
                               id="enrollment_opens_at" 
                               value="{{ old('enrollment_opens_at', $cohort->enrollment_opens_at?->format('Y-m-d')) }}"
                               class="form-input w-full @error('enrollment_opens_at') border-red-500 @enderror">
                        @error('enrollment_opens_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="enrollment_closes_at" class="block text-sm font-medium text-dark-300 mb-2">Enrollment Closes</label>
                        <input type="date" 
                               name="enrollment_closes_at" 
                               id="enrollment_closes_at" 
                               value="{{ old('enrollment_closes_at', $cohort->enrollment_closes_at?->format('Y-m-d')) }}"
                               class="form-input w-full @error('enrollment_closes_at') border-red-500 @enderror">
                        @error('enrollment_closes_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Capacity -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Capacity</h2>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Max Fellows -->
                <div>
                    <label for="max_fellows" class="block text-sm font-medium text-dark-300 mb-2">Maximum Fellows *</label>
                    <input type="number" 
                           name="max_fellows" 
                           id="max_fellows" 
                           value="{{ old('max_fellows', $cohort->max_fellows) }}"
                           min="{{ max(1, $cohort->fellows_count) }}"
                           max="500"
                           class="form-input w-full @error('max_fellows') border-red-500 @enderror"
                           required>
                    @error('max_fellows')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-dark-500 text-sm mt-1">Currently {{ $cohort->fellows_count }} enrolled</p>
                </div>

                <!-- Min Fellows -->
                <div>
                    <label for="min_fellows" class="block text-sm font-medium text-dark-300 mb-2">Minimum Fellows *</label>
                    <input type="number" 
                           name="min_fellows" 
                           id="min_fellows" 
                           value="{{ old('min_fellows', $cohort->min_fellows) }}"
                           min="1"
                           class="form-input w-full @error('min_fellows') border-red-500 @enderror"
                           required>
                    @error('min_fellows')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <div>
                @if($cohort->status === 'draft' || $cohort->fellows_count === 0)
                <form action="{{ route('admin.cohorts.destroy', $cohort) }}" method="POST" class="inline"
                      onsubmit="return confirm('Are you sure you want to delete this cohort? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-ghost text-red-400 hover:text-red-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Cohort
                    </button>
                </form>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.cohorts.show', $cohort) }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Toggle enrollment window fields
    const enrollmentCheckbox = document.getElementById('has_enrollment_window');
    if (enrollmentCheckbox) {
        enrollmentCheckbox.addEventListener('change', function() {
            const enrollmentDates = document.getElementById('enrollment_dates');
            if (this.checked) {
                enrollmentDates.classList.remove('hidden');
            } else {
                enrollmentDates.classList.add('hidden');
                document.getElementById('enrollment_opens_at').value = '';
                document.getElementById('enrollment_closes_at').value = '';
            }
        });
    }

    // Validate end date is after start date
    const startDateInput = document.getElementById('start_date');
    if (startDateInput && !startDateInput.disabled) {
        startDateInput.addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    }
</script>
@endpush
@endsection
