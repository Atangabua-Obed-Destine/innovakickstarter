@extends('layouts.app')

@section('title', 'Submit Activity')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Activities
    </a>

    <!-- Page Header -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-white mb-2">Submit New Activity</h1>
        <p class="text-dark-400">
            Submit your learning activities to earn Career Capital points. Each submission will be reviewed and points awarded upon approval.
        </p>
    </div>

    <!-- Activity Form -->
    <form action="{{ route('activities.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Track & Type Selection -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Activity Details
            </h2>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="track_id" class="form-label">Career Track <span class="text-red-400">*</span></label>
                    <select name="track_id" id="track_id" class="form-input" required>
                        <option value="">Select a track...</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ old('track_id', $preselectedTrack) == $track->id ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('track_id')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="form-label">Activity Type <span class="text-red-400">*</span></label>
                    <select name="type" id="type" class="form-input" required x-data x-model="$refs.typeSelect.value" x-ref="typeSelect">
                        <option value="">Select type...</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->value }}" {{ old('type', $preselectedType) == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Activity Information -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Activity Information
            </h2>

            <div class="space-y-4">
                <div>
                    <label for="title" class="form-label">Activity Title <span class="text-red-400">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" 
                           class="form-input" placeholder="e.g., Completed AWS Solutions Architect Certification" required>
                    @error('title')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Description <span class="text-red-400">*</span></label>
                    <textarea name="description" id="description" rows="4" class="form-input" 
                              placeholder="Describe what you did, what you learned, and the outcomes..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="completed_at" class="form-label">Completion Date <span class="text-red-400">*</span></label>
                        <input type="date" name="completed_at" id="completed_at" value="{{ old('completed_at', date('Y-m-d')) }}" 
                               class="form-input" max="{{ date('Y-m-d') }}" required>
                        @error('completed_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hours_spent" class="form-label">Hours Spent</label>
                        <input type="number" name="hours_spent" id="hours_spent" value="{{ old('hours_spent') }}" 
                               class="form-input" min="0.5" step="0.5" placeholder="e.g., 10">
                        @error('hours_spent')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Evidence & Links -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                Evidence & Documentation
            </h2>

            <div class="space-y-4">
                <div>
                    <label for="evidence_url" class="form-label">Evidence URL</label>
                    <input type="url" name="evidence_url" id="evidence_url" value="{{ old('evidence_url') }}" 
                           class="form-input" placeholder="https://github.com/yourproject or certificate link">
                    <p class="text-dark-500 text-xs mt-1">Link to your project, certificate, blog post, or other evidence</p>
                    @error('evidence_url')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Attachments (Optional)</label>
                    <div class="border-2 border-dashed border-dark-600 rounded-lg p-6 text-center hover:border-primary-500 transition-colors cursor-pointer"
                         x-data="{ files: [] }"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent="$el.classList.add('border-primary-500')"
                         @dragleave.prevent="$el.classList.remove('border-primary-500')"
                         @drop.prevent="$refs.fileInput.files = $event.dataTransfer.files; files = Array.from($refs.fileInput.files)">
                        <input type="file" name="attachments[]" multiple x-ref="fileInput" class="hidden" 
                               @change="files = Array.from($event.target.files)"
                               accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif">
                        <svg class="w-10 h-10 text-dark-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-dark-400 text-sm">
                            <span class="text-primary-400">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-dark-500 text-xs mt-1">PDF, DOC, or images up to 10MB</p>
                        
                        <template x-if="files.length > 0">
                            <div class="mt-4 text-left space-y-2">
                                <template x-for="file in files" :key="file.name">
                                    <div class="flex items-center gap-2 p-2 bg-dark-800 rounded">
                                        <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-dark-200 text-sm flex-1 truncate" x-text="file.name"></span>
                                        <span class="text-dark-500 text-xs" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    @error('attachments')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tags -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Tags (Optional)
            </h2>
            
            <div x-data="{ tags: [], newTag: '' }">
                <div class="flex gap-2 mb-2">
                    <input type="text" x-model="newTag" class="form-input flex-1" 
                           placeholder="Add a tag (e.g., JavaScript, Cloud, Leadership)"
                           @keydown.enter.prevent="if(newTag.trim() && !tags.includes(newTag.trim())) { tags.push(newTag.trim()); newTag = ''; }">
                    <button type="button" class="btn btn-secondary" 
                            @click="if(newTag.trim() && !tags.includes(newTag.trim())) { tags.push(newTag.trim()); newTag = ''; }">
                        Add
                    </button>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <template x-for="(tag, index) in tags" :key="index">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-dark-700 text-dark-200 rounded-full text-sm">
                            <span x-text="tag"></span>
                            <input type="hidden" name="tags[]" :value="tag">
                            <button type="button" @click="tags.splice(index, 1)" class="text-dark-400 hover:text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Points Estimate -->
        <div class="card p-6 bg-gradient-to-r from-primary-600/20 to-blue-600/20 border-primary-500/30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-primary-600/30 flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-dark-300 text-sm">Estimated Points</p>
                    <p class="text-2xl font-bold text-white">15 - 50 points</p>
                    <p class="text-dark-400 text-sm">Points vary based on activity type and quality</p>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Submit Activity
            </button>
        </div>
    </form>
</div>
@endsection
