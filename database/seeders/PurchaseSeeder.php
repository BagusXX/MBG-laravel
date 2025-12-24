<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\PurchaseBahanBaku;
use Carbon\Carbon;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purchases =
            [
                [
                    'kode' => 'PUR-001',
                    'user_id' => 1,
                    'harga' => 15000,
                    'jumlah' => 10,
                    'supplier_id' => 1,
                    'bahan_baku_id' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'kode' => 'PUR-002',
                    'user_id' => 1,
                    'harga' => 20000,
                    'jumlah' => 5,
                    'supplier_id' => 2,
                    'bahan_baku_id' => 2,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'kode' => 'PUR-003',
                    'user_id' => 1,
                    'harga' => 18000,
                    'jumlah' => 8,
                    'supplier_id' => 1,
                    'bahan_baku_id' => 3,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            ];
        PurchaseBahanBaku::insert($purchases);
    }
}
