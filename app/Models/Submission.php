<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'submissions';

    protected $fillable = [
        'kode',
        'tanggal',
        'kitchen_id',
        'menu_id',
        'porsi',
        'total_harga',
        'status',
        'parent_id',
        'supplier_id',
        'tipe'
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class, 'kitchen_id', 'id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parentSubmission()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function details()
    {
        return $this->hasMany(SubmissionDetails::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Parent saja (pengajuan awal)
    public function scopeOnlyParent($query)
    {
        return $query->whereNull('parent_id');
    }

    // Child saja (approval)
    public function scopeOnlyChild($query)
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
        return !is_null($this->parent_id);
    }

    public function shouldBeLocked(): bool
    {
        // Contoh logic: Parent dianggap selesai jika semua child (approval) sudah selesai/ditolak
        // Cek apakah masih ada child yang statusnya BUKAN 'selesai' atau 'ditolak'
        $pendingChildren = $this->children()
            ->whereNotIn('status', ['selesai', 'ditolak'])
            ->exists();

        return !$pendingChildren;
    }

    // Parent tidak boleh dihapus
    protected static function booted()
    {
        static::updated(function ($submission) {

            if ($submission->isChild() && $submission->status === 'selesai') {
                $parent = $submission->parentSubmission;

                if ($parent && $parent->shouldBeLocked()) {
                    $parent->update(['status' => 'selesai']);
                }
            }
        });

        static::deleting(function ($submission) {
            if ($submission->isParent() && $submission->children()->exists()) {
                throw new \Exception('Parent tidak boleh dihapus');
            }
        });
    }


}
