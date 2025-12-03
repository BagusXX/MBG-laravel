<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    protected $table = 'kitchens'; // sesuaikan kalau beda

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kepala_dapur',
        'nomor_kepala_dapur',
    ];
}
