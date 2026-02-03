<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapEnrollment extends Model
{
    protected $fillable = [ 'user_id', 'roadmap_id', 'started_at', 'completed_at', 'xp_points', 
     'status', ];
      public function user() { 
        return $this->belongsTo(User::class); 
        } 
        public function roadmap() {
             return $this->belongsTo(Roadmap::class);
              }
              protected $casts = [
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
];

}
