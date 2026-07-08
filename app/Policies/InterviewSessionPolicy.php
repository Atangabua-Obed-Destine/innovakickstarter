<?php

namespace App\Policies;

use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Interview Session Policy
 * 
 * Controls access to interview sessions based on user role and ownership.
 */
class InterviewSessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any interview sessions.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all, fellows can view their own list
        return true;
    }

    /**
     * Determine if the user can view the interview session.
     */
    public function view(User $user, InterviewSession $interview): bool
    {
        // Admin can view any interview
        if ($user->isAdmin()) {
            return true;
        }

        // Fellows can view their own interviews
        return $user->id === $interview->fellow_id;
    }

    /**
     * Determine if the user can create interview sessions.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create an interview
        return true;
    }

    /**
     * Determine if the user can update the interview session.
     */
    public function update(User $user, InterviewSession $interview): bool
    {
        // Admin can update any interview
        if ($user->isAdmin()) {
            return true;
        }

        // Fellows can update their own interviews (send messages, etc.)
        return $user->id === $interview->fellow_id;
    }

    /**
     * Determine if the user can delete the interview session.
     */
    public function delete(User $user, InterviewSession $interview): bool
    {
        // Only admins can delete interviews
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the interview session.
     */
    public function restore(User $user, InterviewSession $interview): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the interview session.
     */
    public function forceDelete(User $user, InterviewSession $interview): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can start the interview.
     */
    public function start(User $user, InterviewSession $interview): bool
    {
        return $user->id === $interview->fellow_id;
    }

    /**
     * Determine if the user can complete the interview.
     */
    public function complete(User $user, InterviewSession $interview): bool
    {
        // Admin or the interview owner
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $interview->fellow_id;
    }
}
