<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubLessonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'description' => 'sometimes|string',
            'position'    => 'prohibited',
        ];
    }

    public function messages()
    {
        return [
            'description.string'  => 'الوصف يجب أن يكون نصاً',
            'position.prohibited' => 'لا يمكن تعديل ترتيب الدرس الفرعي من هنا، استخدم reorder',
        ];
    }
}
