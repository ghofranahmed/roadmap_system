<?php

namespace App\Services\Chatbot;

interface LLMProviderInterface
{
    /**
     * Generate a reply from the LLM provider.
     *
     * @param  array  $context   Conversation history: [['role' => 'user|assistant', 'body' => '...'], ...]
     * @param  string $message   The latest user message
     * @param  array  $metadata  Enrichment data: system_prompt, enrollments, next_lessons
     * @return array  ['reply' => string, 'tokens_used' => int|null]
     */
    public function chat(array $context, string $message, array $metadata = []): array;
}

