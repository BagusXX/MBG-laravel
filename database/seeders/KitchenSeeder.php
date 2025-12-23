<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen; // Pastikan Model Kitchen sudah ada
use Illuminate\Support\Facades\DB;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        $kitchens = [
            [
                'kode' => 'KITCH-01',
                'nama' => 'Dapur Utama (Central)',
                'alamat' => 'Jl. Sudirman No. 123, Jakarta',
                'kepala_dapur' => 'Budi Santoso',
                'nomor_kepala_dapur' => '081234567890',
                'region_id' =>1,
            ],
            [
                'kode' => 'KITCH-02',
                'nama' => 'Dapur Cabang Bandung',
                'alamat' => 'Jl. Merdeka No. 45, Bandung',
                'kepala_dapur' => 'Siti Aminah',
                'nomor_kepala_dapur' => '081987654321',
                'region_id' => 2,
            ],
            [
                'kode' => 'KITCH-03',
                'nama' => 'Dapur Cabang Surabaya',
                'alamat' => 'Jl. Basuki Rahmat No. 88, Surabaya',
                'kepala_dapur' => 'Agus Wijaya',
                'nomor_kepala_dapur' => '085612345678',
                'region_id' =>3,
            ],
        ];

        foreach ($kitchens as $kitchen) {
            // updateOrCreate digunakan agar tidak terjadi error duplicate entry jika seeder dijalankan ulang
            Kitchen::updateOrCreate(
                ['kode' => $kitchen['kode']], 
                $kitchen
            );
        }
    }
}