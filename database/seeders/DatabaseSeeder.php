<?php

namespace Database\Seeders;

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
        // // Seed default roles
        // User::factory()->create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'superadmin',
        // ]);

        // User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'admin',
        // ]);
        $this->call(RegionSeeder::class);
        $this->call(KitchenSeeder::class);
        $this->call(UserRoleSeeder::class);
    }
}
