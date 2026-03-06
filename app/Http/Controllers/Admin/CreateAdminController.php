<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateAdminController extends Controller
{
    /**
     * Show the form for creating a new admin user.
     * Both Normal Admin and Tech Admin can access this page.
     * Normal Admin can only create admin role.
     * Tech Admin can create both admin and tech_admin roles.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Check authorization - both admin and tech_admin can access
        if (!$user || !$user->isAnyAdmin()) {
            abort(403, 'Unauthorized. Required role: admin or tech_admin');
        }

        return view('admin.create-admin', [
            'canCreateTechAdmin' => $user->isTechAdmin(),
        ]);
    }

    /**
     * Store a newly created admin user.
     * Both Normal Admin and Tech Admin can create admin users.
     * Normal Admin can only create admin role.
     * Tech Admin can create both admin and tech_admin roles.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check authorization - both admin and tech_admin can access
        if (!$user || !$user->isAnyAdmin()) {
            abort(403, 'Unauthorized. Required role: admin or tech_admin');
        }

        // Determine allowed roles based on current user
        $allowedRoles = $user->isTechAdmin() ? ['admin', 'tech_admin'] : ['admin'];
        
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'is_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        // Additional security: Prevent normal admin from creating tech_admin
        if ($user->isNormalAdmin() && $validated['role'] === 'tech_admin') {
            throw ValidationException::withMessages([
                'role' => ['Normal admins cannot create technical admin accounts.'],
            ]);
        }

        // Server-side authorization: Check if user can assign the requested role
        if (!Gate::allows('assignRole', [User::class, $validated['role']])) {
            throw ValidationException::withMessages([
                'role' => ['You are not authorized to assign this role.'],
            ]);
        }

        // Prevent creating 'user' role from this page (this is for admins only)
        if ($validated['role'] === 'user') {
            throw ValidationException::withMessages([
                'role' => ['Regular users cannot be created from this page. Only admin roles are allowed.'],
            ]);
        }

        // Validate that role is in allowed roles
        if (!in_array($validated['role'], $allowedRoles)) {
            throw ValidationException::withMessages([
                'role' => ['Invalid role. Only ' . implode(' or ', $allowedRoles) . ' roles are allowed.'],
            ]);
        }

        // Create the admin user
        $createdUser = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_notifications_enabled' => $validated['is_notifications_enabled'] ?? true,
        ]);

        return redirect()
            ->route('admin.create-admin')
            ->with('success', "Admin user '{$createdUser->username}' has been created successfully with role: " . match($createdUser->role) {
                'admin' => 'Normal Admin',
                'tech_admin' => 'Technical Admin',
                default => $createdUser->role,
            });
    }
}

