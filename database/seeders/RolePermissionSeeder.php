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

                'master.region.create',
                'master.region.update',
                'master.region.delete',

                'master.supplier.create',
                'master.supplier.update',
                'master.supplier.delete',

                'master.kitchen.create',
                'master.kitchen.update',
                'master.kitchen.delete',

                'recipe.view',

                'transaction.submission.view',
                'transaction.operational-submission.view',

                //TRANSACTION - SUBMISSION APPROVAL
                'transaction.submission-approval.view',
                'transaction.submission-approval.update',
                'transaction.submission-approval.process',
                'transaction.submission-approval.complete',
                'transaction.submission-approval.split',
                'transaction.submission-approval.update-harga',
                'transaction.submission-approval.add-bahan-baku',
                'transaction.submission-approval.delete-detail',
                'transaction.submission-approval.invoice',
                'transaction.submission-approval.parent-invoice',

                'transaction.operational-approval.view',
                'transaction.operational-approval.store',
                'transaction.operational-approval.update',
                'transaction.operational-approval.delete',
                'transaction.operational-approval.show',
                'transaction.operational-approval.update-status',
                'transaction.operational-approval.invoice',
                'transaction.operational-approval.invoice-parent',
                'transaction.operational-approval.update-prices',

                'transaction.sale-kitchen.view',
                'transaction.sale-kitchen.create',
                'transaction.sale-kitchen.delete',
                'transaction.sale-kitchen.update',

                'transaction.sale-partner.view',
                'transaction.sale-partner.create',
                'transaction.sale-partner.delete',
                'transaction.sale-partner.update',

                // REPORT
                'report.sales-kitchen.view',
                'report.sales-kitchen.invoice',
                'report.sales-partner.view',
                'report.sales-partner.invoice',
                'report.purchase-operational.view',
                'report.purchase-operational.invoice',
                'report.profit.view',
                'report.profit.invoice',

                'transaction.sales.view',
                'transaction.purchase.view',
            ]);

        // OPERATOR DAPUR
        Role::where('name', 'operatorDapur')
            ->first()
            ->syncPermissions([
                // MASTER
                'master.supplier.view',

                'master.unit.view',
                'master.unit.create',
                'master.unit.update',

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
                'transaction.submission.delete',
                'transaction.submission.show',

                'transaction.operational-submission.view',
                'transaction.operational-submission.store',
                'transaction.operational-submission.delete',
                'transaction.operational-submission.show',
                'transaction.operational-submission.update',
                'transaction.operational-submission.invoice',
                'transaction.operational-submission.invoice-parent',

                'transaction.sale-kitchen.view',

                'report.purchase-operational.view',
                'report.purchase-operational.invoice',

            ]);

        // MITRA
        Role::where('name', 'mitra')
            ->first()
            ->syncPermissions([
                'master.bahan-baku.view',
                'master.unit.view',
                'master.menu.view',
                'master.kitchen.view',
                'master.region.view',
                'master.operational.view',
                'master.supplier.view',

                'transaction.submission.view',
                'transaction.submission-approval.view',
                'transaction.operational-submission.view',
                'transaction.operational-approval.view',
                'transaction.sale-kitchen.view',
                'transaction.sale-partner.view',
                'transaction.purchase.view',

                'transaction.sales.view',
                'transaction.purchase.view',

                'report.sales-kitchen.view',

                'report.sales-partner.view',
                'report.sales-partner.invoice',

                'report.purchase-operational.view',
                'report.profit.view',

            ]);

        Role::where('name', 'operatorRegion')
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

                'report.sales-kitchen.view',
                'report.sales-partner.view',
                'report.purchase-operational.view',
                'report.profit.view',
            ]);
    }
}
