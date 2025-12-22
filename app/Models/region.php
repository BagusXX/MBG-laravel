<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{
    //
    protected $table = 'regions';
    protected $fillable = [
        'kode_region',
        'nama_region',
        'penanggung_jawab',
    ];
}
