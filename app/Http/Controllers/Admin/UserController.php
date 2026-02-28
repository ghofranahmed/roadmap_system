<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        // Defense in depth: ensure only normal admin can access these routes
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || !$user->isNormalAdmin()) {
                abort(403, 'Unauthorized. Admin access required.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        $query->orderByDesc('created_at');

        $users = $query->paginate($request->get('per_page', 15))->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $currentAdmin = $request->user();

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'in:user,admin,tech_admin'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Prevent normal admin from assigning or changing role to tech_admin
        if (isset($validated['role']) && $validated['role'] === 'tech_admin') {
            if ($currentAdmin->isNormalAdmin()) {
                return redirect()
                    ->back()
                    ->withErrors(['role' => 'Normal admins cannot assign technical admin role.'])
                    ->withInput();
            }
        }

        // Update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $this->authorize('delete', $user);

        // Extra safety: prevent deleting self (also enforced in policy)
        if ($user->id === $request->user()->id) {
            return redirect()
                ->route('admin.users.show', $user)
                ->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Revoke all tokens (logout everywhere) for the specified user.
     */
    public function revokeTokens(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $user->tokens()->delete();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'All sessions have been revoked successfully.');
    }
}


