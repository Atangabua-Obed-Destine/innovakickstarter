<?php

namespace App\Http\Controllers\Fellow;

use App\Enums\FellowType;
use App\Http\Controllers\Controller;
use App\Models\InternshipProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

/**
 * Fellow Onboarding Controller
 * 
 * Handles the multi-step onboarding wizard for new fellows.
 * Supports three fellow types: Academic Intern, Corporate Intern,
 * and Independent Fellow, each with different data collection needs.
 * 
 * Steps:
 *   1. Welcome (information only)
 *   2. Fellow Type selection
 *   3. Internship Details (conditional — academic/corporate only)
 *   4. Profile completion
 *   5. Goals selection
 *   6. All Set
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class OnboardingController extends Controller
{
    /**
     * Display the onboarding wizard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $internshipProfile = $user->internshipProfile;

        return view('fellow.onboarding', [
            'user' => $user,
            'internshipProfile' => $internshipProfile,
            'fellowTypes' => FellowType::cases(),
            'academicLevels' => InternshipProfile::ACADEMIC_LEVELS,
            'predefinedDurations' => InternshipProfile::PREDEFINED_DURATIONS,
            'internshipStatusBanner' => $this->buildStatusBanner($internshipProfile),
        ]);
    }

    /**
     * Build a status banner payload for the onboarding page based on the
     * current internship profile review state. Null when nothing to show.
     */
    protected function buildStatusBanner(?InternshipProfile $profile): ?array
    {
        if (!$profile) {
            return null;
        }

        return match ($profile->status) {
            InternshipProfile::STATUS_PENDING => [
                'tone' => 'amber',
                'icon' => '⏳',
                'title' => 'Awaiting admin review',
                'message' => 'An admin will verify your institution, supervisor and letter, then confirm the official duration. In the meantime, please complete the rest of your onboarding steps below.',
            ],
            InternshipProfile::STATUS_NEEDS_REVISION => [
                'tone' => 'orange',
                'icon' => '✏️',
                'title' => 'Please revise your submission',
                'message' => $profile->review_notes ?: 'The admin has asked for some updates. Please review your details and resubmit.',
            ],
            InternshipProfile::STATUS_REJECTED => [
                'tone' => 'red',
                'icon' => '⛔',
                'title' => 'Internship not approved',
                'message' => $profile->review_notes ?: 'Your internship profile was rejected. Contact the admin for next steps.',
            ],
            InternshipProfile::STATUS_APPROVED => [
                'tone' => 'blue',
                'icon' => '✅',
                'title' => 'Approved — starts ' . optional($profile->approved_start_date)->format('M j, Y'),
                'message' => 'Your internship is approved. Access opens on '
                    . optional($profile->approved_start_date)->format('M j, Y')
                    . '. Please complete any remaining onboarding steps below to proceed to your dashboard.',
            ],
            InternshipProfile::STATUS_ACTIVE => [
                'tone' => 'emerald',
                'icon' => '🟢',
                'title' => 'Internship active',
                'message' => ($profile->days_remaining ?? 0) . ' days remaining until '
                    . optional($profile->approved_end_date)->format('M j, Y') . '.',
            ],
            InternshipProfile::STATUS_COMPLETED => [
                'tone' => 'dark',
                'icon' => '🏁',
                'title' => 'Internship completed',
                'message' => 'Your approved internship period has ended'
                    . ($profile->approved_end_date ? ' on ' . $profile->approved_end_date->format('M j, Y') : '')
                    . '. Contact the admin if you need an extension.',
            ],
            default => null,
        };
    }

    /**
     * Save fellow type selection (Step 2).
     */
    public function saveFellowType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fellow_type' => ['required', new Enum(FellowType::class)],
        ]);

        $user = $request->user();
        $user->update(['fellow_type' => $validated['fellow_type']]);

        return response()->json([
            'success' => true,
            'message' => 'Fellow type saved.',
            'fellow_type' => $validated['fellow_type'],
            'requires_internship_details' => FellowType::from($validated['fellow_type'])->requiresInternshipDetails(),
        ]);
    }

    /**
     * Save internship details (Step 3 — academic/corporate only).
     */
    public function saveInternshipDetails(Request $request): JsonResponse
    {
        $user = $request->user();
        $fellowType = $user->fellow_type;

        // Ensure this is an academic or corporate fellow
        if (!$fellowType || !$fellowType->requiresInternshipDetails()) {
            return response()->json([
                'success' => false,
                'message' => 'Internship details are only required for academic and corporate fellows.',
            ], 422);
        }

        // Build validation rules based on fellow type
        $rules = [
            'institution_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'supervisor_name' => 'required|string|max:255',
            'supervisor_email' => 'nullable|email|max:255',
            'supervisor_phone' => 'nullable|string|max:50',
            'duration_type' => 'required|in:predefined,custom',
            'notes' => 'nullable|string|max:1000',
        ];

        // Academic-specific fields
        if ($fellowType->requiresAcademicFields()) {
            $rules['academic_level'] = 'required|string|in:' . implode(',', array_keys(InternshipProfile::ACADEMIC_LEVELS));
            $rules['student_id'] = 'nullable|string|max:100';
        }

        // Duration fields
        $rules['predefined_duration_months'] = 'required_if:duration_type,predefined|nullable|integer|in:' . implode(',', InternshipProfile::PREDEFINED_DURATIONS);
        $rules['start_date'] = 'required_if:duration_type,custom|nullable|date|after_or_equal:today';
        $rules['end_date'] = 'required_if:duration_type,custom|nullable|date|after:start_date';

        // Internship letter upload
        $rules['internship_letter'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'; // 5MB max

        $validated = $request->validate($rules);

        // Handle file upload
        $letterPath = null;
        if ($request->hasFile('internship_letter')) {
            $letterPath = $request->file('internship_letter')
                ->store('internship-letters/' . $user->uuid, 'public');
        }

        // Create or update internship profile
        $profileData = [
            'type' => $fellowType->value,
            'institution_name' => $validated['institution_name'],
            'department' => $validated['department'] ?? null,
            'supervisor_name' => $validated['supervisor_name'],
            'supervisor_email' => $validated['supervisor_email'] ?? null,
            'supervisor_phone' => $validated['supervisor_phone'] ?? null,
            'duration_type' => $validated['duration_type'],
            'predefined_duration_months' => $validated['predefined_duration_months'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        $existingProfile = InternshipProfile::where('user_id', $user->id)->first();
        if (!$existingProfile || !in_array($existingProfile->status, [InternshipProfile::STATUS_APPROVED, InternshipProfile::STATUS_ACTIVE])) {
            $profileData['status'] = InternshipProfile::STATUS_PENDING;
        }

        // Academic-specific fields
        if ($fellowType->requiresAcademicFields()) {
            $profileData['academic_level'] = $validated['academic_level'];
            $profileData['student_id'] = $validated['student_id'] ?? null;
        }

        // Add letter path if uploaded
        if ($letterPath) {
            $profileData['internship_letter_path'] = $letterPath;
        }

        $profile = InternshipProfile::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        return response()->json([
            'success' => true,
            'message' => 'Internship details saved.',
            'profile_id' => $profile->uuid,
        ]);
    }

    /**
     * Save profile information (Step 4).
     */
    public function saveProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $request->user()->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Basic info saved.',
        ]);
    }

    /**
     * Save goal selections (Step 5).
     */
    public function saveGoals(Request $request): JsonResponse
    {
        $allowedGoals = [
            'first_internship', 'first_job', 'career_switch', 'promotion',
            'freelance', 'startup', 'portfolio', 'network',
        ];
        $allowedTimelines = ['3_months', '6_months', '12_months', 'exploring'];

        $validated = $request->validate([
            'exploring' => 'sometimes|boolean',
            'goals' => 'required_without:exploring|array',
            'goals.*' => 'string|in:' . implode(',', $allowedGoals),
            'primary_goal' => 'nullable|string|in:' . implode(',', $allowedGoals),
            'goal_timeline' => 'nullable|string|in:' . implode(',', $allowedTimelines),
            'goal_success_vision' => 'nullable|string|max:250',
        ]);

        $exploring = (bool) ($validated['exploring'] ?? false);
        $goals = $validated['goals'] ?? [];
        $primary = $validated['primary_goal'] ?? null;

        if ($exploring) {
            $goals = [];
            $primary = null;
        }

        // primary_goal must be one of the selected goals; otherwise reset it.
        if ($primary && !in_array($primary, $goals, true)) {
            $primary = $goals[0] ?? null;
        }

        $user = $request->user();
        $user->update([
            'skills' => array_merge($user->skills ?? [], [
                'goals' => $goals,
                'primary_goal' => $primary,
                'goal_timeline' => $validated['goal_timeline'] ?? ($exploring ? 'exploring' : null),
                'goal_success_vision' => $validated['goal_success_vision'] ?? null,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Goals saved.',
        ]);
    }

    /**
     * Complete onboarding (Step 6).
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'onboarding_completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding complete! Now let\'s complete your profile.',
            'redirect' => route('profile.complete'),
        ]);
    }
}
