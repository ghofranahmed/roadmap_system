<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^\d+$/', // Reject purely numeric titles
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'position' => [
                'prohibited', // Position cannot be updated directly, use reorder endpoint
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The lesson title is required.',
            'title.min' => 'The lesson title must be at least 3 characters.',
            'title.max' => 'The lesson title may not be greater than 255 characters.',
            'title.not_regex' => 'The lesson title cannot be only numbers.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'position.prohibited' => 'Position cannot be updated directly. Use the reorder endpoint instead.',
        ];
    }
}

