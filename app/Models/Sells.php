<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Sells extends Model
{
    //

    use HasFactory, SoftDeletes;
    protected $table = 'sells';

    protected $fillable = [
        'kode',
        'tanggal',
        'tipe',
        'harga',
        'bobot_jumlah',
        'user_id',
        'kitchen_id',
        'bahan_baku_id',
        'satuan_id',
        'recipe_bahan_baku_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function kitchen(){
        return $this->belongsTo(Kitchen::class);
    }

    public function bahanBaku(){
        return $this->belongsTo(BahanBaku::class);
    }

    public function satuan(){
        return $this->belongsTo(Unit::class, 'satuan_id');
    }

    public function recipeBahanBaku(){
        return $this->belongsTo(RecipeBahanBaku::class);
    }
}
