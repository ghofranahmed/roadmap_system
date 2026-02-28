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
     * Only Technical Admin can access this page.
     */
    public function create()
    {
        // Check authorization - only tech_admin can access
        if (!auth()->user() || !auth()->user()->isTechAdmin()) {
            abort(403, 'Only Technical Admins can access this page.');
        }

        return view('admin.create-admin');
    }

    /**
     * Store a newly created admin user.
     * Only Technical Admin can create admin users.
     */
    public function store(Request $request)
    {
        // Check authorization - only tech_admin can access
        if (!auth()->user() || !auth()->user()->isTechAdmin()) {
            abort(403, 'Only Technical Admins can create admin users.');
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,tech_admin'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'is_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

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

        // Validate that role is either admin or tech_admin
        if (!in_array($validated['role'], ['admin', 'tech_admin'])) {
            throw ValidationException::withMessages([
                'role' => ['Invalid role. Only admin or tech_admin roles are allowed.'],
            ]);
        }

        // Create the admin user
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_notifications_enabled' => $validated['is_notifications_enabled'] ?? true,
        ]);

        return redirect()
            ->route('admin.create-admin')
            ->with('success', "Admin user '{$user->username}' has been created successfully with role: " . match($user->role) {
                'admin' => 'Normal Admin',
                'tech_admin' => 'Technical Admin',
                default => $user->role,
            });
    }
}

