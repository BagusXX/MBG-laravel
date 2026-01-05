<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'submissions';

    protected $fillable = [
        'kode',
        'tanggal',
        'kitchen_kode',
        'kitchen_id',
        'menu_id',
        'total_harga',
        'porsi',
        'status'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function details()
    {
        return $this->hasMany(SubmissionDetails::class);
    }
}
