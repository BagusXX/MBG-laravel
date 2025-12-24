<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('operationals')->insert([
            [
                'kode' => 'BOP001',
                'nama' => 'Listrik',
                'harga' => 1.000000,
                'tempat_beli' => 'Toko A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP002',
                'nama' => 'Air',
                'harga' => 900.000,
                'tempat_beli' => 'Toko B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP003',
                'nama' => 'Gas',
                'harga' => 565.000,
                'tempat_beli' => 'Toko C',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP004',
                'nama' => 'Internet',
                'harga' => 2.000000,
                'tempat_beli' => 'Toko D',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
