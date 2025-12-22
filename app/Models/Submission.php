<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{

    protected $table = 'submission';

    protected $fillable = [
        'kode',
        'tanggal',
        'kitchen_id',
        'menu_id',
        'porsi'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
