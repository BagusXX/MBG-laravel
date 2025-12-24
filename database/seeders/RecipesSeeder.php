<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data =[
            [
                'kitchen_id' => 1,
                'menu_id' => 1,
            ],
            [
                'kitchen_id' => 1,
                'menu_id' => 2,
            ],
            [
                'kitchen_id' => 2,
                'menu_id' => 3,
            ],
            [
                'kitchen_id' => 2,
                'menu_id' => 4,
            ],
            [
                'kitchen_id' => 3,
                'menu_id' => 5,
            ],
            [
                'kitchen_id' => 2,
                'menu_id' => 5,
            ],
        ];

        DB::table('recipes')->insert($data);
    }
}
