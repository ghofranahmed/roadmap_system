<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearningUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحيات تُدار عبر Middleware
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'position' => 'integer|min:1', // اختياري، يمكن حسابه تلقائياً
        ];
    }
}