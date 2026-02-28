<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements LLMProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.chatbot.openai_key', '');
        
        // STRICT: Throw exception if API key is missing or empty
        if (empty($this->apiKey)) {
            throw new \RuntimeException(
                'OPENAI_API_KEY is not set or is empty. Please set it in your .env file.'
            );
        }
        
        // Default model: gpt-4o-mini (safer default than gpt-3.5-turbo)
        $this->model = config('services.chatbot.openai_model', 'gpt-4o-mini');
        $this->timeout = config('services.chatbot.request_timeout', 15);
    }

    /**
     * Send message to OpenAI Chat Completions API and return the reply.
     */
    public function chat(array $context, string $message, array $metadata = []): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        // Build messages array (OpenAI Chat Completions format)
        $messages = [];

        // System prompt
        $systemPrompt = $metadata['system_prompt'] ?? null;
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }

        // Conversation history
        foreach ($context as $msg) {
            $messages[] = [
                'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['body'],
            ];
        }

        // Current user message
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->post($url, [
                'model'       => $this->model,
                'messages'    => $messages,
                'max_tokens'  => 500,
                'temperature' => 0.7,
            ]);

        if ($response->failed()) {
            Log::warning('OpenAI API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('OpenAI API request failed: ' . $response->status());
        }

        $data = $response->json();

        $reply      = $data['choices'][0]['message']['content'] ?? '';
        $tokensUsed = $data['usage']['total_tokens'] ?? null;

        return [
            'reply'      => $reply,
            'tokens_used' => $tokensUsed,
        ];
    }
}

