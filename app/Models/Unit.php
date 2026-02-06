<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'units';
    protected $fillable = ['satuan','base_unit','multiplier', 'keterangan'];

    public function recipe_bahan_baku()
    {
        return $this->hasMany(RecipeBahanBaku::class);
    }

    public function bahanBaku()
    {
        return $this->hasMany(BahanBaku::class);
    }

    public function submission_details(){
        return $this->hasMany(SubmissionDetails::class);
    }
}
