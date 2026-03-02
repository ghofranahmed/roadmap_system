<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearningUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'roadmap_id' => [
                'required',
                'integer',
                'exists:roadmaps,id',
            ],
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^\d+$/', // Reject purely numeric titles
            ],
            'position' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'unit_type' => [
                'nullable',
                'string',
                'max:255',
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
            'roadmap_id.required' => 'The roadmap is required.',
            'roadmap_id.exists' => 'The selected roadmap does not exist.',
            'title.required' => 'The learning unit title is required.',
            'title.min' => 'The learning unit title must be at least 3 characters.',
            'title.max' => 'The learning unit title may not be greater than 255 characters.',
            'title.not_regex' => 'The learning unit title cannot be only numbers.',
            'position.min' => 'The position must be at least 1.',
            'unit_type.max' => 'The unit type may not be greater than 255 characters.',
        ];
    }
}

