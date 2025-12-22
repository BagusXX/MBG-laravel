<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class region extends Model
{
    //
    protected $table = 'regions';

    protected $fillable = [
        'nama_region',
        'kode_region'
    ];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
}
