<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;


use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory, Notifiable;
     protected $fillable = [ 'name', 'email', 'password', ]; 
     protected $hidden = [ 'password', 'remember_token', ];
     protected function casts(): array {
         return [ 'password' => 'hashed', ]; 
         }
    public function setting() {
    return $this->hasMany(Setting::class);
}

}
