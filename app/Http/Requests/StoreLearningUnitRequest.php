<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLearningUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحيات تُدار عبر Middleware
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'title' => ($isUpdate ? 'sometimes|' : 'required|') . 'string|max:255',
            'position' => $isUpdate ? 'prohibited' : 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'position.prohibited' => 'لا يمكن تعديل position من هنا، استخدم endpoint إعادة الترتيب',
        ];
    }
}