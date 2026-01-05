<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OperationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('operationals')->insert([
            [
                'kode' => 'BOP001',
                'nama' => 'Listrik',
                'kitchen_kode' => 'DPR11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP002',
                'nama' => 'Air',
                'kitchen_kode' => 'DPR12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP003',
                'nama' => 'Gas',
                'kitchen_kode' => 'DPR13',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'BOP004',
                'nama' => 'Internet',
                'kitchen_kode' => 'DPR11',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
