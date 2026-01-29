<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionOperational extends Model
{
    protected $table = 'submission_operationals';

    protected $fillable = [
        'kode',
        'parent_id',
        'kitchen_kode',
        'supplier_id',
        'tipe',         // 'pengajuan', 'disetujui'
        'status',       // 'diajukan', 'diproses', 'disetujui', 'ditolak', 'selesai'
        'total_harga',
        'keterangan',
        'tanggal',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Parent Submission (Self Reference)
    public function parentSubmission()
    {
        return $this->belongsTo(submissionOperational::class, 'parent_id');
    }

    // Children (Pecahan PO)
    public function children()
    {
        return $this->hasMany(submissionOperational::class, 'parent_id');
    }

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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOnlyParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopePengajuan($query)
    {
        return $query->where('tipe', 'pengajuan');
    }

    public function scopeApproval($query)
    {
        return $query->where('tipe', 'disetujui');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER & BOOT
    |--------------------------------------------------------------------------
    */

    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    public function isChild(): bool
    {
        return ! is_null($this->parent_id);
    }

    protected static function booted()
    {
        static::saving(function ($submission) {
            // Rule 1: Child wajib punya supplier
            if ($submission->isChild() && empty($submission->supplier_id)) {
                throw new \Exception('Submission Approval (PO) wajib memiliki supplier.');
            }

            // Rule 2: Parent (Pengajuan murni) sebaiknya tidak punya supplier
            // Kita paksa null jika parent, untuk menjaga konsistensi data
            if ($submission->isParent()) {
                $submission->supplier_id = null; 
            }
        });

        static::deleting(function ($submission) {
            // Cegah hapus Parent jika sudah di-split (punya anak)
            if ($submission->isParent() && $submission->children()->exists()) {
                throw new \Exception('Tidak dapat menghapus pengajuan yang sudah diproses (memiliki PO).');
            }
        });
    }
}