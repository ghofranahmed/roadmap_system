<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotSetting extends Model
{
    protected $fillable = [
        'provider',
        'model_name',
        'temperature',
        'max_tokens',
        'max_context_messages',
        'request_timeout',
        'is_enabled',
        'system_prompt_template',
        'updated_by',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'max_tokens' => 'integer',
        'max_context_messages' => 'integer',
        'request_timeout' => 'integer',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the current chatbot settings (singleton pattern).
     * Returns the first record or creates a default one.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'provider' => config('services.chatbot.provider', 'openai'),
                'model_name' => null,
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'max_context_messages' => config('services.chatbot.max_context_messages', 10),
                'request_timeout' => config('services.chatbot.request_timeout', 15),
                'is_enabled' => true,
                'system_prompt_template' => null,
            ]
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
