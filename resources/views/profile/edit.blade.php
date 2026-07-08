@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Profile</h1>
            <p class="text-dark-400 mt-1">Update your personal information and career preferences.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Cancel
        </a>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PATCH')

        <!-- Personal Information -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Personal Information</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-dark-300 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-dark-300 mb-1">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" 
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('username') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-dark-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                    @error('email') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-dark-300 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" 
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('phone') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-dark-300 mb-1">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $user->location) }}" placeholder="e.g. Douala, Cameroon"
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('location') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="availability" class="block text-sm font-medium text-dark-300 mb-1">Availability</label>
                    <select name="availability" id="availability"
                            class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="full-time" {{ old('availability', $user->availability) === 'full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="part-time" {{ old('availability', $user->availability) === 'part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="freelance" {{ old('availability', $user->availability) === 'freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="not-available" {{ old('availability', $user->availability) === 'not-available' ? 'selected' : '' }}>Not Available</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label for="bio" class="block text-sm font-medium text-dark-300 mb-1">Bio</label>
                <textarea name="bio" id="bio" rows="4" placeholder="Tell us about yourself..."
                          class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">{{ old('bio', $user->bio) }}</textarea>
                @error('bio') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Links & Social -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Links & Social</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label for="linkedin_url" class="block text-sm font-medium text-dark-300 mb-1">LinkedIn URL</label>
                    <input type="url" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="https://linkedin.com/in/..."
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label for="github_url" class="block text-sm font-medium text-dark-300 mb-1">GitHub URL</label>
                    <input type="url" name="github_url" id="github_url" value="{{ old('github_url', $user->github_url) }}" placeholder="https://github.com/..."
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
                <div>
                    <label for="portfolio_url" class="block text-sm font-medium text-dark-300 mb-1">Portfolio URL</label>
                    <input type="url" name="portfolio_url" id="portfolio_url" value="{{ old('portfolio_url', $user->portfolio_url) }}" placeholder="https://..."
                           class="w-full px-4 py-2 bg-dark-800 border border-dark-700 rounded-lg text-white placeholder-dark-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Privacy -->
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Privacy Settings</h3>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_public" value="0">
                <input type="checkbox" name="is_public" value="1" {{ old('is_public', $user->is_public) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500">
                <div>
                    <p class="text-dark-200 font-medium">Public Profile</p>
                    <p class="text-dark-500 text-sm">Make your profile visible to recruiters and the public talent directory.</p>
                </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer mt-4">
                <input type="hidden" name="open_to_opportunities" value="0">
                <input type="checkbox" name="open_to_opportunities" value="1" {{ old('open_to_opportunities', $user->open_to_opportunities) ? 'checked' : '' }}
                       class="w-5 h-5 rounded border-dark-600 bg-dark-800 text-primary-500 focus:ring-primary-500">
                <div>
                    <p class="text-dark-200 font-medium">Open to Opportunities</p>
                    <p class="text-dark-500 text-sm">Let recruiters know you're actively looking for roles.</p>
                </div>
            </label>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('profile.show') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
