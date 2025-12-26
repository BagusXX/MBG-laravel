<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data =[
            [
                'kode' => 'BHNDPR11001',
                'nama' => 'Tepung Terigu',
                'harga' => 15000,
                'satuan_id' =>  1,
                'kitchen_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11002',
                'nama' => 'Telur Ayam',
                'harga' => 28000,
                'satuan_id' => 1,
                'kitchen_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11003',
                'nama' => 'Minyak Goreng',
                'harga' => 18000,
                'satuan_id' => 1,
                'kitchen_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11004',
                'nama' => 'Tepung Terigu',
                'harga' => 12000,
                'satuan_id' => 1,
                'kitchen_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11005',
                'nama' => 'Bawang Merah',
                'harga' => 35000,
                'satuan_id' => 1,
                'kitchen_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11006',
                'nama' => 'Bawang Putih',
                'harga' => 35000,
                'satuan_id' => 1,
                'kitchen_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BHNDPR11007',
                'nama' => 'Bawang Bombay',
                'harga' => 35000,
                'satuan_id' => 1,
                'kitchen_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('bahan_baku')->insert($data);
    }
}
