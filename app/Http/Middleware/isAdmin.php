<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower(Auth::user()->jabatan ?? '');
        if (in_array($role, ['admin', 'superadmin'], true)) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Anda Bukan Admin');
        
    }
}
