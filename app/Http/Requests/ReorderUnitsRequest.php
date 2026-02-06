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
            // يجب أن تكون مصفوفة ومطلوبة
            'unit_ids' => 'required|array|min:1',
            
            // كل عنصر داخل المصفوفة يجب أن يكون رقم صحيح وموجود في جدول learning_units
            'unit_ids.*' => 'required|integer|exists:learning_units,id',
        ];
    }

    public function messages()
    {
        return [
            'unit_ids.required' => 'قائمة الوحدات مطلوبة.',
            'unit_ids.*.exists' => 'إحدى الوحدات المرسلة غير موجودة في النظام.',
        ];
    }
}