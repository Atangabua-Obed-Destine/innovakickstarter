<?php

namespace App\Http\Middleware;

use App\Enums\FellowType;
use App\Models\InternshipProfile;
use App\Models\Notification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Internship Approved Middleware
 *
 * Blocks academic / corporate fellows from the platform until an admin
 * has reviewed and approved their internship profile. Also auto-transitions
 * an approved internship to "completed" once the admin-approved end date passes.
 */
class EnsureInternshipApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('fellow')) {
            return $next($request);
        }

        // Independent (or unset) fellows don't need internship approval.
        $type = $user->fellow_type;
        if (!$type || !$type instanceof FellowType || !$type->requiresInternshipDetails()) {
            return $next($request);
        }

        /** @var InternshipProfile|null $profile */
        $profile = $user->internshipProfile()->first();

        // No profile submitted yet — fellow must complete onboarding.
        if (!$profile) {
            if ($request->routeIs('fellow.onboarding*', 'logout', 'profile.*')) {
                return $next($request);
            }
            return redirect()->route('fellow.onboarding')
                ->with('warning', 'Please complete your internship details to continue.');
        }

        // Auto-transition expired approvals.
        if (
            in_array($profile->status, [InternshipProfile::STATUS_APPROVED, InternshipProfile::STATUS_ACTIVE], true)
            && $profile->is_expired
            && !$profile->completed_at
        ) {
            $profile->update([
                'status' => InternshipProfile::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'internship_status',
                'title' => 'Internship period ended',
                'message' => "Your approved internship ended on {$profile->approved_end_date->format('M j, Y')}. Contact your admin if you'd like to extend it.",
                'action_url' => route('fellow.onboarding'),
            ]);

            $profile->refresh();
        }

        // Approved + within window = full access.
        if (
            in_array($profile->status, [InternshipProfile::STATUS_APPROVED, InternshipProfile::STATUS_ACTIVE], true)
            && !$profile->is_expired
        ) {
            return $next($request);
        }

        // Anything else (pending, needs_revision, rejected, completed) is blocked.
        // Let them reach onboarding, profile and logout so they can fix things.
        if ($request->routeIs('fellow.onboarding*', 'logout', 'profile.*', 'verification.*')) {
            return $next($request);
        }

        return redirect()->route('fellow.onboarding');
    }
}
