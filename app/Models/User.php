<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_picture',
        'last_active_at',
        'last_login_at',
        'role',
        'is_notifications_enabled',
        // Removed: google_id, github_id, avatar (use linked_accounts table instead)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_active_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_notifications_enabled' => 'boolean',
    ];
    public function enrollments() { 
        return $this->hasMany(RoadmapEnrollment::class); 
        } 
    public function notifications() { 
            return $this->hasMany(Notification::class);
             } 
    public function chatMessages() { 
        return $this->hasMany(ChatMessage::class); 
        } 
    public function quizAttempts() { 
        return $this->hasMany(QuizAttempt::class);
         } 
    public function challengeAttempts() { 
        return $this->hasMany(ChallengeAttempt::class);
         } 
    public function lessonProgress() { 
        return $this->hasMany(LessonTracking::class); 
        }
    public function linkedAccounts() { 
        return $this->hasMany(LinkedAccount::class);
         }
     public function settings()
{
    return $this->hasMany(Setting::class);
} /**
     * التحقق مما إذا كان المستخدم مشتركاً في خريطة طريق معينة
     */
public function hasEnrolled($roadmapId)
{
    // تأكد أنك لا تكتب $roadmapId->id() هنا
    return $this->enrollments()->where('roadmap_id', $roadmapId)->exists();
}
    
    /**
     * دالة مساعدة للتحقق من الصلاحيات (Admin)
     * ستستخدمها في Middleware الأدمن
     * @deprecated Use role check instead
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'tech_admin']);
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is normal admin
     */
    public function isNormalAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is technical admin
     */
    public function isTechAdmin(): bool
    {
        return $this->role === 'tech_admin';
    }

    /**
     * Check if user is any kind of admin (admin or tech_admin)
     */
    public function isAnyAdmin(): bool
    {
        return in_array($this->role, ['admin', 'tech_admin']);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function chatbotSessions()
    {
        return $this->hasMany(ChatbotSession::class);
    }

    /**
     * Get the name for Filament admin panel display.
     * 
     * @return string Always returns a non-null string for Filament display
     */
    public function getFilamentName(): string
    {
        return $this->name 
            ?? $this->username 
            ?? $this->email 
            ?? 'Admin';
    }
    public function canAccessPanel(Panel $panel): bool
{
    return $this->isAnyAdmin();
}

    /**
     * Get the user's display name (for AdminLTE navbar).
     * Returns username, email, or 'Admin' as fallback.
     * This is accessed as $user->name in Blade templates.
     * 
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->username ?? $this->email ?? 'Admin';
    }

    /**
     * Get the user's image URL for AdminLTE.
     * Returns profile_picture if available, or a default avatar.
     * 
     * @return string
     */
    public function adminlte_image(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        // Return default AdminLTE user image
        return asset('vendor/adminlte/dist/img/user2-160x160.jpg');
    }

    /**
     * Get the user's description for AdminLTE user menu.
     * Returns role or email as description.
     * 
     * @return string
     */
    public function adminlte_desc(): string
    {
        return ucfirst(str_replace('_', ' ', $this->role ?? 'user'));
    }

    /**
     * Get the user's profile URL for AdminLTE.
     * Returns null as we don't have a profile page yet.
     * 
     * @return string|null
     */
    public function adminlte_profile_url(): ?string
    {
        return null;
    }
    
}
