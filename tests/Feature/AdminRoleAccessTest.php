<?php

use App\Models\Roadmap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: create users for each role
|--------------------------------------------------------------------------
*/

function createUser(string $role = 'user'): User
{
    return User::factory()->{$role === 'admin' ? 'admin' : ($role === 'tech_admin' ? 'techAdmin' : 'create')}();
}

function userToken(User $user): string
{
    return $user->createToken('test')->plainTextToken;
}

/*
|--------------------------------------------------------------------------
| 1) Regular user cannot access any /admin route (403)
|--------------------------------------------------------------------------
*/

it('blocks regular user from admin roadmaps list', function () {
    $user = User::factory()->create(); // role = user
    $token = userToken($user);

    $this->getJson('/api/v1/admin/roadmaps', ['Authorization' => "Bearer $token"])
        ->assertStatus(403);
});

it('blocks regular user from admin users list', function () {
    $user = User::factory()->create();
    $token = userToken($user);

    $this->getJson('/api/v1/admin/users', ['Authorization' => "Bearer $token"])
        ->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| 2) Admin can read content but cannot create/update/delete content
|--------------------------------------------------------------------------
*/

it('allows admin to GET /admin/roadmaps', function () {
    $admin = User::factory()->admin()->create();
    $token = userToken($admin);
    Roadmap::factory()->create();

    $this->getJson('/api/v1/admin/roadmaps', ['Authorization' => "Bearer $token"])
        ->assertStatus(200)
        ->assertJsonStructure(['success', 'data']);
});

it('blocks admin from POST /admin/roadmaps (no CRUD)', function () {
    $admin = User::factory()->admin()->create();
    $token = userToken($admin);

    $this->postJson('/api/v1/admin/roadmaps', [
        'title' => 'Test',
        'level' => 'beginner',
        'description' => 'test',
    ], ['Authorization' => "Bearer $token"])
        ->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| 3) Tech admin can CRUD content but cannot access user management
|--------------------------------------------------------------------------
*/

it('allows tech_admin to POST /admin/roadmaps', function () {
    $techAdmin = User::factory()->techAdmin()->create();
    $token = userToken($techAdmin);

    $this->postJson('/api/v1/admin/roadmaps', [
        'title' => 'New Roadmap',
        'level' => 'beginner',
        'description' => 'A test roadmap',
    ], ['Authorization' => "Bearer $token"])
        ->assertStatus(201);
});

it('blocks tech_admin from GET /admin/users', function () {
    $techAdmin = User::factory()->techAdmin()->create();
    $token = userToken($techAdmin);

    $this->getJson('/api/v1/admin/users', ['Authorization' => "Bearer $token"])
        ->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| 4) Unauthenticated access returns 401
|--------------------------------------------------------------------------
*/

it('returns 401 for unauthenticated admin route access', function () {
    $this->getJson('/api/v1/admin/roadmaps')
        ->assertStatus(401);
});

