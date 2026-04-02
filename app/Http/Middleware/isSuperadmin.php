<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class isSuperadmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower(Auth::user()->jabatan ?? '');
        if ($role === 'superadmin') {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Anda Bukan Superadmin');
    }
}