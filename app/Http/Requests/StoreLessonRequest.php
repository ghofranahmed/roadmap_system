<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean'
        ];
    }
    
    public function messages()
    {
        return [
            'title.required' => 'عنوان الدرس مطلوب',
            'title.max' => 'عنوان الدرس يجب ألا يتجاوز 255 حرفاً'
        ];
    }
}