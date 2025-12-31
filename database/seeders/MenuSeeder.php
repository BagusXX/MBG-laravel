<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'MNDPR0100001', 'nama' => 'Nasi Goreng Spesial', 'kitchen_kode' => 'DPR11'],
            ['kode' => 'MNDPR0100002', 'nama' => 'Mie Ayam Jamur', 'kitchen_kode' => 'DPR11'],
            ['kode' => 'MNDPR0200001', 'nama' => 'Sate Ayam Madura', 'kitchen_kode' => 'DPR12'],
            ['kode' => 'MNDPR0200002', 'nama' => 'Gado-Gado Surabaya', 'kitchen_kode' => 'DPR12'],
            ['kode' => 'MNDPR0300001', 'nama' => 'Rendang Daging Sapi', 'kitchen_kode' => 'DPR13'],
            ['kode' => 'MNDPR0300002', 'nama' => 'Ayam Bakar Taliwang', 'kitchen_kode' => 'DPR13'],
        ];

        foreach ($data as $item) {
            $kitchen = Kitchen::where('kode', $item['kitchen_kode'])->first();

            if ($kitchen) {
                Menu::updateOrCreate(
                    ['kode' => $item['kode']],
                    [
                        'nama' => $item['nama'],
                        'kitchen_id' => $kitchen->id,
                    ]
                );
            }
        }
    }
}
