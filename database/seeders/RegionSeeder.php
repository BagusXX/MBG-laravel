<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('regions')->insert([
            [
                'kode_region' => 'RGN01',
                'nama_region' => 'Region Utara',
                'penanggung_jawab' => 'Andi Setiawan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_region' => 'RGN02',
                'nama_region' => 'Region Selatan',
                'penanggung_jawab' => 'Budi Santoso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_region' => 'RGN03',
                'nama_region' => 'Region Timur',
                'penanggung_jawab' => 'Citra Lestari',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_region' => 'RGN04',
                'nama_region' => 'Region Barat',
                'penanggung_jawab' => 'Dewi Kartika',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
