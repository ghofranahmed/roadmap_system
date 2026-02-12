<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:book,video,article',
            'language' => 'required|in:ar,en',
            'link' => 'required|url'
        ];
    }
}