<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
