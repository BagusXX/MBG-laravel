<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\BahanBaku;

class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'BHNDPR11001', 'nama' => 'Tepung Terigu', 'harga' => 15000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR11'],
            ['kode' => 'BHNDPR11002', 'nama' => 'Telur Ayam', 'harga' => 28000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR12'],
            ['kode' => 'BHNDPR11003', 'nama' => 'Minyak Goreng', 'harga' => 18000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR12'],
            ['kode' => 'BHNDPR11004', 'nama' => 'Tepung Terigu', 'harga' => 12000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR13'],
            ['kode' => 'BHNDPR11005', 'nama' => 'Bawang Merah', 'harga' => 35000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR13'],
            ['kode' => 'BHNDPR11006', 'nama' => 'Bawang Putih', 'harga' => 35000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR13'],
            ['kode' => 'BHNDPR11007', 'nama' => 'Bawang Bombay', 'harga' => 35000, 'satuan_id' => 1, 'kitchen_kode' => 'DPR13'],
        ];

        foreach ($data as $item) {
            $kitchen = Kitchen::where('kode', $item['kitchen_kode'])->first();

            if ($kitchen) {
                BahanBaku::updateOrCreate(
                    ['kode' => $item['kode']],
                    [
                        'nama' => $item['nama'],
                        'harga' => $item['harga'],
                        'satuan_id' => $item['satuan_id'],
                        'kitchen_id' => $kitchen->id,
                    ]
                );
            }
        }
    }
}
