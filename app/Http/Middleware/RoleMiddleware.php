<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
{
    if (!auth()->check()) {
        abort(403);
    }

    if (auth()->user()->role !== $role) {
        abort(403, 'Anda tidak memiliki akses.');
    }

    return $next($request);
}

}
