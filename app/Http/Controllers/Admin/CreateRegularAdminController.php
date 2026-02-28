<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminCreationRateLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateRegularAdminController extends Controller
{
    protected AdminCreationRateLimitService $rateLimitService;

    public function __construct(AdminCreationRateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Show the form for creating a new regular admin user.
     * Only Regular Admin can access this page.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Check authorization - only regular admin can access
        if (!$user || !$user->isNormalAdmin()) {
            abort(403, 'Only Regular Admins can access this page.');
        }

        // Check rate limit
        $rateLimitCheck = $this->rateLimitService->checkRateLimit($user, 'admin');
        
        return view('admin.create-regular-admin', [
            'rateLimitInfo' => $rateLimitCheck,
        ]);
    }

    /**
     * Store a newly created regular admin user.
     * Only Regular Admin can create regular admin users.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Check authorization - only regular admin can access
        if (!$user || !$user->isNormalAdmin()) {
            abort(403, 'Only Regular Admins can create regular admin users.');
        }

        // Check rate limit
        $rateLimitCheck = $this->rateLimitService->checkRateLimit($user, 'admin');
        if (!$rateLimitCheck['allowed']) {
            $remainingTime = $rateLimitCheck['remaining_time'];
            $remainingMinutes = $rateLimitCheck['remaining_minutes'];
            $hours = floor($remainingMinutes / 60);
            $minutes = $remainingMinutes % 60;
            
            throw ValidationException::withMessages([
                'rate_limit' => [
                    $rateLimitCheck['message'] . 
                    ($remainingTime ? " You can create another admin in " . 
                        ($hours > 0 ? "{$hours} hour(s) and " : "") . 
                        "{$minutes} minute(s)." : "")
                ],
            ]);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'is_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        // Force admin role - Regular Admin can ONLY create Regular Admin
        $validated['role'] = 'admin';

        // Server-side authorization: Check if user can assign the requested role
        if (!Gate::allows('assignRole', [User::class, $validated['role']])) {
            throw ValidationException::withMessages([
                'role' => ['You are not authorized to assign this role.'],
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

        // Log the creation for rate limiting
        $this->rateLimitService->logCreation($user, $createdUser, 'admin');

        return redirect()
            ->route('admin.create-regular-admin')
            ->with('success', "Regular Admin user '{$createdUser->username}' has been created successfully.");
    }
}

