<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SuperAdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan pakai guard web
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {

            // 1. Ambil atau buat role superadmin
            $superAdminRole = Role::firstOrCreate([
                'name' => 'superadmin',
                'guard_name' => 'web',
            ]);

            // 2. Ambil SEMUA permission
            $permissions = Permission::where('guard_name', 'web')->get();

            // 3. Assign semua permission ke role superadmin
            $superAdminRole->syncPermissions($permissions);

            // 4. OPTIONAL: Assign role superadmin ke semua user superadmin
            User::whereHas('roles', function ($q) {
                $q->where('name', 'superadmin');
            })->each(function ($user) use ($superAdminRole) {
                $user->syncRoles([$superAdminRole]);
            });
        });
    }
}
