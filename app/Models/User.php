<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
        'kitchen_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }
}
