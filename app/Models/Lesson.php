<?php

namespace App\Models;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{  use HasFactory;
      protected $fillable = [
        'learning_unit_id',
        'title',
        'description',
        'position',
        'is_active'
    ];
      protected $casts = [
        'is_active' => 'boolean'
    ];
   public function learningUnit() { 
    return $this->belongsTo(LearningUnit::class);
     } 
       public function subLessons() { 
        return $this->hasMany(SubLesson::class)->orderBy('position');
    } 
   public function tracking() {
     return $this->hasMany(LessonTracking::class); 
     }

}
