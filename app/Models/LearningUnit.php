<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningUnit extends Model
{
    protected $fillable = [ 'roadmap_id', 'title', 'position' ]; 
    public function roadmap() { 
      return $this->belongsTo(Roadmap::class); 
      } 
     public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }
    public function quizzes() {
           return $this->hasMany(Quiz::class);
     } 
    public function challenges() { 
      return $this->hasMany(Challenge::class);
       }
}
