<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'min_xp' => ['required','integer','min:0'],
            'language' => ['required','string','in:php,python,javascript,java,cpp'],
            'starter_code' => ['nullable','string'],
            'test_cases' => ['required','array','min:1'],
            'test_cases.*.stdin' => ['nullable','string'],
            'test_cases.*.expected_output' => ['required','string'],
            'is_active' => ['sometimes','boolean'],
        ];
    }
}
