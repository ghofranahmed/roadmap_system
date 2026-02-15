<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @property \App\Models\User $user
 * @property \App\Models\Challenge $challenge
 */
class ChallengeAttempt extends Model
{
    protected $fillable = [
        'challenge_id',
        'user_id',
        'submitted_code',
        'execution_output',
        'passed',
    ];

    protected $casts = [
        'passed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}

