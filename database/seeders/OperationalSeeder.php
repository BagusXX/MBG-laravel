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
                'nama' => 'Beli Gas 50 kg',
                'kitchen_kode' => 'DPR11',
                'harga_default' => 950000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP002',
                'nama' => 'Beli Sabun 1 liter',
                'kitchen_kode' => 'DPR12',
                'harga_default' => 9500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP003',
                'nama' => 'Beli Tisu 1 pack',
                'harga_default' => 10000,
                'kitchen_kode' => 'DPR13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP004',
                'nama' => 'Beli Kertas 1 rim',
                'harga_default' => 50000,
                'kitchen_kode' => 'DPR11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
