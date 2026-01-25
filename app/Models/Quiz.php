<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [ 'learning_unit_id', 'is_active', 'max_xp', 'min_xp']; 
    public function learningUnit() {
         return $this->belongsTo(LearningUnit::class); 
         } 
    public function questions() {
         return $this->hasMany(QuizQuestion::class); 
         }
     public function attempts() { 
          return $this->hasMany(QuizAttempt::class); 
          }
         
}

