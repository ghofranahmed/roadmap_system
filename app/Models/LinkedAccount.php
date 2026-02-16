<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkedAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'provider_email',
        'avatar_url',
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        // Note: If you need to store tokens, consider encrypting them:
        // 'access_token' => Encrypted::class,
        // 'refresh_token' => Encrypted::class,
    ]; 
    public function user() { 
        return $this->belongsTo(User::class);
         }
}
