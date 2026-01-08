<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kitchen;

class KitchenSeeder extends Seeder
{
    public function run(): void
    {
        $kitchens = [
            // --- REGION SOLO RAYA (ID 1) ---
            [
                'kode' => 'DPR01',
                'nama' => 'Dapur Jebres Kota Surakarta',
                'alamat' => 'Jebres, Surakarta',
                'kepala_dapur' => 'Admin Jebres',
                'nomor_kepala_dapur' => '08110000001',
                'region_id' => 1,
            ],
            [
                'kode' => 'DPR02',
                'nama' => 'Dapur Baki Kota Sukoharjo',
                'alamat' => 'Baki, Sukoharjo',
                'kepala_dapur' => 'Admin Baki',
                'nomor_kepala_dapur' => '08110000002',
                'region_id' => 1,
            ],
            [
                'kode' => 'DPR03',
                'nama' => 'Dapur Gemolong Sragen',
                'alamat' => 'Gemolong, Sragen',
                'kepala_dapur' => 'Admin Gemolong',
                'nomor_kepala_dapur' => '08110000003',
                'region_id' => 1,
            ],
            [
                'kode' => 'DPR04',
                'nama' => 'Dapur Jatinom Klaten',
                'alamat' => 'Jatinom, Klaten',
                'kepala_dapur' => 'Admin Jatinom',
                'nomor_kepala_dapur' => '08110000004',
                'region_id' => 1,
            ],
            [
                'kode' => 'DPR05',
                'nama' => 'Dapur Ngemplak Boyolali',
                'alamat' => 'Ngemplak, Boyolali',
                'kepala_dapur' => 'Admin Ngemplak',
                'nomor_kepala_dapur' => '08110000005',
                'region_id' => 1,
            ],

            // --- REGION PANTURA BARAT (ID 2) ---
            [
                'kode' => 'DPR06',
                'nama' => 'Dapur Kajen Kab. Pekalongan',
                'alamat' => 'Kajen, Pekalongan',
                'kepala_dapur' => 'Admin Kajen',
                'nomor_kepala_dapur' => '08110000006',
                'region_id' => 2,
            ],
            [
                'kode' => 'DPR07',
                'nama' => 'Dapur Cepiring Kendal',
                'alamat' => 'Cepiring, Kendal',
                'kepala_dapur' => 'Admin Cepiring',
                'nomor_kepala_dapur' => '08110000007',
                'region_id' => 2,
            ],

            // --- REGION PANTURA TIMUR 1 (ID 3) ---
            [
                'kode' => 'DPR08',
                'nama' => 'Dapur Kebonagung Demak',
                'alamat' => 'Kebonagung, Demak',
                'kepala_dapur' => 'Admin Kebonagung',
                'nomor_kepala_dapur' => '08110000008',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR09',
                'nama' => 'Dapur Kaliwungu Kudus',
                'alamat' => 'Kaliwungu, Kudus',
                'kepala_dapur' => 'Admin Kaliwungu',
                'nomor_kepala_dapur' => '08110000009',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR10',
                'nama' => 'Dapur Welahan Jepara',
                'alamat' => 'Welahan, Jepara',
                'kepala_dapur' => 'Admin Welahan',
                'nomor_kepala_dapur' => '08110000010',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR11',
                'nama' => 'Dapur Mayong Jepara',
                'alamat' => 'Mayong, Jepara',
                'kepala_dapur' => 'Admin Mayong',
                'nomor_kepala_dapur' => '08110000011',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR12',
                'nama' => 'Dapur Nalungsari Jepara',
                'alamat' => 'Nalungsari, Jepara',
                'kepala_dapur' => 'Admin Nalungsari',
                'nomor_kepala_dapur' => '08110000012',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR13',
                'nama' => 'Dapur Keling Jepara',
                'alamat' => 'Keling, Jepara',
                'kepala_dapur' => 'Admin Keling',
                'nomor_kepala_dapur' => '08110000013',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR14',
                'nama' => 'Dapur Donorejo Jepara',
                'alamat' => 'Donorejo, Jepara',
                'kepala_dapur' => 'Admin Donorejo',
                'nomor_kepala_dapur' => '08110000014',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR15',
                'nama' => 'Dapur Kembang Jepara',
                'alamat' => 'Kembang, Jepara',
                'kepala_dapur' => 'Admin Kembang',
                'nomor_kepala_dapur' => '08110000015',
                'region_id' => 3,
            ],
            [
                'kode' => 'DPR16',
                'nama' => 'Dapur Kedungdung Jepara',
                'alamat' => 'Kedungdung, Jepara',
                'kepala_dapur' => 'Admin Kedungdung',
                'nomor_kepala_dapur' => '08110000016',
                'region_id' => 3,
            ],

            // --- REGION PANTURA TIMUR 2 (ID 4) ---
            [
                'kode' => 'DPR17',
                'nama' => 'Dapur Sluke Rembang',
                'alamat' => 'Sluke, Rembang',
                'kepala_dapur' => 'Admin Sluke',
                'nomor_kepala_dapur' => '08110000017',
                'region_id' => 4,
            ],
            [
                'kode' => 'DPR18',
                'nama' => 'Dapur Sedan Rembang',
                'alamat' => 'Sedan, Rembang',
                'kepala_dapur' => 'Admin Sedan',
                'nomor_kepala_dapur' => '08110000018',
                'region_id' => 4,
            ],
            [
                'kode' => 'DPR19',
                'nama' => 'Dapur Kaliori Rembang',
                'alamat' => 'Kaliori, Rembang',
                'kepala_dapur' => 'Admin Kaliori',
                'nomor_kepala_dapur' => '08110000019',
                'region_id' => 4,
            ],
            [
                'kode' => 'DPR20',
                'nama' => 'Dapur Blora Blora',
                'alamat' => 'Blora, Blora',
                'kepala_dapur' => 'Admin Blora',
                'nomor_kepala_dapur' => '08110000020',
                'region_id' => 4,
            ],
        ];

        foreach ($kitchens as $kitchen) {
            Kitchen::updateOrCreate(
                ['kode' => $kitchen['kode']],
                $kitchen
            );
        }
    }
}