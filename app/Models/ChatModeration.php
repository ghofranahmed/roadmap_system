<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatModeration extends Model
{
    protected $fillable = [
        'chat_room_id',
        'user_id',
        'type',
        'muted_until',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'muted_until' => 'datetime',
    ];

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ───── Scopes ───── */

    public function scopeMutes($query)
    {
        return $query->where('type', 'mute');
    }

    public function scopeBans($query)
    {
        return $query->where('type', 'ban');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'ban')
              ->orWhere(function ($q2) {
                  $q2->where('type', 'mute')
                     ->where(function ($q3) {
                         $q3->whereNull('muted_until')
                            ->orWhere('muted_until', '>', now());
                     });
              });
        });
    }
}

