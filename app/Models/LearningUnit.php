<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningUnit extends Model
{   use HasFactory;
    protected $fillable = ['roadmap_id', 'title', 'position', 'is_active', 'unit_type'];

    protected $casts = [
        'is_active' => 'boolean',
    ]; 
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
