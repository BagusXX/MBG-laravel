<?php

namespace Database\Seeders;

use App\Http\Controllers\OperationalController;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
        use WithoutModelEvents;

        /**
         * Seed the application's database.
         */
        public function run(): void
        {
                $this->call(PermissionSeeder::class);
                $this->call(RoleSeeder::class);
                $this->call(RolePermissionSeeder::class);
                $this->call(RegionSeeder::class);
                $this->call(KitchenSeeder::class);
                $this->call(UserRoleSeeder::class);
                
                $this->call(SuperAdminPermissionSeeder::class);
                $this->call(OperationalSeeder::class);
                $this->call(UnitsSeeder::class);
                $this->call(MenuSeeder::class);
                $this->call(BahanBakuSeeder::class);
                // $this->call(RecipeBahanBakuSeeder::class);
                // $this->call(SubmissionsSeeder::class);
                $this->call(SuppliersSeeder::class);
                $this->call(KitchenSupplierSeeder::class);
                // $this->call(SubmissionsDetailSeeder::class);


                // $this->call(SellsSeeder::class);
                // $this->call(PurchaseSeeder::class);
        }
}
