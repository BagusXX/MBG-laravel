<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Kitchen extends Model
{
    protected $fillable = ['name'];

    public function admins()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_ADMIN_DAPUR);
    }
}

