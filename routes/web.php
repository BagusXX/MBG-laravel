<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

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

Route::get('dashboard/master/bahan-baku', function () {
    return view('master.materials');
})->name('master.materials');

Route::get('dashboard/master/nama-menu', function () {
    return view('master.menu');
})->name('master.menu');

Route::get('dashboard/master/dapur', function () {
    return view('master.kitchen');
})->name('master.kitchen');

Route::get('dashboard/setup/user', function () {
    return view('setup.user');
})->name('setup.user');

Route::get('dashboard/setup/racik-menu', function () {
    return view('setup.createmenu');
})->name('setup.createmenu');

Route::get('dashboard/transaksi/pengajuan-menu', function () {
    return view('transaction.submission');
})->name('transaction.submission');

Route::get('dashboard/laporan', function () {
    return view('report.index');
})->name('report.index');
