<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['kitchen_id', 'menu_id'];

    public function getTotalHargaAttribute()
    {
        return $this->bahanBaku->sum(function ($b) {
            return $b->pivot->harga * $b->pivot->jumlah;
        });
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function bahanBaku()
    {
        return $this->belongsToMany(BahanBaku::class, 'recipe_bahan_baku')
            ->withPivot('jumlah', 'satuan', 'harga');
    }
}
