<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan_baku';
    protected $fillable = ['kode', 'nama', 'harga', 'satuan', 'kitchen_id',];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function bahanBaku()
    {
        return $this->hasMany(beli::class);
    }
}
