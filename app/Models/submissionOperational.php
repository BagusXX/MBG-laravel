<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submissionOperational extends Model
{
    //
    protected $table = 'submission_operationals';

    protected $fillable = 
    [
        'kode',
        'tanggal',
        'kitchen_kode',
        'total_harga',
        'status',
        'keterangan'

    ];

    public function kitchen(){
        return $this->belongsTo(Kitchen::class,'kitchen_kode', 'kode');
    }

    public function details(){
        return $this->hasMany(submissionOperationalDetails::class,'operational_submission_id');
    }

    

}
