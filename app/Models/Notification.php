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
        'announcement_id',
        'priority',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scheduled_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ─── Relationships ───

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    // ─── Scopes ───

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        // Include both user-specific notifications and broadcast notifications (user_id = null)
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id'); // Broadcast notifications
        });
    }

    public function scopeBroadcast(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopePersonal(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '>', now());
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('scheduled_at')
              ->orWhere('scheduled_at', '<=', now());
        })->where('is_active', true);
    }

    // ─── Helpers ───

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
