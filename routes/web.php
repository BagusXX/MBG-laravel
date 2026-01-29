<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionApprovalController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OperationalApprovalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\PurchaseBahanBakuController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OperationalController;
use App\Http\Controllers\OperationalSubmissionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportPurchaseOperationalController;
use App\Http\Controllers\SaleMaterialsKitchenController;
use App\Http\Controllers\SaleMaterialsPartnerController;
use App\Http\Controllers\ReportSalesKitchenController;
use App\Http\Controllers\ReportSalesPartnerController;
use App\Http\Controllers\ProfitController;

require __DIR__ . '/auth.php';

//Route::get('/', fn() => redirect()->route('dashboard.master.bahan-baku.index'));
Route::get('/', [HomePageController::class, 'index'])->name('portal.index');
Route::get('/menunggu-persetujuan', function () {
    return view('auth.waiting-approval');
})->middleware('auth')->name('waiting.approval');

Route::middleware(['auth', 'disetujui'])->group(function () {

    /*
    |------------------------------------------------------------------
    | MASTER DATA
    |------------------------------------------------------------------
    */

    Route::prefix('dashboard/master/bahan-baku')
        ->name('dashboard.master.bahan-baku.')
        ->controller(BahanBakuController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.bahan-baku.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.bahan-baku.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.bahan-baku.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.bahan-baku.delete')->name('destroy');
            Route::get('/generate-code/{kitchenId}', 'generateKodeAjax')
                ->middleware('permission:master.bahan-baku.create')
                ->name('generateCode');
        });

    Route::prefix('dashboard/master/satuan')
        ->name('master.unit.')
        ->controller(UnitController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.unit.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.unit.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.unit.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.unit.delete')->name('destroy');
        });

    Route::prefix('dashboard/master/nama-menu')
        ->name('master.menu.')
        ->controller(MenuController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.menu.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.menu.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.menu.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.menu.delete')->name('destroy');
        });

    Route::prefix('dashboard/master/dapur')
        ->name('master.kitchen.')
        ->controller(KitchenController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.kitchen.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.kitchen.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.kitchen.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.kitchen.delete')->name('destroy');
        });

    Route::prefix('dashboard/master/region')
        ->name('master.region.')
        ->controller(RegionController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.region.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.region.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.region.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.region.delete')->name('destroy');
        });

    Route::prefix('dashboard/master/operational')
        ->name('master.operational.')
        ->controller(OperationalController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.operational.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.operational.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:master.operational.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:master.operational.delete')->name('destroy');
        });

    Route::prefix('dashboard/master/supplier')
        ->name('master.supplier.')
        ->controller(SupplierController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:master.supplier.view')->name('index');
            Route::post('/', 'store')->middleware('permission:master.supplier.create')->name('store');
            Route::post('/update/{supplier}', 'update')->middleware('permission:master.supplier.update')->name('update_post');
            Route::get('/{supplier}/edit')->middleware('permission:master.supplier.update')->name('edit');
            Route::put('/{supplier}', 'update')->middleware('permission:master.supplier.update')->name('update');
            Route::delete('/{supplier}', 'destroy')->middleware('permission:master.supplier.delete')->name('destroy');
        });

    /*
    |------------------------------------------------------------------
    | SETUP
    |------------------------------------------------------------------
    */

    Route::prefix('dashboard/setup/user')
        ->name('setup.user.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:setup.user.view')->name('index');
            Route::post('/', 'store')->middleware('permission:setup.user.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:setup.user.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:setup.user.delete')->name('destroy');
            Route::patch('/{id}/approve', 'approve')->middleware('permission:setup.user.approve')->name('approve');
            Route::patch('/{id}/reject', 'reject')->middleware('permission:setup.user.approve')->name('reject');
        });

    Route::prefix('dashboard/setup/role')
        ->name('setup.role.')
        ->controller(RoleController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:setup.role.view')->name('index');
            Route::post('/', 'store')->middleware('permission:setup.role.create')->name('store');
            Route::put('/{id}', 'update')->middleware('permission:setup.role.update')->name('update');
            Route::delete('/{id}', 'destroy')->middleware('permission:setup.role.delete')->name('destroy');
        });

    Route::prefix('dashboard/setup/permission')
        ->name('setup.permission.')
        ->middleware('role:superadmin')
        ->controller(PermissionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    Route::prefix('dashboard/setup/racik-menu')
        ->name('recipe.')
        ->controller(RecipeController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:recipe.view')
                ->name('index');

            Route::post('/', 'store')
                ->middleware('permission:recipe.create')
                ->name('store');

            Route::get('/menus-by-kitchen/{kitchen}', 'getMenusByKitchen')
                ->middleware('permission:recipe.view')
                ->name('menus.by.kitchen');

            Route::get('/bahan-by-kitchen/{kitchen}', 'getBahanByKitchen')
                ->middleware('permission:recipe.view')
                ->name('bahan.by.kitchen');

            Route::get('/bahan/{id}', 'getBahanDetail')
                ->middleware('permission:recipe.view')
                ->name('bahan.detail');

            // DETAIL 1 RESEP
            Route::get('/detail/{menu}/{kitchen}', 'getRecipeDetail')
                ->middleware('permission:recipe.view')
                ->name('detail');

            // UPDATE 1 RESEP
            Route::put('/{menu}/{kitchen}', 'update')
                ->middleware('permission:recipe.update')
                ->name('update');

            // DELETE 1 RESEP
            Route::delete('/{menu}/{kitchen}', 'destroy')
                ->middleware('permission:recipe.delete')
                ->name('destroy');

            Route::post('/duplicate', 'duplicate')
                ->name('duplicate');
        });


    /*
    |------------------------------------------------------------------
    | TRANSAKSI
    |------------------------------------------------------------------
    */

    Route::prefix('dashboard/transaksi/pengajuan-menu')
        ->name('transaction.submission.')
        ->controller(SubmissionController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:transaction.submission.view')
                ->name('index');

            Route::post('/', 'store')
                ->middleware('permission:transaction.submission.store')
                ->name('store');

            Route::get('/{submission}/detail', 'show')
                ->middleware('permission:transaction.submission.show')
                ->name('detail');

            Route::put('/{submission}', 'update')
                ->middleware('permission:transaction.submission.update')
                ->name('update');

            Route::delete('/{submission}', 'destroy')
                ->middleware('permission:transaction.submission.delete')
                ->name('destroy');

            Route::get('/{submission}/data', 'getSubmissionData')
                ->middleware('permission:transaction.submission.view')
                ->name('data');

            // Helpers
            Route::get('/helper/menu-by-kitchen/{kitchenId}', 'getMenuByKitchen')
                ->middleware('permission:transaction.submission.view')
                ->name('menu-by-kitchen');
        });


    // Route Approval Pengajuan Menu
    Route::prefix('dashboard/transaksi/approval-menu')
        ->name('transaction.submission-approval.')
        ->controller(SubmissionApprovalController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:transaction.submission-approval.view')
                ->name('index');

            // [AJAX] Ambil Data Header Submission
            Route::get('/{submission}/data', 'getSubmissionData')
                ->middleware('permission:transaction.submission-approval.view')
                ->name('data');

            // [AJAX] Ambil List Detail Bahan (PENTING: Sebelumnya route ini belum ada)
            Route::get('/{submission}/details', 'getDetails')
                ->middleware('permission:transaction.submission-approval.view')
                ->name('details');

            // [AJAX] Ambil List Bahan Baku untuk Tambah Manual (PENTING: Sebelumnya belum ada)
            Route::get('/helper/bahan-baku/{kitchen}', 'getBahanBakuByKitchen')
                ->middleware('permission:transaction.submission-approval.add-bahan-baku')
                ->name('helper.bahan-baku');

            // Update Harga (Bulk Update)
            Route::patch('/{submission}/update-harga', 'updateHarga')
                ->middleware('permission:transaction.submission-approval.update-harga')
                ->name('update-harga');

            // Tambah Bahan Manual
            // PERBAIKAN: Nama method disesuaikan dengan Controller ('addManualBahan')
            Route::post('/{submission}/add-manual', 'addManualBahan')
                ->middleware('permission:transaction.submission-approval.add-bahan-baku')
                ->name('add-manual');

            // Hapus Detail Item
            Route::delete('/{submission}/detail/{detail}', 'deleteDetail')
                ->middleware('permission:transaction.submission-approval.delete-detail')
                ->name('delete-detail');

            // Update Status (Terima / Tolak / Selesai)
            // PERBAIKAN: Menggunakan satu route ke 'updateStatus'
            // Frontend nanti kirim body: { status: 'selesai' } atau { status: 'ditolak' }
            Route::patch('/{submission}/status', 'updateStatus')
                ->middleware('permission:transaction.submission-approval.process') // Sesuaikan permission
                ->name('update-status');

            // Split ke Supplier
            Route::post('/{submission}/split', 'splitToSupplier')
                ->middleware('permission:transaction.submission-approval.split')
                ->name('split');

            Route::delete('/child/{submission}', 'destroyChild')
                ->middleware('permission:transaction.submission-approval.delete-detail') // Gunakan permission yang relevan
                ->name('destroy-child');

            Route::get('/{submission}/invoice', [SubmissionApprovalController::class, 'printInvoice'])
                ->middleware('permission:transaction.submission-approval.invoice')
                ->name('invoice');

            Route::get('/{submission}/parent-invoice', [SubmissionApprovalController::class, 'printParentInvoice'])
                ->middleware('permission:transaction.submission-approval.parent-invoice')
                ->name('print-parent-invoice');
        });



    Route::prefix('dashboard/transaksi/jual-bahan-baku-dapur')
        ->name('transaction.sale-materials-kitchen.')
        ->controller(SaleMaterialsKitchenController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:transaction.sale-kitchen.view')->name('index');
            Route::post('/', 'store')->middleware('permission:transaction.sale-kitchen.create')->name('store');
            Route::get('/invoice/{kode}', 'printInvoice')->middleware('permission:transaction.sale-kitchen.view')->name('invoice');
            // Route::get('/invoice/{kode}/download', 'downloadInvoice')->middleware('permission:transaction.sale-kitchen.view')->name('invoice.download');
            Route::get('/bahan-by-kitchen/{kitchen}', 'getBahanByKitchen')
                ->middleware('permission:transaction.sale-kitchen.view')
                ->name('bahan-by-kitchen');

            Route::get('/submission/{submission}/details', 'getSubmissionDetails')
                ->middleware('permission:transaction.sale-kitchen.view')
                ->name('submission.details');
        });

    Route::prefix('dashboard/transaksi/jual-bahan-baku-mitra')
        ->name('transaction.sale-materials-partner.')
        ->controller(SaleMaterialsPartnerController::class)
        ->group(function () {
            Route::get('/', 'index')->middleware('permission:transaction.sale-partner.view')->name('index');
            Route::post('/', 'store')->middleware('permission:transaction.sale-partner.create')->name('store');
            Route::get('/invoice/{kode}', 'printInvoice')->middleware('permission:transaction.sale-partner.view')->name('invoice');
            // Route::get('/invoice/{kode}/download', 'downloadInvoice')->middleware('permission:transaction.sale-partner.view')->name('invoice.download');
            Route::get('/bahan-by-kitchen/{kitchen}', 'getBahanByKitchen')
                ->middleware('permission:transaction.sale-partner.view')
                ->name('bahan-by-kitchen');
        });
    Route::prefix('dashboard/transaksi/pengajuan-operasional')
        ->name('transaction.operational-submission.')
        ->controller(OperationalSubmissionController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:transaction.operational-submission.view')
                ->name('index');

            Route::post('/', 'store')
                ->middleware('permission:transaction.operational-submission.store')
                ->name('store');

            Route::put('/{id}', 'update')
                ->middleware('permission:transaction.operational-submission.update')
                ->name('update');

            Route::delete('/{id}', 'destroy')
                ->middleware('permission:transaction.operational-submission.delete')
                ->name('destroy');

            Route::get('/{id}', 'show')
                ->middleware('permission:transaction.operational-submission.view') // Sesuaikan permission jika ada khusus 'show'
                ->name('show');

            Route::get('/{id}/invoice', 'invoice')
                ->middleware('permission:transaction.operational-submission.invoice')
                ->name('invoice');

            // TAMBAHKAN INI: Route Invoice Parent (Rekapitulasi)
            Route::get('/{id}/invoice-parent', 'invoiceParent')
                ->middleware('permission:transaction.operational-submission.invoice-parent')
                ->name('invoice-parent');
        });

    Route::prefix('dashboard/transaksi/daftar-operasional')
        ->name('transaction.operational-approval.')
        ->controller(OperationalApprovalController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:transaction.operational-approval.view')
                ->name('index');

            Route::get('/{id}', 'show')
                ->middleware('permission:transaction.operational-approval.show')
                ->name('show');

            Route::post('/', 'store')
                ->middleware('permission:transaction.operational-approval.store')
                ->name('store');

            Route::patch('/{id}', 'update')
                ->middleware('permission:transaction.operational-approval.update')
                ->name('update');

            Route::delete('/{id}', 'destroy')
                ->middleware('permission:transaction.operational-approval.delete')
                ->name('destroy');

            Route::patch('/{id}/status', 'updateStatus')
                ->middleware('permission:transaction.operational-approval.update-status')
                ->name('update-status');

            Route::get('/{id}/invoice', 'invoice')
                ->middleware('permission:transaction.operational-approval.invoice')
                ->name('invoice');

            Route::delete('/child/{id}', 'destroyChild')
                ->middleware('permission:transaction.operational-approval.delete')
                ->name('destroy-child');
            Route::put('/{id}/selesai', 'selesai')
                ->middleware('permission:transaction.operational-approval.selesai')
                ->name('selesai');
            Route::get('/{id}/invoice-parent', 'invoiceParent')
                ->middleware('permission:transaction.operational-approval.invoice-parent')
                ->name('invoice-parent');
            Route::post('/{id}/update-prices', 'updatePrices')
                ->middleware('permission:transaction.operational-approval.update-prices')
                ->name('update-prices');
            Route::put('/operational-submission/{id}',  'update')
                ->name('operational-submission.update');
        });

    Route::prefix('dashboard/transaksi')
        ->name('transaction.')
        ->controller(SubmissionController::class)
        ->group(function () {
            Route::get('/daftar-pemesanan', 'index')
                ->middleware('permission:transaction.request-materials.view')
                ->name('request-materials');

            Route::get(
                '/penjualan-bahan-baku',
                fn() => view('transaction.sales-materials')
            )
                ->middleware('permission:transaction.sales.view')
                ->name('sales-materials');

            Route::get(
                '/pembelian-bahan-baku',
                fn() =>
                view('transaction.purchase-materials')
            )->name('purchase-materials');
        });


    Route::prefix('dashboard/transaksi/pembelian-bahan-baku')
        ->name('transaction.purchase-materials.')
        ->controller(PurchaseBahanBakuController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::get('/{id}/invoice', 'printInvoice')->name('invoice');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });





    /*
    |------------------------------------------------------------------
    | LAPORAN
    |------------------------------------------------------------------
    */

    Route::prefix('dashboard/laporan')
        ->name('report.')
        ->group(function () {
            Route::get('/penjualan-dapur', [ReportSalesKitchenController::class, 'index'])
                ->middleware('permission:report.sales-kitchen.view')
                ->name('sales-kitchen');
            Route::get('/penjualan-dapur/invoice', [ReportSalesKitchenController::class, 'invoice'])
                ->middleware('permission:report.sales-kitchen.invoice')
                ->name('sales-kitchen.invoice');

            Route::get('/pembelian-operational', [ReportPurchaseOperationalController::class, 'index'])
                ->middleware('permission:report.purchase-operational.view')
                ->name('purchase-operational');

            Route::get('/pembelian-operational/invoice', [ReportPurchaseOperationalController::class, 'invoice'])
                ->middleware('permission:report.purchase-operational.invoice')
                ->name('purchase-operational.invoice');

            Route::get('/penjualan-mitra', [ReportSalesPartnerController::class, 'index'])
                ->middleware('permission:report.sales-partner.view')
                ->name('sales-partner');

            Route::get('/penjualan-mitra/invoice', [ReportSalesPartnerController::class, 'invoice'])
                ->middleware('permission:report.sales-partner.invoice')
                ->name('sales-partner.invoice');

            Route::get('/selisih', [ProfitController::class, 'index'])
                ->middleware('permission:report.profit.view')
                ->name('profit');

            Route::get('/selisih/invoice', [ProfitController::class, 'invoice'])
                ->middleware('permission:report.profit.invoice')
                ->name('profit.invoice');
        });

    Route::prefix('dashboard/profile')
        ->name('profile.')
        ->controller(ProfileController::class)
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::patch('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
            Route::patch('/password', 'updatePassword')->name('password.update');
        });

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard.master.bahan-baku.index');
    })->middleware('auth')->name('dashboard');
});
