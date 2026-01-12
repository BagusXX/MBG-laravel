<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KitchenSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kitchens = Kitchen::pluck('kode')->toArray();

        Supplier::all()->each(function ($supplier) use ($kitchens) {
            // ambil kitchen secara acak (1 s/d semua)
            $randomKitchens = collect($kitchens)
                ->shuffle()
                ->take(rand(1, count($kitchens)))
                ->toArray();

            $supplier->kitchens()->sync($randomKitchens);
        }); 
    }
}
