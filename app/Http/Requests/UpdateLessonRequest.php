<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'position'    => 'prohibited',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'title.max'           => 'عنوان الدرس يجب ألا يتجاوز 255 حرفاً',
            'position.prohibited' => 'لا يمكن تعديل ترتيب الدرس من هنا، استخدم endpoint إعادة الترتيب',
        ];
    }
}
