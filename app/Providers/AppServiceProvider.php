<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. Tambahkan custom Gate ini
        Gate::define('report.sales-summary.legacy', function ($user) {
            // Pastikan user secara default memang punya peran/permission untuk melihat laporan
            if (!$user->hasPermissionTo('report.sales-summary.view')) {
                return false;
            }
            // Cek apakah dapur-dapur milik user pernah melakukan transaksi (submission) sebelum 1 April 2026
            return $user->kitchens()->whereHas('submissions', function ($query) {
                $query->whereDate('tanggal', '<', '2026-04-01');
            })->exists();
        });
    }
}
