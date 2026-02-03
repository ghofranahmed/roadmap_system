<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoadmapRequest extends FormRequest
{
     /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // سيتم التحكم في الصلاحية عبر middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'is_active' => 'boolean',
        ];

        // في حالة التحديث، تكون الحقول اختيارية
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = 'sometimes|string|max:255';
            $rules['description'] = 'sometimes|string';
            $rules['level'] = 'sometimes|in:beginner,intermediate,advanced';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان المسار مطلوب',
            'title.max' => 'عنوان المسار يجب ألا يتجاوز 255 حرفًا',
            'description.required' => 'وصف المسار مطلوب',
            'level.required' => 'مستوى المسار مطلوب',
            'level.in' => 'مستوى المسار يجب أن يكون واحدًا من: beginner, intermediate, advanced',
        ];
    }
}
