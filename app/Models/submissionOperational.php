<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class submissionOperational extends Model
{
    //
    protected $table = 'submission_operationals';

    protected $fillable =
    [
        'kode',
        'parent_id',
        'kitchen_kode',
        'supplier_id',
        'tipe',
        'status',
        'total_harga',
        'keterangan',
        'tanggal',

    ];


    public function parentSubmission()
    {
        return $this->belongsTo(submissionOperational::class, 'parent_id');
    }

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
    | SCOPES (BIAR QUERY BERSIH)
    |--------------------------------------------------------------------------
    */

    // Parent saja (pengajuan awal)
    public function scopeOnlyParent($query)
    {
        return $query->whereNull('parent_id');
    }

    // Child saja (approval)
    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Pengajuan
    public function scopePengajuan($query)
    {
        return $query->where('tipe', 'pengajuan');
    }

    // Approval
    public function scopeApproval($query)
    {
        return $query->where('tipe', 'disetujui');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHOD (BUSINESS RULE)
    |--------------------------------------------------------------------------
    */

    // Apakah parent?
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    // Apakah child?
    public function isChild(): bool
    {
        return ! is_null($this->parent_id);
    }

    // Parent tidak boleh dihapus
    protected static function booted()
    {
        static::saving(function ($submission) {
        // Child wajib punya supplier
        if ($submission->isChild() && empty($submission->supplier_id)) {
            throw new \Exception('Submission child wajib memiliki supplier');
        }

        // Parent tidak boleh punya supplier
        if ($submission->isParent() && ! empty($submission->supplier_id)) {
            throw new \Exception('Submission parent tidak boleh memiliki supplier');
        }
        });    

        static::deleting(function ($submission) {
            if ($submission->isParent() && $submission->children()->exists()) {
                throw new \Exception('Pengajuan utama tidak boleh dihapus');
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(submissionOperational::class, 'parent_id');
    }
}
