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
        'recipe_bahan_baku_id',
        'bahan_baku_id',
        'qty_digunakan',
        'harga_satuan',
        'harga_dapur',
        'harga_mitra',
        'subtotal_harga',
    ];

    /* ================= RELATION ================= */

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function recipeBahanBaku()
    {
        return $this->belongsTo(RecipeBahanBaku::class, 'recipe_bahan_baku_id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
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

            // Qty tidak boleh diubah (selalu dari recipe x porsi)
            // if ($detail->isDirty('qty_digunakan') && $detail->exists) {
            //     throw new \LogicException('Qty bahan baku tidak boleh diubah');
            // }

            // Harga satuan wajib ada
            if (is_null($detail->harga_satuan)) {
                throw new \LogicException('Harga satuan wajib diisi');
            }

            // Parent → harga dapur boleh, harga mitra harus null
            // if ($detail->isParent()) {
            //     $detail->harga_mitra = null;
            // }

            // Child → harga mitra wajib
            if ($detail->isChild() && is_null($detail->harga_mitra)) {
                throw new \LogicException('Harga mitra wajib diisi pada submission supplier');
            }

            // Auto hitung subtotal
            $detail->subtotal_harga =
                ($detail->harga_mitra ?? $detail->harga_dapur ?? $detail->harga_satuan)
                * $detail->qty_digunakan;
        });
    }
}
