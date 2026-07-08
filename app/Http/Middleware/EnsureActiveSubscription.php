<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Active Subscription Middleware
 * 
 * Ensures recruiters have an active subscription to access marketplace.
 * Redirects to subscription page if expired/missing.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class EnsureActiveSubscription
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

        // Only applies to recruiters
        if (!$user->hasRole('recruiter')) {
            return $next($request);
        }

        // Check if on allowed routes (subscription pages)
        $allowedRoutes = [
            'recruiter.subscription.*',
            'recruiter.dashboard',
            'profile.*',
            'logout',
        ];

        foreach ($allowedRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        // Check for active subscription
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->route('recruiter.subscription.index')
                ->with('warning', 'An active subscription is required to access the talent marketplace.');
        }

        return $next($request);
    }
}
