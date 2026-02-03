<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model

{
    protected $fillable = ['modified_by_user_id', 'key', 'value'];

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_user_id');
    }
}


  

