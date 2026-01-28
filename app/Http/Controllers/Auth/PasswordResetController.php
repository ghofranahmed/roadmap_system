<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // أقصى عدد محاولات لطلب إعادة التعيين
    private $maxAttempts = 3;
    
    // مدة الحظر بعد تجاوز المحاولات (دقائق)
    private $decayMinutes = 60;

    /**
     * طلب إعادة تعيين كلمة المرور
     */
public function forgotPassword(ForgotPasswordRequest $request)
{
    try {
        $email = $request->validated()['email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'المستخدم غير موجود'
            ], 404); // إذا كان البريد غير موجود
        }

        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes(30); // صلاحية 30 دقيقة

        // حذف أي توكنات قديمة
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // إدخال التوكن الجديد في قاعدة البيانات
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // إنشاء رابط إعادة تعيين كلمة المرور
        $resetLink = url('/reset-password?token=' . $token . '&email=' . urlencode($email));

        // إرسال البريد عبر Mailtrap أو Gmail
        Mail::to($email)->send(new PasswordResetMail($resetLink, 30));

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني'
        ], 200);

    } catch (\Exception $e) {
        // في حالة حدوث أي استثناء، نقوم بطباعة الخطأ
        Log::error('خطأ في عملية إعادة تعيين كلمة المرور: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ في إرسال البريد الإلكتروني أو إدخال البيانات في قاعدة البيانات'
        ], 500);
    }
}


public function resetPassword(ResetPasswordRequest $request)
{
    $validated = $request->validated();
    $token = $validated['token'];
    $email = $validated['email'];
    $password = $validated['password'];

    // التحقق من صلاحية الرمز
    $resetRecord = DB::table('password_reset_tokens')
        ->where('email', $email)
        ->where('token', $token)
        ->first();

    if (!$resetRecord) {
        return response()->json([
            'status' => 'error',
            'message' => 'الرمز غير صالح أو منتهي الصلاحية'
        ], 400);
    }

    $createdAt = Carbon::parse($resetRecord->created_at);
    $expiresAt = $createdAt->addMinutes(30);

    if (Carbon::now()->gt($expiresAt)) {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->delete();

        return response()->json([
            'status' => 'error',
            'message' => 'الرمز منتهي الصلاحية'
        ], 400);
    }

    // التحقق من قوة كلمة المرور
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل، مع أرقام وأحرف ورموز'
        ], 400);
    }

    // تحديث كلمة المرور
    $user = User::where('email', $email)->first();
    $user->password = Hash::make($password);
    $user->save();

    // حذف التوكن بعد استخدامه
    DB::table('password_reset_tokens')
        ->where('email', $email)
        ->where('token', $token)
        ->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'تم تغيير كلمة المرور بنجاح'
    ], 200);
}



    /**
     * التحقق من صلاحية الرمز
     */
    public function verifyToken(Request $request)
{
    $validated = $request->validate([
        'token' => 'required|string',
        'email' => 'required|email|exists:users,email'
    ]);

    $token = $validated['token'];
    $email = $validated['email'];

    $resetRecord = DB::table('password_reset_tokens')
        ->where('email', $email)
        ->where('token', $token)
        ->first();

    if (!$resetRecord) {
        return response()->json([
            'status' => 'error',
            'message' => 'الرمز غير صالح أو منتهي الصلاحية'
        ], 400);
    }

    // التحقق من صلاحية الرمز (30 دقيقة)
    $createdAt = Carbon::parse($resetRecord->created_at);
    $expiresAt = $createdAt->addMinutes(30);

    if (Carbon::now()->gt($expiresAt)) {
        // حذف الرمز المنتهي
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->delete();

        return response()->json([
            'status' => 'error',
            'message' => 'الرمز منتهي الصلاحية'
        ], 400);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'الرمز صالح',
        'expires_at' => $expiresAt->format('Y-m-d H:i:s')
    ], 200);
}


    /**
     * إعادة تعيين كلمة المرور
     */
  
    /**
     * معرفة عدد المحاولات المتبقية
     */
    public function getAttemptsRemaining(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'البيانات غير صالحة',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $validated = $validator->validated();
        $email = $validated['email'];
        $ip = $request->ip();
        
        $attemptsKey = 'password_reset:' . $email;
        $ipKey = 'password_reset_ip:' . $ip;
        
        $emailAttempts = RateLimiter::remaining($attemptsKey, $this->maxAttempts);
        $ipAttempts = RateLimiter::remaining($ipKey, $this->maxAttempts * 2);
        
        $isEmailBlocked = RateLimiter::tooManyAttempts($attemptsKey, $this->maxAttempts);
        $isIpBlocked = RateLimiter::tooManyAttempts($ipKey, $this->maxAttempts * 2);
        
        $response = [
            'status' => 'success',
            'data' => [
                'email_attempts_remaining' => $emailAttempts,
                'ip_attempts_remaining' => $ipAttempts,
                'max_email_attempts' => $this->maxAttempts,
                'max_ip_attempts' => $this->maxAttempts * 2,
                'is_email_blocked' => $isEmailBlocked,
                'is_ip_blocked' => $isIpBlocked
            ]
        ];
        
        if ($isEmailBlocked) {
            $seconds = RateLimiter::availableIn($attemptsKey);
            $response['data']['email_blocked_until'] = Carbon::now()->addSeconds($seconds)->format('Y-m-d H:i:s');
            $response['data']['email_blocked_seconds'] = $seconds;
        }
        
        if ($isIpBlocked) {
            $seconds = RateLimiter::availableIn($ipKey);
            $response['data']['ip_blocked_until'] = Carbon::now()->addSeconds($seconds)->format('Y-m-d H:i:s');
            $response['data']['ip_blocked_seconds'] = $seconds;
        }
        
        return response()->json($response, 200);
    }
}