<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Resource extends Model
{ use HasFactory;
       protected $fillable = [
        'sub_lesson_id',
        'title',
        'type',
        'language',
        'link'
    ];
    
    protected $casts = [
        'type' => 'string',
        'language' => 'string'
    ];
   
    public function subLesson() {
         return $this->belongsTo(SubLesson::class); 
         } 
         
}
