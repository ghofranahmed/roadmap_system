# Dead Code Detection Report

**Date:** 2026-03-06  
**Scope:** Full Laravel Roadmap Learning Platform  
**Method:** Systematic codebase scan for unused components

---

## Executive Summary

This report identifies **dead code** (code that exists but is never used) across the entire codebase. Items are categorized by confidence level (High/Medium/Low) to guide safe removal.

**Key Findings:**
- **High Confidence Removals:** 4 items
- **Medium Confidence Reviews:** 2 items
- **Total Dead Code Identified:** 6 items

---

## Dead Code Items

### 1. Database Column: `chat_messages.attachment`

**Type:** Database Field  
**File:** `app/Models/ChatMessage.php` (line 16)  
**Location:** `$fillable` array  
**Reason:** Field is defined in model's `$fillable` but **never used** in:
- `ChatMessageController::store()` - Only uses `content` and `sent_at`
- `ChatMessageController::update()` - Only uses `content` and `edited_at`
- No validation rules reference it
- No database queries select or filter by it
- No views display it

**Confidence:** **High**  
**Risk:** Low  
**Recommendation:** **Remove**

**Evidence:**
```php
// app/Models/ChatMessage.php:12-19
protected $fillable = [
    'chat_room_id',
    'user_id',
    'content',
    'attachment',  // ← Never used
    'sent_at',
    'edited_at',
];
```

**Search Results:**
- `grep -r "attachment" app/Http/Controllers/ChatMessageController.php` → No matches
- `grep -r "->attachment" app/` → No matches

---

### 2. Database Column: `chatbot_settings.model_name`

**Type:** Database Field  
**File:** `app/Models/ChatbotSetting.php` (line 12)  
**Location:** `$fillable` array  
**Reason:** Field is:
- Validated in `SmartTeacherController::update()` (line 52)
- Stored in database
- **Never used** in any LLM provider:
  - `OpenAIProvider` - Uses `provider`, `temperature`, `max_tokens` only
  - `GroqProvider` - Uses `provider`, `temperature`, `max_tokens` only
  - `GeminiProvider` - Uses `provider`, `temperature`, `max_tokens` only
  - `DummyProvider` - No settings used
- Not passed to any API calls
- Not used in `ChatbotReplyService`

**Confidence:** **High**  
**Risk:** Low  
**Recommendation:** **Remove**

**Evidence:**
```php
// app/Http/Controllers/Admin/SmartTeacherController.php:52
'model_name' => 'nullable|string|max:255',  // ← Validated but never used

// app/Models/ChatbotSetting.php:12
'model_name',  // ← In fillable but never accessed
```

**Search Results:**
- `grep -r "model_name" app/Services/Chatbot/` → No matches
- `grep -r "\$settings->model_name" app/` → No matches

---

### 3. Model Relationship: `ChatbotSetting::updater()`

**Type:** Eloquent Relationship  
**File:** `app/Models/ChatbotSetting.php` (lines 51-54)  
**Location:** Relationship method  
**Reason:** Relationship is defined but **never called** anywhere:
- Not used in controllers
- Not used in views
- Not used in services
- Not eager loaded in queries
- `updated_by` field is written but relationship is never accessed

**Confidence:** **High**  
**Risk:** Low  
**Recommendation:** **Remove**

**Evidence:**
```php
// app/Models/ChatbotSetting.php:51-54
public function updater(): BelongsTo
{
    return $this->belongsTo(User::class, 'updated_by');
}
```

**Search Results:**
- `grep -r "->updater()" app/` → No matches
- `grep -r "updater()" app/` → Only definition found

**Note:** The `updated_by` column itself is still used (written in `SmartTeacherController::update()` line 62), so keep the column, only remove the relationship method.

---

### 4. Model Relationship: `AdminCreationLog::createdUser()`

**Type:** Eloquent Relationship  
**File:** `app/Models/AdminCreationLog.php` (lines 32-35)  
**Location:** Relationship method  
**Reason:** Relationship is defined but **never called** anywhere:
- Not used in `AdminCreationRateLimitService`
- Not used in controllers
- Not used in views
- Not eager loaded in queries
- `created_user_id` field is written but relationship is never accessed

**Confidence:** **High**  
**Risk:** Low  
**Recommendation:** **Remove**

**Evidence:**
```php
// app/Models/AdminCreationLog.php:32-35
public function createdUser(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_user_id');
}
```

**Search Results:**
- `grep -r "->createdUser()" app/` → No matches
- `grep -r "createdUser()" app/` → Only definition found

**Note:** The `created_user_id` column itself is still used (written in `AdminCreationRateLimitService`), so keep the column, only remove the relationship method.

---

### 5. Database Column: `chat_messages.sent_at` (Write-Only)

**Type:** Database Field  
**File:** `app/Models/ChatMessage.php` (line 17)  
**Location:** `$fillable` and `$casts` arrays  
**Reason:** Field is:
- **Written** in `ChatMessageController::store()` (line 104) and `storeByRoom()` (line 345)
- **Never read** or displayed anywhere:
  - Not in API responses
  - Not in views
  - Not used for filtering or sorting
  - Not used in queries

**Confidence:** **Medium**  
**Risk:** Low (may be intended for audit trail)  
**Recommendation:** **Review** - Consider keeping if audit trail is needed, otherwise remove

**Evidence:**
```php
// app/Http/Controllers/ChatMessageController.php:104
'sent_at' => now(),  // ← Written but never read
```

**Search Results:**
- `grep -r "->sent_at" app/` → No matches (except in model definition)
- `grep -r "sent_at" resources/views/` → No matches

---

### 6. Database Column: `chat_messages.edited_at` (Write-Only)

**Type:** Database Field  
**File:** `app/Models/ChatMessage.php` (line 18)  
**Location:** `$fillable` and `$casts` arrays  
**Reason:** Field is:
- **Written** in `ChatMessageController::update()` (line 149)
- **Never read** or displayed anywhere:
  - Not in API responses
  - Not in views
  - Not used for filtering or sorting
  - Not used in queries

**Confidence:** **Medium**  
**Risk:** Low (may be intended for audit trail)  
**Recommendation:** **Review** - Consider keeping if audit trail is needed, otherwise remove

**Evidence:**
```php
// app/Http/Controllers/ChatMessageController.php:149
'edited_at' => now(),  // ← Written but never read
```

**Search Results:**
- `grep -r "->edited_at" app/` → No matches (except in model definition)
- `grep -r "edited_at" resources/views/` → No matches

---

## Items Previously Identified as Dead (But Actually Used)

The following items were identified in the audit report but upon closer inspection are **actually used**:

### ❌ `chatbot_sessions.title` - **USED**
- **Used in:** `ChatbotController::store()` (line 40), `sendMessage()` (line 87), `processMessage()` (lines 132, 158)
- **Status:** Keep

### ❌ `chatbot_messages.body` - **USED**
- **Used in:** `ChatbotReplyService::generateReply()` (line 43, 46), all LLM providers
- **Status:** Keep

### ❌ `chatbot_messages.role` - **USED**
- **Used in:** `ChatbotReplyService::generateReply()` (line 43, 46), all LLM providers
- **Status:** Keep

---

## Summary Table

| # | Item | Type | File | Confidence | Risk | Recommendation |
|---|------|------|------|------------|------|----------------|
| 1 | `chat_messages.attachment` | Column | `app/Models/ChatMessage.php:16` | **High** | Low | **Remove** |
| 2 | `chatbot_settings.model_name` | Column | `app/Models/ChatbotSetting.php:12` | **High** | Low | **Remove** |
| 3 | `ChatbotSetting::updater()` | Relationship | `app/Models/ChatbotSetting.php:51-54` | **High** | Low | **Remove** |
| 4 | `AdminCreationLog::createdUser()` | Relationship | `app/Models/AdminCreationLog.php:32-35` | **High** | Low | **Remove** |
| 5 | `chat_messages.sent_at` | Column | `app/Models/ChatMessage.php:17` | Medium | Low | **Review** |
| 6 | `chat_messages.edited_at` | Column | `app/Models/ChatMessage.php:18` | Medium | Low | **Review** |

---

## Removal Plan

### Phase 1: High Confidence Removals (Safe)

**Items to Remove:**
1. `chat_messages.attachment` column
2. `chatbot_settings.model_name` column
3. `ChatbotSetting::updater()` relationship method
4. `AdminCreationLog::createdUser()` relationship method

**Files to Modify:**
- `app/Models/ChatMessage.php` - Remove `attachment` from `$fillable`
- `app/Models/ChatbotSetting.php` - Remove `model_name` from `$fillable`, remove `updater()` method
- `app/Models/AdminCreationLog.php` - Remove `createdUser()` method
- `app/Http/Controllers/Admin/SmartTeacherController.php` - Remove `model_name` validation

**Migrations to Create:**
- `2026_03_06_XXXXXX_remove_unused_chat_message_attachment.php`
- `2026_03_06_XXXXXX_remove_unused_chatbot_setting_model_name.php`

### Phase 2: Medium Confidence Reviews (Optional)

**Items to Review:**
- `chat_messages.sent_at` - Keep if audit trail needed, otherwise remove
- `chat_messages.edited_at` - Keep if audit trail needed, otherwise remove

**Decision:** Leave for now (may be useful for future audit features)

---

## Verification Checklist

### Pre-Removal
- [x] Scan codebase for all references
- [x] Verify no usage in controllers
- [x] Verify no usage in services
- [x] Verify no usage in views
- [x] Verify no usage in API responses
- [x] Check database for existing data

### Post-Removal
- [ ] Run migrations
- [ ] Run tests: `php artisan test`
- [ ] Verify routes: `php artisan route:list`
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Test application manually
- [ ] Verify no errors in logs

---

## Notes

1. **Foreign Key Columns:** The `updated_by` and `created_user_id` columns are kept even though their relationships are removed, as they are still written and may be needed for audit purposes.

2. **Write-Only Fields:** `sent_at` and `edited_at` are write-only but may be useful for future audit features. Decision to keep them for now.

3. **Model Name Field:** The `model_name` field was likely intended for future use but was never implemented. Safe to remove.

4. **Attachment Field:** The `attachment` field was likely planned for file uploads but never implemented. Safe to remove.

---

**Report Generated:** 2026-03-06  
**Next Step:** Proceed with Phase 1 removals (High Confidence items only)

