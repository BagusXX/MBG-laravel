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
        'kitchen_id',
        'operasional_id',
        'total_harga',
        'status',

    ];

    public function kitchen(){
        return $this->belongsTo(Kitchen::class);
    }

    public function operational(){
        return $this->belongsTo(operationals::class); 
    }

    public function details(){
        return $this->hasMany(submissionOperationalDetails::class);
    }

    

}
