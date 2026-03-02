<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'learning_unit_id' => [
                'required',
                'integer',
                'exists:learning_units,id',
            ],
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
            'position' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'learning_unit_id.required' => 'The learning unit is required.',
            'learning_unit_id.exists' => 'The selected learning unit does not exist.',
            'title.required' => 'The lesson title is required.',
            'title.min' => 'The lesson title must be at least 3 characters.',
            'title.max' => 'The lesson title may not be greater than 255 characters.',
            'title.not_regex' => 'The lesson title cannot be only numbers.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'position.min' => 'The position must be at least 1.',
        ];
    }
}

