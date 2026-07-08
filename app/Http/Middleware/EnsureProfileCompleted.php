<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Profile Completed Middleware
 * 
 * Redirects users to complete their profile if not done.
 * Essential for fellows before accessing the dashboard.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Skip for admins and certain routes
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if on allowed routes
        $allowedRoutes = [
            'profile.complete',
            'profile.complete.store',
            'fellow.onboarding',
            'fellow.onboarding.*',
            'logout',
            'verification.*',
        ];

        foreach ($allowedRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        // Check if profile is completed
        if (!$user->profile_completed_at) {
            if ($user->hasRole('fellow')) {
                // Fellows without onboarding go to onboarding first
                if (!$user->onboarding_completed_at) {
                    return redirect()->route('fellow.onboarding')
                        ->with('info', 'Please complete your onboarding to continue.');
                }

                // Fellows who finished onboarding go to complete-profile
                return redirect()->route('profile.complete')
                    ->with('info', 'Almost there! Complete your profile to continue.');
            }

            return redirect()->route('profile.complete')
                ->with('warning', 'Please complete your profile to continue.');
        }

        return $next($request);
    }
}
