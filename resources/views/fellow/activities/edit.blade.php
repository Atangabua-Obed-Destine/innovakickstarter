@extends('layouts.app')

@section('title', 'Edit Activity')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back Button -->
    <a href="{{ route('activities.show', $activity) }}" class="inline-flex items-center gap-2 text-dark-400 hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Activity
    </a>

    <!-- Page Header -->
    <div class="card p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Edit Activity</h1>
                <p class="text-dark-400">
                    Update your activity submission. Changes will reset the review status to pending.
                </p>
            </div>
            <span class="badge {{ $activity->status->value === 'revision_needed' ? 'bg-amber-600/20 text-amber-400 border-amber-500/30' : 'badge-warning' }}">
                {{ ucfirst(str_replace('_', ' ', $activity->status->value)) }}
            </span>
        </div>
        
        @if($activity->status->value === 'revision_needed' && isset($activity->metadata['revision_notes']))
            <div class="mt-4 p-4 bg-amber-600/10 border border-amber-500/30 rounded-lg">
                <h4 class="text-amber-400 font-medium mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Revision Requested
                </h4>
                <p class="text-dark-200 text-sm">{{ $activity->metadata['revision_notes'] }}</p>
            </div>
        @endif
    </div>

    <!-- Activity Form -->
    <form action="{{ route('activities.update', $activity) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

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
                            <option value="{{ $track->id }}" {{ old('track_id', $activity->track_id) == $track->id ? 'selected' : '' }}>
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
                    <select name="type" id="type" class="form-input" required>
                        <option value="">Select type...</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->value }}" {{ old('type', $activity->type->value) == $type->value ? 'selected' : '' }}>
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
                    <input type="text" name="title" id="title" value="{{ old('title', $activity->title) }}" 
                           class="form-input" placeholder="e.g., Completed AWS Solutions Architect Certification" required>
                    @error('title')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Description <span class="text-red-400">*</span></label>
                    <textarea name="description" id="description" rows="4" class="form-input" 
                              placeholder="Describe what you did, what you learned, and the outcomes..." required>{{ old('description', $activity->description) }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="completed_at" class="form-label">Completion Date <span class="text-red-400">*</span></label>
                        <input type="date" name="completed_at" id="completed_at" 
                               value="{{ old('completed_at', $activity->completed_at?->format('Y-m-d')) }}" 
                               class="form-input" max="{{ date('Y-m-d') }}" required>
                        @error('completed_at')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hours_spent" class="form-label">Hours Spent</label>
                        <input type="number" name="hours_spent" id="hours_spent" 
                               value="{{ old('hours_spent', $activity->hours_spent) }}" 
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
                    <input type="url" name="evidence_url" id="evidence_url" 
                           value="{{ old('evidence_url', $activity->evidence_url) }}" 
                           class="form-input" placeholder="https://github.com/yourproject or certificate link">
                    <p class="text-dark-500 text-xs mt-1">Link to your project, certificate, blog post, or other evidence</p>
                    @error('evidence_url')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Existing Attachments -->
                @if(!empty($activity->metadata['attachments']))
                    <div>
                        <label class="form-label">Current Attachments</label>
                        <div class="space-y-2">
                            @foreach($activity->metadata['attachments'] as $attachment)
                                <div class="flex items-center gap-2 p-2 bg-dark-800 rounded">
                                    <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-dark-200 text-sm flex-1 truncate">{{ $attachment['name'] }}</span>
                                    <a href="{{ Storage::url($attachment['path']) }}" target="_blank" 
                                       class="text-primary-400 hover:text-primary-300 text-sm">View</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <label class="form-label">Add New Attachments (Optional)</label>
                    <div class="border-2 border-dashed border-dark-600 rounded-lg p-6 text-center hover:border-primary-500 transition-colors cursor-pointer"
                         x-data="{ files: [] }"
                         @click="$refs.fileInput.click()">
                        <input type="file" name="attachments[]" multiple x-ref="fileInput" class="hidden" 
                               @change="files = Array.from($event.target.files)"
                               accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif">
                        <svg class="w-10 h-10 text-dark-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-dark-400 text-sm">
                            <span class="text-primary-400">Click to upload</span> additional files
                        </p>
                        
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
            
            <div x-data="{ tags: {{ json_encode($activity->metadata['tags'] ?? []) }}, newTag: '' }">
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

        <!-- Submit Buttons -->
        <div class="flex justify-between">
            <button type="button" onclick="if(confirm('Are you sure you want to delete this activity?')) { document.getElementById('delete-form').submit(); }" 
                    class="btn btn-outline text-red-400 border-red-500/30 hover:bg-red-600/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Activity
            </button>
            
            <div class="flex gap-3">
                <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update & Resubmit
                </button>
            </div>
        </div>
    </form>

    <!-- Delete Form (hidden) -->
    <form id="delete-form" action="{{ route('activities.destroy', $activity) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
