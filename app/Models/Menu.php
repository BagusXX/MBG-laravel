<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'kitchen_id',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function recipes(){
        return $this->hasMany(RecipeBahanBaku::class);
    }

    public function submissions(){
        return $this->hasMany(Submission::class);
    }
}
