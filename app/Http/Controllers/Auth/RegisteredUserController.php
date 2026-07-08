<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Registration Controller
 * 
 * Handles user registration with role-based setup.
 * Fellows get enrolled in track selection, recruiters get trial setup.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $role = $request->query('role', 'fellow');
        
        return view('auth.register', [
            'role' => $role,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'fellow');
        
        // Validate based on role
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:fellow,recruiter'],
        ];

        // Add recruiter-specific fields
        if ($role === 'recruiter') {
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['company_website'] = ['nullable', 'url', 'max:255'];
            $rules['phone'] = ['required', 'string', 'max:50'];
        }

        $validated = $request->validate($rules);

        // Create user
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => UserRole::from($validated['role']),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'is_active' => true,
            'email_verified_at' => \App\Models\AdminSetting::get('email_verification_required', true) ? null : now(),
        ]);

        // Assign Spatie role
        $user->assignRole($validated['role']);

        event(new Registered($user));

        Auth::login($user);

        // Log registration
        \App\Models\AuditLog::create([
            'fellow_id' => $user->id,
            'admin_id' => $user->id, // Self-registration
            'action' => 'registered',
            'category' => 'profile',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'justification' => "New {$validated['role']} account created via self-registration",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Redirect based on role
        return $this->redirectByRole($user);
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectByRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::MENTOR => redirect()->route('mentor.dashboard'),
            UserRole::RECRUITER => redirect()->route('recruiter.onboarding'),
            default => redirect()->route('fellow.onboarding'),
        };
    }
}
