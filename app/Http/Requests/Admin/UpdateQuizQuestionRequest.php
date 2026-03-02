<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'quiz_id' => [
                'required',
                'integer',
                'exists:quizzes,id',
            ],
            'question_text' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'options' => [
                'required',
                'array',
                'min:2',
                'max:10',
            ],
            'options.*' => [
                'required',
                'string',
                'max:500',
            ],
            'correct_answer' => [
                'required',
                'string',
                'max:500',
            ],
            'question_xp' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'order' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('correct_answer') && $this->has('options')) {
                $options = $this->input('options', []);
                $correctAnswer = $this->input('correct_answer');
                
                if (!in_array($correctAnswer, $options)) {
                    $validator->errors()->add('correct_answer', 'The correct answer must match one of the options.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'quiz_id.required' => 'The quiz is required.',
            'quiz_id.exists' => 'The selected quiz does not exist.',
            'question_text.required' => 'The question text is required.',
            'question_text.min' => 'The question text must be at least 10 characters.',
            'question_text.max' => 'The question text may not be greater than 5000 characters.',
            'options.required' => 'At least 2 options are required.',
            'options.min' => 'At least 2 options are required.',
            'options.max' => 'Maximum 10 options allowed.',
            'options.*.required' => 'Each option is required.',
            'options.*.max' => 'Each option may not be greater than 500 characters.',
            'correct_answer.required' => 'The correct answer is required.',
            'correct_answer.max' => 'The correct answer may not be greater than 500 characters.',
            'question_xp.min' => 'Question XP must be at least 0.',
            'question_xp.max' => 'Question XP may not be greater than 100.',
            'order.min' => 'The order must be at least 1.',
        ];
    }
}

