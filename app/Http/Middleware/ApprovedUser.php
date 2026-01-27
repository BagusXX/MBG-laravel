<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->status !== 'disetujui') {

            // Hindari infinite redirect
            if ($request->routeIs('waiting.approval')) {
                return $next($request);
            }

            return redirect()->route('waiting.approval');
        }


        return $next($request);
    }
}
