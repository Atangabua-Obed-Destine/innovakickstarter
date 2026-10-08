<?php

namespace App\Http\Middleware;

use App\Enums\FellowType;
use App\Models\InternshipProfile;
use App\Services\AttestationService;
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

        // Check for overdue fees past grace period (blocks all fellows, regardless of type)
        $hasOverdueFees = \App\Models\Fee::forFellow($user->id)->overduePastGrace()->exists();
        if ($hasOverdueFees) {
            if ($request->routeIs('fees.*', 'logout', 'profile.*', 'verification.*')) {
                return $next($request);
            }
            return redirect()->route('fees.index')
                ->with('warning', 'Your access is restricted due to overdue fees. Please settle your balance or upload a payment receipt to restore full access.');
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
            app(AttestationService::class)->completeInternship($profile);

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
        // Let them reach onboarding, profile and logout so they can fix things,
        // and fees so a completed intern can settle up before collecting an attestation.
        if ($request->routeIs('fellow.onboarding*', 'fees.*', 'logout', 'profile.*', 'verification.*')) {
            return $next($request);
        }

        return redirect()->route('fellow.onboarding');
    }
}
