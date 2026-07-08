@extends('layouts.app')

@section('title', 'Create Track')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <nav class="text-sm text-dark-400 mb-4">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Dashboard</a>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.tracks.index') }}" class="hover:text-white">Tracks</a>
            <span class="mx-2">›</span>
            <span class="text-dark-300">Create</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">Create New Track</h1>
        <p class="text-dark-400 mt-1">Define a new career track for fellows to enroll in.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.tracks.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card p-6">
            <h2 class="text-lg font-medium text-white mb-4">Basic Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="form-label">Track Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="form-input"
                        placeholder="e.g., Full Stack Development">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="form-label">URL Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                        class="form-input"
                        placeholder="full-stack-development (auto-generated if empty)">
                    @error('slug')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" required
                        class="form-input">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->value }}" {{ old('category') === $category->value ? 'selected' : '' }}>
                                {{ $category->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Duration -->
                <div>
                    <label for="duration_weeks" class="form-label">Duration (Weeks)</label>
                    <input type="number" name="duration_weeks" id="duration_weeks" value="{{ old('duration_weeks', 12) }}" min="1" max="52"
                        class="form-input">
                    @error('duration_weeks')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="form-label">Description *</label>
                <textarea name="description" id="description" rows="4" required
                    class="form-input"
                    placeholder="Describe what fellows will learn in this track...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-medium text-white mb-4">Appearance</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Icon -->
                <div>
                    <label for="icon" class="form-label">Icon (Heroicon name)</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', 'academic-cap') }}"
                        class="form-input"
                        placeholder="academic-cap">
                    <p class="mt-1 text-xs text-dark-500">e.g., code-bracket, server, chart-bar</p>
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="form-label">Theme Color</label>
                    <div class="mt-1 flex items-center space-x-2">
                        <input type="color" name="color" id="color" value="{{ old('color', '#7C3AED') }}"
                            class="h-10 w-16 rounded border-dark-600 bg-dark-800 cursor-pointer">
                        <input type="text" id="color_text" value="{{ old('color', '#7C3AED') }}"
                            class="form-input"
                            readonly>
                    </div>
                </div>

                <!-- Max Fellows -->
                <div>
                    <label for="max_fellows" class="form-label">Max Fellows</label>
                    <input type="number" name="max_fellows" id="max_fellows" value="{{ old('max_fellows') }}" min="0"
                        class="form-input"
                        placeholder="Unlimited if empty">
                    @error('max_fellows')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h2 class="text-lg font-medium text-white mb-4">Settings</h2>
            
            <div class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-dark-600 bg-dark-800 rounded">
                <label for="is_active" class="ml-2 block text-sm text-dark-300">
                    Active - Fellows can enroll in this track
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('admin.tracks.index') }}" class="px-4 py-2 text-dark-400 hover:text-white transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                Create Track
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Sync color picker with text input
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value;
    });
    
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (!slugField.value) {
            slugField.placeholder = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        }
    });
</script>
@endpush
@endsection
