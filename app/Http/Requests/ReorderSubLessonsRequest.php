<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSubLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'sublesson_ids'   => ['required', 'array', 'min:1'],
            'sublesson_ids.*' => ['required', 'integer', 'distinct', 'exists:sub_lessons,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'sublesson_ids.required'  => 'مصفوفة معرفات الدروس الفرعية مطلوبة',
            'sublesson_ids.array'     => 'sublesson_ids يجب أن تكون مصفوفة',
            'sublesson_ids.min'       => 'يجب إرسال درس فرعي واحد على الأقل',
            'sublesson_ids.*.exists'  => 'أحد معرفات الدروس الفرعية غير موجود',
            'sublesson_ids.*.distinct' => 'يجب ألا تتكرر معرفات الدروس الفرعية',
        ];
    }
}
