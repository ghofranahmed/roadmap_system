<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAnyAdmin();
    }

    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'type'         => 'required|in:general,technical,opportunity',
            'target_type'  => 'required|in:all,specific_users,inactive_users,low_progress',
            'target_rules' => 'nullable|array',
            'target_rules.user_ids'      => 'required_if:target_type,specific_users|array',
            'target_rules.user_ids.*'    => 'integer|exists:users,id',
            'target_rules.inactive_days' => 'required_if:target_type,inactive_users|integer|min:1',
            'target_rules.max_progress'  => 'required_if:target_type,low_progress|integer|min:0|max:100',
            'publish_at'   => 'nullable|date|after:now',
            'status'       => 'nullable|in:draft,scheduled,published',
        ];
    }

    public function messages(): array
    {
        return [
            'target_rules.user_ids.required_if'      => 'User IDs are required when target_type is specific_users.',
            'target_rules.inactive_days.required_if'  => 'Inactive days is required when target_type is inactive_users.',
            'target_rules.max_progress.required_if'   => 'Max progress percentage is required when target_type is low_progress.',
        ];
    }
}

