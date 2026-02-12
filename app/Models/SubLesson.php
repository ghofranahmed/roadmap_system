<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;



use Illuminate\Database\Eloquent\Model;

class SubLesson extends Model
{

    use HasFactory;
     protected $fillable = [
        'lesson_id',
        'position',
        'description'
    ];
    public function lesson() {
         return $this->belongsTo(Lesson::class);
          }
    public function resources() { 
        return $this->hasMany(Resource::class);
             }
}
