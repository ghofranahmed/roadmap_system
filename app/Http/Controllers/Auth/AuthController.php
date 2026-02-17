<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\UpdateProfilePictureRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'user'; // Default role
        $data['is_notifications_enabled'] = true; // Default notification preference
        
        /** @var User $user */
        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'تم تسجيل المستخدم بنجاح', 201);
    }

    /**
     * Login user and return token.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('بيانات الدخول غير صحيحة', null, 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update([
            'last_active_at' => now(),
            'last_login_at' => now(),
        ]);

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'تم تسجيل الدخول بنجاح');
    }

    /**
     * Get authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->update(['last_active_at' => now()]);

        return $this->successResponse($user);
    }

    /**
     * Update user account details.
     *
     * @param UpdateAccountRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccount(UpdateAccountRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validated();

        if ($request->has('password')) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return $this->errorResponse('كلمة المرور الحالية غير صحيحة', null, 401);
            }
            $data['password'] = Hash::make($data['password']);
            unset($data['current_password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);
        $user->update(['last_active_at' => now()]);

        return $this->successResponse($user, 'تم تحديث الحساب بنجاح');
    }

    /**
     * Update only the user's profile picture.
     *
     * @param UpdateProfilePictureRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfilePicture(UpdateProfilePictureRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->update([
            'profile_picture' => $path,
            'last_active_at' => now()
        ]);

        return $this->successResponse([
            'profile_picture_url' => asset('storage/' . $path),
            'user' => $user
        ], 'تم تحديث الصورة الشخصية بنجاح');
    }

    /**
     * Logout the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Delete the authenticated user's account.
     *
     * @param DeleteAccountRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount(DeleteAccountRequest $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('كلمة المرور غير صحيحة', null, 401);
        }

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->successResponse(null, 'تم حذف الحساب بنجاح');
    }
        /**
     * Delete the authenticated user's profile picture.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProfilePicture(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->update([
                'profile_picture' => null,
                'last_active_at' => now()
            ]);

            return $this->successResponse($user, 'تم حذف الصورة الشخصية بنجاح');
        }

        return $this->errorResponse('لا توجد صورة شخصية لحذفها', null, 404);
    }

    /**
     * Update notification preference
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNotificationPreference(Request $request)
    {
        $request->validate([
            'is_notifications_enabled' => 'required|boolean',
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->update([
            'is_notifications_enabled' => $request->is_notifications_enabled,
        ]);

        return $this->successResponse([
            'is_notifications_enabled' => $user->is_notifications_enabled,
        ], 'تم تحديث تفضيلات الإشعارات بنجاح');
    }

}
