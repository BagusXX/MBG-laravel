<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionDetails extends Model
{
    //

    protected $table ='submission_details';
    protected $fillable = [
    'submission_id',
    'recipe_bahan_baku_id',
    'qty_digunakan',
    'harga_satuan_saat_itu',
    'subtotal_harga',
];


    public function submission(){
        return $this->belongsTo(Submission::class);
    }

    public function recipe(){
        return $this->belongsTo(RecipeBahanBaku::class,'recipe_bahan_baku_id');
    }
}
