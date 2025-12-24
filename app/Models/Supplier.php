<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';
    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'region_id',
        'kontak',
        'nomor',
        'region_id'
    ];

    public function region()
    {
        return $this->belongsTo(region::class);
    }
}
