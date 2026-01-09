<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;


class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    
        // SUPERADMIN
        Role::where('name', 'superadmin')
            ->first()
            ->syncPermissions(Permission::all());

        // OPERATOR KOPERASI
        Role::where('name', 'operatorkoperasi')
            ->first()
            ->syncPermissions([
                'master.bahan-baku.view',
                'master.unit.view',
                'master.menu.view',
                'master.kitchen.view',
                'master.region.view',
                'master.operational.view',
                'master.supplier.view',

                'recipe.view',

                'transaction.submission.view',
                'transaction.operational-submission.view',
                'transaction.sale-kitchen.view',
                'transaction.sale-partner.view',
                'transaction.purchase.view',
            ]);

        // OPERATOR DAPUR
        Role::where('name', 'operatorDapur')
            ->first()
            ->syncPermissions([
                // MASTER
                'master.supplier.view',
                'master.supplier.create',
                'master.supplier.update',
                'master.supplier.delete',

                'master.unit.view',
                'master.unit.create',
                'master.unit.update',
                'master.unit.delete',

                'master.bahan-baku.view',
                'master.bahan-baku.create',
                'master.bahan-baku.update',
                'master.bahan-baku.delete',

                'master.menu.view',
                'master.menu.create',
                'master.menu.update',
                'master.menu.delete',

                'master.operational.view',
                'master.operational.create',
                'master.operational.update',
                'master.operational.delete',

                // SETUP
                'recipe.view',
                'recipe.create',
                'recipe.update',
                'recipe.delete',

                // TRANSAKSI
                'transaction.submission.view',
                'transaction.submission.store',

                'transaction.operational-submission.view',
                'transaction.operational-submission.store',

                'transaction.sale-kitchen.view',
                'transaction.sale-kitchen.create',
            ]);

        // MITRA
        Role::where('name', 'mitra')
            ->first()
            ->syncPermissions([
                'master.bahan-baku.view',

                'transaction.submission.view',
                'transaction.operational-submission.view',
                'transaction.sale-kitchen.view',
                'transaction.sale-partner.view',
            ]);

        Role::where('name','operatorRegion')
            ->first()
            ->syncPermissions([
                'master.bahan-baku.view',
                'master.unit.view',
                'master.menu.view',
                'master.kitchen.view',
                'master.region.view',
                'master.operational.view',
                'master.supplier.view',
                'recipe.view',
                'transaction.submission.view',
                'transaction.operational-submission.view',
                'transaction.operational-approval.view',
                'transaction.request-materials.view',
                'transaction.sale-kitchen.view',
                'transaction.sale-partner.view',
                'transaction.sales.view',
                'transaction.purchase.view',
                'report.submission.view',
                'report.purchase.view',
                'report.sales.view',
            ]);
    }
}
