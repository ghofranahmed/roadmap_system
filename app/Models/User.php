<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_active_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
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
}
