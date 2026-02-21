<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'lesson_ids'   => ['required', 'array', 'min:1'],
            'lesson_ids.*' => ['required', 'integer', 'distinct', 'exists:lessons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_ids.required'  => 'مصفوفة معرفات الدروس مطلوبة',
            'lesson_ids.array'     => 'lesson_ids يجب أن تكون مصفوفة',
            'lesson_ids.min'       => 'يجب إرسال درس واحد على الأقل',
            'lesson_ids.*.exists'  => 'أحد معرفات الدروس غير موجود',
            'lesson_ids.*.distinct' => 'يجب ألا تتكرر معرفات الدروس',
        ];
    }
}
