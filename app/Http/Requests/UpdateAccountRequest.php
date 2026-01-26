<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rules\Password;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'username' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'current_password' => 'required_with:password|string',
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // هنا فقط
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'current_password.required_with' => 'كلمة المرور الحالية مطلوبة لتغيير كلمة المرور',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
            'profile_picture.image' => 'يجب أن تكون الصورة من نوع صورة',
            'profile_picture.mimes' => 'يجب أن تكون الصورة بصيغة jpeg, png, jpg, gif, أو webp',
            'profile_picture.max' => 'حجم الصورة يجب أن لا يتعدى 2MB',
        ];
    }
}
