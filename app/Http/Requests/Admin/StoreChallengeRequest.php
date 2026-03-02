<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallengeRequest extends FormRequest
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
            'min_xp' => [
                'required',
                'integer',
                'min:0',
            ],
            'language' => [
                'required',
                'in:javascript,python,java,c,cpp',
            ],
            'starter_code' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'test_cases' => [
                'required',
                'array',
                'min:1',
            ],
            'test_cases.*.stdin' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'test_cases.*.expected_output' => [
                'required',
                'string',
                'max:1000',
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
            'title.required' => 'The challenge title is required.',
            'title.min' => 'The challenge title must be at least 3 characters.',
            'title.max' => 'The challenge title may not be greater than 255 characters.',
            'title.not_regex' => 'The challenge title cannot be only numbers.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'min_xp.required' => 'Minimum XP is required.',
            'min_xp.integer' => 'Minimum XP must be an integer.',
            'min_xp.min' => 'Minimum XP must be at least 0.',
            'language.required' => 'The programming language is required.',
            'language.in' => 'The language must be one of: javascript, python, java, c, cpp.',
            'starter_code.max' => 'The starter code may not be greater than 10000 characters.',
            'test_cases.required' => 'At least one test case is required.',
            'test_cases.min' => 'At least one test case is required.',
            'test_cases.*.stdin.max' => 'Test case input may not be greater than 1000 characters.',
            'test_cases.*.expected_output.required' => 'Expected output is required for each test case.',
            'test_cases.*.expected_output.max' => 'Expected output may not be greater than 1000 characters.',
        ];
    }
}

