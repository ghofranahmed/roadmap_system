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
            'position' => 'sometimes|integer|min:1'
        ];
    }
    
    public function messages()
    {
        return [
            'description.string' => 'الوصف يجب أن يكون نصاً',
            'position.min' => 'الترتيب يجب أن يكون على الأقل 1'
        ];
    }
}