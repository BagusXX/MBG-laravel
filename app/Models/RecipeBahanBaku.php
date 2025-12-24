<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeBahanBaku extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $table = 'recipe_bahan_baku';
    protected $fillable = [
        'kitchen_id',
        'menu_id',
        'bahan_baku_id',
        'jumlah',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function bahan_baku()
    {
        return $this->hasMany(BahanBaku::class);
    }
}
