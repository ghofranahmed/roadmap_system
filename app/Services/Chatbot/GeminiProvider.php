<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements LLMProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey  = config('services.chatbot.gemini_key', '');
        $this->model   = config('services.chatbot.gemini_model', 'gemini-2.0-flash');
        $this->timeout = config('services.chatbot.request_timeout', 15);
    }

    /**
     * Send message to Google Gemini API and return the reply.
     */
    public function chat(array $context, string $message, array $metadata = []): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Build conversation contents for Gemini (role: "user" or "model")
        $contents = [];
        foreach ($context as $msg) {
            $contents[] = [
                'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['body']]],
            ];
        }

        // Add current user message
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]],
        ];

        $payload = [
            'contents'         => $contents,
            'generationConfig' => [
                'maxOutputTokens' => 500,
                'temperature'     => 0.7,
            ],
        ];

        // Attach system instruction if available
        $systemPrompt = $metadata['system_prompt'] ?? null;
        if ($systemPrompt) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $systemPrompt]],
            ];
        }

        $response = Http::timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if ($response->failed()) {
            Log::warning('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API request failed: ' . $response->status());
        }

        $data = $response->json();

        $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? null;

        return [
            'reply'      => $reply,
            'tokens_used' => $tokensUsed,
        ];
    }
}

