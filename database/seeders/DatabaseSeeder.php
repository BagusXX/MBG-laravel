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
        $this->call(RegionSeeder::class);
        $this->call(KitchenSeeder::class);
        $this->call(UserRoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(SuperAdminPermissionSeeder::class);
        $this->call(OperationalSeeder::class);
        // $this->call(PurchaseSeeder::class);
    }
}
