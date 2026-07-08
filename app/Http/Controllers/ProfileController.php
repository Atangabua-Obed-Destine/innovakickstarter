<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\Track;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Profile Controller
 * 
 * Handles user profile management including:
 * - Profile viewing and editing
 * - Avatar upload
 * - Public profile display
 * - Profile completion flow
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ProfileController extends Controller
{
    /**
     * Display the public talent directory.
     */
    public function directory(): View
    {
        return view('public.talent.directory');
    }

    /**
     * Display the current user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load([
            'fellowTracks.track',
            'activities' => fn($q) => $q->where('status', 'approved')->latest()->limit(5),
        ]);

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Display the profile edit form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            
            $path = $request->file('avatar')->store('avatars/' . $user->uuid, 'public');
            $validated['avatar_url'] = $path;
        }

        // Handle resume upload (for fellows)
        if ($request->hasFile('resume')) {
            if ($user->resume_url && Storage::disk('public')->exists($user->resume_url)) {
                Storage::disk('public')->delete($user->resume_url);
            }
            
            $path = $request->file('resume')->store('resumes/' . $user->uuid, 'public');
            $validated['resume_url'] = $path;
        }

        // Handle company logo (for recruiters)
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('companies/' . $user->uuid, 'public');
            $validated['company_logo'] = $path;
        }

        // Check if profile is now complete
        $wasComplete = $user->profile_completed_at !== null;
        
        $user->update($validated);

        // Mark profile as complete if all required fields are filled
        if (!$wasComplete && $this->isProfileComplete($user->fresh())) {
            $user->update(['profile_completed_at' => now()]);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Display the profile completion form (for new users).
     */
    public function complete(Request $request): View
    {
        $user = $request->user();

        // Get available tracks for fellows
        $tracks = $user->hasRole('fellow') 
            ? Track::where('is_active', true)->get()
            : collect();

        return view('profile.complete', [
            'user' => $user,
            'tracks' => $tracks,
        ]);
    }

    /**
     * Store completed profile.
     */
    public function storeComplete(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Validate based on role
        $rules = [
            'bio' => ['required', 'string', 'min:50', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
        ];

        if ($user->hasRole('fellow')) {
            $rules = array_merge($rules, [
                'headline' => ['required', 'string', 'max:150'],
                'linkedin_url' => ['required', 'url', 'regex:/linkedin\.com/'],
                'skills' => ['required', 'array', 'min:3', 'max:20'],
                'track_id' => ['required', 'exists:tracks,id'],
            ]);
        }

        if ($user->hasRole('recruiter')) {
            $rules = array_merge($rules, [
                'company_name' => ['required', 'string', 'max:255'],
                'company_description' => ['required', 'string', 'min:50', 'max:2000'],
            ]);
        }

        $validated = $request->validate($rules);

        // Update user profile
        $user->update([
            'bio' => $validated['bio'],
            'location' => $validated['location'],
            'headline' => $validated['headline'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'github_url' => $request->input('github_url'),
            'availability' => $request->input('availability', 'immediate'),
            'open_to_opportunities' => $request->boolean('open_to_opportunities'),
            'profile_completed_at' => now(),
        ]);

        // Enroll fellow in selected track (use updateOrCreate to avoid duplicates)
        if ($user->hasRole('fellow') && isset($validated['track_id'])) {
            // First, set all existing tracks as non-primary
            $user->fellowTracks()->update(['is_primary' => false]);
            
            // Then create or update the selected track
            \App\Models\FellowTrack::updateOrCreate(
                [
                    'fellow_id' => $user->id,
                    'track_id' => $validated['track_id'],
                ],
                [
                    'is_primary' => true,
                    'effort_allocation' => 100,
                    'started_at' => now(),
                ]
            );
        }

        // Log completion using Laravel Log (not AuditLog which requires more fields)
        \Illuminate\Support\Facades\Log::info('Profile completed', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Redirect to appropriate dashboard
        return match (true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasRole('recruiter') => redirect()->route('recruiter.dashboard'),
            $user->hasRole('mentor') => redirect()->route('mentor.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }

    /**
     * Display a public fellow profile.
     */
    public function public(User $user): View
    {
        // Only show public profiles
        if (!$user->is_public || !$user->hasRole('fellow')) {
            abort(404);
        }

        $user->load([
            'fellowTracks.track',
            'activities' => fn($q) => $q->where('status', 'approved')
                ->where('is_public', true)
                ->latest()
                ->limit(10),
        ]);

        $primaryTrack = $user->primaryTrack;

        return view('profile.public', [
            'user' => $user,
            'primaryTrack' => $primaryTrack,
        ]);
    }

    /**
     * Display a public fellow profile by ID (for directory links).
     */
    public function publicById(User $user): View
    {
        // Only show public profiles of fellows
        if (!$user->is_public || !$user->hasRole('fellow')) {
            abort(404);
        }

        $user->load([
            'fellowTracks.track',
            'activities' => fn($q) => $q->where('status', 'approved')
                ->where('is_public', true)
                ->latest()
                ->limit(10),
            'interviewSessions' => fn($q) => $q->where('status', 'completed')->latest()->limit(5),
        ]);

        $primaryTrack = $user->primaryTrack;

        // Calculate career capital breakdown using the service
        $careerCapitalBreakdown = [];
        if ($primaryTrack && $primaryTrack->track) {
            $calculator = app(\App\Services\CareerCapitalCalculator::class);
            $track = $primaryTrack->track;
            
            $careerCapitalBreakdown = [
                ['name' => 'Technical Skills', 'score' => round($calculator->calculateTechnicalScore($user, $track)), 'color' => 'from-primary-500 to-purple-600', 'desc' => 'Projects & Contributions', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                ['name' => 'Interview Performance', 'score' => round($calculator->calculateInterviewScore($user, $track)), 'color' => 'from-blue-500 to-cyan-600', 'desc' => 'Mock Interview Results', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ['name' => 'Portfolio Quality', 'score' => round($calculator->calculatePortfolioScore($user, $track)), 'color' => 'from-teal-500 to-green-600', 'desc' => 'Showcase Projects', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['name' => 'Collaboration', 'score' => round($calculator->calculateCollaborationScore($user, $track)), 'color' => 'from-amber-500 to-orange-600', 'desc' => 'Teamwork & Community', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['name' => 'Continuous Learning', 'score' => round($calculator->calculateLearningScore($user, $track)), 'color' => 'from-rose-500 to-pink-600', 'desc' => 'Growth & Certifications', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ];
        }

        // Get fellow's skills from activities (tech_stack)
        $skills = collect();
        foreach ($user->activities as $activity) {
            if ($activity->tech_stack) {
                $skills = $skills->merge(collect($activity->tech_stack));
            }
        }
        $topSkills = $skills->countBy()->sortDesc()->take(10)->keys()->toArray();

        // Get experience from activities
        $experience = $user->activities
            ->where('type', 'project')
            ->take(3)
            ->map(fn($a) => [
                'title' => $a->title,
                'company' => $a->organization ?? 'Personal Project',
                'period' => $a->created_at->format('M Y'),
                'desc' => \Illuminate\Support\Str::limit($a->description, 200),
            ])->toArray();

        return view('public.profile.show', [
            'fellow' => $user,
            'primaryTrack' => $primaryTrack,
            'careerCapitalBreakdown' => $careerCapitalBreakdown,
            'topSkills' => $topSkills,
            'experience' => $experience,
        ]);
    }

    /**
     * Toggle profile visibility.
     */
    public function toggleVisibility(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['is_public' => !$user->is_public]);

        $status = $user->is_public ? 'visible to recruiters' : 'hidden from recruiters';

        return redirect()->route('profile.show')
            ->with('success', "Your profile is now {$status}.");
    }

    /**
     * Check if profile has all required fields.
     */
    protected function isProfileComplete(User $user): bool
    {
        // Basic requirements for all users
        $basicComplete = !empty($user->bio) && !empty($user->location);

        if ($user->hasRole('fellow')) {
            return $basicComplete 
                && !empty($user->headline)
                && !empty($user->linkedin_url)
                && !empty($user->skills)
                && $user->fellowTracks()->exists();
        }

        if ($user->hasRole('recruiter')) {
            return $basicComplete 
                && !empty($user->company_name)
                && !empty($user->company_description);
        }

        return $basicComplete;
    }
}
