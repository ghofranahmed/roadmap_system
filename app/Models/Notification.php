<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [ 'user_id', 'title', 'message', 'is_active', 'scheduled_at', ];
     protected $casts = [ 'is_active' => 'boolean', 'scheduled_at' => 'datetime', ];
     public function user() {
         return $this->belongsTo(User::class);
          }
          
}
