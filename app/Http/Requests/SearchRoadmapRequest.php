<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SearchRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'nullable|string|min:2|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'limit' => 'nullable|integer|min:1|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'query.min' => 'كلمة البحث يجب أن تكون على الأقل حرفين',
            'query.max' => 'كلمة البحث يجب ألا تتجاوز 100 حرف',
            'level.in' => 'المستوى يجب أن يكون أحد: beginner, intermediate, advanced',
            'limit.min' => 'الحد الأدنى للنتائج يجب أن يكون 1',
            'limit.max' => 'الحد الأقصى للنتائج هو 20',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}