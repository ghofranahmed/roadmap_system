<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
  public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'الصورة مطلوبة',
            'profile_picture.image' => 'يجب أن تكون الصورة من نوع صورة',
            'profile_picture.mimes' => 'يجب أن تكون الصورة بصيغة jpeg, png, jpg, gif, أو webp',
            'profile_picture.max' => 'حجم الصورة يجب أن لا يتعدى 2MB',
        ];
    }
}
