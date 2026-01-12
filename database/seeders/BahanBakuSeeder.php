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
        ['nama' => 'Ayam Potong (Kg)', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Ayam Potong (Gram)', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Baking Powder', 'harga' => 35000, 'satuan_id' => 2],
        ['nama' => 'Bawang Bombay', 'harga' => 35000, 'satuan_id' => 1],
        ['nama' => 'Bawang Merah', 'harga' => 35000, 'satuan_id' => 1],
        ['nama' => 'Bawang Putih', 'harga' => 40000, 'satuan_id' => 1],
        ['nama' => 'Beras', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Cabe Hijau Teropong', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Cabe Merah Teropong', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Cabe Merah Keriting', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Daun Bawang', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Daun Jeruk', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Daun Salam', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Daun Sereh', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Garam', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Gula Merah', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Gula Pasir', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Jahe', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Jinten Bubuk', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Kecap Asin', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Kecap Manis Lele', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Kemiri', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Kencur', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Kentang', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Ketumbar Bubuk', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Ketumbar Bubuk Desaku', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Kol', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Kunyit Bubuk Desaku', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Labu Siam', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Lengkuas', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Masako Ayam', 'harga' => 15000, 'satuan_id' => 2],
        ['nama' => 'Mayonaise', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Merica Bubuk Desaku', 'harga' => 15000, 'satuan_id' => 1],
        ['nama' => 'Minyak Goreng', 'harga' => 15000, 'satuan_id' => 3],
        ['nama' => 'Minyak Wijen', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Palmia', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Santan Kara', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Saus Cabe Delmonte', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Saus Teriyaki', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Saus Tiram', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Saus Tomat Delmonte', 'harga' => 15000, 'satuan_id' => 4],
        ['nama' => 'Sawi Putih', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Tahu', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Telur Ayam', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Tempe', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Tepung Maizena', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Tepung Terigu Segitiga', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Timun', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Tomat', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Wortel', 'harga' => 28000, 'satuan_id' => 1],
        ['nama' => 'Wijen Sangrai', 'harga' => 28000, 'satuan_id' => 1],

        // teh celup sosro (kemasan isi 50), bakso bandeng & kelapa butir (butir) => pastikan satuan nya besok pas ke pati (jangan dihapus)
    ];

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
                    'harga' => $bahan['harga'],
                    'satuan_id' => $bahan['satuan_id'],
                    'kitchen_id' => $dapur->id,
                ]
            );
        }
    }
}
}
