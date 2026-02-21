<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderUnitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Middleware handles auth
    }

    public function rules(): array
    {
        return [
            'unit_id' => 'required|integer|exists:learning_units,id',
            'new_position' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'معرّف الوحدة مطلوب.',
            'unit_id.integer' => 'معرّف الوحدة يجب أن يكون رقمًا صحيحًا.',
            'unit_id.exists' => 'الوحدة المحددة غير موجودة.',
            'new_position.required' => 'الموقع الجديد مطلوب.',
            'new_position.integer' => 'الموقع الجديد يجب أن يكون رقمًا صحيحًا.',
            'new_position.min' => 'الموقع الجديد يجب أن يكون 1 على الأقل.',
        ];
    }
}