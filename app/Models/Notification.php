<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_active',
        'scheduled_at',
        'read_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helpers ───

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
