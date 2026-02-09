<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminApi
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->jabatan === 'Admin') {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'Akses ditolak. Hanya admin.',
        ], 403);
    }
}
