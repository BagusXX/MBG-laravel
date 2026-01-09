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
        'supplier_id',
        'total_harga',
        'status',
        'keterangan'

    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_kode', 'kode');
    }

    public function details()
    {
        return $this->hasMany(submissionOperationalDetails::class, 'operational_submission_id');
    }
}
