<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\Menu;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\DB;

class RecipeBahanBakuSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $data = [
    //         [
    //             'kitchen_kode' => 'DPR11',
    //             'menu_kode' => 'MNDPR0100001',
    //             'bahan_kode' => 'BHNDPR11001',
    //             'jumlah' => 0.2,
    //         ],
    //         [
    //             'kitchen_kode' => 'DPR11',
    //             'menu_kode' => 'MNDPR0100001',
    //             'bahan_kode' => 'BHNDPR11002',
    //             'jumlah' => 1,
    //         ],
    //         [
    //             'kitchen_kode' => 'DPR11',
    //             'menu_kode' => 'MNDPR0100001',
    //             'bahan_kode' => 'BHNDPR11003',
    //             'jumlah' => 0.05,
    //         ],
    //     ];

    //     foreach ($data as $item) {
    //         $kitchen = Kitchen::where('kode', $item['kitchen_kode'])->first();
    //         $menu    = Menu::where('kode', $item['menu_kode'])->first();
    //         $bahan   = BahanBaku::where('kode', $item['bahan_kode'])->first();

    //         if ($kitchen && $menu && $bahan) {
    //             DB::table('recipe_bahan_baku')->updateOrInsert(
    //                 [
    //                     'menu_id' => $menu->id,
    //                     'bahan_baku_id' => $bahan->id,
    //                 ],
    //                 [
    //                     'kitchen_id' => $kitchen->id,
    //                     'jumlah' => $item['jumlah'],
    //                     'updated_at' => now(),
    //                     'created_at' => now(),
    //                 ]
    //             );
    //         }
    //     }
    // }

    public function run() : void
    {
        $semuaDapur = Kitchen::all();

        foreach ($semuaDapur as $dapur) {
            
            // 2. Ambil semua menu milik dapur ini
            $menusDiDapurIni = Menu::where('kitchen_id', $dapur->id)->get();

            foreach ($menusDiDapurIni as $menu) {
                
                // 3. Ambil 7 bahan baku secara ACAK yang ada di dapur ini
                // inRandomOrder() memastikan bahan yang didapat bervariasi untuk setiap menu
                $bahansAcak = BahanBaku::where('kitchen_id', $dapur->id)
                    ->inRandomOrder()
                    ->take(7)
                    ->get();

                foreach ($bahansAcak as $bahan) {
                    
                    // 4. Masukkan ke tabel pivot sesuai kolom yang Anda minta
                    DB::table('recipe_bahan_baku')->updateOrInsert(
                        [
                            'kitchen_id'    => $dapur->id,
                            'menu_id'       => $menu->id,
                            'bahan_baku_id' => $bahan->id,
                        ],
                        [
                            // Generate jumlah acak antara 0.1 sampai 2.0
                            'jumlah'        => rand(1, 20) / 10, 
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]
                    );
                }
            }
        }
    }
}
