# 🚀 OAuth Postman Testing - Quick Start

## Quick Import

1. Open Postman
2. Click **Import** (top left)
3. Select `OAuth_Endpoints.postman_collection.json`
4. Collection imported! ✅

## Setup Environment Variables

1. Click **Environments** → **+** (Create new)
2. Name: `Roadmap API`
3. Add these variables:

| Variable | Initial Value | Current Value |
|----------|--------------|---------------|
| `base_url` | `https://roadmap_system.test/api/v1` | `https://roadmap_system.test/api/v1` |
| `google_id_token` | (leave empty) | (paste token when ready) |
| `github_oauth_code` | (leave empty) | (paste code when ready) |

4. Click **Save**
5. Select the environment from dropdown (top right)

## Get Test Tokens

### Google ID Token (Quick Method)
1. Visit: https://developers.google.com/oauthplayground/
2. Click ⚙️ → Check "Use your own OAuth credentials"
3. Enter your `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`
4. Select scopes: `userinfo.email`, `userinfo.profile`, `openid`
5. Click "Authorize APIs" → Sign in
6. Click "Exchange authorization code for tokens"
7. Copy the `id_token` (long JWT string)
8. Paste into Postman variable: `google_id_token`

### GitHub OAuth Code (Quick Method)
1. Visit (replace `YOUR_CLIENT_ID`):
   ```
   https://github.com/login/oauth/authorize?client_id=YOUR_CLIENT_ID&scope=user:email
   ```
2. Authorize the app
3. You'll be redirected with `?code=abc123...` in URL
4. Copy the `code` value
5. Paste into Postman variable: `github_oauth_code`
6. ⚠️ **Use within 10 minutes** (codes expire)

## Test Requests

### ✅ Test Google OAuth
1. Open collection: **OAuth Authentication Endpoints**
2. Open folder: **Google OAuth**
3. Select: **Google Login - Valid Token**
4. Click **Send**
5. ✅ Should return 200 with token and user data
6. Token automatically saved to `oauth_token` variable

### ✅ Test GitHub OAuth
1. Open folder: **GitHub OAuth**
2. Select: **GitHub Login - Valid Code**
3. Click **Send**
4. ✅ Should return 200 with token and user data
5. Token automatically saved to `oauth_token` variable

### ❌ Test Error Cases
- **Google Login - Invalid Token** → Should return 401
- **Google Login - Missing Field** → Should return 422
- **GitHub Login - Invalid Code** → Should return 401
- **GitHub Login - Missing Field** → Should return 422

## Use Token in Other Requests

After successful OAuth login, use the saved token:

1. In any request, go to **Authorization** tab
2. Select **Bearer Token**
3. Enter: `{{oauth_token}}`
4. Or manually add header:
   ```
   Authorization: Bearer {{oauth_token}}
   ```

## Request URLs

- **Google:** `POST {{base_url}}/auth/google`
- **GitHub:** `POST {{base_url}}/auth/github`

## Request Bodies

**Google:**
```json
{
  "id_token": "{{google_id_token}}"
}
```

**GitHub:**
```json
{
  "code": "{{github_oauth_code}}"
}
```

## Expected Responses

### Success (200)
```json
{
  "status": "success",
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "username": "john-doe",
    "email": "john@example.com"
  }
}
```

### Error (401)
```json
{
  "status": "error",
  "message": "Invalid Google token"
}
```

## Full Documentation

See `POSTMAN_OAUTH_TESTING_GUIDE.md` for:
- Detailed token acquisition methods
- Complete test scripts
- Error simulation techniques
- Troubleshooting guide

---

**Ready to test! 🎉**

