<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionDetails extends Model
{
    //

    use HasFactory;
    protected $table = 'submission_details';

    protected $fillable = [
        'submission_id',
        'bahan_baku_id',
        'qty_digunakan',
        'harga_dapur',
        'harga_mitra',
        'subtotal_dapur',
        'subtotal_mitra',
        // 'subtotal_harga',
        'satuan_id'
    ];

    /* ================= RELATION ================= */

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }


    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'satuan_id');
    }

    /* ================= HELPER ================= */

    public function isParent(): bool
    {
        return $this->submission?->isParent() ?? false;
    }

    public function isChild(): bool
    {
        return $this->submission?->isChild() ?? false;
    }

    /* ================= BUSINESS LOGIC ================= */

    protected static function booted()
    {
        static::saving(function ($detail) {
            // Hanya validasi jika tipe submission adalah child (disetujui/approval)
            if ($detail->isChild()) {
                // Gunakan 0 sebagai default jika null agar tidak kena LogicException
                if (is_null($detail->harga_dapur))
                    $detail->harga_dapur = 0;
                if (is_null($detail->harga_mitra))
                    $detail->harga_mitra = 0;
            }
        });
    }
}
