<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'question_text' => ['sometimes', 'string', 'max:5000'],
            'options' => ['sometimes', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:500'],
            'correct_answer' => ['sometimes', 'string', 'max:500'],
            'question_xp' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'order' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}

