<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
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
                'nullable',
                'string',
                'max:255',
            ],
            'min_xp' => [
                'required',
                'integer',
                'min:0',
            ],
            'max_xp' => [
                'required',
                'integer',
                'min:0',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('min_xp') && $this->has('max_xp')) {
                if ($this->input('min_xp') > $this->input('max_xp')) {
                    $validator->errors()->add('min_xp', 'Minimum XP must be less than or equal to Maximum XP.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'learning_unit_id.required' => 'The learning unit is required.',
            'learning_unit_id.exists' => 'The selected learning unit does not exist.',
            'title.max' => 'The quiz title may not be greater than 255 characters.',
            'min_xp.required' => 'Minimum XP is required.',
            'min_xp.integer' => 'Minimum XP must be an integer.',
            'min_xp.min' => 'Minimum XP must be at least 0.',
            'max_xp.required' => 'Maximum XP is required.',
            'max_xp.integer' => 'Maximum XP must be an integer.',
            'max_xp.min' => 'Maximum XP must be at least 0.',
        ];
    }
}

