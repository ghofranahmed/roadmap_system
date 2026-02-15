<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;




class Quiz extends Model
{
    protected $fillable = ['learning_unit_id', 'is_active', 'max_xp', 'min_xp'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function learningUnit(): BelongsTo
    {
        return $this->belongsTo(LearningUnit::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
   
  
         


