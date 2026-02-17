<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'target_type',
        'target_rules',
        'publish_at',
        'status',
        'created_by',
        // Legacy columns kept for backward compatibility
        'description',
        'starts_at',
        'ends_at',
        'link',
    ];

    protected $casts = [
        'target_rules' => 'array',
        'publish_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // ─── Relationships ───

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Scopes ───

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDueForPublishing(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
                     ->where('publish_at', '<=', now());
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter announcements relevant to a specific user.
     */
    public function scopeRelevantTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            // All users
            $q->where('target_type', 'all');

            // Specific users
            $q->orWhere(function (Builder $sub) use ($user) {
                $sub->where('target_type', 'specific_users')
                    ->whereJsonContains('target_rules->user_ids', $user->id);
            });

            // Inactive users
            $q->orWhere(function (Builder $sub) use ($user) {
                $sub->where('target_type', 'inactive_users')
                    ->where(function (Builder $inner) use ($user) {
                        if ($user->last_login_at) {
                            // Get inactive_days from target_rules, default 7
                            $inner->whereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(target_rules, '$.inactive_days')) IS NOT NULL AND ? <= NOW() - INTERVAL CAST(JSON_UNQUOTE(JSON_EXTRACT(target_rules, '$.inactive_days')) AS UNSIGNED) DAY",
                                [$user->last_login_at]
                            );
                        }
                        // If user never logged in, they are inactive
                        $inner->orWhereNull('target_rules');
                    });
            });

            // Low progress users
            $q->orWhere(function (Builder $sub) use ($user) {
                $sub->where('target_type', 'low_progress');
                // Low progress filtering is done at the application level
                // since it requires complex enrollment/tracking joins
            });
        });
    }
}
