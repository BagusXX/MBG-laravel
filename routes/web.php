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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// PROFILE
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// SUPERADMIN ONLY – boleh tambah admin
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/admin/create', [AdminController::class, 'create'])->name('admin.create');
    Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store');
});

// ADMIN ONLY
Route::middleware(['auth', 'role:admin'])
    ->get('/admin', function () {
        return 'Dashboard Admin';
    });

require __DIR__.'/auth.php';

//BAHAN BAKU
Route::middleware(['auth'])->group(function () {
    // Tampilkan daftar bahan baku
    Route::get('dashboard/master/bahan-baku', [BahanBakuController::class, 'index'])
        ->name('master.materials');

    // Simpan bahan baku baru
    Route::post('dashboard/master/bahan-baku', [BahanBakuController::class, 'store'])
        ->name('master.materials.store');

    // Hapus bahan baku
    Route::delete('dashboard/master/bahan-baku/{id}', [BahanBakuController::class, 'destroy'])
        ->name('master.materials.destroy');
    
    Route::get('dashboard/master/bahan-baku/generate-code/{kitchenId}', 
        [BahanBakuController::class, 'generateKodeAjax']
    )->name('master.materials.generateCode');
});

// UNIT <--> SATUAN
Route::middleware(['auth'])->group(function () {
    Route::get('dashboard/master/satuan', [UnitController::class, 'index'])
    ->name('master.unit');
    
    Route::post('dashboard/master/satuan', [UnitController::class, 'store'])
    ->name('master.unit.store');

    Route::put('dashboard/master/satuan/{id}', [UnitController::class, 'update'])
    ->name('master.unit.update');
    
    Route::delete('dashboard/master/satuan/{id}', [UnitController::class, 'destroy'])
    ->name('master.unit.destroy');
});

//MENU
Route::middleware(['auth'])->group(function () {
    // Tampilkan daftar menu
    Route::get('dashboard/master/nama-menu', [MenuController::class, 'index'])
        ->name('master.menu');

    // Simpan menu baru
    Route::post('dashboard/master/nama-menu', [MenuController::class, 'store'])
        ->name('master.menu.store');

    // Hapus menu
    Route::delete('dashboard/master/nama-menu/{id}', [MenuController::class, 'destroy'])
        ->name('master.menu.destroy');
});

// DAPUR – DATA KITCHEN
Route::middleware(['auth'])->group(function () {

    // Halaman daftar dapur
    Route::get('dashboard/master/dapur', [KitchenController::class, 'index'])
        ->name('master.kitchen');

    // Simpan dapur baru
    Route::post('dashboard/master/dapur', [KitchenController::class, 'store'])
        ->name('master.kitchen.store');

    // Hapus dapur
    Route::delete('dashboard/master/dapur/{id}', [KitchenController::class, 'destroy'])
        ->name('master.kitchen.destroy');

    // Update dapur
    Route::put('dashboard/master/dapur/{id}', [KitchenController::class, 'update'])
        ->name('master.kitchen.update');
});

// USER SETUP
Route::middleware(['auth'])->group(function () {

    // Tampilkan daftar user
    Route::get('dashboard/setup/user', [UserController::class, 'index'])
        ->name('setup.user');

    // Simpan user baru
    Route::post('dashboard/setup/user', [UserController::class, 'store'])
        ->name('setup.user.store');

    // Hapus user
    Route::delete('dashboard/setup/user/{id}', [UserController::class, 'destroy'])
        ->name('setup.user.destroy');

    // Edit user
    Route::put('dashboard/setup/user/{id}', [UserController::class, 'update'])
        ->name('setup.user.update');
});

// RUTE RACIK MENU
Route::middleware(['auth'])->group(function () {

    // Tampilkan daftar racik menu
    Route::get('dashboard/setup/racik-menu', [RecipeController::class, 'index'])
        ->name('setup.createmenu');

    // Simpan racik menu
    Route::post('dashboard/setup/racik-menu', [RecipeController::class, 'store'])
        ->name('setup.createmenu.store');
});

Route::get('dashboard/master/supplier', function() {
    return view('master.supplier');
})->name('master.supplier');

Route::get('/dashboard/transaksi/pengajuan-menu', [SubmissionController::class, 'index'])
    ->name('transaction.submission');

Route::get('dashboard/transaksi/pembelian-bahan-baku', function () {
    return view('transaction.purchase-materials');
})->name('transaction.purchase-materials');

Route::get('dashboard/transaksi/penjualan-bahan-baku', function () {
    return view('transaction.sales-materials');
})->name('transaction.sales-materials');

Route::get('dashboard/laporan/pengajuan-menu', function () {
    return view('report.submission');
})->name('report.submission');

Route::get('dashboard/laporan/pembelian-bahan-baku', function () {
    return view('report.purchase-materials');
})->name('report.purchase-materials');

Route::get('dashboard/laporan/penjualan-bahan-baku', function () {
    return view('report.sales-materials');
})->name('report.sales-materials');

Route::get('/dashboard/master/supplier', function () {
    return view('master.supplier');
})->name('master.supplier');

Route::prefix('dashboard/setup')->middleware('auth')->group(function () {
    Route::get('/racik-menu', [App\Http\Controllers\RecipeController::class, 'index'])
        ->name('setup.createmenu');

    Route::post('/racik-menu/store', [App\Http\Controllers\RecipeController::class, 'store'])
        ->name('recipe.store');
});

Route::get('/dashboard/master/supplier', [\App\Http\Controllers\SupplierController::class, 'index'])
    ->name('master.supplier');

Route::prefix('dashboard/master')->name('master.')->group(function () {
    Route::get('supplier', [SupplierController::class, 'index'])->name('supplier');
    Route::post('supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::get('supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::put('supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
});

Route::middleware(['auth'])->group(function () {

    // HALAMAN PENGAJUAN MENU
    Route::get(
        '/dashboard/transaksi/pengajuan-menu',
        [SubmissionController::class, 'index']
    )->name('transaction.submission');

    // SIMPAN PENGAJUAN MENU
    Route::post(
        '/dashboard/transaksi/pengajuan-menu',
        [SubmissionController::class, 'store']
    )->name('submissions.store');

    // HAPUS PENGAJUAN MENU (opsional, tapi view kamu sudah pakai)
    Route::delete(
        '/dashboard/transaksi/pengajuan-menu/{id}',
        [SubmissionController::class, 'destroy']
    )->name('submissions.destroy');

    Route::get(
    '/dashboard/transaksi/pengajuan-menu/menu-by-kitchen/{kitchen}',
    [SubmissionController::class, 'getMenuByKitchen']
    )->name('submissions.menu-by-kitchen');

});