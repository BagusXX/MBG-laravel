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
            'master.bahan-baku.update',
            'master.bahan-baku.create',
            'master.bahan-baku.delete',

            'master.unit.view',
            'master.unit.create',
            'master.unit.update',
            'master.unit.delete',

            'master.menu.view',
            'master.menu.update',
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
            'setup.user.approve',

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
            'transaction.submission.store',
            'transaction.submission.delete',
            'transaction.submission.show',
            'transaction.submission.update',

            // TRANSACTION - SUBMISSION APPROVAL
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

            'transaction.operational-submission.view',
            'transaction.operational-submission.store',
            'transaction.operational-submission.delete',
            'transaction.operational-submission.show',
            'transaction.operational-submission.update',
            'transaction.operational-submission.invoice',
            'transaction.operational-submission.invoice-parent',

            'transaction.operational-approval.view',
            'transaction.operational-approval.store',
            'transaction.operational-approval.update',
            'transaction.operational-approval.delete',
            'transaction.operational-approval.show',
            'transaction.operational-approval.update-status',
            'transaction.operational-approval.invoice',
            'transaction.operational-approval.invoice-parent',
            'transaction.operational-approval.update-prices',

            'transaction.request-materials.view',
            'transaction.sale-kitchen.view',
            'transaction.sale-kitchen.create',
            'transaction.sale-kitchen.delete',
            'transaction.sale-kitchen.update',
            
            'transaction.sale-partner.view',
            'transaction.sale-partner.create',
            'transaction.sale-partner.delete',
            'transaction.sale-partner.update',


            'transaction.sales.view',
            'transaction.purchase.view',

            // REPORT
            'report.sales-kitchen.view',
            'report.sales-kitchen.invoice',
            'report.sales-partner.view',
            'report.sales-partner.invoice',
            'report.purchase-operational.view',
            'report.purchase-operational.invoice',
            'report.profit.view',
            'report.profit.invoice',
            'report.sales-profit.view',
            'report.sales-profit.invoice',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
