<?php

namespace App\Http\Requests;

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
            'learning_unit_id' => ['sometimes', 'integer', 'exists:learning_units,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'max_xp' => ['sometimes', 'integer', 'min:0'],
            'min_xp' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('min_xp', 'lte:max_xp', function ($input) {
            return isset($input->max_xp);
        });
    }
}

