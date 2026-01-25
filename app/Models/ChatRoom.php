<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [ 'name', 'roadmap_id', 'is_active', ]; 
    protected $casts = [ 'is_active' => 'boolean', ]; 
    public function messages() {
         return $this->hasMany(ChatMessage::class);
          }
     public function roadmap() {
        return $this->belongsTo(Roadmap::class);
    }
}
