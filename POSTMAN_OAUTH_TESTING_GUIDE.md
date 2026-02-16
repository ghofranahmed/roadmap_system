# 🔐 OAuth Endpoints - Postman Testing Guide

## 📋 Overview

This guide provides complete Postman testing setup for OAuth authentication endpoints:
- **POST /api/v1/auth/google** - Google OAuth Login
- **POST /api/v1/auth/github** - GitHub OAuth Login

**Base URL:** `https://roadmap_system.test/api/v1`

---

## 1️⃣ Google OAuth Endpoint

### Request Configuration

**Method:** `POST`  
**URL:** `https://roadmap_system.test/api/v1/auth/google`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "id_token": "YOUR_GOOGLE_ID_TOKEN_HERE"
}
```

### Example Successful Response (200)
```json
{
  "status": "success",
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
  "user": {
    "id": 1,
    "username": "john-doe",
    "email": "john.doe@example.com"
  }
}
```

### Example Error Responses

**Invalid Token (401):**
```json
{
  "status": "error",
  "message": "Invalid Google token"
}
```

**Missing Required Data (422):**
```json
{
  "status": "error",
  "message": "Google token missing required data"
}
```

**Validation Error (422):**
```json
{
  "message": "The id token field is required.",
  "errors": {
    "id_token": ["The id token field is required."]
  }
}
```

---

## 2️⃣ GitHub OAuth Endpoint

### Request Configuration

**Method:** `POST`  
**URL:** `https://roadmap_system.test/api/v1/auth/github`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "code": "YOUR_GITHUB_OAUTH_CODE_HERE"
}
```

### Example Successful Response (200)
```json
{
  "status": "success",
  "token": "2|abcdefghijklmnopqrstuvwxyz1234567890",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "john.doe@example.com"
  }
}
```

### Example Error Responses

**Invalid Code (401):**
```json
{
  "status": "error",
  "message": "Invalid GitHub code"
}
```

**Failed to Exchange Code (401):**
```json
{
  "status": "error",
  "message": "Failed to exchange code for token"
}
```

**Failed to Fetch User Info (401):**
```json
{
  "status": "error",
  "message": "Failed to fetch user info from GitHub"
}
```

**Missing Required Data (422):**
```json
{
  "status": "error",
  "message": "GitHub account missing required data"
}
```

**Validation Error (422):**
```json
{
  "message": "The code field is required.",
  "errors": {
    "code": ["The code field is required."]
  }
}
```

---

## 3️⃣ How to Obtain Test Tokens

### Google ID Token

#### Option 1: Using Google OAuth Playground (Easiest for Testing)
1. Go to [Google OAuth 2.0 Playground](https://developers.google.com/oauthplayground/)
2. Click the gear icon (⚙️) in the top right
3. Check "Use your own OAuth credentials"
4. Enter your `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`
5. In the left panel, select:
   - `https://www.googleapis.com/auth/userinfo.email`
   - `https://www.googleapis.com/auth/userinfo.profile`
   - `openid`
6. Click "Authorize APIs"
7. Sign in with your Google account
8. Click "Exchange authorization code for tokens"
9. Copy the `id_token` from the response (it's a long JWT string)

#### Option 2: Using Google Sign-In JavaScript SDK (For Web Apps)
```javascript
// In your web app
gapi.load('auth2', function() {
  gapi.auth2.init({
    client_id: 'YOUR_GOOGLE_CLIENT_ID'
  }).then(function() {
    var authInstance = gapi.auth2.getAuthInstance();
    authInstance.signIn().then(function(googleUser) {
      var idToken = googleUser.getAuthResponse().id_token;
      console.log('ID Token:', idToken);
      // Use this token in Postman
    });
  });
});
```

#### Option 3: Using Mobile SDK (For Mobile Apps)
- **Android:** Use Google Sign-In SDK, get ID token from `GoogleSignInAccount.getIdToken()`
- **iOS:** Use Google Sign-In SDK, get ID token from `GIDSignIn.sharedInstance.currentUser.authentication.idToken`

### GitHub OAuth Code

#### Step 1: Create GitHub OAuth App
1. Go to GitHub → Settings → Developer settings → OAuth Apps
2. Click "New OAuth App"
3. Fill in:
   - **Application name:** Roadmap System Test
   - **Homepage URL:** `https://roadmap_system.test`
   - **Authorization callback URL:** `https://roadmap_system.test/api/v1/auth/github/callback`
4. Click "Register application"
5. Note your `Client ID` and `Client Secret`

#### Step 2: Get Authorization Code
1. Open this URL in your browser (replace `YOUR_CLIENT_ID`):
```
https://github.com/login/oauth/authorize?client_id=YOUR_CLIENT_ID&scope=user:email
```
2. Authorize the application
3. You'll be redirected to your callback URL with a `code` parameter:
```
https://roadmap_system.test/api/v1/auth/github/callback?code=abc123def456...
```
4. Copy the `code` value from the URL

**⚠️ Important:** The code expires in 10 minutes. Use it quickly or get a new one.

#### Alternative: Using cURL to Get Code
```bash
# Step 1: Get authorization URL
echo "Visit this URL:"
echo "https://github.com/login/oauth/authorize?client_id=YOUR_CLIENT_ID&scope=user:email"

# Step 2: After authorization, you'll get redirected with a code
# Extract the code from the redirect URL
```

---

## 4️⃣ How to Simulate Invalid Token Errors

### For Google Endpoint

**Test 1: Invalid Token Format**
```json
{
  "id_token": "invalid-token-format"
}
```
**Expected:** 401 - "Invalid Google token"

**Test 2: Expired Token**
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjEyMzQ1NiJ9.expired.token.signature"
}
```
**Expected:** 401 - "Invalid Google token"

**Test 3: Token Missing Email**
Use a token from a Google account that doesn't have email access.  
**Expected:** 422 - "Google token missing required data"

**Test 4: Missing Field**
```json
{}
```
**Expected:** 422 - Validation error

### For GitHub Endpoint

**Test 1: Invalid Code**
```json
{
  "code": "invalid-code-123"
}
```
**Expected:** 401 - "Invalid GitHub code" or "Failed to exchange code for token"

**Test 2: Expired Code**
Use a code that's older than 10 minutes.  
**Expected:** 401 - "Invalid GitHub code"

**Test 3: Already Used Code**
Use a code that was already exchanged.  
**Expected:** 401 - "Invalid GitHub code"

**Test 4: Missing Field**
```json
{}
```
**Expected:** 422 - Validation error

**Test 5: Empty Code**
```json
{
  "code": ""
}
```
**Expected:** 422 - Validation error

---

## 5️⃣ Postman Test Scripts

### Google OAuth Test Script

```javascript
// Test 1: Status Code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test 2: Response has status field
pm.test("Response has status field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('status');
});

// Test 3: Status is success
pm.test("Status is success", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.status).to.eql('success');
});

// Test 4: Response contains token
pm.test("Response contains token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('token');
    pm.expect(jsonData.token).to.be.a('string');
    pm.expect(jsonData.token.length).to.be.above(0);
});

// Test 5: Response contains user object
pm.test("Response contains user object", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('user');
    pm.expect(jsonData.user).to.be.an('object');
});

// Test 6: User has required fields
pm.test("User has required fields", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.user).to.have.property('id');
    pm.expect(jsonData.user).to.have.property('username');
    pm.expect(jsonData.user).to.have.property('email');
});

// Test 7: Save token to environment
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.status === 'success' && jsonData.token) {
        pm.environment.set('oauth_token', jsonData.token);
        pm.environment.set('oauth_user_id', jsonData.user.id);
        pm.environment.set('oauth_user_email', jsonData.user.email);
        console.log('Token saved to environment variable: oauth_token');
    }
}

// Test 8: Response time is acceptable
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});
```

### GitHub OAuth Test Script

```javascript
// Test 1: Status Code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test 2: Response has status field
pm.test("Response has status field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('status');
});

// Test 3: Status is success
pm.test("Status is success", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.status).to.eql('success');
});

// Test 4: Response contains token
pm.test("Response contains token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('token');
    pm.expect(jsonData.token).to.be.a('string');
    pm.expect(jsonData.token.length).to.be.above(0);
});

// Test 5: Response contains user object
pm.test("Response contains user object", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('user');
    pm.expect(jsonData.user).to.be.an('object');
});

// Test 6: User has required fields
pm.test("User has required fields", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.user).to.have.property('id');
    pm.expect(jsonData.user).to.have.property('username');
    pm.expect(jsonData.user).to.have.property('email');
});

// Test 7: Save token to environment
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.status === 'success' && jsonData.token) {
        pm.environment.set('oauth_token', jsonData.token);
        pm.environment.set('oauth_user_id', jsonData.user.id);
        pm.environment.set('oauth_user_email', jsonData.user.email);
        console.log('Token saved to environment variable: oauth_token');
    }
}

// Test 8: Response time is acceptable
pm.test("Response time is less than 2000ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(2000);
});
```

### Error Response Test Script (Use for both endpoints)

```javascript
// Test 1: Status code is error (401 or 422)
pm.test("Status code is error", function () {
    pm.expect(pm.response.code).to.be.oneOf([401, 422]);
});

// Test 2: Response has status field
pm.test("Response has status field", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('status');
});

// Test 3: Status is error
pm.test("Status is error", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.status).to.eql('error');
});

// Test 4: Response has error message
pm.test("Response has error message", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('message');
    pm.expect(jsonData.message).to.be.a('string');
    pm.expect(jsonData.message.length).to.be.above(0);
});
```

---

## 6️⃣ Postman Environment Variables

Create a Postman Environment with these variables:

### Variables to Set:
```
base_url = https://roadmap_system.test/api/v1
google_id_token = (paste your Google ID token here)
github_oauth_code = (paste your GitHub OAuth code here)
oauth_token = (automatically set by test scripts)
oauth_user_id = (automatically set by test scripts)
oauth_user_email = (automatically set by test scripts)
```

### How to Use:
1. In Postman, click "Environments" in the left sidebar
2. Click "+" to create a new environment
3. Name it "Roadmap API - Production" or "Roadmap API - Local"
4. Add the variables above
5. Select the environment from the dropdown in the top right

---

## 7️⃣ Testing Workflow

### Complete Testing Flow:

1. **Setup Environment**
   - Create Postman environment
   - Set `base_url` variable

2. **Get Test Tokens**
   - Get Google ID token (see section 3)
   - Get GitHub OAuth code (see section 3)
   - Update environment variables

3. **Test Google OAuth**
   - Send request with valid token
   - Verify response (200, token, user data)
   - Check token is saved to environment
   - Test with invalid token
   - Test with missing field

4. **Test GitHub OAuth**
   - Send request with valid code
   - Verify response (200, token, user data)
   - Check token is saved to environment
   - Test with invalid code
   - Test with missing field

5. **Test Rate Limiting**
   - Send 6 requests rapidly (limit is 5 per minute)
   - Verify 429 status code on 6th request

6. **Test Token Usage**
   - Use saved `oauth_token` in Authorization header for protected endpoints
   - Format: `Bearer {{oauth_token}}`

---

## 8️⃣ Common Issues & Solutions

### Issue: "Invalid Google token"
**Solution:** 
- Ensure token is fresh (not expired)
- Verify `GOOGLE_CLIENT_ID` matches the token's audience
- Check token includes email scope

### Issue: "Invalid GitHub code"
**Solution:**
- Codes expire in 10 minutes - get a fresh one
- Ensure code hasn't been used already
- Verify `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET` are correct

### Issue: "Failed to exchange code for token"
**Solution:**
- Check GitHub OAuth app credentials
- Verify callback URL matches exactly
- Ensure code wasn't already used

### Issue: Rate Limit (429)
**Solution:**
- Wait 1 minute between test batches
- Or increase throttle limit in routes (for testing only)

---

## 9️⃣ Import Postman Collection

1. Open Postman
2. Click "Import" button (top left)
3. Select the `OAuth_Endpoints.postman_collection.json` file
4. The collection will be imported with all requests and tests pre-configured

---

## 🔟 Quick Reference

### Google OAuth Endpoint
```
POST https://roadmap_system.test/api/v1/auth/google
Content-Type: application/json

{
  "id_token": "YOUR_TOKEN"
}
```

### GitHub OAuth Endpoint
```
POST https://roadmap_system.test/api/v1/auth/github
Content-Type: application/json

{
  "code": "YOUR_CODE"
}
```

### Use Token in Other Requests
```
Authorization: Bearer {{oauth_token}}
```

---

**Happy Testing! 🚀**

