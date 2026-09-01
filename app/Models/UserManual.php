<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserManual extends Model
{
    protected $table = 'user_manuals';

    protected $fillable = [
        'role_name',
        'nama_file',
        'file_path',
    ];
}
