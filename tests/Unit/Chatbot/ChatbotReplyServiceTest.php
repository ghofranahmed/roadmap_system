<?php

use App\Models\ChatbotSession;
use App\Models\User;
use App\Services\Chatbot\ChatbotReplyService;
use App\Services\Chatbot\LLMProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| 10) Reply service returns provider reply
|--------------------------------------------------------------------------
*/

it('returns the reply from the injected LLM provider', function () {
    $user    = User::factory()->create();
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Unit Test']);

    $mock = Mockery::mock(LLMProviderInterface::class);
    $mock->shouldReceive('chat')
        ->once()
        ->andReturn(['reply' => 'Hello from mock!', 'tokens_used' => 10]);

    $service = new ChatbotReplyService($mock);
    $result  = $service->generateReply($session, 'Hi there');

    expect($result['reply'])->toBe('Hello from mock!');
    expect($result['tokens_used'])->toBe(10);
});

it('passes conversation context to the provider', function () {
    $user    = User::factory()->create();
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Context Test']);

    // Add some messages to the session
    $session->messages()->create(['role' => 'user', 'body' => 'First question']);
    $session->messages()->create(['role' => 'assistant', 'body' => 'First answer']);

    $capturedContext = null;
    $capturedMessage = null;

    $mock = Mockery::mock(LLMProviderInterface::class);
    $mock->shouldReceive('chat')
        ->once()
        ->andReturnUsing(function ($context, $message, $metadata) use (&$capturedContext, &$capturedMessage) {
            $capturedContext = $context;
            $capturedMessage = $message;
            return ['reply' => 'Context-aware reply', 'tokens_used' => null];
        });

    $service = new ChatbotReplyService($mock);
    $result  = $service->generateReply($session, 'Second question');

    expect($result['reply'])->toBe('Context-aware reply');
    expect($capturedContext)->toHaveCount(2);

    // Verify context contains both prior messages (order may vary by DB engine)
    $roles  = array_column($capturedContext, 'role');
    $bodies = array_column($capturedContext, 'body');
    expect($roles)->toContain('user');
    expect($roles)->toContain('assistant');
    expect($bodies)->toContain('First question');
    expect($bodies)->toContain('First answer');
    expect($capturedMessage)->toBe('Second question');
});

/*
|--------------------------------------------------------------------------
| 11) Provider failure fallback
|--------------------------------------------------------------------------
*/

it('falls back to DummyProvider when the LLM provider throws an exception', function () {
    $user    = User::factory()->create();
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Fallback Test']);

    $mock = Mockery::mock(LLMProviderInterface::class);
    $mock->shouldReceive('chat')
        ->once()
        ->andThrow(new \RuntimeException('API connection failed'));

    $service = new ChatbotReplyService($mock);
    $result  = $service->generateReply($session, 'Hello');

    // Should return a DummyProvider reply (not throw an exception)
    expect($result['reply'])->toBeString()->not->toBeEmpty();
    expect($result['tokens_used'])->toBeNull();
});

it('logs the error when provider fails and fallback is used', function () {
    $user    = User::factory()->create();
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Log Test']);

    \Illuminate\Support\Facades\Log::shouldReceive('error')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Chatbot provider failed'));

    $mock = Mockery::mock(LLMProviderInterface::class);
    $mock->shouldReceive('chat')
        ->once()
        ->andThrow(new \RuntimeException('Timeout'));

    $service = new ChatbotReplyService($mock);
    $service->generateReply($session, 'Test');
});

