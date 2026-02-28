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
            'title'            => 'required|string|max:255',
            'description'     => 'required|string',
            'type'             => 'required|in:general,technical,opportunity',
            'link'             => 'nullable|url|max:2048',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after_or_equal:starts_at',
            'send_notification' => 'sometimes|boolean',
            'target_type'      => 'required_if:send_notification,1|in:all,specific_users,inactive_users,low_progress',
            'target_rules'     => 'required_if:target_type,specific_users|nullable|array',
            'target_rules.*'   => 'exists:users,id',
            'status'           => 'sometimes|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'              => 'Type must be one of: general, technical, opportunity.',
            'ends_at.after_or_equal' => 'End date must be after or equal to the start date.',
        ];
    }
}
