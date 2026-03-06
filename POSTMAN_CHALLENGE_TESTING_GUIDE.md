# Postman Testing Guide - Challenge Features

Complete testing guide for all challenge features implemented today.

## Base Configuration

**Base URL:** `{{base_url}}/api/v1`

**Required Headers:**
```
Authorization: Bearer {{token_user}}
Accept: application/json
Content-Type: application/json
```

**Variables to Set:**
- `{{base_url}}` - Your API base URL (e.g., `http://localhost:8000`)
- `{{token_user}}` - User authentication token
- `{{challenge_id}}` - Challenge ID to test
- `{{attempt_id}}` - Challenge attempt ID

---

## Test Sequence Overview

1. **Prerequisites:** User must be enrolled in a roadmap
2. **Get Challenge Details** (verify lock status)
3. **Test Locked Challenge** (if user XP < min_xp)
4. **Start Challenge Attempt**
5. **Submit Challenge Solution** (various test cases)
6. **Test Retake Feature**
7. **Test Resubmission Prevention**
8. **Security Verification**

---

## 1️⃣ Challenge Unlock Logic (min_xp)

### Test 1.1: Get Challenge Details (Locked)

**Endpoint:** `GET /challenges/{challengeId}`

**Request:**
```
GET {{base_url}}/api/v1/challenges/{{challenge_id}}
```

**Headers:**
```
Authorization: Bearer {{token_user}}
Accept: application/json
```

**Expected Response (403 - If Locked):**
```json
{
  "success": false,
  "message": "Challenge is locked. You need more XP to unlock this challenge.",
  "data": {
    "is_locked": true,
    "required_xp": 100,
    "user_xp": 50,
    "missing_xp": 50
  }
}
```

**Expected Response (200 - If Unlocked):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "learning_unit_id": 5,
    "title": "Reverse String Challenge",
    "description": "Write a function to reverse a string",
    "language": "python",
    "min_xp": 100,
    "is_active": true,
    "starter_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "is_locked": false,
    "required_xp": 100,
    "user_xp": 150,
    "missing_xp": 0
  }
}
```

**Verification Points:**
- ✅ `test_cases` is NOT in response
- ✅ `starter_code` only included if unlocked
- ✅ Lock information included (`is_locked`, `required_xp`, `user_xp`, `missing_xp`)

---

### Test 1.2: Try to Start Attempt on Locked Challenge

**Endpoint:** `POST /challenges/{challengeId}/attempts`

**Request:**
```
POST {{base_url}}/api/v1/challenges/{{challenge_id}}/attempts
```

**Headers:**
```
Authorization: Bearer {{token_user}}
Accept: application/json
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Challenge is locked. You need more XP to unlock this challenge.",
  "data": {
    "is_locked": true,
    "required_xp": 100,
    "user_xp": 50,
    "missing_xp": 50
  }
}
```

**Verification:**
- ✅ Status code: 403 Forbidden
- ✅ Lock details included in response

---

## 2️⃣ Start Challenge Attempt

### Test 2.1: Start Attempt (Unlocked Challenge)

**Endpoint:** `POST /challenges/{challengeId}/attempts`

**Request:**
```
POST {{base_url}}/api/v1/challenges/{{challenge_id}}/attempts
```

**Headers:**
```
Authorization: Bearer {{token_user}}
Accept: application/json
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Attempt started successfully",
  "data": {
    "id": 123,
    "challenge_id": 1,
    "user_id": 10,
    "submitted_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "execution_output": null,
    "passed": false,
    "created_at": "2026-02-15T10:30:00.000000Z",
    "updated_at": "2026-02-15T10:30:00.000000Z"
  }
}
```

**Verification Points:**
- ✅ Status code: 201 Created
- ✅ `attempt_id` in response
- ✅ `submitted_code` contains starter_code
- ✅ `execution_output` is null (active attempt)
- ✅ `passed` is false (not yet evaluated)

**Save Variables:**
- Save `data.id` as `{{attempt_id}}` for next tests

---

## 3️⃣ Submit Challenge Solution

### Test 3.1: Submit Valid Code (All Test Cases Pass)

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request:**
```
PUT {{base_url}}/api/v1/challenge-attempts/{{attempt_id}}/submit
```

**Headers:**
```
Authorization: Bearer {{token_user}}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    return s[::-1]"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": true,
    "attempt": {
      "id": 123,
      "challenge_id": 1,
      "user_id": 10,
      "submitted_code": "def reverse_string(s):\n    return s[::-1]",
      "execution_output": "[{\"case\":1,\"passed\":true,\"output\":\"olleh\\n\",\"expected_output\":\"olleh\",\"error\":null},{\"case\":2,\"passed\":true,\"output\":\"world\\n\",\"expected_output\":\"world\",\"error\":null}]",
      "passed": true,
      "created_at": "2026-02-15T10:30:00.000000Z",
      "updated_at": "2026-02-15T10:35:15.000000Z"
    },
    "details": [
      {
        "case": 1,
        "passed": true,
        "output": "olleh\n",
        "expected_output": "olleh",
        "error": null
      },
      {
        "case": 2,
        "passed": true,
        "output": "world\n",
        "expected_output": "world",
        "error": null
      }
    ]
  }
}
```

**Verification Points:**
- ✅ Status code: 200 OK
- ✅ `passed: true` (all test cases passed)
- ✅ `details` array contains per-test-case results
- ✅ Each detail includes: `case`, `passed`, `output`, `expected_output`, `error`
- ✅ `output` contains actual stdout from compiler
- ✅ Normalization handled (newlines normalized)

---

### Test 3.2: Submit Invalid Code (Some Test Cases Fail)

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request:**
```
PUT {{base_url}}/api/v1/challenge-attempts/{{attempt_id}}/submit
```

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    return s"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": false,
    "attempt": {
      "id": 124,
      "challenge_id": 1,
      "user_id": 10,
      "submitted_code": "def reverse_string(s):\n    return s",
      "execution_output": "[{\"case\":1,\"passed\":false,\"output\":\"hello\\n\",\"expected_output\":\"olleh\",\"error\":null},{\"case\":2,\"passed\":true,\"output\":\"world\\n\",\"expected_output\":\"world\",\"error\":null}]",
      "passed": false,
      "created_at": "2026-02-15T10:40:00.000000Z",
      "updated_at": "2026-02-15T10:40:12.000000Z"
    },
    "details": [
      {
        "case": 1,
        "passed": false,
        "output": "hello\n",
        "expected_output": "olleh",
        "error": null
      },
      {
        "case": 2,
        "passed": true,
        "output": "world\n",
        "expected_output": "world",
        "error": null
      }
    ]
  }
}
```

**Verification Points:**
- ✅ `passed: false` (not all test cases passed)
- ✅ Failed test case shows incorrect output
- ✅ Passed test case shows correct output

---

### Test 3.3: Submit Code with Compilation Error

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    retrn s[::-1]"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": false,
    "attempt": {
      "id": 125,
      "challenge_id": 1,
      "user_id": 10,
      "submitted_code": "def reverse_string(s):\n    retrn s[::-1]",
      "execution_output": "[{\"case\":1,\"passed\":false,\"output\":\"\",\"expected_output\":\"olleh\",\"error\":\"SyntaxError: invalid syntax\"}]",
      "passed": false,
      "created_at": "2026-02-15T10:45:00.000000Z",
      "updated_at": "2026-02-15T10:45:05.000000Z"
    },
    "details": [
      {
        "case": 1,
        "passed": false,
        "output": "",
        "expected_output": "olleh",
        "error": "SyntaxError: invalid syntax"
      }
    ]
  }
}
```

**Verification Points:**
- ✅ `error` field contains compilation/runtime error
- ✅ `output` is empty when error occurs
- ✅ `passed: false` due to error

---

## 4️⃣ Output Comparison Policy (Normalization)

### Test 4.1: Output with Extra Newlines (Should Pass)

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    result = s[::-1]\n    print(result)\n    return result"
}
```

**Note:** If the code outputs with trailing newline but expected doesn't have it, normalization should handle it.

**Expected Response:**
```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": true,
    "details": [
      {
        "case": 1,
        "passed": true,
        "output": "olleh\r\n",
        "expected_output": "olleh",
        "error": null
      }
    ]
  }
}
```

**Verification Points:**
- ✅ Normalization converts `\r\n` → `\n` and trims
- ✅ Comparison succeeds despite newline differences
- ✅ `passed: true` even with different newline formats

---

### Test 4.2: Output with Trailing Spaces (Should Pass)

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    return s[::-1] + ' '"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "passed": true,
    "details": [
      {
        "case": 1,
        "passed": true,
        "output": "olleh ",
        "expected_output": "olleh",
        "error": null
      }
    ]
  }
}
```

**Verification Points:**
- ✅ `trim()` removes trailing spaces
- ✅ Comparison succeeds after normalization

---

## 5️⃣ Retake Challenge Feature

### Test 5.1: Start First Attempt

**Endpoint:** `POST /challenges/{challengeId}/attempts`

**Request:**
```
POST {{base_url}}/api/v1/challenges/{{challenge_id}}/attempts
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Attempt started successfully",
  "data": {
    "id": 126,
    "challenge_id": 1,
    "user_id": 10,
    "submitted_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "execution_output": null,
    "passed": false
  }
}
```

**Save:** `data.id` as `{{first_attempt_id}}`

---

### Test 5.2: Start Second Attempt (Retake - Should Abandon First)

**Endpoint:** `POST /challenges/{challengeId}/attempts`

**Request:**
```
POST {{base_url}}/api/v1/challenges/{{challenge_id}}/attempts
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Attempt started successfully",
  "data": {
    "id": 127,
    "challenge_id": 1,
    "user_id": 10,
    "submitted_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "execution_output": null,
    "passed": false
  }
}
```

**Verification Points:**
- ✅ New attempt created (different ID)
- ✅ Previous attempt should be marked as abandoned

---

### Test 5.3: Verify Previous Attempt is Abandoned

**Endpoint:** `GET /challenge-attempts/{challengeAttemptId}`

**Request:**
```
GET {{base_url}}/api/v1/challenge-attempts/{{first_attempt_id}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 126,
    "challenge_id": 1,
    "user_id": 10,
    "submitted_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "execution_output": "{\"status\":\"abandoned\",\"message\":\"Attempt was abandoned when user started a new attempt\"}",
    "passed": false
  }
}
```

**Verification Points:**
- ✅ `execution_output` is NOT null (marked as abandoned)
- ✅ Contains `"status": "abandoned"` in JSON
- ✅ Previous attempt preserved (not deleted)

---

### Test 5.4: Verify Only One Active Attempt Exists

**Endpoint:** `GET /challenges/{challengeId}/my-attempts`

**Request:**
```
GET {{base_url}}/api/v1/challenges/{{challenge_id}}/my-attempts
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Attempts retrieved successfully",
  "data": {
    "data": [
      {
        "id": 127,
        "challenge_id": 1,
        "user_id": 10,
        "submitted_code": "...",
        "execution_output": null,
        "passed": false,
        "created_at": "2026-02-15T11:00:00.000000Z"
      },
      {
        "id": 126,
        "challenge_id": 1,
        "user_id": 10,
        "submitted_code": "...",
        "execution_output": "{\"status\":\"abandoned\",...}",
        "passed": false,
        "created_at": "2026-02-15T10:55:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 2
  }
}
```

**Verification Points:**
- ✅ Only one attempt has `execution_output: null` (active)
- ✅ Previous attempts have `execution_output` set (abandoned/submitted)
- ✅ All attempts preserved in history

---

## 6️⃣ Prevent Resubmission

### Test 6.1: Try to Resubmit Already Submitted Attempt

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request:**
```
PUT {{base_url}}/api/v1/challenge-attempts/{{attempt_id}}/submit
```

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    return s[::-1]"
}
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "This attempt has already been passed and cannot be resubmitted"
}
```

**Or (if attempt was submitted but failed):**
```json
{
  "success": false,
  "message": "Unauthorized action."
}
```

**Verification Points:**
- ✅ Status code: 403 Forbidden
- ✅ Cannot resubmit attempt with `execution_output` already set
- ✅ Policy blocks resubmission

---

### Test 6.2: Try to Submit Abandoned Attempt

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request:**
```
PUT {{base_url}}/api/v1/challenge-attempts/{{first_attempt_id}}/submit
```

**Request Body:**
```json
{
  "code": "def reverse_string(s):\n    return s[::-1]"
}
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized action."
}
```

**Verification Points:**
- ✅ Cannot submit abandoned attempt
- ✅ `execution_output` is not null, so policy blocks it

---

## 7️⃣ Security Checks

### Test 7.1: Verify test_cases Never Returned

**Endpoint:** `GET /challenges/{challengeId}`

**Request:**
```
GET {{base_url}}/api/v1/challenges/{{challenge_id}}
```

**Verification:**
- ✅ Response does NOT contain `test_cases` field
- ✅ Only safe metadata returned (title, description, language, min_xp)
- ✅ `starter_code` only if unlocked

---

### Test 7.2: Verify Locked Challenge Cannot Be Executed

**Scenario:** User with XP < min_xp tries to submit

**Endpoint:** `PUT /challenge-attempts/{challengeAttemptId}/submit`

**Request:**
```
PUT {{base_url}}/api/v1/challenge-attempts/{{attempt_id}}/submit
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Challenge is locked. You need more XP to unlock this challenge.",
  "data": {
    "is_locked": true,
    "required_xp": 100,
    "user_xp": 50,
    "missing_xp": 50
  }
}
```

**Verification Points:**
- ✅ Status code: 403 Forbidden
- ✅ Lock enforced even on submit (not just start)
- ✅ Lock details included

---

## Complete Test Sequence

### Recommended Testing Order

1. **Setup:**
   - Login and get `{{token_user}}`
   - Get a challenge ID: `{{challenge_id}}`
   - Verify user XP vs challenge min_xp

2. **Test Locked Challenge:**
   - `GET /challenges/{challengeId}` (verify lock status)
   - `POST /challenges/{challengeId}/attempts` (should fail if locked)

3. **Test Unlocked Challenge:**
   - `GET /challenges/{challengeId}` (verify unlocked)
   - `POST /challenges/{challengeId}/attempts` (start attempt)
   - Save `{{attempt_id}}`

4. **Test Submission:**
   - `PUT /challenge-attempts/{attemptId}/submit` (valid code)
   - `PUT /challenge-attempts/{attemptId}/submit` (invalid code)
   - `PUT /challenge-attempts/{attemptId}/submit` (code with error)

5. **Test Normalization:**
   - Submit code with extra newlines/spaces
   - Verify normalization handles differences

6. **Test Retake:**
   - `POST /challenges/{challengeId}/attempts` (start new attempt)
   - `GET /challenge-attempts/{firstAttemptId}` (verify abandoned)
   - `GET /challenges/{challengeId}/my-attempts` (verify only one active)

7. **Test Resubmission Prevention:**
   - `PUT /challenge-attempts/{attemptId}/submit` (try resubmit)
   - Verify 403 error

8. **Test Security:**
   - Verify `test_cases` never in responses
   - Verify lock enforced on all endpoints

---

## Postman Collection Variables

Set these variables in your Postman environment:

```
base_url = http://localhost:8000
token_user = <your_auth_token>
challenge_id = <challenge_id_to_test>
attempt_id = <will_be_set_from_responses>
first_attempt_id = <will_be_set_from_responses>
```

---

## Automated Tests (Postman Test Scripts)

### Test Script for Start Attempt

```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response contains attempt_id", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('id');
    pm.expect(jsonData.data.execution_output).to.be.null;
    
    // Save attempt_id for next requests
    pm.environment.set("attempt_id", jsonData.data.id);
});

pm.test("starter_code is included", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('submitted_code');
});
```

### Test Script for Submit Attempt

```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response contains passed status", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('passed');
    pm.expect(jsonData.data).to.have.property('details');
});

pm.test("Details array contains test case results", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data.details).to.be.an('array');
    pm.expect(jsonData.data.details.length).to.be.greaterThan(0);
    
    jsonData.data.details.forEach(function(detail) {
        pm.expect(detail).to.have.property('case');
        pm.expect(detail).to.have.property('passed');
        pm.expect(detail).to.have.property('output');
        pm.expect(detail).to.have.property('expected_output');
    });
});

pm.test("test_cases are NOT in response", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.not.have.property('test_cases');
    pm.expect(jsonData.data.attempt).to.not.have.property('test_cases');
});
```

### Test Script for Locked Challenge

```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});

pm.test("Lock information included", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('is_locked');
    pm.expect(jsonData.data.is_locked).to.be.true;
    pm.expect(jsonData.data).to.have.property('required_xp');
    pm.expect(jsonData.data).to.have.property('user_xp');
    pm.expect(jsonData.data).to.have.property('missing_xp');
});
```

---

## Example Challenge Setup (For Testing)

If you need to create a test challenge via admin API:

**Challenge Structure:**
```json
{
  "learning_unit_id": 1,
  "title": "Reverse String Challenge",
  "description": "Write a function to reverse a string",
  "min_xp": 100,
  "language": "python",
  "starter_code": "def reverse_string(s):\n    # Your code here\n    pass",
  "test_cases": [
    {
      "stdin": "",
      "expected_output": "olleh"
    },
    {
      "stdin": "",
      "expected_output": "world"
    }
  ],
  "is_active": true
}
```

**Note:** `stdin` can be empty for Option 2 grading (stdout vs expected_output only).

---

## Troubleshooting

### Common Issues

1. **403 Forbidden on startAttempt:**
   - Check user XP vs challenge min_xp
   - Verify user is enrolled in roadmap
   - Check challenge is_active = true

2. **403 Forbidden on submitAttempt:**
   - Verify attempt belongs to user
   - Check attempt.execution_output is null (not already submitted)
   - Verify challenge is still unlocked

3. **Compiler errors:**
   - Verify JDoodle credentials in .env
   - Check code syntax matches language
   - Verify language is supported (python, javascript, java, cpp)

4. **Normalization not working:**
   - Check output has newlines/spaces
   - Verify normalizeOutput() is called
   - Compare normalized strings, not raw

---

## Summary Checklist

- [ ] Challenge unlock logic (min_xp) enforced
- [ ] Start attempt works for unlocked challenges
- [ ] Submit solution compiles and evaluates correctly
- [ ] Output normalization handles newlines/spaces
- [ ] Retake creates new attempt and abandons previous
- [ ] Resubmission prevented (403 on already submitted)
- [ ] test_cases never returned to frontend
- [ ] Lock enforced on all endpoints (start + submit)

---

**End of Testing Guide**

