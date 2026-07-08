@extends('layouts.app')

@section('title', 'Create Program')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.programs.index') }}" class="p-2 text-dark-400 hover:text-white hover:bg-dark-700 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Create Program</h1>
            <p class="text-dark-400">Set up a new fellowship program batch</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.programs.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Basic Information</h2>
            
            <!-- Program Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-dark-300 mb-2">Program Name *</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       placeholder="e.g., IKS Fellowship 2025, January Batch 2025"
                       class="form-input w-full @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dark-500 text-sm mt-1">A clear, distinctive name identifying this program batch</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description</label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          placeholder="Describe this program batch, goals, special initiatives, or focus areas..."
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
                    <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming - Program announced but not yet enrolling</option>
                    <option value="enrolling" {{ old('status') == 'enrolling' ? 'selected' : '' }}>Enrolling - Open for enrollment</option>
                </select>
                @error('status')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Max Capacity -->
            <div>
                <label for="max_capacity" class="block text-sm font-medium text-dark-300 mb-2">Maximum Capacity</label>
                <input type="number" 
                       name="max_capacity" 
                       id="max_capacity" 
                       value="{{ old('max_capacity') }}"
                       placeholder="e.g., 100"
                       min="1"
                       class="form-input w-full @error('max_capacity') border-red-500 @enderror">
                @error('max_capacity')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dark-500 text-sm mt-1">Leave empty for unlimited capacity</p>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Timeline</h2>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-dark-300 mb-2">Start Date</label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           value="{{ old('start_date') }}"
                           class="form-input w-full @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-dark-300 mb-2">End Date</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           value="{{ old('end_date') }}"
                           class="form-input w-full @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Graduation Date -->
            <div>
                <label for="graduation_date" class="block text-sm font-medium text-dark-300 mb-2">Graduation Date</label>
                <input type="date" 
                       name="graduation_date" 
                       id="graduation_date" 
                       value="{{ old('graduation_date') }}"
                       class="form-input w-full @error('graduation_date') border-red-500 @enderror">
                @error('graduation_date')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dark-500 text-sm mt-1">Official graduation ceremony date</p>
            </div>
        </div>

        <!-- Sponsor Information -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Sponsor Information (Optional)</h2>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Sponsor Name -->
                <div>
                    <label for="sponsor_name" class="block text-sm font-medium text-dark-300 mb-2">Sponsor Name</label>
                    <input type="text" 
                           name="sponsor_name" 
                           id="sponsor_name" 
                           value="{{ old('sponsor_name') }}"
                           placeholder="e.g., Tech Foundation"
                           class="form-input w-full @error('sponsor_name') border-red-500 @enderror">
                    @error('sponsor_name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sponsor Website -->
                <div>
                    <label for="sponsor_website" class="block text-sm font-medium text-dark-300 mb-2">Sponsor Website</label>
                    <input type="url" 
                           name="sponsor_website" 
                           id="sponsor_website" 
                           value="{{ old('sponsor_website') }}"
                           placeholder="https://sponsor.com"
                           class="form-input w-full @error('sponsor_website') border-red-500 @enderror">
                    @error('sponsor_website')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Sponsor Logo -->
            <div>
                <label for="sponsor_logo" class="block text-sm font-medium text-dark-300 mb-2">Sponsor Logo URL</label>
                <input type="url" 
                       name="sponsor_logo" 
                       id="sponsor_logo" 
                       value="{{ old('sponsor_logo') }}"
                       placeholder="https://sponsor.com/logo.png"
                       class="form-input w-full @error('sponsor_logo') border-red-500 @enderror">
                @error('sponsor_logo')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Certificate Settings -->
        <div class="card p-6 space-y-6">
            <h2 class="text-lg font-semibold text-white border-b border-dark-700 pb-3">Certificate Settings (Optional)</h2>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Certificate Prefix -->
                <div>
                    <label for="certificate_prefix" class="block text-sm font-medium text-dark-300 mb-2">Certificate Number Prefix</label>
                    <input type="text" 
                           name="certificate_prefix" 
                           id="certificate_prefix" 
                           value="{{ old('certificate_prefix') }}"
                           placeholder="e.g., IKS-2025"
                           class="form-input w-full @error('certificate_prefix') border-red-500 @enderror">
                    @error('certificate_prefix')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-dark-500 text-sm mt-1">Used for generating certificate numbers (e.g., IKS-2025-001)</p>
                </div>

                <!-- Certificate Template -->
                <div>
                    <label for="certificate_template" class="block text-sm font-medium text-dark-300 mb-2">Certificate Template</label>
                    <input type="text" 
                           name="certificate_template" 
                           id="certificate_template" 
                           value="{{ old('certificate_template') }}"
                           placeholder="e.g., templates/cert-2025.blade.php"
                           class="form-input w-full @error('certificate_template') border-red-500 @enderror">
                    @error('certificate_template')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Milestones -->
        <div class="card p-6 space-y-6" x-data="{ milestones: {{ json_encode(old('milestones', [])) }} }">
            <div class="flex items-center justify-between border-b border-dark-700 pb-3">
                <h2 class="text-lg font-semibold text-white">Program Milestones (Optional)</h2>
                <button type="button" 
                        @click="milestones.push({ name: '', target_date: '', description: '' })"
                        class="btn btn-sm btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Milestone
                </button>
            </div>
            
            <p class="text-dark-500 text-sm">Define key milestones that fellows should achieve during the program.</p>

            <template x-for="(milestone, index) in milestones" :key="index">
                <div class="flex gap-4 items-start p-4 bg-dark-800/50 rounded-lg">
                    <div class="flex-1 grid sm:grid-cols-3 gap-4">
                        <div>
                            <label :for="'milestone_name_' + index" class="block text-sm font-medium text-dark-300 mb-1">Name</label>
                            <input type="text" 
                                   :name="'milestones[' + index + '][name]'"
                                   x-model="milestone.name"
                                   placeholder="e.g., Portfolio Complete"
                                   class="form-input w-full text-sm">
                        </div>
                        <div>
                            <label :for="'milestone_date_' + index" class="block text-sm font-medium text-dark-300 mb-1">Target Date</label>
                            <input type="date" 
                                   :name="'milestones[' + index + '][target_date]'"
                                   x-model="milestone.target_date"
                                   class="form-input w-full text-sm">
                        </div>
                        <div>
                            <label :for="'milestone_desc_' + index" class="block text-sm font-medium text-dark-300 mb-1">Description</label>
                            <input type="text" 
                                   :name="'milestones[' + index + '][description]'"
                                   x-model="milestone.description"
                                   placeholder="Brief description"
                                   class="form-input w-full text-sm">
                        </div>
                    </div>
                    <button type="button" 
                            @click="milestones.splice(index, 1)"
                            class="p-2 text-dark-400 hover:text-red-400 transition-colors mt-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>

            <div x-show="milestones.length === 0" class="text-center py-8 text-dark-500">
                <p>No milestones defined yet. Click "Add Milestone" to create program checkpoints.</p>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.programs.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create Program
            </button>
        </div>
    </form>
</div>
@endsection
