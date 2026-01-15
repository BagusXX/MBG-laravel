<?php

namespace Database\Seeders;

use App\Models\Kitchen;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kitchenCodes = Kitchen::pluck('kode')->toArray();

        Supplier::factory(10)->create()->each(function ($supplier) use ($kitchenCodes) {

            $randomKitchenCodes = collect($kitchenCodes)
                ->shuffle()
                ->take(rand(3, count($kitchenCodes)))
                ->toArray();

            $supplier->kitchens()->attach($randomKitchenCodes);
        });

    }
}
