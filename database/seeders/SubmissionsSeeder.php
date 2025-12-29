<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                "kode" => "PEM001",
                "tanggal" => "2025-12-24",
                "kitchen_id" => 1,
                "menu_id" => 1,
                "porsi" => 10,
                "total_harga" => 150000,
                "status" => 'diterima',
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "kode" => "PEM002",
                "tanggal" => "2025-12-25",
                "kitchen_id" => 1,
                "menu_id" => 2,
                "porsi" => 5,
                "total_harga" => 75000,
                "status" => 'diproses',
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "kode" => "PEM003",
                "tanggal" => "2025-12-26",
                "kitchen_id" => 2,
                "menu_id" => 3,
                "porsi" => 20,
                "total_harga" => 300000,
                "status" => 'diajukan',
                "created_at" => now(),
                "updated_at" => now()
            ]
        ];

        DB::table('submissions')->insert($data);
    }
}
