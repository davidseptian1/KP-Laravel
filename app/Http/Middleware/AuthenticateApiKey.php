<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = trim((string) $request->header('X-API-KEY', ''));

        if ($providedKey === '') {
            return response()->json([
                'status' => false,
                'message' => 'API key tidak ditemukan. Gunakan header X-API-KEY.',
            ], 401);
        }

        $apiKey = ApiKey::where('key_hash', hash('sha256', $providedKey))
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return response()->json([
                'status' => false,
                'message' => 'API key tidak valid atau nonaktif.',
            ], 401);
        }

        if ($apiKey->expires_at && now()->greaterThan($apiKey->expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'API key sudah kedaluwarsa.',
            ], 401);
        }

        $apiKey->last_used_at = now();
        $apiKey->save();

        $request->attributes->set('api_key_id', $apiKey->id);

        return $next($request);
    }
}
