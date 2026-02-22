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
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
    
        return [
            'title' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'min_xp' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'min:0'],
    
            'language' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'in:javascript,python,java,c,cpp'
            ],
    
            'starter_code' => ['nullable', 'string'],
    
            'is_active' => ['sometimes', 'boolean'],
    
            'test_cases' => [
                $isUpdate ? 'sometimes' : 'required',
                'array',
                'min:1'
            ],
    
            'test_cases.*.stdin' => ['nullable', 'string'],
            'test_cases.*.expected_output' => ['required', 'string'],
        ];
    }
}
