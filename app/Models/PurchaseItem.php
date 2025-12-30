<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'bahan_baku_id',
        'jumlah',
        'harga',
    ];

    public function PurchaseBahanBaku()
    {
        return $this->belongsTo(PurchaseBahanBaku::class);
    }
    public function BahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
