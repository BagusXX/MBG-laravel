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
                'satuan' => 'kg',
                'keterangan' => 'kilogram'
            ],
            [
                'satuan' => 'gram',
                'keterangan' => 'gram'
            ],
            [
                'satuan' => 'liter',
                'keterangan' => 'liter'
            ],
            [
                'satuan' => 'ml',
                'keterangan' => 'mili liter'
            ],

        ];

        DB::table('units')->insert($data);
    }
}
