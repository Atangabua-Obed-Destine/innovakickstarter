@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
        <p class="text-dark-400 mt-1">Manage your profile information and preferences.</p>
    </div>

    <!-- Profile Card -->
    <div class="card p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8">
            <!-- Avatar -->
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-white text-3xl font-bold">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
                    @else
                        {{ strtoupper(collect(explode(' ', auth()->user()->name ?? 'U'))->map(fn($w) => substr($w, 0, 1))->take(2)->join('')) }}
                    @endif
                </div>
                <label class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-dark-700 border-2 border-dark-900 flex items-center justify-center cursor-pointer hover:bg-dark-600 transition-colors">
                    <svg class="w-4 h-4 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <input type="file" class="hidden" accept="image/*">
                </label>
            </div>
            
            <div>
                <h2 class="text-xl font-semibold text-white">{{ auth()->user()->name ?? 'Fellow' }}</h2>
                <p class="text-dark-400">{{ auth()->user()->email }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="badge badge-primary">Fellow</span>
                    <span class="badge {{ $tierClass ?? 'tier-rookie' }}">{{ $tier ?? 'Rookie' }} Level</span>
                </div>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personal Information
                </h3>
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                               class="form-input" required>
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" value="{{ auth()->user()->email }}" 
                               class="form-input bg-dark-700 cursor-not-allowed" disabled>
                        <p class="text-dark-500 text-xs mt-1">Contact admin to change email</p>
                    </div>
                    
                    <div>
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" 
                               class="form-input" placeholder="+237 6XX XXX XXX">
                        @error('phone')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-dark-700">

            <!-- Professional Information -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Professional Information
                </h3>
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Headline / Title</label>
                        <input type="text" name="headline" value="{{ old('headline', $profile->headline ?? '') }}" 
                               class="form-input" placeholder="e.g., Aspiring Software Developer">
                    </div>
                    
                    <div>
                        <label class="form-label">Location</label>
                        <input type="text" name="location" value="{{ old('location', $profile->location ?? '') }}" 
                               class="form-input" placeholder="e.g., Douala, Cameroon">
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" rows="4" class="form-input" 
                                  placeholder="Tell recruiters about yourself, your goals, and what makes you unique...">{{ old('bio', $profile->bio ?? '') }}</textarea>
                        <p class="text-dark-500 text-xs mt-1">This will be visible on your public profile</p>
                    </div>
                </div>
            </div>

            <hr class="border-dark-700">

            <!-- Career Track -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Career Track
                </h3>
                
                <div>
                    <label class="form-label">Primary Career Track</label>
                    <select name="track_id" class="form-input">
                        <option value="">Select a track...</option>
                        @foreach($tracks ?? [] as $track)
                            <option value="{{ $track->id }}" {{ (old('track_id', $profile->track_id ?? '') == $track->id) ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="border-dark-700">

            <!-- Social Links -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Social Links
                </h3>
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">LinkedIn Profile</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-dark-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </span>
                            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url ?? '') }}" 
                                   class="form-input pl-10" placeholder="https://linkedin.com/in/yourprofile">
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">GitHub Profile</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-dark-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </span>
                            <input type="url" name="github_url" value="{{ old('github_url', $profile->github_url ?? '') }}" 
                                   class="form-input pl-10" placeholder="https://github.com/yourusername">
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Personal Website</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-dark-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                            </span>
                            <input type="url" name="website_url" value="{{ old('website_url', $profile->website_url ?? '') }}" 
                                   class="form-input pl-10" placeholder="https://yourwebsite.com">
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Portfolio URL</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-dark-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>
                            <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $profile->portfolio_url ?? '') }}" 
                                   class="form-input pl-10" placeholder="https://behance.net/yourportfolio">
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-dark-700">

            <!-- Privacy Settings -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Privacy Settings
                </h3>
                
                <div class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" 
                               {{ old('is_public', $profile->is_public ?? true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500 focus:ring-offset-dark-800">
                        <span class="text-dark-200">Make my profile visible to recruiters in the marketplace</span>
                    </label>
                    
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="show_email" value="1" 
                               {{ old('show_email', $profile->show_email ?? false) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500 focus:ring-offset-dark-800">
                        <span class="text-dark-200">Show my email on public profile</span>
                    </label>
                    
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="open_to_opportunities" value="1" 
                               {{ old('open_to_opportunities', $profile->open_to_opportunities ?? true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500 focus:ring-offset-dark-800">
                        <span class="text-dark-200">I'm open to job opportunities</span>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="card p-6 border-red-500/30">
        <h3 class="text-lg font-semibold text-red-400 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Danger Zone
        </h3>
        <p class="text-dark-400 mb-4">These actions are irreversible. Please be certain.</p>
        
        <div class="flex flex-wrap gap-3">
            <button type="button" class="btn bg-red-600/20 text-red-400 border border-red-500/30 hover:bg-red-600/30">
                Delete Account
            </button>
            <button type="button" class="btn btn-secondary">
                Download My Data
            </button>
        </div>
    </div>
</div>
@endsection
