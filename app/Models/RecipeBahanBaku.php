<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeBahanBaku extends Model
{
    //

    protected $table = 'recipe_bahan_baku';
    protected $fillable = [
        'recipe_id',
        'bahan_baku_id',
        'jumlah',
        'satuan'
    ];
}
