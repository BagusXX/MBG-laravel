<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data= [
            [
                'satuan' => 'gram',
                'base_unit' => 'gram',
                'multiplier' => 1,
                'keterangan' => 'Berat (base)',
            ],
            [
                'satuan' => 'kilogram',
                'base_unit' => 'gram',
                'multiplier' => 1000,
                'keterangan' => 'Berat',
            ],

            // VOLUME
            [
                'satuan' => 'ml',
                'base_unit' => 'ml',
                'multiplier' => 1,
                'keterangan' => 'Volume (base)',
            ],
            [
                'satuan' => 'liter',
                'base_unit' => 'ml',
                'multiplier' => 1000,
                'keterangan' => 'Volume',
            ],

            // JUMLAH
            [
                'satuan' => 'pcs',
                'base_unit' => 'pcs',
                'multiplier' => 1,
                'keterangan' => 'Jumlah (base)',
            ],
            [
                'satuan' => 'pack',
                'base_unit' => 'pcs',
                'multiplier' => 6,
                'keterangan' => 'Jumlah',
            ],
            [
                'satuan' => 'dus',
                'base_unit' => 'pcs',
                'multiplier' => 12,
                'keterangan' => 'Jumlah',
            ],

        ];

        DB::table('units')->insert($data);
    }
}
