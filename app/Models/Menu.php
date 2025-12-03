<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'kitchen_id',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }
}
