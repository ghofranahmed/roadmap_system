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
        
        /** @var User $user */
        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return response()->json([
            'message' => 'تم تسجيل المستخدم بنجاح',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
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
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_active_at' => now()]);

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
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

        return response()->json(['user' => $user]);
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
                return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 401);
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

        return response()->json([
            'message' => 'تم تحديث الحساب بنجاح',
            'user' => $user
        ]);
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

        return response()->json([
            'message' => 'تم تحديث الصورة الشخصية بنجاح',
            'profile_picture_url' => asset('storage/' . $path),
            'user' => $user
        ]);
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
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
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
            return response()->json(['message' => 'كلمة المرور غير صحيحة'], 401);
        }

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'تم حذف الحساب بنجاح']);
    }
}
