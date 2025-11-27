<?php

use App\Http\Controllers\ProfileController;
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ROUTE SUPER ADMIN

Route::middleware(['auth', 'role:superadmin'])
    ->get('/superadmin', function () {
        return 'Halo Super Admin';
    });

// ROUTE ADMIN DAPUR

Route::middleware(['auth', 'role:admindapur'])
    ->get('/admin-dapur', function () {
        return 'Dashboard Admin Dapur';
    });

require __DIR__.'/auth.php';

Route::get('/master/bahan-baku', function () {
    return view('master.bahanbaku');
})->name('master.bahanbaku');

Route::get('/master/nama-menu', function () {
    return view('master.namamenu');
})->name('master.namamenu');

Route::get('/master/dapur', function () {
    return view('master.dapur');
})->name('master.dapur');

Route::get('/setup/user', function () {
    return view('setup.user');
})->name('setup.user');

Route::get('/setup/racik-menu', function () {
    return view('setup.racikmenu');
})->name('setup.racikmenu');

Route::get('/transaksi/pengajuan-menu', function () {
    return view('transaksi.pengajuanmenu');
})->name('transaksi.pengajuanmenu');

Route::get('/laporan', function () {
    return view('laporan.index');
})->name('laporan.index');
