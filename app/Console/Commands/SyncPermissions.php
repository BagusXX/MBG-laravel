<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{
    protected $signature = 'permission:sync';
    protected $description = 'Sync permissions from route middleware';

    public function handle(): int
    {
        $permissions = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->middleware() as $middleware) {
                if (str_starts_with($middleware, 'permission:')) {
                    $permissions[] = str_replace('permission:', '', $middleware);
                }
            }
        }

        foreach (array_unique($permissions) as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->info('Permission berhasil disinkronkan');
        return self::SUCCESS;
    }
}
