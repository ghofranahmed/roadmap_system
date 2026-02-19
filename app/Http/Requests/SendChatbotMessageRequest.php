<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message'    => 'required|string|max:5000',
            'session_id' => 'nullable|integer|exists:chatbot_sessions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Message is required.',
            'message.max'      => 'Message cannot exceed 5000 characters.',
            'session_id.exists' => 'The selected session does not exist.',
        ];
    }
}

