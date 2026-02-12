<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:book,video,article',
            'language' => 'sometimes|in:ar,en',
            'link' => 'sometimes|url'
        ];
    }
    
    public function messages()
    {
        return [
            'title.string' => 'العنوان يجب أن يكون نصاً',
            'title.max' => 'العنوان يجب ألا يتجاوز 255 حرفاً',
            'type.in' => 'النوع يجب أن يكون: book, video, article',
            'language.in' => 'اللغة يجب أن تكون: ar أو en',
            'link.url' => 'الرابط يجب أن يكون رابطاً صالحاً'
        ];
    }
}