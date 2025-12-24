<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                'kode' => 'MNDPR0100001',
                'nama' => 'Nasi Goreng Spesial',
                'kitchen_id' => 1,
            ],
            [
                'kode' => 'MNDPR0100002',
                'nama' => 'Mie Ayam Jamur',
                'kitchen_id' => 1,
            ],
            [
                'kode' => 'MNDPR0200001',
                'nama' => 'Sate Ayam Madura',
                'kitchen_id' => 2,
            ],
            [
                'kode' => 'MNDPR0200002',
                'nama' => 'Gado-Gado Surabaya',
                'kitchen_id' => 2,
            ],
            [
                'kode' => 'MNDPR0300001',
                'nama' => 'Rendang Daging Sapi',
                'kitchen_id' => 3,
            ],
            [
                'kode' => 'MNDPR0300002',
                'nama' => 'Ayam Bakar Taliwang',
                'kitchen_id' => 3,
            ],
        ];

        DB::table('menus')->insert($data);
    }
}
