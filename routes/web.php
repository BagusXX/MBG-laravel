<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecipeController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/dashboard/master/bahan-baku', function () {
//     return view('master.materials');
// })->middleware(['auth', 'verified'])->name('master.materials');

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
        ->name('setup.racikmenu');

    // Simpan racik menu
    Route::post('dashboard/setup/racik-menu', [RecipeController::class, 'store'])
        ->name('setup.racikmenu.store');
});



Route::get('dashboard/transaksi/pengajuan-menu', function () {
    return view('transaction.submission');
})->name('transaction.submission');

Route::get('dashboard/laporan', function () {
    return view('report.index');
})->name('report.index');

