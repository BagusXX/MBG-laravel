<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                'kode' => 'SPR0001',
                'nama' => 'Haryanto',
                'alamat' => 'Temanggung',
                'kontak' => 'Wawan',
                'nomor' => '088216194722',
                 

            ],
            [
                'kode' => 'SPR0002',
                'nama' => 'Haryanti',
                'alamat' => 'Semarang',
                'kontak' => 'Wiwin',
                'nomor' => '088216194723',
                

            ],
            [
                'kode' => 'SPR0003',
                'nama' => 'Reisa',
                'alamat' => 'Ungaran',
                'kontak' => 'Parjo',
                'nomor' => '088216194722',
                

            ],
            [
                'kode' => 'SPR0004',
                'nama' => 'Hasan',
                'alamat' => 'Tunjakan',
                'kontak' => 'Anda',
                'nomor' => '088216194722',
                 

            ],

        ];

        DB::table('suppliers')->insert($data);

    }
}
