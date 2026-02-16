<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'learning_unit_id' => ['required', 'integer', 'exists:learning_units,id'],
            'is_active' => ['sometimes', 'boolean'],
            'max_xp' => ['required', 'integer', 'min:0'],
            'min_xp' => ['required', 'integer', 'min:0', 'lte:max_xp'],
        ];
    }
}

