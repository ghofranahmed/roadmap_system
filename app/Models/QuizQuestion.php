<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    // ملاحظة: الجدول في المايجريشن لا يحتوي على timestamps
    public $timestamps = false;

    protected $fillable = [
        'quiz_id', 
        'question_text', 
        'options', 
        'correct_answer', 
        'question_xp', 
        'order'
    ];

    protected $casts = [
        'options' => 'array', // لتحويل حقل الـ JSON إلى Array تلقائياً
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}