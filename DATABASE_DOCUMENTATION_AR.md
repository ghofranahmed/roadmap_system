# توثيق قاعدة البيانات - منصة تعليمية للخريطة التعليمية

## الفهرس
1. [مقدمة](#مقدمة)
2. [الجداول الأساسية](#الجداول-الأساسية)
3. [ملخص الكيانات والعلاقات](#ملخص-الكيانات-والعلاقات)
4. [نقاط تحتاج مراجعة](#نقاط-تحتاج-مراجعة)

---

## مقدمة

هذا التوثيق يشرح بنية قاعدة البيانات لمنصة تعليمية للخريطة التعليمية (Roadmap Learning Platform) المبنية على Laravel. يتم توثيق جميع الجداول والحقول والعلاقات بينها وسلوكيات الحذف المتتالي (Cascade).

---

## الجداول الأساسية

### 1. جدول `users` (المستخدمون)

**الهدف من الجدول:**  
يخزن معلومات المستخدمين الأساسية في النظام، بما في ذلك المستخدمين العاديين والمسؤولين.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `username` | `string(255)` | Index, Unique |
| `email` | `string(255)` | Unique, Index |
| `email_verified_at` | `timestamp` | Nullable |
| `password` | `string(255)` | Hashed |
| `profile_picture` | `string(255)` | Nullable |
| `last_active_at` | `timestamp` | Nullable, Index |
| `last_login_at` | `timestamp` | Nullable |
| `role` | `enum('user','admin','tech_admin')` | Default: 'user', Index |
| `is_notifications_enabled` | `boolean` | Default: true |
| `remember_token` | `string(100)` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `hasMany` → `roadmap_enrollments` (enrollments)
- `hasMany` → `notifications` (notifications)
- `hasMany` → `chat_messages` (chatMessages)
- `hasMany` → `quiz_attempts` (quizAttempts)
- `hasMany` → `challenge_attempts` (challengeAttempts)
- `hasMany` → `lesson_trackings` (lessonProgress)
- `hasMany` → `linked_accounts` (linkedAccounts)
- `hasMany` → `settings` (settings)
- `hasMany` → `announcements` (announcements) - عبر `created_by`
- `hasMany` → `chatbot_sessions` (chatbotSessions)
- `hasMany` → `admin_creation_logs` (creator) - عبر `creator_id`
- `hasMany` → `admin_creation_logs` (createdUser) - عبر `created_user_id`
- `hasMany` → `chat_moderations` (user) - عبر `user_id`
- `hasMany` → `chat_moderations` (moderator) - عبر `created_by`

**ملاحظات إضافية:**
- لا يوجد SoftDeletes
- الحسابات المحمية: `admin@system.com`, `techadmin@system.com` لا يمكن حذفها
- الجلسات (sessions) تُحذف تلقائياً عند حذف المستخدم (CASCADE)

---

### 2. جدول `roadmaps` (الخرائط التعليمية)

**الهدف من الجدول:**  
يخزن الخرائط التعليمية الرئيسية التي تحتوي على وحدات تعليمية متعددة.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `title` | `string(255)` | عنوان المسار |
| `level` | `enum('beginner','intermediate','advanced')` | Index |
| `description` | `text` | وصف المسار |
| `is_active` | `boolean` | Default: true |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `hasMany` → `learning_units` (learningUnits)
- `hasMany` → `roadmap_enrollments` (enrollments)
- `hasOne` → `chat_rooms` (chatRoom)

**ملاحظات إضافية:**
- عند حذف roadmap، تُحذف تلقائياً: learning_units → lessons → sub_lessons → resources
- عند حذف roadmap، تُحذف تلقائياً: learning_units → quizzes → quiz_questions
- عند حذف roadmap، تُحذف تلقائياً: learning_units → challenges → challenge_attempts
- عند حذف roadmap، تُحذف تلقائياً: chat_rooms → chat_messages, chat_moderations

---

### 3. جدول `learning_units` (الوحدات التعليمية)

**الهدف من الجدول:**  
يخزن الوحدات التعليمية التي تنتمي لخريطة تعليمية معينة. كل وحدة يمكن أن تحتوي على درس أو اختبار أو تحدي.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `roadmap_id` | `bigint` | FK → roadmaps.id, CASCADE DELETE, Index |
| `title` | `string(255)` | عنوان الوحدة |
| `unit_type` | `enum('lesson','quiz','challenge')` | Nullable |
| `position` | `integer` | Index, Unique(roadmap_id, position) |
| `is_active` | `boolean` | Default: true |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `roadmaps` (roadmap)
- `hasMany` → `lessons` (lessons)
- `hasOne` → `lessons` (lesson)
- `hasMany` → `quizzes` (quizzes)
- `hasOne` → `quizzes` (quiz)
- `hasMany` → `challenges` (challenges)
- `hasOne` → `challenges` (challenge)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف roadmap، تُحذف جميع learning_units تلقائياً
- Unique Constraint: (roadmap_id, position) يضمن عدم تكرار المواضع في نفس المسار
- كل learning_unit يمكن أن يكون له درس واحد فقط (unique constraint على learning_unit_id في lessons)

---

### 4. جدول `lessons` (الدروس)

**الهدف من الجدول:**  
يخزن الدروس التي تنتمي لوحدة تعليمية معينة. كل درس يمكن أن يحتوي على دروس فرعية.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `learning_unit_id` | `bigint` | FK → learning_units.id, CASCADE DELETE, Index |
| `title` | `string(255)` | عنوان الدرس |
| `description` | `text` | Nullable |
| `position` | `unsignedInteger` | Default: 1, Unique(learning_unit_id, position) |
| `is_active` | `boolean` | Default: true |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `learning_units` (learningUnit)
- `hasMany` → `sub_lessons` (subLessons)
- `hasMany` → `lesson_trackings` (tracking)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف learning_unit، تُحذف جميع lessons تلقائياً
- Unique Constraint: (learning_unit_id, position) يضمن عدم تكرار المواضع في نفس الوحدة
- Index على (learning_unit_id, position) لتحسين الأداء

---

### 5. جدول `sub_lessons` (الدروس الفرعية)

**الهدف من الجدول:**  
يخزن الدروس الفرعية التي تنتمي لدرس رئيسي. كل درس فرعي يمكن أن يحتوي على موارد تعليمية.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `lesson_id` | `bigint` | FK → lessons.id, CASCADE DELETE, Index |
| `position` | `unsignedInteger` | Default: 1, Unique(lesson_id, position) |
| `description` | `text` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `lessons` (lesson)
- `hasMany` → `resources` (resources)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف lesson، تُحذف جميع sub_lessons تلقائياً
- Unique Constraint: (lesson_id, position) يضمن عدم تكرار المواضع في نفس الدرس

---

### 6. جدول `resources` (الموارد التعليمية)

**الهدف من الجدول:**  
يخزن الموارد التعليمية (كتب، فيديوهات، مقالات) المرتبطة بدرس فرعي.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `sub_lesson_id` | `bigint` | FK → sub_lessons.id, CASCADE DELETE |
| `title` | `string(255)` | عنوان المصدر |
| `type` | `enum('book','video','article')` | نوع المصدر |
| `language` | `enum('ar','en')` | Default: 'en' |
| `link` | `string(255)` | رابط المصدر |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `sub_lessons` (subLesson)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف sub_lesson، تُحذف جميع resources تلقائياً

---

### 7. جدول `lesson_trackings` (تتبع تقدم الدروس)

**الهدف من الجدول:**  
يتتبع تقدم المستخدمين في الدروس (إكمال، آخر تحديث).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `lesson_id` | `bigint` | FK → lessons.id, CASCADE DELETE |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `is_complete` | `boolean` | Default: false |
| `last_updated_at` | `timestamp` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `lessons` (lesson)
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف lesson أو user، تُحذف جميع lesson_trackings تلقائياً
- Unique Constraint: (lesson_id, user_id) يضمن عدم تكرار التتبع لنفس المستخدم والدرس

---

### 8. جدول `roadmap_enrollments` (الاشتراكات في الخرائط)

**الهدف من الجدول:**  
يتتبع اشتراكات المستخدمين في الخرائط التعليمية وحالة التقدم.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `roadmap_id` | `bigint` | FK → roadmaps.id, CASCADE DELETE |
| `started_at` | `datetime` | Nullable |
| `completed_at` | `datetime` | Nullable |
| `xp_points` | `integer` | Default: 0 |
| `status` | `enum('active','completed','paused')` | Default: 'active' |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (user)
- `belongsTo` → `roadmaps` (roadmap)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف user أو roadmap، تُحذف جميع roadmap_enrollments تلقائياً
- Unique Constraint: (user_id, roadmap_id) يضمن عدم تكرار الاشتراك لنفس المستخدم والمسار

---

### 9. جدول `quizzes` (الاختبارات)

**الهدف من الجدول:**  
يخزن الاختبارات المرتبطة بوحدة تعليمية معينة.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `learning_unit_id` | `bigint` | FK → learning_units.id, CASCADE DELETE, Unique |
| `title` | `string(255)` | Nullable |
| `is_active` | `boolean` | Default: true |
| `max_xp` | `integer` | Default: 0 |
| `min_xp` | `integer` | Default: 0 |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `learning_units` (learningUnit)
- `hasMany` → `quiz_questions` (questions)
- `hasMany` → `quiz_attempts` (attempts)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف learning_unit، تُحذف جميع quizzes تلقائياً
- Unique Constraint: learning_unit_id يضمن أن كل وحدة تعليمية لها اختبار واحد فقط

---

### 10. جدول `quiz_questions` (أسئلة الاختبارات)

**الهدف من الجدول:**  
يخزن أسئلة الاختبارات مع خياراتها وإجاباتها الصحيحة.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `quiz_id` | `bigint` | FK → quizzes.id, CASCADE DELETE |
| `question_text` | `text` | نص السؤال |
| `options` | `json` | الخيارات (مصفوفة JSON) |
| `correct_answer` | `string(255)` | الإجابة الصحيحة |
| `question_xp` | `integer` | Default: 1 |
| `order` | `unsignedInteger` | Default: 1 |

**العلاقات:**
- `belongsTo` → `quizzes` (quiz)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف quiz، تُحذف جميع quiz_questions تلقائياً
- لا يوجد timestamps في هذا الجدول
- حقل `options` يُخزن كـ JSON ويُحول تلقائياً إلى Array في Eloquent

---

### 11. جدول `quiz_attempts` (محاولات الاختبارات)

**الهدف من الجدول:**  
يتتبع محاولات المستخدمين في حل الاختبارات ونتائجهم.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `quiz_id` | `bigint` | FK → quizzes.id, CASCADE DELETE |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `answers` | `json` | إجابات المستخدم (مصفوفة JSON) |
| `score` | `integer` | Default: 0 |
| `passed` | `boolean` | Default: false |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `quizzes` (quiz)
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف quiz أو user، تُحذف جميع quiz_attempts تلقائياً
- حقل `answers` يُخزن كـ JSON ويُحول تلقائياً إلى Array في Eloquent

---

### 12. جدول `challenges` (التحديات البرمجية)

**الهدف من الجدول:**  
يخزن التحديات البرمجية المرتبطة بوحدة تعليمية معينة.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `learning_unit_id` | `bigint` | FK → learning_units.id, CASCADE DELETE, Unique |
| `title` | `string(255)` | عنوان التحدي |
| `description` | `text` | Nullable |
| `min_xp` | `integer` | Default: 0 |
| `language` | `string(255)` | اللغة البرمجية |
| `starter_code` | `longText` | Nullable |
| `test_cases` | `json` | حالات الاختبار (مصفوفة JSON) |
| `is_active` | `boolean` | Default: true |

**العلاقات:**
- `belongsTo` → `learning_units` (learningUnit)
- `hasMany` → `challenge_attempts` (attempts)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف learning_unit، تُحذف جميع challenges تلقائياً
- Unique Constraint: learning_unit_id يضمن أن كل وحدة تعليمية لها تحدي واحد فقط
- لا يوجد timestamps في هذا الجدول
- حقل `test_cases` مخفي من JSON serialization لأسباب أمنية

---

### 13. جدول `challenge_attempts` (محاولات التحديات)

**الهدف من الجدول:**  
يتتبع محاولات المستخدمين في حل التحديات البرمجية ونتائجهم.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `challenge_id` | `bigint` | FK → challenges.id, CASCADE DELETE |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `submitted_code` | `longText` | الكود المقدم |
| `execution_output` | `longText` | Nullable |
| `passed` | `boolean` | Default: false |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `challenges` (challenge)
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف challenge أو user، تُحذف جميع challenge_attempts تلقائياً

---

### 14. جدول `linked_accounts` (الحسابات المرتبطة)

**الهدف من الجدول:**  
يخزن حسابات المستخدمين المرتبطة بخدمات خارجية (Google, GitHub).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE, Index |
| `provider` | `string(255)` | Unique(provider, provider_user_id) |
| `provider_user_id` | `string(255)` | Unique(provider, provider_user_id) |
| `access_token` | `text` | Nullable |
| `refresh_token` | `text` | Nullable |
| `expires_at` | `timestamp` | Nullable |
| `provider_email` | `string(255)` | Nullable |
| `avatar_url` | `string(255)` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف user، تُحذف جميع linked_accounts تلقائياً
- Unique Constraint: (provider, provider_user_id) يضمن عدم تكرار الحساب
- Unique Constraint: (user_id, provider) يضمن عدم ربط نفس المستخدم بنفس المزود مرتين

---

### 15. جدول `announcements` (الإعلانات)

**الهدف من الجدول:**  
يخزن الإعلانات العامة التي يرسلها المسؤولون للمستخدمين.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `title` | `string(255)` | عنوان الإعلان |
| `description` | `text` | وصف الإعلان |
| `type` | `enum('general','technical','opportunity')` | Default: 'general', Index |
| `link` | `string(255)` | Nullable |
| `starts_at` | `timestamp` | Nullable |
| `ends_at` | `timestamp` | Nullable |
| `created_by` | `bigint` | FK → users.id, Nullable, NULL ON DELETE, Index |
| `send_notification` | `boolean` | Default: false, Index |
| `target_type` | `enum('all','specific_users','inactive_users','low_progress')` | Default: 'all' |
| `target_rules` | `json` | Nullable |
| `status` | `enum('draft','published')` | Default: 'draft', Index |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (creator) - عبر `created_by`
- `hasMany` → `notifications` (notifications)

**ملاحظات إضافية:**
- NULL ON DELETE: عند حذف المستخدم الذي أنشأ الإعلان، يُصبح `created_by` = NULL (الإعلان يبقى)
- حقل `target_rules` يُخزن كـ JSON ويُحول تلقائياً إلى Array في Eloquent

---

### 16. جدول `notifications` (الإشعارات)

**الهدف من الجدول:**  
يخزن الإشعارات المرسلة للمستخدمين (شخصية أو عامة).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `user_id` | `bigint` | FK → users.id, Nullable, CASCADE DELETE, Index |
| `title` | `string(255)` | عنوان الإشعار |
| `message` | `text` | نص الإشعار |
| `type` | `string(255)` | Default: 'general', Index |
| `is_active` | `boolean` | Default: true |
| `scheduled_at` | `timestamp` | Nullable |
| `read_at` | `timestamp` | Nullable, Index |
| `announcement_id` | `bigint` | FK → announcements.id, Nullable, NULL ON DELETE, Index |
| `priority` | `enum('low','medium','high')` | Default: 'medium', Index |
| `metadata` | `json` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (user)
- `belongsTo` → `announcements` (announcement)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف user، تُحذف جميع notifications الشخصية تلقائياً
- NULL ON DELETE: عند حذف announcement، يُصبح `announcement_id` = NULL (الإشعار يبقى)
- `user_id` = NULL يعني إشعار عام (broadcast)
- Index على (user_id, read_at) لتحسين استعلامات الإشعارات
- حقل `metadata` يُخزن كـ JSON ويُحول تلقائياً إلى Array في Eloquent

---

### 17. جدول `chat_rooms` (غرف الدردشة)

**الهدف من الجدول:**  
يخزن غرف الدردشة المرتبطة بكل خريطة تعليمية.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `name` | `string(255)` | اسم الغرفة |
| `roadmap_id` | `bigint` | FK → roadmaps.id, CASCADE DELETE |
| `is_active` | `boolean` | Default: true |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `roadmaps` (roadmap)
- `hasMany` → `chat_messages` (messages)
- `hasMany` → `chat_moderations` (moderations)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف roadmap، تُحذف جميع chat_rooms تلقائياً
- علاقة One-to-One مع roadmaps (كل roadmap له غرفة دردشة واحدة)

---

### 18. جدول `chat_messages` (رسائل الدردشة)

**الهدف من الجدول:**  
يخزن رسائل المستخدمين في غرف الدردشة.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `chat_room_id` | `bigint` | FK → chat_rooms.id, CASCADE DELETE |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `content` | `text` | Nullable |
| `attachment` | `string(255)` | Nullable |
| `sent_at` | `timestamp` | Nullable |
| `edited_at` | `timestamp` | Nullable |
| `deleted_at` | `timestamp` | Nullable (SoftDeletes) |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `chat_rooms` (chatRoom)
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف chat_room أو user، تُحذف جميع chat_messages تلقائياً
- **SoftDeletes**: الرسائل لا تُحذف نهائياً، بل تُوضع علامة deleted_at
- حقل `edited_at` يُستخدم لتتبع تعديل الرسائل

---

### 19. جدول `chat_moderations` (إدارة الدردشة)

**الهدف من الجدول:**  
يتتبع إجراءات الإدارة في الدردشة (كتم، حظر).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `chat_room_id` | `bigint` | FK → chat_rooms.id, CASCADE DELETE |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `type` | `enum('mute','ban')` | نوع الإجراء |
| `muted_until` | `timestamp` | Nullable |
| `reason` | `string(255)` | Nullable |
| `created_by` | `bigint` | FK → users.id, CASCADE DELETE |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `chat_rooms` (chatRoom)
- `belongsTo` → `users` (user) - عبر `user_id`
- `belongsTo` → `users` (moderator) - عبر `created_by`

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف chat_room أو user، تُحذف جميع chat_moderations تلقائياً
- Unique Constraint: (chat_room_id, user_id, type) يضمن عدم تكرار نفس الإجراء
- حقل `muted_until` يُستخدم للكتم المؤقت

---

### 20. جدول `chatbot_sessions` (جلسات الذكاء الاصطناعي)

**الهدف من الجدول:**  
يخزن جلسات المحادثة مع الذكاء الاصطناعي لكل مستخدم.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `user_id` | `bigint` | FK → users.id, CASCADE DELETE, Index |
| `title` | `string(255)` | Nullable |
| `last_activity_at` | `timestamp` | Nullable, Index |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (user)
- `hasMany` → `chatbot_messages` (messages)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف user، تُحذف جميع chatbot_sessions تلقائياً
- Index على (user_id, last_activity_at) لتحسين استعلامات الجلسات

---

### 21. جدول `chatbot_messages` (رسائل الذكاء الاصطناعي)

**الهدف من الجدول:**  
يخزن رسائل المحادثة في جلسات الذكاء الاصطناعي.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `chatbot_session_id` | `bigint` | FK → chatbot_sessions.id, CASCADE DELETE, Index |
| `role` | `string(255)` | 'user' أو 'assistant' |
| `body` | `text` | نص الرسالة |
| `tokens_used` | `unsignedInteger` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `chatbot_sessions` (session)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف chatbot_session، تُحذف جميع chatbot_messages تلقائياً
- Index على (chatbot_session_id, created_at) لتحسين استعلامات الرسائل

---

### 22. جدول `settings` (الإعدادات)

**الهدف من الجدول:**  
يخزن إعدادات النظام القابلة للتخصيص.

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `modified_by_user_id` | `bigint` | FK → users.id, Nullable, NULL ON DELETE |
| `key` | `string(255)` | Unique |
| `value` | `text` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (modifier) - عبر `modified_by_user_id`

**ملاحظات إضافية:**
- NULL ON DELETE: عند حذف المستخدم الذي عدّل الإعداد، يُصبح `modified_by_user_id` = NULL (الإعداد يبقى)
- Unique Constraint على `key` يضمن عدم تكرار نفس المفتاح

---

### 23. جدول `chatbot_settings` (إعدادات الذكاء الاصطناعي)

**الهدف من الجدول:**  
يخزن إعدادات تكوين الذكاء الاصطناعي (المزود، النموذج، المعاملات).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `provider` | `string(255)` | Default: 'openai' |
| `model_name` | `string(255)` | Nullable |
| `temperature` | `decimal(3,2)` | Default: 0.7 |
| `max_tokens` | `integer` | Default: 1000 |
| `max_context_messages` | `integer` | Default: 10 |
| `request_timeout` | `integer` | Default: 15 |
| `is_enabled` | `boolean` | Default: true |
| `system_prompt_template` | `text` | Nullable |
| `updated_by` | `unsignedBigInteger` | FK → users.id, Nullable, SET NULL ON DELETE |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (updater) - عبر `updated_by`

**ملاحظات إضافية:**
- SET NULL ON DELETE: عند حذف المستخدم الذي عدّل الإعداد، يُصبح `updated_by` = NULL (الإعداد يبقى)

---

### 24. جدول `system_settings` (إعدادات النظام)

**الهدف من الجدول:**  
يخزن إعدادات النظام العامة (مفتاح-قيمة).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `key` | `string(255)` | Unique |
| `value` | `text` | Nullable |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

**العلاقات:**
- لا توجد علاقات خارجية

**ملاحظات إضافية:**
- Unique Constraint على `key` يضمن عدم تكرار نفس المفتاح

---

### 25. جدول `admin_creation_logs` (سجلات إنشاء المسؤولين)

**الهدف من الجدول:**  
يتتبع إنشاء حسابات المسؤولين الجديدة (للمراقبة والحد من المعدل).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `bigint` | PK, Auto Increment |
| `creator_id` | `bigint` | FK → users.id, CASCADE DELETE, Index |
| `created_user_id` | `bigint` | FK → users.id, CASCADE DELETE |
| `created_role` | `string(255)` | 'admin' أو 'tech_admin' |
| `created_at` | `timestamp` | Index |
| `updated_at` | `timestamp` | |

**العلاقات:**
- `belongsTo` → `users` (creator) - عبر `creator_id`
- `belongsTo` → `users` (createdUser) - عبر `created_user_id`

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف creator أو created_user، تُحذف جميع admin_creation_logs تلقائياً
- Index على (creator_id, created_role, created_at) لتحسين استعلامات المراقبة

---

### 26. جدول `sessions` (جلسات Laravel)

**الهدف من الجدول:**  
يخزن جلسات Laravel للمستخدمين (للحفاظ على حالة تسجيل الدخول).

| الخاصية | نوع البيانات | ملاحظات |
|---------|-------------|---------|
| `id` | `string(255)` | PK |
| `user_id` | `bigint` | FK → users.id, Nullable, CASCADE DELETE |
| `ip_address` | `string(45)` | Nullable |
| `user_agent` | `text` | Nullable |
| `payload` | `longText` | |
| `last_activity` | `integer` | Index |

**العلاقات:**
- `belongsTo` → `users` (user)

**ملاحظات إضافية:**
- CASCADE DELETE: عند حذف user، تُحذف جميع sessions تلقائياً
- هذا جدول Laravel الافتراضي لإدارة الجلسات

---

## ملخص الكيانات والعلاقات

### تصنيف الجداول حسب الوحدة

#### 1. المصادقة والمستخدمون (Authentication & Users)
- `users` - المستخدمون
- `sessions` - جلسات Laravel
- `linked_accounts` - الحسابات المرتبطة
- `admin_creation_logs` - سجلات إنشاء المسؤولين

#### 2. الخرائط التعليمية (Roadmaps)
- `roadmaps` - الخرائط التعليمية
- `learning_units` - الوحدات التعليمية
- `lessons` - الدروس
- `sub_lessons` - الدروس الفرعية
- `resources` - الموارد التعليمية
- `roadmap_enrollments` - الاشتراكات

#### 3. الاختبارات (Quizzes)
- `quizzes` - الاختبارات
- `quiz_questions` - أسئلة الاختبارات
- `quiz_attempts` - محاولات الاختبارات

#### 4. التحديات البرمجية (Challenges)
- `challenges` - التحديات
- `challenge_attempts` - محاولات التحديات

#### 5. التتبع والتقدم (Tracking & Progress)
- `lesson_trackings` - تتبع تقدم الدروس

#### 6. المجتمع والدردشة (Community & Chat)
- `chat_rooms` - غرف الدردشة
- `chat_messages` - رسائل الدردشة
- `chat_moderations` - إدارة الدردشة

#### 7. الذكاء الاصطناعي (AI Chatbot)
- `chatbot_sessions` - جلسات الذكاء الاصطناعي
- `chatbot_messages` - رسائل الذكاء الاصطناعي
- `chatbot_settings` - إعدادات الذكاء الاصطناعي

#### 8. الإعلانات والإشعارات (Announcements & Notifications)
- `announcements` - الإعلانات
- `notifications` - الإشعارات

#### 9. الإعدادات (Settings)
- `settings` - الإعدادات
- `system_settings` - إعدادات النظام

---

### ملخص العلاقات (ERD نصي)

#### علاقات المستخدمين (Users)
- `users` (1) → `roadmap_enrollments` (N)
- `users` (1) → `notifications` (N)
- `users` (1) → `chat_messages` (N)
- `users` (1) → `quiz_attempts` (N)
- `users` (1) → `challenge_attempts` (N)
- `users` (1) → `lesson_trackings` (N)
- `users` (1) → `linked_accounts` (N)
- `users` (1) → `settings` (N)
- `users` (1) → `announcements` (N) - عبر `created_by`
- `users` (1) → `chatbot_sessions` (N)
- `users` (1) → `admin_creation_logs` (N) - عبر `creator_id`
- `users` (1) → `admin_creation_logs` (N) - عبر `created_user_id`
- `users` (1) → `chat_moderations` (N) - عبر `user_id`
- `users` (1) → `chat_moderations` (N) - عبر `created_by`
- `users` (1) → `sessions` (N)

#### علاقات الخرائط التعليمية (Roadmaps)
- `roadmaps` (1) → `learning_units` (N)
- `roadmaps` (1) → `roadmap_enrollments` (N)
- `roadmaps` (1) → `chat_rooms` (1) - One-to-One

#### علاقات الوحدات التعليمية (Learning Units)
- `learning_units` (N) → `roadmaps` (1)
- `learning_units` (1) → `lessons` (N)
- `learning_units` (1) → `lessons` (1) - One-to-One
- `learning_units` (1) → `quizzes` (N)
- `learning_units` (1) → `quizzes` (1) - One-to-One
- `learning_units` (1) → `challenges` (N)
- `learning_units` (1) → `challenges` (1) - One-to-One

#### علاقات الدروس (Lessons)
- `lessons` (N) → `learning_units` (1)
- `lessons` (1) → `sub_lessons` (N)
- `lessons` (1) → `lesson_trackings` (N)

#### علاقات الدروس الفرعية (Sub Lessons)
- `sub_lessons` (N) → `lessons` (1)
- `sub_lessons` (1) → `resources` (N)

#### علاقات الاختبارات (Quizzes)
- `quizzes` (N) → `learning_units` (1)
- `quizzes` (1) → `quiz_questions` (N)
- `quizzes` (1) → `quiz_attempts` (N)

#### علاقات التحديات (Challenges)
- `challenges` (N) → `learning_units` (1)
- `challenges` (1) → `challenge_attempts` (N)

#### علاقات الدردشة (Chat)
- `chat_rooms` (N) → `roadmaps` (1)
- `chat_rooms` (1) → `chat_messages` (N)
- `chat_rooms` (1) → `chat_moderations` (N)

#### علاقات الذكاء الاصطناعي (AI Chatbot)
- `chatbot_sessions` (N) → `users` (1)
- `chatbot_sessions` (1) → `chatbot_messages` (N)

#### علاقات الإعلانات (Announcements)
- `announcements` (N) → `users` (1) - عبر `created_by` (Nullable)
- `announcements` (1) → `notifications` (N)

---

### سلوكيات الحذف المتتالي (Cascade Behaviors)

#### CASCADE DELETE (حذف تلقائي)
- `users` → `sessions`, `roadmap_enrollments`, `notifications`, `chat_messages`, `quiz_attempts`, `challenge_attempts`, `lesson_trackings`, `linked_accounts`, `chatbot_sessions`, `admin_creation_logs`, `chat_moderations`
- `roadmaps` → `learning_units`, `roadmap_enrollments`, `chat_rooms`
- `learning_units` → `lessons`, `quizzes`, `challenges`
- `lessons` → `sub_lessons`, `lesson_trackings`
- `sub_lessons` → `resources`
- `quizzes` → `quiz_questions`, `quiz_attempts`
- `challenges` → `challenge_attempts`
- `chat_rooms` → `chat_messages`, `chat_moderations`
- `chatbot_sessions` → `chatbot_messages`

#### NULL ON DELETE (تعيين NULL)
- `announcements.created_by` → `users.id` (الإعلان يبقى عند حذف المنشئ)
- `notifications.announcement_id` → `announcements.id` (الإشعار يبقى عند حذف الإعلان)
- `settings.modified_by_user_id` → `users.id` (الإعداد يبقى عند حذف المعدّل)

#### SET NULL ON DELETE (تعيين NULL)
- `chatbot_settings.updated_by` → `users.id` (الإعداد يبقى عند حذف المحدّث)

---

## نقاط تحتاج مراجعة

### 1. جداول ناقصة أو غير واضحة
- **لا توجد جداول ناقصة** - جميع الجداول المذكورة في Models موجودة في Migrations

### 2. علاقات غير واضحة
- **علاقة One-to-One بين `roadmaps` و `chat_rooms`**: غير محددة بوضوح في Migration (يجب التأكد من وجود constraint)
- **علاقة One-to-One بين `learning_units` و `lessons/quizzes/challenges`**: موجودة كـ Unique constraint ولكن يمكن تحسينها

### 3. حقول غير مستخدمة
- **`users.is_admin`**: موجود في casts ولكن تم استبداله بـ `role` (يجب التحقق من إزالته)
- **`challenges.timestamps`**: غير موجود في Migration ولكن موجود في Model كـ `public $timestamps = false` (صحيح)

### 4. تحسينات مقترحة
- **إضافة Index على `roadmap_enrollments.status`** لتحسين استعلامات التصفية
- **إضافة Index على `quiz_attempts.passed`** لتحسين استعلامات النتائج
- **إضافة Index على `challenge_attempts.passed`** لتحسين استعلامات النتائج
- **مراجعة SoftDeletes**: فقط `chat_messages` يستخدم SoftDeletes، قد تحتاج جداول أخرى (مثل `users`, `roadmaps`) إلى SoftDeletes

### 5. قيود أمنية
- **`challenges.test_cases`**: مخفي من JSON serialization (صحيح)
- **`linked_accounts.tokens`**: يجب التفكير في التشفير (موجود تعليق في Model)

### 6. ملاحظات على البيانات
- **JSON Fields**: `quiz_questions.options`, `quiz_attempts.answers`, `challenges.test_cases`, `announcements.target_rules`, `notifications.metadata` - جميعها تُحول تلقائياً إلى Array في Eloquent
- **Enum Fields**: العديد من الحقول تستخدم Enum مما يضمن سلامة البيانات

---

## الخلاصة

قاعدة البيانات منظمة بشكل جيد مع علاقات واضحة وسلوكيات حذف متتالي مناسبة. معظم الجداول تحتوي على timestamps وindexes مناسبة. النظام يدعم:
- خرائط تعليمية متعددة المستويات
- تتبع تقدم المستخدمين
- اختبارات وتحديات برمجية
- نظام إشعارات متقدم
- دردشة مجتمعية مع إدارة
- ذكاء اصطناعي للمساعدة
- نظام إدارة مسؤولين متعدد المستويات

---

**تاريخ التوثيق:** 2026  
**الإصدار:** 1.0  
**المنصة:** Laravel Roadmap Learning Platform

