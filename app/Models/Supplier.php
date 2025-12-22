<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';
    protected $fillable = [
         'nama', 'alamat', 'kontak', 'region_id'
    ];

    public function region()
    {
        return $this->belongsTo(region::class);
    }
}
