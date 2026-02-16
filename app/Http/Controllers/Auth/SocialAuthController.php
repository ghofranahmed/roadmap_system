<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LinkedAccount;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function google(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $client = new GoogleClient(['client_id' => config('services.google.client_id')]);
        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json(['status' => 'error', 'message' => 'Invalid Google token'], 401);
        }

        $googleId = $payload['sub'] ?? null;
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? 'User';
        $avatar = $payload['picture'] ?? null;

        if (!$googleId || !$email) {
            return response()->json(['status' => 'error', 'message' => 'Google token missing required data'], 422);
        }

        $token = DB::transaction(function () use ($googleId, $email, $name, $avatar) {
            // Check if this Google account is already linked
            $linked = LinkedAccount::where('provider', 'google')
                ->where('provider_user_id', $googleId)
                ->first();

            if ($linked) {
                $user = $linked->user;
            } else {
                // Find or create user by email
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // Generate username from email or name
                    $username = $this->generateUsername($email, $name);
                    
                    $user = User::create([
                        'username' => $username,
                        'email' => $email,
                        'password' => bcrypt(Str::random(32)),
                        'email_verified_at' => now(), // Social logins are verified
                        'role' => 'user', // Default role
                        'is_notifications_enabled' => true, // Default notification preference
                    ]);
                }

                // Check if user already has this provider linked
                $existingLink = LinkedAccount::where('user_id', $user->id)
                    ->where('provider', 'google')
                    ->first();

                if (!$existingLink) {
                    LinkedAccount::create([
                        'user_id' => $user->id,
                        'provider' => 'google',
                        'provider_user_id' => $googleId,
                        'provider_email' => $email,
                        'avatar_url' => $avatar,
                    ]);
                }
            }

            return $user->createToken('google-mobile')->plainTextToken;
        });

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function github(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Exchange code for access token
        $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [
            'client_id' => config('services.github.client_id'),
            'client_secret' => config('services.github.client_secret'),
            'code' => $request->code,
        ]);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to exchange code for token'], 401);
        }

        $data = [];
        parse_str($response->body(), $data);

        if (!isset($data['access_token'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid GitHub code'], 401);
        }

        $accessToken = $data['access_token'];

        // Get user info from GitHub
        $userResponse = Http::withToken($accessToken)->get('https://api.github.com/user');

        if ($userResponse->failed()) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch user info from GitHub'], 401);
        }

        $githubUser = $userResponse->json();

        $githubId = $githubUser['id'] ?? null;
        $email = $githubUser['email'] ?? null;
        $name = $githubUser['name'] ?? $githubUser['login'] ?? 'User';
        $avatar = $githubUser['avatar_url'] ?? null;

        // If email is not public, get it from emails endpoint
        if (!$email) {
            $emailsResponse = Http::withToken($accessToken)->get('https://api.github.com/user/emails');
            if ($emailsResponse->successful()) {
                $emails = $emailsResponse->json();
                foreach ($emails as $emailData) {
                    if ($emailData['primary'] && $emailData['verified']) {
                        $email = $emailData['email'];
                        break;
                    }
                }
            }
        }

        if (!$githubId || !$email) {
            return response()->json(['status' => 'error', 'message' => 'GitHub account missing required data'], 422);
        }

        $token = DB::transaction(function () use ($githubId, $email, $name, $avatar) {
            // Check if this GitHub account is already linked
            $linked = LinkedAccount::where('provider', 'github')
                ->where('provider_user_id', $githubId)
                ->first();

            if ($linked) {
                $user = $linked->user;
            } else {
                // Find or create user by email
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // Generate username from email or name
                    $username = $this->generateUsername($email, $name);
                    
                    $user = User::create([
                        'username' => $username,
                        'email' => $email,
                        'password' => bcrypt(Str::random(32)),
                        'email_verified_at' => now(), // Social logins are verified
                        'role' => 'user', // Default role
                        'is_notifications_enabled' => true, // Default notification preference
                    ]);
                }

                // Check if user already has this provider linked
                $existingLink = LinkedAccount::where('user_id', $user->id)
                    ->where('provider', 'github')
                    ->first();

                if (!$existingLink) {
                    LinkedAccount::create([
                        'user_id' => $user->id,
                        'provider' => 'github',
                        'provider_user_id' => $githubId,
                        'provider_email' => $email,
                        'avatar_url' => $avatar,
                    ]);
                }
            }

            return $user->createToken('github-mobile')->plainTextToken;
        });

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Generate a unique username from email or name
     */
    private function generateUsername(string $email, string $name): string
    {
        // Try to use name first
        if ($name && $name !== 'User') {
            $baseUsername = Str::slug($name);
            $username = $baseUsername;
            $counter = 1;
            
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            return $username;
        }
        
        // Fallback to email prefix
        $emailPrefix = explode('@', $email)[0];
        $baseUsername = Str::slug($emailPrefix);
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
}
