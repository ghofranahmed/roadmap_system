<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    // المايجريشن لا يحتوي على timestamps
    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'answers',
        'score',
        'passed',
    ];

    protected $casts = [
        'answers' => 'array', // لتخزين إجابات الطالب كـ JSON
        'passed' => 'boolean',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


