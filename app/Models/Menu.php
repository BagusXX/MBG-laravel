<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode',
        'nama',
        'kitchen_id',
    ];

    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    public function recipes()
    {
        return $this->hasMany(RecipeBahanBaku::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // app/Models/Menu.php

    public static function generateUniqueKode($kodeDapur, $isDuplicate = false)
    {
        // Jika duplikasi, beri prefix atau panjang digit berbeda
        // Contoh: Menu Asli = MNDPR01111, Menu Duplicate = MNDPR01-DUP001
        $prefix = "MN" . $kodeDapur;
        if ($isDuplicate) {
            $prefix .= "DUP"; // Tambahan penanda duplikasi
        }

        $lastItem = self::withTrashed()
            ->where('kode', 'LIKE', "{$prefix}%")
            ->orderBy('kode', 'desc')
            ->first();

        $number = 1;
        if ($lastItem) {
            // Ambil angka terakhir setelah prefix
            $lastNumber = (int) str_replace($prefix, '', $lastItem->kode);
            $number = $lastNumber + 1;
        }

        while (true) {
            // Gunakan str_pad agar panjang digit konsisten (misal 3 digit)
            $tryCode = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);

            $exists = self::withTrashed()->where('kode', $tryCode)->exists();
            if (!$exists) {
                return $tryCode;
            }
            $number++;
        }
    }

}
