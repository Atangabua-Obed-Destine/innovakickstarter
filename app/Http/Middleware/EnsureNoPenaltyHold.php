<?php

namespace App\Http\Middleware;

use App\Services\StandingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A fellow with an unpaid (and not waived) weekly-bar penalty can still learn,
 * review and ask for help, but can't start or submit activities, take interviews
 * or join competitions until it is settled.
 */
class EnsureNoPenaltyHold
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('fellow') || !app(StandingService::class)->onHold($user)) {
            return $next($request);
        }

        $message = 'Your progress is on hold until your weekly-bar penalty is paid (or waived by the team). You can still learn, review and ask for help.';
        if ($request->expectsJson()) {
            return response()->json(['ok' => false, 'error' => $message, 'hold' => true], 423);
        }

        return redirect()->route('standing.show')->with('error', $message);
    }
}
