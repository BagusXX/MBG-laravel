<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    protected $table = 'dapur'; // ← ini wajib
    protected $fillable = [
        'nama',
        'alamat',
        'kepala_dapur',
        'nomor_kepala_dapur',
    ];
}
