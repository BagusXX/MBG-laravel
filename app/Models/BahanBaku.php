<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bahan_baku';
    protected $fillable = ['kode', 'nama', 'harga', 'satuan_id', 'kitchen_id',];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function bahanBaku()
    {
        return $this->hasMany(Purchase::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'satuan_id', 'id');
    }

    public function recipes(){
        return $this->hasMany(RecipeBahanBaku::class);
    }
}
