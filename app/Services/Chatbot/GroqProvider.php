<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqProvider implements LLMProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey  = config('services.chatbot.groq_key', '');
        $this->model   = config('services.chatbot.groq_model', 'llama-3.3-70b-versatile');
        $this->timeout = config('services.chatbot.request_timeout', 15);
    }

    /**
     * Send message to Groq API (OpenAI-compatible) and return the reply.
     */
    public function chat(array $context, string $message, array $metadata = []): array
    {
        $url = 'https://api.groq.com/openai/v1/chat/completions';

        // Build messages array (OpenAI format)
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
            Log::warning('Groq API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Groq API request failed: ' . $response->status());
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

