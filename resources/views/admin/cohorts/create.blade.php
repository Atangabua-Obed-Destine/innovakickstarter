@extends('layouts.app')

@section('title', 'Create Cohort')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.cohorts.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Create Cohort</h1>
            <p class="text-dark-400">Set up a new cohort for fellows</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.cohorts.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Basic Information</h2>
            
            <!-- Cohort Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-300 mb-2">Cohort Name *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
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
                        class="form-input w-full @error('track_id') border-red-500 @enderror"
                        required>
                    <option value="">Select a track...</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}" {{ old('track_id') == $track->id ? 'selected' : '' }}>
                            {{ $track->name }}
                        </option>
                    @endforeach
                </select>
                @error('track_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dark-500 text-sm mt-1">Each cohort is associated with one track</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          placeholder="Brief description of this cohort, goals, or special notes..."
                          class="form-input w-full @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-dark-300 mb-2">Initial Status *</label>
                <select name="status" 
                        id="status" 
                        class="form-input w-full @error('status') border-red-500 @enderror"
                        required>
                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft - Still setting up, not visible to fellows</option>
                    <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming - Open for enrollment</option>
                </select>
                @error('status')
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
                           value="{{ old('start_date') }}"
                           min="{{ date('Y-m-d') }}"
                           class="form-input w-full @error('start_date') border-red-500 @enderror"
                           required>
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
                           value="{{ old('end_date') }}"
                           class="form-input w-full @error('end_date') border-red-500 @enderror"
                           required>
                    @error('end_date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Enrollment Window (Optional) -->
            <div class="pt-4 border-t border-dark-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-dark-300">Enrollment Window (Optional)</h3>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="has_enrollment_window" class="form-checkbox" 
                               {{ old('enrollment_opens_at') ? 'checked' : '' }}>
                        <span class="text-dark-400 text-sm">Set enrollment dates</span>
                    </label>
                </div>
                
                <div id="enrollment_dates" class="grid sm:grid-cols-2 gap-6 {{ old('enrollment_opens_at') ? '' : 'hidden' }}">
                    <div>
                        <label for="enrollment_opens_at" class="block text-sm font-medium text-dark-300 mb-2">Enrollment Opens</label>
                        <input type="date" 
                               name="enrollment_opens_at" 
                               id="enrollment_opens_at" 
                               value="{{ old('enrollment_opens_at') }}"
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
                               value="{{ old('enrollment_closes_at') }}"
                               class="form-input w-full @error('enrollment_closes_at') border-red-500 @enderror">
                        @error('enrollment_closes_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-dark-500 text-sm mt-2">If not set, enrollment is open until the cohort starts</p>
            </div>
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
                           value="{{ old('max_fellows', 50) }}"
                           min="1"
                           max="500"
                           class="form-input w-full @error('max_fellows') border-red-500 @enderror"
                           required>
                    @error('max_fellows')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-dark-500 text-sm mt-1">Maximum number of fellows in this cohort</p>
                </div>

                <!-- Min Fellows -->
                <div>
                    <label for="min_fellows" class="block text-sm font-medium text-dark-300 mb-2">Minimum Fellows *</label>
                    <input type="number" 
                           name="min_fellows" 
                           id="min_fellows" 
                           value="{{ old('min_fellows', 10) }}"
                           min="1"
                           class="form-input w-full @error('min_fellows') border-red-500 @enderror"
                           required>
                    @error('min_fellows')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-dark-500 text-sm mt-1">Minimum needed to run this cohort</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.cohorts.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Cohort
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Toggle enrollment window fields
    document.getElementById('has_enrollment_window').addEventListener('change', function() {
        const enrollmentDates = document.getElementById('enrollment_dates');
        if (this.checked) {
            enrollmentDates.classList.remove('hidden');
        } else {
            enrollmentDates.classList.add('hidden');
            document.getElementById('enrollment_opens_at').value = '';
            document.getElementById('enrollment_closes_at').value = '';
        }
    });

    // Validate end date is after start date
    document.getElementById('start_date').addEventListener('change', function() {
        document.getElementById('end_date').min = this.value;
    });
</script>
@endpush
@endsection
