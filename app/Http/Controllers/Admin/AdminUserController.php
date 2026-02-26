<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Constructor - Defense in depth: ensure only admin role
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isNormalAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin role required.',
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * List all users with pagination and filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->has('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->has('is_active')) {
            // Assuming you have is_active column, if not, remove this
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        // Ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return $this->paginatedResponse($users, 'تم جلب المستخدمين بنجاح');
    }

    /**
     * Show single user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->successResponse($user, 'تم جلب المستخدم بنجاح');
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentAdmin = auth()->user();

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['sometimes', 'in:user,admin,tech_admin'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ]);

        // Prevent normal admin from assigning or changing role to tech_admin
        // Normal admins can only assign: user, admin (not tech_admin)
        if (isset($validated['role']) && $validated['role'] === 'tech_admin') {
            if ($currentAdmin->isNormalAdmin()) {
                return $this->errorResponse(
                    'Unauthorized. Normal admins cannot assign technical admin role.',
                    null,
                    403
                );
            }
        }

        // Update password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return $this->successResponse($user, 'تم تحديث المستخدم بنجاح');
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return $this->errorResponse('لا يمكنك حذف حسابك الخاص', null, 403);
        }

        $user->delete();

        return $this->successResponse(null, 'تم حذف المستخدم بنجاح', 204);
    }

    /**
     * Revoke all tokens (logout everywhere)
     */
    public function revokeTokens($id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();

        return $this->successResponse(null, 'تم إلغاء جميع الجلسات بنجاح');
    }
}
