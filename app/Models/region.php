<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class region extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $table = 'regions';
    protected $fillable = [
        'nama_region',
        'penanggung_jawab',
        'kode_region',
    ];

    public function kitchen(){
        return $this->hasMany(Kitchen::class);
    }


}
