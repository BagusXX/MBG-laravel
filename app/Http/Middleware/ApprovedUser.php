<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApprovedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // safety
        if (!$user) {
            return redirect()->route('login');
        }

        // ✅ SUDAH DISETUJUI → LANJUT KE DASHBOARD
        if ($user->status === 'disetujui') {

            // kalau masih di halaman waiting tapi sudah approved
            if ($request->routeIs('waiting.approval')) {
                return redirect()->route('dashboard.master.bahan-baku.index');
            }

            return $next($request);
        }

        // ⏳ MASIH WAITING
        if ($user->status === 'menunggu') {

            // kalau akses dashboard → paksa ke waiting
            if (!$request->routeIs('waiting.approval')) {
                return redirect()->route('waiting.approval');
            }

            return $next($request);
        }

        // ❌ DITOLAK
        if ($user->status === 'ditolak') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun Anda ditolak oleh Superadmin.'
                ]);
        }

        return $next($request);
    }
}
