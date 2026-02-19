<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';
    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'kontak',
        'nomor',
        'gambar',
        'ttd'
    ];

    public function kitchens(){
        return $this->belongsToMany(Kitchen::class,'kitchen_suppliers','suppliers_id','kitchen_kode','id','kode');
    }

    public function bank_account(){
        return $this->hasMany(BankAccount::class,'suppliers_id');
    }
}
