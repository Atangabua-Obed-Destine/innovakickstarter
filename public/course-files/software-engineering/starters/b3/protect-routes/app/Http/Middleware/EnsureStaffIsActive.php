<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Runs after auth:sanctum. A token can outlive a job: if the staff member has left,
// block every request even though their token is still valid.
class EnsureStaffIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // TODO 1: get the logged-in user with $request->user().
        // TODO 2: if there is a user and is_active is false, stop here and answer
        //         403 {"message": "This staff account is deactivated."}
        // TODO 3: otherwise let the request continue to the controller:
        return $next($request);
    }
}
