<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSubLessonsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isTechAdmin();
    }

    public function rules(): array
    {
        return [
            'sublesson_ids' => ['required', 'array', 'min:1'],
            'sublesson_ids.*' => ['required', 'integer', 'exists:sub_lessons,id'],
        ];
    }
}

