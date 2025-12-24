<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeBahanBakuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            [
                'menu_id' => 1,
                'bahan_baku_id' => 1, // Beras
                'jumlah' => 0.2, // 200 gram
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'menu_id' => 1,
                'bahan_baku_id' => 2, // Telur
                'jumlah' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'menu_id' => 1,
                'bahan_baku_id' => 3, // Minyak
                'jumlah' => 0.05,
                'created_at' => now(),
                'updated_at' => now()
            ],

            // --- Bahan untuk Resep 2 ---
            [
                'menu_id' => 2,
                'bahan_baku_id' => 4,
                'jumlah' => 0.5,
                'created_at' => now(),
                'updated_at' => now()
            ],

        ];

        DB::table('recipe_bahan_baku')->insert($data);
    }
}
