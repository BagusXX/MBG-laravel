<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $daftarMenu = [
            [ 'nama' => 'Nasi Goreng Spesial'],
            [ 'nama' => 'Mie Ayam Jamur'],
            [ 'nama' => 'Sate Ayam Madura'],
            [ 'nama' => 'Gado-Gado Surabaya'],
            [ 'nama' => 'Rendang Daging Sapi'],
            [ 'nama' => 'Ayam Bakar Taliwang'],
        ];

        $semuaDapur = Kitchen::all();

        foreach ($semuaDapur as $dapur) {
            foreach ($daftarMenu as $index => $menu) {
            
                // 3. Generate Nomor Urut (5 digit: 00001, 00002, dst)
                $nomorUrut = str_pad($index + 1, 5, '0', STR_PAD_LEFT);
                
                /** * 4. Generate Kode Menu
                 * Format: MN + KODE_DAPUR + NOMOR_URUT
                 * Hasil: MNDPR1100001
                 */
                $kodeMenu = 'MN' . $dapur->kode . $nomorUrut;

                Menu::updateOrCreate(
                    ['kode' => $kodeMenu],
                    [
                        'nama' => $menu['nama'],
                        'kitchen_id' => $dapur->id,
                    ]
                );
            }
        }   
    }    
}