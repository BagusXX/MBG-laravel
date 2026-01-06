<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class operationals extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected $table = 'operationals';

    protected $fillable = [
        'kode',
        'nama',
        'harga_default',
        'kitchen_kode'
    ];

    public function kitchen(){
        return $this->belongsTo(Kitchen::class, 'kitchen_kode', 'kode');
    }

    public function submissionDetails(){

        return $this->hasMany(submissionOperationalDetails::class, 'operational_id');

    }
}
