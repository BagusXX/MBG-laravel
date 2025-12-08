<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kitchen extends Model
{
    use SoftDeletes;
    
    protected $table = 'kitchens';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kepala_dapur',
        'nomor_kepala_dapur',
    ];
}

