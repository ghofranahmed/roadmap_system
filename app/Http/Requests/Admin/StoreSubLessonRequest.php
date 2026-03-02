<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'lesson_id' => [
                'required',
                'integer',
                'exists:lessons,id',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'position' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'lesson_id.required' => 'The lesson is required.',
            'lesson_id.exists' => 'The selected lesson does not exist.',
            'description.required' => 'The sub-lesson description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'description.max' => 'The description may not be greater than 10000 characters.',
            'position.min' => 'The position must be at least 1.',
        ];
    }
}

