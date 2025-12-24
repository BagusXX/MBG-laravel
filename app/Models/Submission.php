<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'submission';

    protected $fillable = [
        'kode',
        'tanggal',
        'kitchen_id',
        'recipe_bahan_baku_id',
        'porsi'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function recipe_bahan_baku()
    {
        return $this->belongsTo(RecipeBahanBaku::class);
    }
}
