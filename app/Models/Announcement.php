<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [ 'title', 'description', 'starts_at', 'ends_at', 'link', ]; 
    protected $casts = [ 'starts_at' => 'datetime', 'ends_at' => 'datetime', ];
}
