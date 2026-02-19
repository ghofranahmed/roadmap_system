<?php

use App\Models\ChatbotSession;
use App\Models\User;
use App\Services\Chatbot\LLMProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helpers (unique names to avoid global collisions with other test files)
|--------------------------------------------------------------------------
*/

function msgUser(): User
{
    return User::factory()->create(['role' => 'user']);
}

function msgToken(User $user): string
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
                return ['reply' => 'Mocked assistant reply.', 'tokens_used' => 42];
            }
        };
    });
});

/*
|--------------------------------------------------------------------------
| 5) Ownership protection
|--------------------------------------------------------------------------
*/

it('blocks user from viewing messages of another users session', function () {
    $userA    = msgUser();
    $userB    = msgUser();
    $tokenA   = msgToken($userA);
    $sessionB = ChatbotSession::create(['user_id' => $userB->id, 'title' => 'Private']);

    $this->getJson("/api/v1/chatbot/sessions/{$sessionB->id}/messages", [
        'Authorization' => "Bearer $tokenA",
    ])->assertStatus(403);
});

it('blocks user from sending message to another users session via session endpoint', function () {
    $userA    = msgUser();
    $userB    = msgUser();
    $tokenA   = msgToken($userA);
    $sessionB = ChatbotSession::create(['user_id' => $userB->id, 'title' => 'Private']);

    $this->postJson("/api/v1/chatbot/sessions/{$sessionB->id}/messages", [
        'message' => 'Trying to access',
    ], [
        'Authorization' => "Bearer $tokenA",
    ])->assertStatus(403);
});

it('blocks user from sending message to another users session via mobile endpoint', function () {
    $userA    = msgUser();
    $userB    = msgUser();
    $tokenA   = msgToken($userA);
    $sessionB = ChatbotSession::create(['user_id' => $userB->id, 'title' => 'Private']);

    $this->postJson('/api/v1/chatbot/messages', [
        'message'    => 'Trying to access',
        'session_id' => $sessionB->id,
    ], [
        'Authorization' => "Bearer $tokenA",
    ])->assertStatus(403);
});

it('blocks user from deleting another users session', function () {
    $userA    = msgUser();
    $userB    = msgUser();
    $tokenA   = msgToken($userA);
    $sessionB = ChatbotSession::create(['user_id' => $userB->id, 'title' => 'Private']);

    $this->deleteJson("/api/v1/chatbot/sessions/{$sessionB->id}", [], [
        'Authorization' => "Bearer $tokenA",
    ])->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| 6) Send message via session endpoint (POST /chatbot/sessions/{id}/messages)
|--------------------------------------------------------------------------
*/

it('stores user + assistant messages and returns both via session endpoint', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Test Chat']);

    $response = $this->postJson("/api/v1/chatbot/sessions/{$session->id}/messages", [
        'message' => 'What should I learn next?',
    ], [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'session'           => ['id', 'title', 'last_activity_at'],
                'user_message'      => ['id', 'chatbot_session_id', 'role', 'body'],
                'assistant_message' => ['id', 'chatbot_session_id', 'role', 'body'],
            ],
        ])
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Reply generated successfully')
        ->assertJsonPath('data.user_message.role', 'user')
        ->assertJsonPath('data.user_message.body', 'What should I learn next?')
        ->assertJsonPath('data.assistant_message.role', 'assistant')
        ->assertJsonPath('data.assistant_message.body', 'Mocked assistant reply.')
        ->assertJsonPath('data.assistant_message.tokens_used', 42);

    // Both messages stored in DB
    $this->assertDatabaseCount('chatbot_messages', 2);
    $this->assertDatabaseHas('chatbot_messages', [
        'chatbot_session_id' => $session->id,
        'role' => 'user',
        'body' => 'What should I learn next?',
    ]);
    $this->assertDatabaseHas('chatbot_messages', [
        'chatbot_session_id' => $session->id,
        'role'       => 'assistant',
        'body'       => 'Mocked assistant reply.',
        'tokens_used' => 42,
    ]);
});

it('updates last_activity_at when message is sent', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create([
        'user_id'          => $user->id,
        'title'            => 'Activity Test',
        'last_activity_at' => now()->subDay(),
    ]);

    $oldActivity = $session->last_activity_at;

    $this->postJson("/api/v1/chatbot/sessions/{$session->id}/messages", [
        'message' => 'Update my activity',
    ], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(201);

    $session->refresh();
    expect($session->last_activity_at->greaterThan($oldActivity))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| 7) Mobile-friendly endpoint (POST /chatbot/messages)
|--------------------------------------------------------------------------
*/

it('auto-creates session when session_id is missing in mobile endpoint', function () {
    $user  = msgUser();
    $token = msgToken($user);

    $this->assertDatabaseCount('chatbot_sessions', 0);

    $response = $this->postJson('/api/v1/chatbot/messages', [
        'message' => 'What is Laravel?',
    ], [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'session'           => ['id', 'title', 'last_activity_at'],
                'user_message'      => ['id', 'chatbot_session_id', 'role', 'body'],
                'assistant_message' => ['id', 'chatbot_session_id', 'role', 'body'],
            ],
        ])
        ->assertJsonPath('success', true);

    // Session auto-created with title from first 50 chars of message
    $this->assertDatabaseCount('chatbot_sessions', 1);
    $this->assertDatabaseCount('chatbot_messages', 2);
    $this->assertDatabaseHas('chatbot_sessions', [
        'user_id' => $user->id,
        'title'   => 'What is Laravel?',
    ]);
});

it('uses existing session when session_id is provided in mobile endpoint', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Existing Chat']);

    $response = $this->postJson('/api/v1/chatbot/messages', [
        'message'    => 'Follow up question',
        'session_id' => $session->id,
    ], [
        'Authorization' => "Bearer $token",
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.session.id', $session->id)
        ->assertJsonPath('data.session.title', 'Existing Chat');

    // No new session created
    $this->assertDatabaseCount('chatbot_sessions', 1);
    $this->assertDatabaseCount('chatbot_messages', 2);
});

it('returns 401 when guest tries to send a message', function () {
    $this->postJson('/api/v1/chatbot/messages', ['message' => 'Hello'])
        ->assertStatus(401);
});

/*
|--------------------------------------------------------------------------
| 8) Validation
|--------------------------------------------------------------------------
*/

it('rejects empty message via session endpoint', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Test']);

    $this->postJson("/api/v1/chatbot/sessions/{$session->id}/messages", [
        'message' => '',
    ], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['message']);
});

it('rejects empty message via mobile endpoint', function () {
    $user  = msgUser();
    $token = msgToken($user);

    $this->postJson('/api/v1/chatbot/messages', [
        'message' => '',
    ], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['message']);
});

it('rejects missing message field', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Test']);

    $this->postJson("/api/v1/chatbot/sessions/{$session->id}/messages", [], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['message']);
});

it('rejects message exceeding 5000 characters', function () {
    $user    = msgUser();
    $token   = msgToken($user);
    $session = ChatbotSession::create(['user_id' => $user->id, 'title' => 'Test']);

    $this->postJson("/api/v1/chatbot/sessions/{$session->id}/messages", [
        'message' => str_repeat('a', 5001),
    ], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['message']);
});

it('rejects invalid session_id in mobile endpoint', function () {
    $user  = msgUser();
    $token = msgToken($user);

    $this->postJson('/api/v1/chatbot/messages', [
        'message'    => 'Hello',
        'session_id' => 99999,
    ], [
        'Authorization' => "Bearer $token",
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['session_id']);
});

/*
|--------------------------------------------------------------------------
| 9) Throttle middleware verification
|--------------------------------------------------------------------------
*/

it('has throttle middleware on chatbot message endpoints', function () {
    $routes = collect(Route::getRoutes()->getRoutes());

    // Session-specific message endpoint (POST /chatbot/sessions/{id}/messages)
    $sessionMsgRoute = $routes->first(fn ($r) =>
        str_contains($r->uri(), 'chatbot/sessions/{id}/messages') && in_array('POST', $r->methods())
    );
    expect($sessionMsgRoute)->not->toBeNull('Session message route not found');
    expect(collect($sessionMsgRoute->middleware()))->toContain('throttle:15,1');

    // Mobile message endpoint (POST /chatbot/messages)
    $mobileMsgRoute = $routes->first(fn ($r) =>
        str_contains($r->uri(), 'chatbot/messages')
        && !str_contains($r->uri(), 'sessions')
        && in_array('POST', $r->methods())
    );
    expect($mobileMsgRoute)->not->toBeNull('Mobile message route not found');
    expect(collect($mobileMsgRoute->middleware()))->toContain('throttle:15,1');
});

