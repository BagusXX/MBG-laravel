<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionDetails extends Model
{
    //

    protected $table ='submission_details';
    protected $guarded = 'id' ;

    public function submission(){
        return $this->belongsTo(Submission::class);
    }

    public function recipe_bahan_baku(){
        return $this->belongsTo(RecipeBahanBaku::class);
    }
}
