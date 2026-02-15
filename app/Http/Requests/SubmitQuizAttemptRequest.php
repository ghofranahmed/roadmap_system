<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Answers are required',
            'answers.array' => 'Answers must be an array',
            'answers.min' => 'At least one answer is required',
        ];
    }
}

