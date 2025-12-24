<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command yang tersedia
     */
    protected $commands = [
        // contoh:
        \App\Console\Commands\SyncPermissions::class,
    ];

    /**
     * Jadwal task (cron)
     */
    protected function schedule($schedule): void
    {
        // $schedule->command('permission:sync')->daily();
    }

    /**
     * Load command
     */
    protected function commands(): void
    {
        //
    }
}
