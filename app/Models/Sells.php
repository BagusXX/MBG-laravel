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
