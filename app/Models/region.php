<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{
    //
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
