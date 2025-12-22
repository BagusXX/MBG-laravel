<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jual extends Model
{
    //
    protected $table = 'juals';

    protected $fillable = [
        'harga_mitra',
        'harga_dapur',
        'bobot_jumlah',
        'user_id',
        'recipe_bahan_baku_id'
    ];

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function recipeBahanBaku(){
        return $this->belongsTo(RecipeBahanBaku::class);
    }
}
