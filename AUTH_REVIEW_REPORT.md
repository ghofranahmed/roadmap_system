# 🔐 Authentication & OAuth Review Report

## 📁 Relevant Files Found

### Migrations
- `database/migrations/0001_01_01_000000_create_users_table.php` - Users table
- `database/migrations/2026_01_24_185325_create_linked_accounts_table.php` - OAuth account linking table
- `database/migrations/0001_01_01_000001_create_personal_access_tokens_table.php` - Sanctum tokens

### Models
- `app/Models/User.php` - User model
- `app/Models/LinkedAccount.php` - OAuth account linking model

### Controllers
- `app/Http/Controllers/Auth/SocialAuthController.php` - **COMMENTED OUT** ⚠️
- `app/Http/Controllers/Auth/AuthController.php` - Standard auth

### Config
- `config/services.php` - Missing Google/GitHub OAuth config
- `config/auth.php` - Standard Laravel auth config
- `config/sanctum.php` - API token config

### Routes
- `routes/api.php` - Social auth routes **COMMENTED OUT** ⚠️

---

## 🚨 CRITICAL ISSUES

### 1. **SocialAuthController is Completely Commented Out**
**File:** `app/Http/Controllers/Auth/SocialAuthController.php`
- **Issue:** Entire controller is wrapped in `/* */` comments
- **Impact:** OAuth endpoints are non-functional
- **Fix:** Uncomment and fix the code (see fixes below)

### 2. **Routes are Commented Out**
**File:** `routes/api.php` (lines 57-58)
- **Issue:** Social auth routes are commented
- **Impact:** Even if controller works, routes won't be accessible
- **Fix:** Uncomment routes

### 3. **User Model Relationship Error**
**File:** `app/Models/User.php` (line 55-57)
```php
public function linkedAccounts() { 
    return $this->hasOne(LinkedAccount::class);  // ❌ WRONG
}
```
- **Issue:** Should be `hasMany` - a user can have multiple providers (Google + GitHub)
- **Impact:** Can only link one provider per user
- **Fix:** Change to `hasMany`

### 4. **Redundant Columns in Users Table**
**File:** `database/migrations/0001_01_01_000000_create_users_table.php` (lines 29-31)
```php
$table->string('google_id')->nullable();
$table->string('github_id')->nullable();
$table->string('avatar')->nullable();
```
- **Issue:** These columns duplicate data already in `linked_accounts` table
- **Impact:** Data inconsistency, unnecessary storage
- **Fix:** Remove these columns (create migration to drop them)

### 5. **Missing Unique Constraint on (user_id, provider)**
**File:** `database/migrations/2026_01_24_185325_create_linked_accounts_table.php`
- **Issue:** No constraint preventing same user from linking same provider twice
- **Impact:** Duplicate links possible
- **Fix:** Add unique index on `['user_id', 'provider']`

### 6. **Security: Tokens Stored as Plain Text**
**File:** `database/migrations/2026_01_24_185325_create_linked_accounts_table.php` (lines 20-21)
```php
$table->string('access_token')->nullable();
$table->string('refresh_token')->nullable();
```
- **Issue:** OAuth tokens stored as plain strings (security risk)
- **Impact:** If DB is compromised, tokens are exposed
- **Fix:** Use Laravel's encrypted casting or remove token storage (recommended: don't store tokens for mobile)

### 7. **Missing Username Generation for Social Logins**
**File:** `app/Http/Controllers/Auth/SocialAuthController.php` (lines 50-56, 144-149)
- **Issue:** User creation uses `name` field, but User model requires `username` (not nullable)
- **Impact:** Will cause database error on social login
- **Fix:** Generate username from email or name

### 8. **Incomplete Error Handling**
**File:** `app/Http/Controllers/Auth/SocialAuthController.php` (lines 129-131)
```php
if (!$githubId || !$email) {
    // ❌ Empty - no return statement
}
```
- **Issue:** Missing error response
- **Impact:** Will cause undefined behavior
- **Fix:** Add proper error response

### 9. **Missing Google Client Configuration**
**File:** `config/services.php`
- **Issue:** No Google OAuth client_id/client_secret config
- **Impact:** Google OAuth won't work
- **Fix:** Add Google config

### 10. **Missing Index on user_id in linked_accounts**
**File:** `database/migrations/2026_01_24_185325_create_linked_accounts_table.php`
- **Issue:** No index on `user_id` for faster lookups
- **Impact:** Slower queries when fetching user's linked accounts
- **Fix:** Add index (foreign key may already create one, but explicit is better)

### 11. **No State Parameter Validation for GitHub**
**File:** `app/Http/Controllers/Auth/SocialAuthController.php`
- **Issue:** GitHub OAuth doesn't validate state parameter (CSRF protection)
- **Impact:** Vulnerable to CSRF attacks
- **Fix:** Add state validation (or use mobile app flow that doesn't need it)

### 12. **Missing Email Verification for Social Logins**
**File:** `app/Http/Controllers/Auth/SocialAuthController.php`
- **Issue:** `email_verified_at` is commented out
- **Impact:** Social login users won't have verified emails
- **Fix:** Set `email_verified_at` to `now()` for social logins

---

## ⚠️ WARNINGS

### 1. **No Validation Against Duplicate Linking**
- **Issue:** Controller doesn't check if user already has this provider linked before creating new link
- **Impact:** Could create duplicate entries (though unique constraint will prevent it)
- **Fix:** Add check before creating LinkedAccount

### 2. **Access Token Column Size**
- **Issue:** `access_token` is `string()` which defaults to VARCHAR(255), but OAuth tokens can be longer
- **Impact:** Token truncation possible
- **Fix:** Use `text()` instead of `string()`

### 3. **Missing Provider Email Storage**
- **Issue:** No field to store provider email (useful for reference)
- **Impact:** Can't track which email was used for each provider
- **Fix:** Optional - add `provider_email` field

---

## ✅ FIXES

### Fix 1: Update Linked Accounts Migration

```php
<?php
// database/migrations/2026_01_24_185325_create_linked_accounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linked_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('provider'); // google, github, etc.
            $table->string('provider_user_id'); // Provider's user ID
            $table->text('access_token')->nullable(); // Changed to text, encrypted in model
            $table->text('refresh_token')->nullable(); // Changed to text, encrypted in model
            $table->timestamp('expires_at')->nullable();
            $table->string('provider_email')->nullable(); // Optional: store provider email
            $table->string('avatar_url')->nullable(); // Optional: store provider avatar
            $table->timestamps();
            
            // Unique: same provider + provider_user_id can only exist once
            $table->unique(['provider', 'provider_user_id']);
            
            // Unique: same user can't link same provider twice
            $table->unique(['user_id', 'provider']);
            
            // Index for faster user lookups
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linked_accounts');
    }
};
```

### Fix 2: Remove Redundant Columns from Users Table

Create new migration:
```bash
php artisan make:migration remove_redundant_social_columns_from_users_table
```

```php
<?php
// database/migrations/YYYY_MM_DD_HHMMSS_remove_redundant_social_columns_from_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'github_id', 'avatar']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable();
            $table->string('github_id')->nullable();
            $table->string('avatar')->nullable();
        });
    }
};
```

### Fix 3: Update User Model

```php
<?php
// app/Models/User.php

// ... existing code ...

protected $fillable = [
    'username',
    'email',
    'password',
    'profile_picture',
    'last_active_at',
    // Remove: 'google_id', 'github_id', 'avatar'
];

// ... existing code ...

// Fix relationship
public function linkedAccounts() { 
    return $this->hasMany(LinkedAccount::class); // Changed from hasOne
}
```

### Fix 4: Update LinkedAccount Model with Encryption

```php
<?php
// app/Models/LinkedAccount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Encrypted;

class LinkedAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'provider_email',
        'avatar_url',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        // Encrypt tokens (optional - only if you need to store them)
        // 'access_token' => Encrypted::class,
        // 'refresh_token' => Encrypted::class,
    ];

    public function user() { 
        return $this->belongsTo(User::class);
    }
}
```

### Fix 5: Update Services Config

```php
<?php
// config/services.php

return [
    // ... existing services ...

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],
];
```

### Fix 6: Fix SocialAuthController

```php
<?php
// app/Http/Controllers/Auth/SocialAuthController.php

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
```

### Fix 7: Uncomment Routes

```php
// routes/api.php

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken'])->middleware('throttle:5,1');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::get('/reset-attempts', [PasswordResetController::class, 'getAttemptsRemaining']);

    // Uncomment these:
    Route::post('/google', [SocialAuthController::class, 'google'])->middleware('throttle:5,1');
    Route::post('/github', [SocialAuthController::class, 'github'])->middleware('throttle:5,1');
});
```

---

## 🚀 RENDER DEPLOYMENT CHECKLIST

### Step 1: Environment Variables

Set these in Render Dashboard → Your Service → Environment:

#### Core Laravel
```bash
APP_NAME="Roadmap System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY  # Run: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_TIMEZONE=UTC
```

#### Database (PostgreSQL recommended for Render)
```bash
DB_CONNECTION=pgsql
DB_HOST=your-db-host.render.com
DB_PORT=5432
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

#### Google OAuth
```bash
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://your-app.onrender.com/api/v1/auth/google/callback
```

#### GitHub OAuth
```bash
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URI=https://your-app.onrender.com/api/v1/auth/github/callback
```

#### Sanctum (for mobile API)
```bash
SANCTUM_STATEFUL_DOMAINS=your-mobile-app-domain.com
```

#### Session & Cache (if using Redis)
```bash
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379
```

#### Mail (if using)
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 2: OAuth Provider Configuration

#### Google Cloud Console
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create OAuth 2.0 credentials
3. Add authorized redirect URIs:
   - `https://your-app.onrender.com/api/v1/auth/google/callback` (if using web flow)
   - For mobile: No redirect needed (using ID token directly)

#### GitHub OAuth App
1. Go to GitHub → Settings → Developer settings → OAuth Apps
2. Create new OAuth App
3. Set:
   - **Application name:** Roadmap System
   - **Homepage URL:** `https://your-app.onrender.com`
   - **Authorization callback URL:** `https://your-app.onrender.com/api/v1/auth/github/callback` (if using web flow)
   - For mobile: Use mobile app callback URL

### Step 3: Render Build & Deploy Settings

#### Build Command
```bash
composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

#### Start Command
```bash
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
```

**OR** if using PHP-FPM with Nginx:
```bash
php-fpm
```

### Step 4: Post-Deployment Commands

Run these in Render Shell or add to build script:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: CORS Configuration

For mobile apps, ensure CORS allows your mobile app origin:

Create `config/cors.php` if it doesn't exist:
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('MOBILE_APP_ORIGIN', '*'), // Set this in .env
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Step 6: Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` is set and secure
- [ ] Database credentials are secure
- [ ] OAuth secrets are set
- [ ] HTTPS is enabled (Render does this automatically)
- [ ] Rate limiting is enabled (already in routes)
- [ ] CORS is configured for your mobile app only

### Step 7: Testing After Deployment

1. **Test Google OAuth:**
   ```bash
   curl -X POST https://your-app.onrender.com/api/v1/auth/google \
     -H "Content-Type: application/json" \
     -d '{"id_token": "YOUR_GOOGLE_ID_TOKEN"}'
   ```

2. **Test GitHub OAuth:**
   ```bash
   curl -X POST https://your-app.onrender.com/api/v1/auth/github \
     -H "Content-Type: application/json" \
     -d '{"code": "YOUR_GITHUB_CODE"}'
   ```

3. **Test Standard Auth:**
   ```bash
   curl -X POST https://your-app.onrender.com/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "test@example.com", "password": "password"}'
   ```

### Step 8: Common Issues & Solutions

#### Issue: OAuth redirects to localhost
**Solution:** Update OAuth app redirect URIs to production URL

#### Issue: Config cache breaks OAuth
**Solution:** Clear config cache: `php artisan config:clear` then rebuild

#### Issue: Database connection fails
**Solution:** Check DB credentials, ensure DB is accessible from Render

#### Issue: Sanctum tokens not working
**Solution:** Ensure `SANCTUM_STATEFUL_DOMAINS` includes your mobile app domain

---

## 📝 Summary

### Critical Fixes Required:
1. ✅ Uncomment and fix SocialAuthController
2. ✅ Uncomment routes
3. ✅ Fix User model relationship (hasOne → hasMany)
4. ✅ Remove redundant columns from users table
5. ✅ Add unique constraint on (user_id, provider)
6. ✅ Fix username generation for social logins
7. ✅ Add Google/GitHub config to services.php
8. ✅ Complete error handling in GitHub method

### Security Improvements:
1. ✅ Encrypt tokens if storing (or don't store them)
2. ✅ Add proper validation
3. ✅ Set email_verified_at for social logins

### Production Readiness:
1. ✅ All environment variables documented
2. ✅ OAuth provider setup instructions
3. ✅ Render deployment steps
4. ✅ Testing checklist

---

**Next Steps:**
1. Apply all fixes above
2. Test locally
3. Deploy to Render with environment variables
4. Test OAuth flows in production
5. Monitor logs for errors

