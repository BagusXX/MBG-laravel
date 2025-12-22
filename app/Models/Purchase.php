<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    //

    protected $table = 'purchase';

    protected $fillable = [
        'harga_mitra',
        'harga_dapur',
        'bobot_jumlah',
        'user_id',
        'recipe_bahan_baku_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
