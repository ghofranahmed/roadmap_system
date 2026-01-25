<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    protected $fillable = [ 'title', 'description', 'level','is_active' ];
    public function enrollments() {
         return $this->hasMany(RoadmapEnrollment::class);
          } 
    public function learningUnits() {
         return $this->hasMany(LearningUnit::class);
          } 
   public function chatRoom() {
    return $this->hasOne(ChatRoom::class);  // One-to-One relationship
}

}
