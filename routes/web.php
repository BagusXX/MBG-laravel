<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SubmissionController;

require __DIR__ . '/auth.php';

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'role:superAdmin'])->group(function () {

    Route::prefix('dashboard/master/bahan-baku')
        ->name('dashboard.master.bahan-baku.')
        ->controller(BahanBakuController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('/generate-code/{kitchenId}', 'generateKodeAjax')->name('generateCode');
        });

    Route::prefix('dashboard/master/satuan')
        ->name('master.unit.')
        ->controller(UnitController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    Route::prefix('dashboard/master/nama-menu')
        ->name('master.menu.')
        ->controller(MenuController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

    Route::prefix('dashboard/master/dapur')
        ->name('master.kitchen.')
        ->controller(KitchenController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::put('/{id}', 'update')->name('update');
        });

    Route::prefix('dashboard/setup/user')
        ->name('setup.user.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::put('/{id}', 'update')->name('update');
        });

    Route::prefix('dashboard/setup/racik-menu')
        ->name('recipe.')
        ->controller(RecipeController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
        });

    

    
    Route::prefix('dashboard/transaksi/pengajuan-menu')
        ->name('transaction.submission.')
        ->controller(SubmissionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('/menu-by-kitchen/{kitchen}', 'getMenuByKitchen')->name('menu-by-kitchen');
        });

    Route::prefix('dashboard/master/supplier')
        ->name('master.supplier.')
        ->controller(SupplierController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{supplier}/edit', 'edit')->name('edit');
            Route::put('/{supplier}', 'update')->name('update');
            Route::delete('/{supplier}', 'destroy')->name('destroy');
        });

    // Route::prefix('dashboard/master')
    //     ->name('dashboard.master.')
    //     ->group(function () {
    //         Route::get('/supplier', function () {
    //             return view('master.supplier');
    //         })->name('supplier');
    //     });

    Route::prefix('dashboard/transaksi')
        ->name('transaction.')
        ->group(function () {
            Route::get('/daftar-pemesanan', function () {
                return view('transaction.request-materials');
            })->name('request-materials');
            Route::get('/penjualan-bahan-baku', function () {
                return view('transaction.sales-materials');
            })->name('sales-materials');
            Route::get('/pembelian-bahan-baku', function () {
                return view('transaction.purchase-materials');
            })->name('purchase-materials');
        });

    Route::prefix('dashboard/laporan')
        ->name('report.')
        ->group(function () {
            Route::get('/pengajuan-menu', function () {
                return view('report.submission');
            })->name('submission');
            Route::get('/pembelian-bahan-baku', function () {
                return view('report.purchase-materials');
            })->name('purchase-materials');
            Route::get('/penjualan-bahan-baku', function () {
                return view('report.sales-materials');
            })->name('sales-materials');
        });

});
