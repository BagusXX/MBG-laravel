<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    //

    // protected $table = 'purchase';

    protected $fillable = [
        'user_id',
        'harga',
        'bobot_jumlah',
        'supplier_id',
        'recipe_bahan_baku_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
