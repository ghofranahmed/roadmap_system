<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoadmapRequest extends FormRequest
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
            'level' => [
                'required',
                'in:beginner,intermediate,advanced',
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
            'title.required' => 'The roadmap title is required.',
            'title.min' => 'The roadmap title must be at least 3 characters.',
            'title.max' => 'The roadmap title may not be greater than 255 characters.',
            'title.not_regex' => 'The roadmap title cannot be only numbers.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'level.required' => 'The level is required.',
            'level.in' => 'The level must be one of: beginner, intermediate, advanced.',
        ];
    }
}

