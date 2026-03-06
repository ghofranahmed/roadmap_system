# Project Audit - Executive Summary

## Top 15 Removal Candidates (Highest Confidence)

| # | Item | Type | Confidence | Risk | Action |
|---|------|------|------------|------|--------|
| 1 | `users.is_admin` | Column | **High** | Low | **Remove** - Deprecated, system uses `role` |
| 2 | `settings` table + `Setting` model | Table/Model | **High** | Medium | **Remove** - No usage found |
| 3 | `User::settings()` | Relationship | **High** | Low | **Remove** - Never accessed |
| 4 | `Setting::modifiedBy()` | Relationship | **High** | Low | **Remove** - Never accessed |
| 5 | `chatbot_sessions.title` | Column | **High** | Low | **Remove** - Never used |
| 6 | `chatbot_messages.body` | Column | **High** | Low | **Remove** - Never used |
| 7 | `chatbot_messages.role` | Column | **High** | Low | **Remove** - Never used |
| 8 | `chat_messages.attachment` | Column | **High** | Low | **Remove** - Never used |
| 9 | `chatbot_settings.model_name` | Column | **High** | Low | **Remove** - Validated but never used |
| 10 | `chat_messages.sent_at` | Column | **Medium** | Low | **Review** - Write-only, may need for audit |
| 11 | `chat_messages.edited_at` | Column | **Medium** | Low | **Review** - Write-only, may need for audit |
| 12 | `notifications.is_active` | Column | **Medium** | Low | **Review** - Used in scope but not checked |
| 13 | `notifications.type` | Column | **Medium** | Low | **Review** - Rarely filtered |
| 14 | `notifications.priority` | Column | **Medium** | Low | **Review** - Rarely filtered |
| 15 | `notifications.metadata` | Column | **Medium** | Low | **Review** - Write-only, may need for future |

## Quick Stats

- **High Confidence Removals:** 9 items
- **Medium Confidence Reviews:** 6 items
- **Total Potential Cleanup:** 15+ items
- **Estimated Time:** 2-4 hours (Phase 1)

## Files to Modify (Phase 1)

### Models
- `app/Models/User.php` - Remove `is_admin` cast, remove `settings()` relationship
- `app/Models/Setting.php` - Delete file (if table empty)
- `app/Models/ChatbotSession.php` - Remove `title` from fillable
- `app/Models/ChatbotMessage.php` - Remove `body`, `role` from fillable
- `app/Models/ChatMessage.php` - Remove `attachment` from fillable
- `app/Models/ChatbotSetting.php` - Remove `model_name` from fillable, remove `updater()` relationship

### Controllers
- `app/Http/Controllers/Admin/SmartTeacherController.php` - Remove `model_name` validation

### Migrations
- Create new migration to drop columns and table

## Verification Commands

```bash
# Before removal
php artisan test
php artisan schema:dump
php artisan route:list > routes_backup.txt

# After removal
php artisan migrate
php artisan test
php artisan route:cache
```

## Full Report

See `PROJECT_AUDIT_REPORT.md` for complete analysis.

