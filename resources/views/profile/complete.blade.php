@extends('layouts.minimal')

@section('title', 'Complete Your Profile')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-dark-100 mb-2">Complete Your Profile</h1>
            <p class="text-dark-400">Let's set up your profile to unlock all features of the Career Capital platform.</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-center gap-2 mb-8">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-medium">1</span>
                <span class="text-sm text-dark-300">Basic Info</span>
            </div>
            <div class="w-8 h-px bg-dark-700"></div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-dark-700 text-dark-400 flex items-center justify-center text-sm font-medium">2</span>
                <span class="text-sm text-dark-400">Skills</span>
            </div>
            <div class="w-8 h-px bg-dark-700"></div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-dark-700 text-dark-400 flex items-center justify-center text-sm font-medium">3</span>
                <span class="text-sm text-dark-400">Complete</span>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card">
            <div class="card-body p-8">
                <form method="POST" action="{{ route('profile.complete.store') }}" class="space-y-6">
                    @csrf

                    <!-- Bio Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            About You
                        </h3>

                        <!-- Headline (for fellows) -->
                        @if($user->hasRole('fellow'))
                        <div class="mb-4">
                            <label for="headline" class="block text-sm font-medium text-dark-300 mb-2">
                                Professional Headline <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="headline"
                                name="headline"
                                value="{{ old('headline', $user->headline) }}"
                                placeholder="e.g., Full-Stack Developer | React & Node.js"
                                class="input w-full @error('headline') border-red-500 @enderror"
                                required
                            >
                            @error('headline')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        @endif

                        <!-- Bio -->
                        <div class="mb-4">
                            <label for="bio" class="block text-sm font-medium text-dark-300 mb-2">
                                Bio <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="bio"
                                name="bio"
                                rows="4"
                                placeholder="Tell us about yourself, your experience, and what you're passionate about..."
                                class="input w-full @error('bio') border-red-500 @enderror"
                                required
                            >{{ old('bio', $user->bio) }}</textarea>
                            <p class="mt-1 text-xs text-dark-500">Minimum 50 characters</p>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-dark-300 mb-2">
                                Location <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location', $user->location) }}"
                                placeholder="e.g., Yaoundé, Cameroon"
                                class="input w-full @error('location') border-red-500 @enderror"
                                required
                            >
                            @error('location')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Professional Info (for fellows) -->
                    @if($user->hasRole('fellow'))
                    <div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Professional Info
                        </h3>

                        <!-- Career Track -->
                        <div class="mb-4">
                            <label for="track_id" class="block text-sm font-medium text-dark-300 mb-2">
                                Career Track <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="track_id"
                                name="track_id"
                                class="input w-full @error('track_id') border-red-500 @enderror"
                                required
                            >
                                <option value="" class="bg-dark-800 text-white">Select your career track</option>
                                @foreach($tracks as $track)
                                    <option value="{{ $track->id }}" class="bg-dark-800 text-white" {{ old('track_id') == $track->id ? 'selected' : '' }}>
                                        {{ $track->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('track_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- LinkedIn URL -->
                        <div class="mb-4">
                            <label for="linkedin_url" class="block text-sm font-medium text-dark-300 mb-2">
                                LinkedIn Profile <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="url"
                                id="linkedin_url"
                                name="linkedin_url"
                                value="{{ old('linkedin_url', $user->linkedin_url) }}"
                                placeholder="https://linkedin.com/in/yourprofile"
                                class="input w-full @error('linkedin_url') border-red-500 @enderror"
                                required
                            >
                            @error('linkedin_url')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- GitHub URL (optional) -->
                        <div class="mb-4">
                            <label for="github_url" class="block text-sm font-medium text-dark-300 mb-2">
                                GitHub Profile <span class="text-dark-500">(optional)</span>
                            </label>
                            <input
                                type="url"
                                id="github_url"
                                name="github_url"
                                value="{{ old('github_url', $user->github_url) }}"
                                placeholder="https://github.com/yourusername"
                                class="input w-full @error('github_url') border-red-500 @enderror"
                            >
                            @error('github_url')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Skills -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-dark-300 mb-2">
                                Skills <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" x-data="{ skills: [] }">
                                <input
                                    type="text"
                                    id="skills_input"
                                    placeholder="Type a skill and press Enter"
                                    class="input w-full"
                                    @keydown.enter.prevent="
                                        if ($el.value.trim() && skills.length < 20) {
                                            skills.push($el.value.trim());
                                            $el.value = '';
                                        }
                                    "
                                >
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(skill, index) in skills" :key="index">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-primary-600/20 text-primary-400 border border-primary-600/30">
                                            <span x-text="skill"></span>
                                            <input type="hidden" name="skills[]" :value="skill">
                                            <button type="button" @click="skills.splice(index, 1)" class="hover:text-red-400">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                                <p class="text-xs text-dark-500">Add at least 3 skills (max 20)</p>
                            </div>
                            @error('skills')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Company Info (for recruiters) -->
                    @if($user->hasRole('recruiter'))
                    <div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Company Information
                        </h3>

                        <!-- Company Name -->
                        <div class="mb-4">
                            <label for="company_name" class="block text-sm font-medium text-dark-300 mb-2">
                                Company Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="{{ old('company_name') }}"
                                placeholder="e.g., TechCorp International"
                                class="input w-full @error('company_name') border-red-500 @enderror"
                                required
                            >
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company Description -->
                        <div class="mb-4">
                            <label for="company_description" class="block text-sm font-medium text-dark-300 mb-2">
                                Company Description <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="company_description"
                                name="company_description"
                                rows="3"
                                placeholder="Brief description of your company..."
                                class="input w-full @error('company_description') border-red-500 @enderror"
                                required
                            >{{ old('company_description') }}</textarea>
                            @error('company_description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Availability (for fellows) -->
                    @if($user->hasRole('fellow'))
                    <div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Availability
                        </h3>

                        <div class="mb-4">
                            <label for="availability" class="block text-sm font-medium text-dark-300 mb-2">
                                When are you available to start?
                            </label>
                            <select
                                id="availability"
                                name="availability"
                                class="input w-full"
                            >
                                <option value="immediate" class="bg-dark-800 text-white" {{ old('availability') == 'immediate' ? 'selected' : '' }}>Immediately</option>
                                <option value="2_weeks" class="bg-dark-800 text-white" {{ old('availability') == '2_weeks' ? 'selected' : '' }}>In 2 weeks</option>
                                <option value="1_month" class="bg-dark-800 text-white" {{ old('availability') == '1_month' ? 'selected' : '' }}>In 1 month</option>
                                <option value="3_months" class="bg-dark-800 text-white" {{ old('availability') == '3_months' ? 'selected' : '' }}>In 3 months</option>
                            </select>
                        </div>

                        <!-- Open to Opportunities -->
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="open_to_opportunities"
                                name="open_to_opportunities"
                                value="1"
                                {{ old('open_to_opportunities', true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-dark-600 bg-dark-800 text-primary-600 focus:ring-primary-500"
                            >
                            <label for="open_to_opportunities" class="text-sm text-dark-300">
                                I'm open to job opportunities and want recruiters to see my profile
                            </label>
                        </div>
                    </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full py-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Complete Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Skip Link -->
        <div class="text-center mt-4">
            <a href="{{ route('dashboard') }}" class="text-sm text-dark-400 hover:text-dark-300 transition-colors">
                Skip for now, I'll complete later
            </a>
        </div>
    </div>
</div>
@endsection
