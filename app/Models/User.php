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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_active_at' => 'datetime',
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
        return $this->hasOne(LinkedAccount::class);
         }
         // لإرسال إيميلات password reset
    /*public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }*/
}
