<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    protected $fillable = [ 'title', 'description', 'level','is_active' ];
     use HasFactory;
    public function enrollments() {
         return $this->hasMany(RoadmapEnrollment::class);
          } 
    public function learningUnits() {
         return $this->hasMany(LearningUnit::class);
          } 
   public function chatRoom() {
    return $this->hasOne(ChatRoom::class);  // One-to-One relationship
}
 /**
     * الحصول على المستوى بالعربية
     */
    public function getArabicLevel(): string
    {
        return match($this->level) {
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
            default => $this->level,
        };
    }
}
