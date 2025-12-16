<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = ['kitchen_id', 'menu_id', 'porsi'];

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
                ->withPivot('jumlah', 'satuan', 'porsi');
}

}