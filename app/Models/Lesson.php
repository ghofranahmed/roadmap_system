<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [  ];
   public function learningUnit() { 
    return $this->belongsTo(LearningUnit::class);
     } 
   public function subLessons() { 
    return $this->hasMany(SubLesson::class);
     } 
   public function tracking() {
     return $this->hasMany(LessonTracking::class); 
     }

}
