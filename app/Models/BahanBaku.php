<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku'; // pastikan sesuai nama tabel di database
    protected $fillable = ['kode', 'nama', 'stok', 'satuan', 'kitchen_id'];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }
}
