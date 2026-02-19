<?php

use App\Models\ChatbotSession;
use App\Models\User;
use App\Services\Chatbot\LLMProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers (unique names to avoid global collisions with other test files)
|--------------------------------------------------------------------------
*/

function sessUser(): User
{
    return User::factory()->create(['role' => 'user']);
}

function sessToken(User $user): string
{
    return $user->createToken('test')->plainTextToken;
}

/*
|--------------------------------------------------------------------------
| Mock LLM Provider — deterministic, no external API calls
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->app->bind(LLMProviderInterface::class, function () {
        return new class implements LLMProviderInterface {
            public function chat(array $context, string $message, array $metadata = []): array
            {
                return ['reply' => 'Mocked assistant reply.', 'tokens_used' => null];
            }
        };
    });
});

/*
|--------------------------------------------------------------------------
| 1) Auth required (guests get 401)
|--------------------------------------------------------------------------
*/

it('returns 401 when guest tries to create a session', function () {
    $this->postJson('/api/v1/chatbot/sessions')
        ->assertStatus(401);
});

it('returns 401 when guest tries to list sessions', function () {
    $this->getJson('/api/v1/chatbot/sessions')
        ->assertStatus(401);
});

it('returns 401 when guest tries to delete a session', function () {
    $this->deleteJson('/api/v1/chatbot/sessions/1')
        ->assertStatus(401);
});

/*
|--------------------------------------------------------------------------
| 2) Create session
|--------------------------------------------------------------------------
*/

it('allows authenticated user to create a chatbot session', function () {
    $user  = sessUser();
    $token = sessToken($user);

    $response = $this->postJson('/api/v1/chatbot/sessions', [
        'title' => 'My Learning Chat',
    ], [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'message', 'data'])
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Session created successfully')
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.title', 'My Learning Chat');

    $this->assertDatabaseHas('chatbot_sessions', [
        'user_id' => $user->id,
        'title'   => 'My Learning Chat',
    ]);
});

it('creates a session with null title when title is omitted', function () {
    $user  = sessUser();
    $token = sessToken($user);

    $this->postJson('/api/v1/chatbot/sessions', [], [
        'Authorization' => "Bearer $token",
    ])
        ->assertStatus(201)
        ->assertJsonPath('data.title', null);

    $this->assertDatabaseHas('chatbot_sessions', [
        'user_id' => $user->id,
        'title'   => null,
    ]);
});

/*
|--------------------------------------------------------------------------
| 3) List sessions — only own, ordered by last_activity_at desc
|--------------------------------------------------------------------------
*/

it('lists only the authenticated users sessions ordered by last_activity_at desc', function () {
    $user  = sessUser();
    $other = sessUser();
    $token = sessToken($user);

    // Create sessions with explicit last_activity_at ordering
    ChatbotSession::create(['user_id' => $user->id, 'title' => 'Old Session', 'last_activity_at' => now()->subHours(2)]);
    ChatbotSession::create(['user_id' => $user->id, 'title' => 'New Session', 'last_activity_at' => now()]);
    ChatbotSession::create(['user_id' => $other->id, 'title' => 'Other User Session']);

    $response = $this->getJson('/api/v1/chatbot/sessions', [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'message', 'data', 'meta', 'links'])
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');

    // First item should be the newest
    $data = $response->json('data');
    expect($data[0]['title'])->toBe('New Session');
    expect($data[1]['title'])->toBe('Old Session');
});

it('does not expose other users sessions in the list', function () {
    $user  = sessUser();
    $other = sessUser();
    $token = sessToken($user);

    ChatbotSession::create(['user_id' => $other->id, 'title' => 'Secret Session']);

    $response = $this->getJson('/api/v1/chatbot/sessions', [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data')
        ->assertJsonMissing(['title' => 'Secret Session']);
});

/*
|--------------------------------------------------------------------------
| 4) Delete session + cascade messages
|--------------------------------------------------------------------------
*/

it('allows user to delete their own session', function () {
    $user    = sessUser();
    $token   = sessToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Delete Me']);

    $this->deleteJson("/api/v1/chatbot/sessions/{$session->id}", [], [
        'Authorization' => "Bearer $token",
    ])
        ->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Session deleted successfully');

    $this->assertDatabaseMissing('chatbot_sessions', ['id' => $session->id]);
});

it('cascades message deletion when session is deleted', function () {
    $user    = sessUser();
    $token   = sessToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'With Messages']);

    // Add messages directly
    $session->messages()->create(['role' => 'user', 'body' => 'Hello']);
    $session->messages()->create(['role' => 'assistant', 'body' => 'Hi there!']);

    $this->assertDatabaseCount('chatbot_messages', 2);

    $this->deleteJson("/api/v1/chatbot/sessions/{$session->id}", [], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(200);

    $this->assertDatabaseCount('chatbot_messages', 0);
    $this->assertDatabaseMissing('chatbot_sessions', ['id' => $session->id]);
});

it('returns 404 when deleting a non-existent session', function () {
    $user  = sessUser();
    $token = sessToken($user);

    $this->deleteJson('/api/v1/chatbot/sessions/99999', [], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(404);
});

