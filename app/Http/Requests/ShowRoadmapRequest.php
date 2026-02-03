<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ShowRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'with_details' => 'nullable|boolean',
            'include_content' => 'nullable|boolean',
            'track_view' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'with_details.boolean' => 'معلمة with_details يجب أن تكون true أو false',
            'include_content.boolean' => 'معلمة include_content يجب أن تكون true أو false',
            'track_view.boolean' => 'معلمة track_view يجب أن تكون true أو false',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // تحويل القيم النصية إلى boolean
        $this->merge([
            'with_details' => filter_var($this->with_details, FILTER_VALIDATE_BOOLEAN),
            'include_content' => filter_var($this->include_content, FILTER_VALIDATE_BOOLEAN),
            'track_view' => filter_var($this->track_view ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}