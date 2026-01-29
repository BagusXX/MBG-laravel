<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // ✅ JIKA SUDAH DISETUJUI → LEWAT
        if ($user->status === 'disetujui') {
            return $next($request);
        }

        // ⛔ JIKA WAITING
        if ($user->status === 'menunggu') {
            if (!$request->routeIs('waiting')) {
                return redirect()->route('waiting');
            }
        }

        // ❌ JIKA DITOLAK
        if ($user->status === 'ditolak') {
            Auth::logout();
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda ditolak'
                ]);
        }

        return $next($request);
    }

}
