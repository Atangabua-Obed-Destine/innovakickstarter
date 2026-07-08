@extends('layouts.app')

@section('title', 'Edit Track: ' . $track->name)

@section('content')
<div class="space-y-6" x-data="trackEditor()">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="text-sm text-dark-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white">Admin</a>
                <span class="mx-2">›</span>
                <a href="{{ route('admin.tracks.index') }}" class="hover:text-white">Tracks</a>
                <span class="mx-2">›</span>
                <span class="text-dark-300">Edit</span>
            </nav>
            <h1 class="text-2xl font-bold text-white">Edit Track</h1>
            <p class="text-dark-400">Configure track settings and scoring rubric</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.tracks.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Tracks
            </a>
        </div>
    </div>

    <form action="{{ route('admin.tracks.update', $track) }}" method="POST" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Settings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Basic Information
                    </h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="form-label">Track Name</label>
                            <input type="text" id="name" name="name" 
                                   value="{{ old('name', $track->name) }}"
                                   class="form-input" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" rows="4" 
                                      class="form-input" required>{{ old('description', $track->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="short_description" class="form-label">Short Description</label>
                            <input type="text" id="short_description" name="short_description" 
                                   value="{{ old('short_description', $track->short_description) }}"
                                   class="form-input" maxlength="150" placeholder="Brief tagline for the track">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="form-label">Category</label>
                                <select id="category" name="category" class="form-input">
                                    @foreach(\App\Enums\TrackCategory::cases() as $category)
                                        <option value="{{ $category->value }}" 
                                                {{ old('category', $track->category?->value) === $category->value ? 'selected' : '' }}>
                                            {{ $category->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="icon" class="form-label">Icon</label>
                                <select id="icon" name="icon" class="form-input">
                                    @foreach(['code', 'cpu', 'cloud', 'shield-check', 'chart-bar', 'device-mobile', 'cube', 'sparkles', 'beaker', 'academic-cap'] as $icon)
                                        <option value="{{ $icon }}" {{ old('icon', $track->icon) === $icon ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $icon)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="color" class="form-label">Theme Color</label>
                                <select id="color" name="color" class="form-input">
                                    @foreach(['primary' => 'Purple', 'blue' => 'Blue', 'teal' => 'Teal', 'green' => 'Green', 'amber' => 'Amber', 'red' => 'Red', 'pink' => 'Pink'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('color', $track->color) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="order" class="form-label">Display Order</label>
                                <input type="number" id="order" name="order" 
                                       value="{{ old('order', $track->order ?? 0) }}"
                                       class="form-input" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scoring Rubric -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Career Capital Scoring Rubric
                    </h2>
                    <p class="text-dark-400 text-sm mb-6">
                        Configure how Career Capital is calculated for this track. Weights must total 100%.
                    </p>

                    <div class="space-y-4">
                        @php
                            $rubric = $track->scoring_rubric ?? \App\Enums\CareerCapitalCategory::defaultRubric();
                        @endphp

                        @foreach(\App\Enums\CareerCapitalCategory::cases() as $category)
                            <div class="flex items-center gap-4 p-4 bg-dark-800 rounded-lg">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center" 
                                     style="background-color: {{ $category->color() }}20;">
                                    <span class="text-lg">{{ $category->icon() }}</span>
                                </div>
                                <div class="flex-1">
                                    <label class="font-medium text-dark-200">{{ $category->label() }}</label>
                                    <p class="text-xs text-dark-500">{{ $category->description() }}</p>
                                </div>
                                <div class="w-24">
                                    <div class="relative">
                                        <input type="number" 
                                               name="category_weights[{{ $category->value }}]" 
                                               x-model.number="weights.{{ $category->value }}"
                                               @input="updateTotal()"
                                               class="form-input pr-8 text-center" 
                                               min="0" max="100" step="5">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-dark-500">%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Total -->
                        <div class="flex items-center justify-between p-4 rounded-lg border-2"
                             :class="totalWeight === 100 ? 'bg-green-900/20 border-green-600/50' : 'bg-red-900/20 border-red-600/50'">
                            <div class="flex items-center gap-3">
                                <template x-if="totalWeight === 100">
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <template x-if="totalWeight !== 100">
                                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </template>
                                <span class="font-semibold" :class="totalWeight === 100 ? 'text-green-400' : 'text-red-400'">
                                    Total Weight
                                </span>
                            </div>
                            <span class="text-2xl font-bold" :class="totalWeight === 100 ? 'text-green-400' : 'text-red-400'">
                                <span x-text="totalWeight"></span>%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Requirements & Outcomes -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Requirements & Outcomes
                    </h2>

                    <div class="space-y-6">
                        <!-- Requirements -->
                        <div>
                            <label class="form-label">Prerequisites (one per line)</label>
                            <textarea name="requirements_text" rows="4" class="form-input" 
                                      placeholder="Basic programming knowledge&#10;Familiarity with web technologies&#10;Git version control">{{ old('requirements_text', is_array($track->requirements) ? implode("\n", $track->requirements) : $track->requirements) }}</textarea>
                            <p class="text-xs text-dark-500 mt-1">What should fellows know before starting this track?</p>
                        </div>

                        <!-- Outcomes -->
                        <div>
                            <label class="form-label">Learning Outcomes (one per line)</label>
                            <textarea name="outcomes_text" rows="4" class="form-input"
                                      placeholder="Build production-ready applications&#10;Ace technical interviews&#10;Lead development teams">{{ old('outcomes_text', is_array($track->outcomes) ? implode("\n", $track->outcomes) : $track->outcomes) }}</textarea>
                            <p class="text-xs text-dark-500 mt-1">What will fellows achieve by completing this track?</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status Card -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Track Status</h2>
                    
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $track->is_active) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="font-medium text-dark-200">Active</span>
                                <p class="text-xs text-dark-500">Track is open for enrollment</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" 
                                   {{ old('is_featured', $track->is_featured) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="font-medium text-dark-200">Featured</span>
                                <p class="text-xs text-dark-500">Show on homepage and track listing</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Track Stats -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Statistics</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Enrolled Fellows</span>
                            <span class="text-2xl font-bold text-white">{{ $track->fellow_tracks_count ?? $track->fellowTracks()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Average Score</span>
                            <span class="text-2xl font-bold text-primary-400">{{ number_format($track->avg_score ?? 0, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-dark-400">Created</span>
                            <span class="text-dark-300">{{ $track->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tier Distribution Preview -->
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Tier Distribution</h2>
                    
                    @php
                        $tierCounts = [
                            'rookie' => $track->fellowTracks()->where('tier', 'rookie')->count(),
                            'intern' => $track->fellowTracks()->where('tier', 'intern')->count(),
                            'professional' => $track->fellowTracks()->where('tier', 'professional')->count(),
                            'elite' => $track->fellowTracks()->where('tier', 'elite')->count(),
                        ];
                        $total = array_sum($tierCounts) ?: 1;
                    @endphp

                    <div class="space-y-3">
                        @foreach(['rookie' => 'Rookie', 'intern' => 'Intern', 'professional' => 'Professional', 'elite' => 'Elite'] as $tier => $label)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-dark-400">{{ $label }}</span>
                                    <span class="text-dark-300">{{ $tierCounts[$tier] }}</span>
                                </div>
                                <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 
                                        @if($tier === 'elite') bg-gradient-to-r from-amber-500 to-yellow-400
                                        @elseif($tier === 'professional') bg-gradient-to-r from-purple-500 to-primary-400
                                        @elseif($tier === 'intern') bg-gradient-to-r from-blue-500 to-teal-400
                                        @else bg-gradient-to-r from-gray-500 to-gray-400
                                        @endif"
                                         style="width: {{ ($tierCounts[$tier] / $total) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Save Button -->
                <button type="submit" 
                        class="btn btn-primary w-full"
                        :disabled="totalWeight !== 100"
                        :class="{ 'opacity-50 cursor-not-allowed': totalWeight !== 100 }">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>

                <p x-show="totalWeight !== 100" class="text-center text-sm text-red-400">
                    Weights must total 100% to save
                </p>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function trackEditor() {
    return {
        weights: {
            @foreach(\App\Enums\CareerCapitalCategory::cases() as $category)
            {{ $category->value }}: {{ $track->scoring_rubric[$category->value] ?? $category->defaultWeight() }},
            @endforeach
        },
        totalWeight: 0,
        
        init() {
            this.updateTotal();
        },
        
        updateTotal() {
            this.totalWeight = Object.values(this.weights).reduce((sum, w) => sum + (parseInt(w) || 0), 0);
        }
    }
}
</script>
@endpush
@endsection
