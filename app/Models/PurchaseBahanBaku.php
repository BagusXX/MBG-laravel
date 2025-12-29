<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseBahanBaku extends Model
{
    //
    use HasFactory, SoftDeletes;

    // protected $table = 'purchase';

    protected $fillable = [
        'kode',
        'supplier_id',
        'user_id',
    ];

    public static function generateKode()
    {
        $latest = self::latest()->first();
        $number = $latest ? (int) substr($latest->kode, 5) + 1 : 1;
        return 'PRCBN' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
