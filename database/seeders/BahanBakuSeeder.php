<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;
use App\Models\BahanBaku;

class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cukup definisikan daftar bahan baku saja di sini
        $daftarBahan = [
            ['nama' => 'Ayam Potong'],
            ['nama' => 'Baking Powder'],
            ['nama' => 'Bawang Bombay'],
            ['nama' => 'Bawang Merah'],
            ['nama' => 'Bawang Putih'],
            ['nama' => 'Beras'],
            ['nama' => 'Cabe Hijau Teropong'],
            ['nama' => 'Cabe Merah Teropong'],
            ['nama' => 'Cabe Merah Keriting'],
            ['nama' => 'Daun Bawang'],
            ['nama' => 'Daun Jeruk'],
            ['nama' => 'Daun Salam'],
            ['nama' => 'Daun Sereh'],
            ['nama' => 'Garam'],
            ['nama' => 'Gula Merah'],
            ['nama' => 'Gula Pasir'],
            ['nama' => 'Jahe'],
            ['nama' => 'Jinten Bubuk'],
            ['nama' => 'Kecap Asin'],
            ['nama' => 'Kecap Manis Lele'],
            ['nama' => 'Kemiri'],
            ['nama' => 'Kencur'],
            ['nama' => 'Kentang'],
            ['nama' => 'Ketumbar Bubuk'],
            ['nama' => 'Ketumbar Bubuk Desaku'],
            ['nama' => 'Kol'],
            ['nama' => 'Kunyit Bubuk Desaku'],
            ['nama' => 'Labu Siam'],
            ['nama' => 'Lengkuas'],
            ['nama' => 'Masako Ayam'],
            ['nama' => 'Mayonaise'],
            ['nama' => 'Merica Bubuk Desaku'],
            ['nama' => 'Minyak Goreng'],
            ['nama' => 'Minyak Wijen'],
            ['nama' => 'Palmia'],
            ['nama' => 'Santan Kara'],
            ['nama' => 'Saus Cabe Delmonte'],
            ['nama' => 'Saus Teriyaki'],
            ['nama' => 'Saus Tiram'],
            ['nama' => 'Saus Tomat Delmonte'],
            ['nama' => 'Sawi Putih'],
            ['nama' => 'Tahu'],
            ['nama' => 'Telur Ayam'],
            ['nama' => 'Tempe'],
            ['nama' => 'Tepung Maizena'],
            ['nama' => 'Tepung Terigu Segitiga'],
            ['nama' => 'Timun'],
            ['nama' => 'Tomat'],
            ['nama' => 'Wortel'],
            ['nama' => 'Wijen Sangrai'],
        ];



        // teh celup sosro (kemasan isi 50), bakso bandeng & kelapa butir (butir) => pastikan satuan nya besok pas ke pati (jangan dihapus)
        ;

        // 2. Ambil semua data dapur yang sudah dibuat oleh KitchenSeeder
        $semuaDapur = Kitchen::all();

        // 3. Loop setiap dapur
        foreach ($semuaDapur as $dapur) {
            foreach ($daftarBahan as $index => $bahan) {
                $nomorUrut = str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                $kodeFinal = 'BHN' . $dapur->kode . $nomorUrut;

                BahanBaku::updateOrCreate(
                    ['kode' => $kodeFinal],
                    [
                        'nama' => $bahan['nama'],
                        'kitchen_id' => $dapur->id,
                    ]
                );
            }
        }
    }
}
