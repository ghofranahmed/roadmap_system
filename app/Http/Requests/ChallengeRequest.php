<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules()
    {
        return [
            'learning_unit_id' => 'required|exists:learning_units,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_xp' => 'required|integer|min:0',
            'language' => 'required|string|in:php,python,javascript,java,cpp', // حسب المتاح
            'starter_code' => 'nullable|string',
            'test_cases' => 'required|json', // نستقبل JSON string
            'is_active' => 'sometimes|boolean',
        ];
    }
}