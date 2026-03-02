<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'position' => [
                'prohibited', // Position cannot be updated directly, use reorder endpoint
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'The sub-lesson description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'description.max' => 'The description may not be greater than 10000 characters.',
            'position.prohibited' => 'Position cannot be updated directly. Use the reorder endpoint instead.',
        ];
    }
}

