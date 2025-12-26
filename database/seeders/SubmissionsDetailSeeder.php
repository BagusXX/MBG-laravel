<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubmissionsDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                "submission_id" => 1,
                "recipe_bahan_baku_id" => 1,
                "qty_digunakan" => 2.0,
                "harga_satuan_saat_itu" => 15000,
                "subtotal_harga" => 30000,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "submission_id" => 1,
                "recipe_bahan_baku_id" => 2,
                "qty_digunakan" => 10,
                "harga_satuan_saat_itu" => 2000,
                "subtotal_harga" => 20000,
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "submission_id" => 2,
                "recipe_bahan_baku_id" => 3,
                "qty_digunakan" => 1000,
                "harga_satuan_saat_itu" => 50,
                "subtotal_harga" => 50000,
                "created_at" => now(),
                "updated_at" => now()
            ]
        ];

        DB::table('submission_details')->insert($data);
    }
}
