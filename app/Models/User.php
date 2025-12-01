<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'username',
        'password',
        'dapur_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // relasi ke dapur
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'dapur_id');
    }
}
