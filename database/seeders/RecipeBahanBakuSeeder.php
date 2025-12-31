<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class RecipeBahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kitchen_kode' => 'DPR11',
                'menu_kode' => 'MNDPR0100001',
                'bahan_kode' => 'BHNDPR11001',
                'jumlah' => 0.2,
            ],
            [
                'kitchen_kode' => 'DPR11',
                'menu_kode' => 'MNDPR0100001',
                'bahan_kode' => 'BHNDPR11002',
                'jumlah' => 1,
            ],
            [
                'kitchen_kode' => 'DPR11',
                'menu_kode' => 'MNDPR0100001',
                'bahan_kode' => 'BHNDPR11003',
                'jumlah' => 0.05,
            ],
        ];

        foreach ($data as $item) {
            $kitchen = Kitchen::where('kode', $item['kitchen_kode'])->first();
            $menu    = Menu::where('kode', $item['menu_kode'])->first();
            $bahan   = BahanBaku::where('kode', $item['bahan_kode'])->first();

            if ($kitchen && $menu && $bahan) {
                DB::table('recipe_bahan_baku')->updateOrInsert(
                    [
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahan->id,
                    ],
                    [
                        'kitchen_id' => $kitchen->id,
                        'jumlah' => $item['jumlah'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
