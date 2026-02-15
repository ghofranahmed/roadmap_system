<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'learning_unit_id',
        'title',
        'description',
        'min_xp',
        'language',
        'starter_code',
        'test_cases',
        'is_active',
    ];

    protected $casts = [
        'test_cases' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'test_cases', // Hide test cases from JSON serialization
    ];

    public function learningUnit()
    {
        return $this->belongsTo(LearningUnit::class);
    }

    public function attempts()
    {
        return $this->hasMany(ChallengeAttempt::class);
    }
}
