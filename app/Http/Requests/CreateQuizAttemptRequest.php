<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'answers' => ['sometimes', 'array', 'min:1'],
            'answers.*' => ['required_with:answers', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.array' => 'Answers must be an array',
            'answers.min' => 'At least one answer is required if answers are provided',
            'answers.*.required_with' => 'Each answer must be a string',
        ];
    }
}

