<?php

// database/seeders/UserRoleSeeder.php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Kitchen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Roles
        $roles = ['superAdmin', 'operatorkoperasi', 'operatorDapur', 'mitra', 'dirut'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 2. Definisi Data User
        $userData = [
            [
                'name' => 'SuperAdmin',
                'email' => 'superadmin@example.com',
                'role' => 'superAdmin',
                'access_kitchens' => ['KITCH-01', 'KITCH-02'], // Akses semua
            ],
            [
                'name' => 'Operator Dapur Pusat',
                'email' => 'dapur.pusat@example.com',
                'role' => 'operatorDapur',
                'access_kitchens' => ['KITCH-01'], // Hanya Jakarta
            ],
            [
                'name' => 'Operator Dapur Bandung',
                'email' => 'dapur.bandung@example.com',
                'role' => 'operatorDapur',
                'access_kitchens' => ['KITCH-02'], // Hanya Bandung
            ],
        ];

        foreach ($userData as $data) {
            // Create User
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            // Assign Role
            $user->syncRoles($data['role']);

            // 3. Hubungkan ke Kitchens via Tabel Pivot
            // Cari ID Kitchen berdasarkan Kode yang ada di array 'access_kitchens'
            $kitchenKodes = Kitchen::whereIn('kode', $data['access_kitchens'])
            ->pluck('kode')
            ->toArray();
            
            // Hubungkan (sync akan menghapus yang lama dan mengisi yang baru, mencegah duplikat)
            $user->kitchens()->sync($kitchenKodes);
        }
    }
}