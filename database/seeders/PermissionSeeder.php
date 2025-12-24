<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $permissions = [

            // MASTER
            'master.bahan-baku.view',
            'master.bahan-baku.create',
            'master.bahan-baku.delete',

            'master.unit.view',
            'master.unit.create',
            'master.unit.update',
            'master.unit.delete',

            'master.menu.view',
            'master.menu.create',
            'master.menu.delete',

            'master.kitchen.view',
            'master.kitchen.create',
            'master.kitchen.update',
            'master.kitchen.delete',

            'master.region.view',
            'master.region.create',
            'master.region.update',
            'master.region.delete',

            'master.operational.view',
            'master.operational.create',
            'master.operational.update',
            'master.operational.delete',

            'master.supplier.view',
            'master.supplier.create',
            'master.supplier.update',
            'master.supplier.delete',

            // SETUP
            'setup.user.view',
            'setup.user.create',
            'setup.user.update',
            'setup.user.delete',

            'setup.role.view',
            'setup.role.create',
            'setup.role.update',
            'setup.role.delete',

            // RACIK MENU
            'recipe.view',
            'recipe.create',
            'recipe.update',
            'recipe.delete',

            // TRANSAKSI
            'transaction.submission.view',
            'transaction.submission.create',
            'transaction.submission.delete',

            'transaction.request-materials.view',
            'transaction.sale-kitchen.view',
            'transaction.sale-partner.view',

            'transaction.sales.view',
            'transaction.purchase.view',

            // REPORT
            'report.submission.view',
            'report.purchase.view',
            'report.sales.view',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
