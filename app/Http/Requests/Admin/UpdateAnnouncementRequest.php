<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isNormalAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'not_regex:/^\d+$/', // Reject purely numeric titles
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'type' => [
                'required',
                'in:general,technical,opportunity',
            ],
            'link' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'starts_at' => [
                'nullable',
                'date',
            ],
            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
            'send_notification' => [
                'sometimes',
                'boolean',
            ],
            'target_type' => [
                'required_if:send_notification,1',
                'in:all,specific_users,inactive_users,low_progress',
            ],
            'target_rules' => [
                'required_if:target_type,specific_users',
                'nullable',
                'array',
            ],
            'target_rules.*' => [
                'exists:users,id',
            ],
            'status' => [
                'sometimes',
                'in:draft,published',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The announcement title is required.',
            'title.min' => 'The announcement title must be at least 3 characters.',
            'title.max' => 'The announcement title may not be greater than 255 characters.',
            'title.not_regex' => 'The announcement title cannot be only numbers.',
            'description.required' => 'The announcement description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'type.required' => 'The announcement type is required.',
            'type.in' => 'The type must be one of: general, technical, opportunity.',
            'link.url' => 'The link must be a valid URL.',
            'link.max' => 'The link may not be greater than 2048 characters.',
            'ends_at.after_or_equal' => 'The end date must be after or equal to the start date.',
            'target_type.required_if' => 'Target audience is required when sending notifications.',
            'target_type.in' => 'The target type must be one of: all, specific_users, inactive_users, low_progress.',
            'target_rules.required_if' => 'Please select at least one user when targeting specific users.',
            'target_rules.*.exists' => 'One or more selected users do not exist.',
        ];
    }
}

