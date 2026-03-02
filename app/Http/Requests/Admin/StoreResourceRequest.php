<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'sub_lesson_id' => [
                'required',
                'integer',
                'exists:sub_lessons,id',
            ],
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^\d+$/', // Reject purely numeric titles
            ],
            'type' => [
                'required',
                'in:book,video,article',
            ],
            'language' => [
                'required',
                'in:ar,en',
            ],
            'link' => [
                'required',
                'url',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sub_lesson_id.required' => 'The sub-lesson is required.',
            'sub_lesson_id.exists' => 'The selected sub-lesson does not exist.',
            'title.required' => 'The resource title is required.',
            'title.min' => 'The resource title must be at least 3 characters.',
            'title.max' => 'The resource title may not be greater than 255 characters.',
            'title.not_regex' => 'The resource title cannot be only numbers.',
            'type.required' => 'The resource type is required.',
            'type.in' => 'The resource type must be one of: book, video, article.',
            'language.required' => 'The language is required.',
            'language.in' => 'The language must be either: ar or en.',
            'link.required' => 'The resource link is required.',
            'link.url' => 'The link must be a valid URL.',
            'link.max' => 'The link may not be greater than 2048 characters.',
        ];
    }
}

