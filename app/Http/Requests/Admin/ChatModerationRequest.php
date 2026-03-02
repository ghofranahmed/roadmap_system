<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChatModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isNormalAdmin();
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();

        if ($action === 'mute') {
            return [
                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'reason' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
                'muted_until' => [
                    'nullable',
                    'date',
                    'after:now',
                ],
            ];
        }

        if ($action === 'ban') {
            return [
                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],
                'reason' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
            ];
        }

        // For unmute and unban
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'reason.max' => 'The reason may not be greater than 500 characters.',
            'muted_until.after' => 'The mute expiration date must be in the future.',
        ];
    }
}

