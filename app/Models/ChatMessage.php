<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'content',
        'attachment',
        'sent_at',
        'edited_at',
    ];

    protected $casts = [
        'sent_at'   => 'datetime',
        'edited_at' => 'datetime',
    ];

    /* ---- Relationships ---- */

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
