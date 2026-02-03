<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class IndexRoadmapRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // يمكن تغييرها بناءً على الصلاحيات
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'is_active' => 'nullable|boolean',
            'search' => 'nullable|string|min:2|max:100',
            'order_by' => 'nullable|in:created_at,title,level,updated_at,enrollments_count',
            'order_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'level.in' => 'المستوى يجب أن يكون أحد: beginner, intermediate, advanced',
            'is_active.boolean' => 'الحالة يجب أن تكون true أو false',
            'search.min' => 'كلمة البحث يجب أن تكون على الأقل حرفين',
            'search.max' => 'كلمة البحث يجب ألا تتجاوز 100 حرف',
            'order_by.in' => 'حقل الترتيب غير صالح',
            'order_direction.in' => 'اتجاه الترتيب يجب أن يكون asc أو desc',
            'per_page.min' => 'عدد العناصر في الصفحة يجب أن يكون على الأقل 1',
            'per_page.max' => 'عدد العناصر في الصفحة يجب ألا يتجاوز 100',
            'page.min' => 'رقم الصفحة يجب أن يكون على الأقل 1',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}
