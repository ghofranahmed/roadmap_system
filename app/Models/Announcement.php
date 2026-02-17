<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'link',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───

    /**
     * Only announcements that are currently active (not expired).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('ends_at')
              ->orWhere('ends_at', '>=', now());
        });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
