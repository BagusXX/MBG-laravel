<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submissionOperationalDetails extends Model
{
    //
    protected $table = 'submission_operational_details';

    protected $fillable = [
        'operational_submission_id',
        'operational_id',
        'qty',
        'harga_satuan',
        'subtotal',
        'keterangan'
    ];

    public function submission(){
        return $this->belongsTo(submissionOperational::class, 'operational_submission_id');
    }

    public function operational(){
        return $this->belongsTo(operationals::class,'operational_id'); 
    }
}
