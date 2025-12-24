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
        $data =[
            [
                'recipe_id' => 1,
                'bahan_baku_id' => 1, // Beras
                'jumlah' => 0.2, // 200 gram
                'satuan' => 'kg',
                'porsi' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'recipe_id' => 1,
                'bahan_baku_id' => 2, // Telur
                'jumlah' => 1, 
                'satuan' => 'kg', // Atau pcs sesuai master data
                'porsi' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'recipe_id' => 1,
                'bahan_baku_id' => 3, // Minyak
                'jumlah' => 0.05, 
                'satuan' => 'l',
                'porsi' => 1,
                'created_at' => now(), 'updated_at' => now()
            ],

             // --- Bahan untuk Resep 2 ---
            [
                'recipe_id' => 2,
                'bahan_baku_id' => 4, 
                'jumlah' => 0.5, 
                'satuan' => 'kg',
                'porsi' => 5, // Untuk 5 porsi
                'created_at' => now(), 'updated_at' => now()
            ],

        ];

        DB::table('recipe_bahan_baku')->insert($data);
    }
}
